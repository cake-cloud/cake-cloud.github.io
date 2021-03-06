<?php

class XenForo_ControllerPublic_Misc extends XenForo_ControllerPublic_Abstract
{
	/**
	 * Displays a form to change the visitor's style, or changes it if a style_id is present.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionStyle()
	{
		$visitor = XenForo_Visitor::getInstance();

		if ($this->_input->inRequest('style_id')
			&& $this->_checkCsrfFromToken($this->_input->filterSingle('_xfToken', XenForo_Input::STRING), false)
		)
		{
			$styleId = $this->_input->filterSingle('style_id', XenForo_Input::UINT);

			if ($styleId)
			{
				$styles = (XenForo_Application::isRegistered('styles')
					? XenForo_Application::get('styles')
					: XenForo_Model::create('XenForo_Model_Style')->getAllStyles()
				);
				if (!isset($styles[$styleId])
					|| (!$styles[$styleId]['user_selectable'] && !$visitor['is_admin'])
				)
				{
					$styleId = 0;
				}
			}

			if ($visitor['user_id'])
			{
				$dw = XenForo_DataWriter::create('XenForo_DataWriter_User');
				$dw->setExistingData($visitor['user_id']);
				$dw->set('style_id', $styleId);
				$dw->save();

				XenForo_Helper_Cookie::deleteCookie('style_id');
			}
			else
			{
				XenForo_Helper_Cookie::setCookie('style_id', $styleId, 86400 * 365);
			}

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$this->getDynamicRedirect(false, false)
			);
		}
		else
		{
			$styles = $this->getModelFromCache('XenForo_Model_Style')->getAllStylesAsFlattenedTree();

			$styleId = $this->_input->filterSingle('style_id', XenForo_Input::UINT);
			if ($styleId && !empty($styles[$styleId]['user_selectable']))
			{
				$style = $styles[$styleId];
			}
			else
			{
				$style = false;
			}

			$viewParams = array(
				'styles' => $styles,
				'targetStyle' => $style,
				'redirect' => $this->getDynamicRedirect(false, false)
			);
			return $this->responseView('XenForo_ViewPublic_Misc_Style', 'style_chooser', $viewParams);
		}
	}

	/**
	 * Displays a form to change the visitor's language, or changes it if a language_id is present.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionLanguage()
	{
		$visitor = XenForo_Visitor::getInstance();

		if ($this->_input->inRequest('language_id'))
		{
			$this->_checkCsrfFromToken($this->_input->filterSingle('_xfToken', XenForo_Input::STRING));

			$languageId = $this->_input->filterSingle('language_id', XenForo_Input::UINT);

			if ($languageId)
			{
				$languages = (XenForo_Application::isRegistered('languages')
					? XenForo_Application::get('languages')
					: XenForo_Model::create('XenForo_Model_Language')->getAllLanguagesForCache()
				);
				if (!isset($languages[$languageId]))
				{
					$languageId = 0;
				}
			}

			if ($visitor['user_id'])
			{
				$dw = XenForo_DataWriter::create('XenForo_DataWriter_User');
				$dw->setExistingData($visitor['user_id']);
				$dw->set('language_id', $languageId);
				$dw->save();

				XenForo_Helper_Cookie::deleteCookie('language_id');
			}
			else
			{
				XenForo_Helper_Cookie::setCookie('language_id', $languageId, 86400 * 365);
			}

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$this->getDynamicRedirect(false, false)
			);
		}
		else
		{
			$languages = (XenForo_Application::isRegistered('languages')
				? XenForo_Application::get('languages')
				: XenForo_Model::create('XenForo_Model_Language')->getAllLanguagesForCache()
			);

			$viewParams = array(
				'languages' => $this->getModelFromCache('XenForo_Model_Language')->getAllLanguages(),
				'redirect' => $this->getDynamicRedirect(false, false)
			);
			return $this->responseView('XenForo_ViewPublic_Misc_Language', 'language_chooser', $viewParams);
		}
	}

	public function actionContact()
	{
		$options = XenForo_Application::get('options');

		if ($options->contactUrl['type'] == 'custom')
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
				$options->contactUrl['custom']
			);
		}
		else if (!$options->contactUrl['type'])
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
				XenForo_Link::buildPublicLink('index')
			);
		}

		if ($this->_request->isPost())
		{
			if (!XenForo_Captcha_Abstract::validateDefault($this->_input))
			{
				return $this->responseCaptchaFailed();
			}

			$user = XenForo_Visitor::getInstance()->toArray();

			if (!$user['user_id'])
			{
				$user['email'] = $this->_input->filterSingle('email', XenForo_Input::STRING);

				if (!XenForo_Helper_Email::isEmailValid($user['email']))
				{
					return $this->responseError(new XenForo_Phrase('please_enter_valid_email'));
				}
			}

			$input = $this->_input->filter(array(
				'subject' => XenForo_Input::STRING,
				'message' => XenForo_Input::STRING
			));

			if (!$user['username'] || !$input['subject'] || !$input['message'])
			{
				return $this->responseError(new XenForo_Phrase('please_complete_required_fields'));
			}

			$this->assertNotFlooding('contact');

			$ip = $this->_request->getClientIp(false);

			$mailParams = array(
				'user' => $user,
				'subject' => $input['subject'],
				'message' => $input['message'],
				'ip' => $ip
			);
			$mail = XenForo_Mail::create('contact', $mailParams, 0);

			$headers = array('X-Contact-IP' => $ip);

			if ($options->contactEmailSenderHeader)
			{
				$headers['Sender'] = $options->contactEmailAddress;
				$fromEmail = $user['email'];
			}
			else
			{
				$fromEmail = '';
			}

			$toEmail = $options->contactEmailAddress ? $options->contactEmailAddress : $options->defaultEmailAddress;

			$mailObj = $mail->getPreparedMailHandler(
				$toEmail, '', $headers,
				$fromEmail, $user['username']
			);
			if ($user['email'] && !$options->contactEmailSenderHeader)
			{
				$mailObj->setReplyTo($user['email'], $user['username']);
			}
			$mail->sendMail($mailObj);

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$this->getDynamicRedirect(),
				new XenForo_Phrase('your_message_has_been_sent')
			);
		}
		else
		{
			$viewParams = array(
				'redirect' => $this->getDynamicRedirect(),
				'isOverlay' => $this->_noRedirect() ? true : false,

				'captcha' => XenForo_Captcha_Abstract::createDefault()
			);

			return $this->responseView('XenForo_ViewPublic_Misc_Contact', 'contact', $viewParams);
		}
	}

	public function actionResetPermissions()
	{
		$session = XenForo_Application::get('session');
		$visitor = XenForo_Visitor::getInstance();

		if (!$session->get('permissionTest') || !$visitor['is_admin'])
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$this->getDynamicRedirect()
			);
		}

		if ($this->_request->isPost())
		{
			// wipe out any caches done while testing permissions
			$session->changeUserId(XenForo_Visitor::getUserId());

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$this->getDynamicRedirect()
			);
		}
		else
		{
			return $this->responseView('XenForo_ViewPublic_Misc_ResetPermissions', 'reset_permissions');
		}
	}

	/**
	 * Provides data to build the site jump menu (forum jump etc.)
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionQuickNavigationMenu()
	{
		$route = $this->_input->filterSingle('route', XenForo_Input::STRING);

		/* @var $nodeModel XenForo_Model_Node */
		$nodeModel = $this->getModelFromCache('XenForo_Model_Node');

