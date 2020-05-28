<?php

class XenForo_ControllerAdmin_ForceAgreement extends XenForo_ControllerAdmin_Abstract
{
	public function actionPrivacyPolicy()
	{
		$privacyPolicyUrl = XenForo_Dependencies_Public::getPrivacyPolicyUrl();

		if (!$privacyPolicyUrl)
		{
			return $this->responseError(new XenForo_Phrase('you_do_not_currently_have_privacy_policy_url'));
		}

		if ($this->isConfirmedPost())
		{
			$this->_getOptionModel()->updateOption('privacyPolicyLastUpdate', time());

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildAdminLink('force-agreement/privacy-policy')
			);
		}
		else
		{
			return $this->responseView('XenForo_ViewAdmin_ForceAgreement_PrivacyPolicy', 'force_agreement_privacy_policy');
		}
	}

	public function actionTerms()
	{
		$tosUrl = XenForo_Dependencies_Public::getTosUrl();

		if (!$tosUrl)
		{
			return $this->responseError(new XenForo_Phrase('you_do_not_currently_have_terms_and_rules_url'));
		}

		if ($this->isConfirmedPost())
		{
			$this->_getOptionModel()->updateOption('termsLastUpdate', time());

			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildAdminLink('force-agreement/terms')
			);
		}
		else
		{
			return $this->responseView('XenForo_ViewAdmin_ForceAgreement_Terms', 'force_agreement_terms');
		}
	}

	/**
	 * @return XenForo_Model_Option
	 */
	protected function _getOptionModel()
	{
		return $this->getModelFromCache('XenForo_Model_Option');
	}
}