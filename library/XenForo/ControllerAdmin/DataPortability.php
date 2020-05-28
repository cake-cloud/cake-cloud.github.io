<?php

class XenForo_ControllerAdmin_DataPortability extends XenForo_ControllerAdmin_Abstract
{
	protected function _preDispatch($action)
	{
		$this->assertAdminPermission('user');
	}

	public function actionIndex()
	{
		return $this->responseReroute(__CLASS__, 'export');
	}

	public function actionExport()
	{
		$userModel = $this->_getUserModel();

		if ($this->isConfirmedPost())
		{
			$username = $this->_input->filterSingle('username', XenForo_Input::STRING);
			if (!$username)
			{
				return $this->responseError(new XenForo_Phrase('please_enter_valid_name'));
			}

			$user = $userModel->getUserByName($username, array('join' => XenForo_Model_User::FETCH_USER_FULL));
			if (!$user)
			{
				return $this->responseError(new XenForo_Phrase('requested_user_not_found'), 404);
			}

			$this->_routeMatch->setResponseType('xml');

			$viewParams = array(
				'xml' => $userModel->getUserExportXml($user)
			);
			return $this->responseView('XenForo_ViewAdmin_DataPortability_Export', '', $viewParams);
		}
		else
		{
			$user = null;

			$userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
			if ($userId)
			{
				$user = $userModel->getUserById($userId);
			}

			$viewParams = array(
				'user' => $user
			);
			return $this->responseView('XenForo_ViewAdmin_DataPortability_Export', 'data_portability_export', $viewParams);
		}
	}

	public function actionImport()
	{
		if ($this->isConfirmedPost())
		{
			$upload = XenForo_Upload::getUploadedFile('upload');
			if (!$upload)
			{
				return $this->responseError(new XenForo_Phrase('please_provide_valid_xml_file'));
			}

			$document = $this->getHelper('Xml')->getXmlFromFile($upload);
			$this->_getUserModel()->importUserXml($document);

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildAdminLink(
					'users/list', null, array(
						'order' => 'register_date', 'direction' => 'desc'
					)
				)
			);
		}
		else
		{
			return $this->responseView('XenForo_ViewAdmin_DataPortability_Import', 'data_portability_import');
		}
	}

	/**
	 * @return XenForo_Model_User
	 */
	protected function _getUserModel()
	{
		return $this->getModelFromCache('XenForo_Model_User');
	}
}