		$nodes = $nodeModel->getViewableNodeList(null, true);
		$nodeTypes = $nodeModel->getAllNodeTypes();

		$quickNavMenuNodeTypes = XenForo_Application::get('options')->quickNavMenuNodeTypes;

		if (!isset($nodeTypes['_all']) && !in_array('_all', $quickNavMenuNodeTypes))
		{
			$nodes = $nodeModel->filterNodeTypesInTree($nodes, $quickNavMenuNodeTypes);
		}

		$nodes = $nodeModel->filterOrphanNodes($nodes);

		$selected = preg_replace('/[^a-z0-9_-]/i', '', $this->_input->filterSingle('selected', XenForo_Input::STRING));

		$options = XenForo_Application::get('options');

		$viewParams = array(
			'route' => $route,
			'nodes' => $nodes,
			'nodeTypes' => $nodeTypes,
			'selected' => $selected,

			'homeLink' => ($options->homePageUrl ? $options->homePageUrl : false),

			'canViewMemberList' => $this->getModelFromCache('XenForo_Model_User')->canViewMemberList(),
		);

		return $this->responseView('XenForo_ViewPublic_Misc_QuickNavigationMenu', 'quick_navigation_menu', $viewParams);
	}

	/**
	 * Returns the lightbox template
	 *
	 * @return XenForo_ControllerResponse_View
	 */
	public function actionLightbox()
	{
		return $this->responseView('XenForo_ViewPublic_Misc_Lightbox', 'lightbox');
	}

	/**
	 * Returns a new CAPTCHA
	 *
	 * @return XenForo_ControllerResponse_View
	 */
	public function actionCaptcha()
	{
		$viewParams = array('captcha' => XenForo_Captcha_Abstract::createDefault(true));

		return $this->responseView('XenForo_ViewPublic_Misc_Captcha', 'captcha', $viewParams);
	}

	/**
	 * Forwards a request for IP address information to the chosen provider site.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionIpInfo()
	{
		$ip = $this->_input->filterSingle('ip', XenForo_Input::STRING);
		if (!Zend_Validate::is($ip, 'Ip'))
		{
			return $this->responseError(new XenForo_Phrase('specified_ip_invalid'));
		}

		if (strpos($ip, ':') === false) // IPv4
		{
			$url = XenForo_Application::get('options')->ipInfoUrl;
		}
		else // IPv6
		{
			$url = XenForo_Application::get('options')->ipv6InfoUrl;
		}
		if (strpos($url, '{ip}') === false)
		{
			$url = 'http://whatismyipaddress.com/ip/{ip}/';
		}

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT,
			str_replace('{ip}', urlencode($ip), $url)
		);
	}

	/**
	 * Forwards a request for location information to the chosen provider site.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionLocationInfo()
	{
		$url = XenForo_Application::get('options')->geoLocationUrl;
		if (strpos($url, '{location}') === false)
		{
			$url = 'http://maps.google.com/maps?q={location}';
		}

		$location = $this->_input->filterSingle('location', XenForo_Input::STRING);
		if ($location == '')
		{
			return $this->responseError(new XenForo_Phrase('specified_location_invalid'));
		}

		if (strpos($url, 'maps.google.') !== false)
		{
			switch (strtolower($location))
			{
				case 'the moon':
				case 'moon':
				{
					$url = 'http://maps.google.com/moon/';
					break;
				}

				case 'mars':
				{
					$url = 'http://maps.google.com/mars/';
					break;
				}
			}
		}

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT,
			str_replace('{location}', urlencode($location), $url)
		);
	}

	public function actionTagAutoComplete()
	{
		if (!XenForo_Application::getOptions()->enableTagging)
		{
			return $this->responseNoPermission();
		}

		/** @var XenForo_Model_Tag $tagModel */
		$tagModel = $this->getModelFromCache('XenForo_Model_Tag');

		$q = $this->_input->filterSingle('q', XenForo_Input::STRING);
		$q = $tagModel->normalizeTag($q);

		if (strlen($q) >= 2)
		{
			$tags = $tagModel->autoCompleteTag($q);
			$tags = XenForo_Application::arrayColumn($tags, 'tag', 'tag');
		}
		else
		{
			$tags = array();
		}

		$view = $this->responseView();
		$view->jsonParams = array(
			'results' => $tags
		);
		return $view;
	}

	/**
	 * Displays a conversation message preview viewable from the Admin CP
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionUserMessagePreview()
	{
		$this->_assertPostOnly();

		$visitor = XenForo_Visitor::getInstance();
		if (!$visitor->is_admin)
		{
			return $this->responseNoPermission();
		}

		$conversation = $this->_input->filter(array(
			'from_user' => XenForo_Input::STRING,

			'message_title' => XenForo_Input::STRING,
			'message_body' => XenForo_Input::STRING,

			'open_invite' => XenForo_Input::BOOLEAN,
			'conversation_locked' => XenForo_Input::BOOLEAN,
			'conversation_leave' => XenForo_Input::BOOLEAN
		));

		$replacements = array(
			'{name}' => $visitor['username'],
			'{id}' => $visitor['user_id']
		);
		$conversation['message_body'] = strtr($conversation['message_body'], $replacements);
		$conversation['message_body'] = XenForo_Helper_String::autoLinkBbCode($conversation['message_body']);

		$viewParams = array(
			'conversation' => $conversation
		);
		return $this->responseView('XenForo_ViewPublic_Misc_MessagePreview', 'user_message_preview', $viewParams);
	}

	public function actionAcceptPrivacyPolicy()
	{
		$visitor = XenForo_Visitor::getInstance();
		$lastUpdate = XenForo_Application::getOptions()->privacyPolicyLastUpdate;

		if (!$visitor->user_id || !$lastUpdate)
		{
			return $this->responseNoPermission();
		}

		if ($visitor->privacy_policy_accepted > $lastUpdate)
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$this->getDynamicRedirect(
					XenForo_Link::buildPublicLink('misc/accept-privacy-policy')
				)
			);
		}

		if ($this->isConfirmedPost())
		{
			if (!$this->_input->filterSingle('accept', XenForo_Input::BOOLEAN))
			{
				return $this->responseError(new XenForo_Phrase('please_read_and_accept_our_privacy_policy_before_continuing'));
			}

			$dw = XenForo_DataWriter::create('XenForo_DataWriter_User');
			$dw->setExistingData($visitor->user_id, true);
			$dw->set('privacy_policy_accepted', time());
			$dw->save();

			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, $this->getDynamicRedirect());
		}
		else
		{
			$privacyPolicyOption = XenForo_Application::getOptions()->privacyPolicyUrl;

			$viewParams = array();

			if ($privacyPolicyOption['type'] == 'default')
			{
				$viewParams['displayPage'] = true;
			}

			return $this->responseView('XenForo_Misc_AcceptPrivacyPolicy', 'accept_privacy_policy', $viewParams);
		}
	}

	public function actionAcceptTerms()
	{
		$visitor = XenForo_Visitor::getInstance();
		$lastUpdate = XenForo_Application::getOptions()->termsLastUpdate;

		if (!$visitor->user_id || !$lastUpdate)
		{
			return $this->responseNoPermission();
		}

		if ($visitor->terms_accepted > $lastUpdate)
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				$this->getDynamicRedirectIfNot(
					XenForo_Link::buildPublicLink('misc/accept-terms')
				)
			);
		}

		if ($this->isConfirmedPost())
		{
			if (!$this->_input->filterSingle('accept', XenForo_Input::BOOLEAN))
			{
				return $this->responseError(new XenForo_Phrase('please_read_and_accept_our_terms_and_rules_before_continuing'));
			}

			$dw = XenForo_DataWriter::create('XenForo_DataWriter_User');
			$dw->setExistingData($visitor->user_id, true);
			$dw->set('terms_accepted', time());
			$dw->save();

			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, $this->getDynamicRedirect());
		}
		else
		{
			$termsOption = XenForo_Application::getOptions()->tosUrl;

			$viewParams = array();

			if ($termsOption['type'] == 'default')
			{
				$viewParams['displayPage'] = true;
			}

			return $this->responseView('XenForo_Misc_AcceptTerms', 'accept_terms', $viewParams);
		}
	}

	/**
	 * @see XenForo_ControllerPublic_Abstract::_assertViewingPermissions()
	 */
	protected function _assertViewingPermissions($action)
	{
		if (strtolower($action) != 'resetpermissions')
		{
			parent::_assertViewingPermissions($action);
		}
	}

	protected function _assertTfaRequirement($action) {}

	protected function _assertPolicyAcceptance($action) {}

	public static function getSessionActivityDetailsForList(array $activities)
	{
		$return = array();
		foreach ($activities AS $key => $activity)
		{
			if (strtolower($activity['controller_action']) == 'contact')
			{
				$return[$key] = new XenForo_Phrase('contacting_staff');
			}
			else
			{
				$return[$key] = false;
			}
		}

		return $return;
	}
}