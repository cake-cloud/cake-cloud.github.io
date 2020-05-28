<?php

class XenForo_ControllerPublic_EmailStop extends XenForo_ControllerPublic_Abstract
{
	protected function _assertIpNotBanned() {}
	protected function _assertViewingPermissions($action) {}
	protected function _assertPolicyAcceptance($action) {}

	public function actionMailingList()
	{
		$userId = $this->_input->filterSingle('u', XenForo_Input::UINT);
		if (!$userId)
		{
			return $this->responseError(new XenForo_Phrase('this_link_is_not_usable_by_you'), 403);
		}

		$confirmKey = $this->_input->filterSingle('c', XenForo_Input::STRING);

		/** @var $userModel XenForo_Model_User */
		$userModel = $this->getModelFromCache('XenForo_Model_User');

		$user = $userModel->getUserById($userId);
		if (!$user || $confirmKey != $userModel->getUserEmailConfirmKey($user))
		{
			return $this->responseError(new XenForo_Phrase('this_link_could_not_be_verified'), 403);
		}

		if ($this->isConfirmedPost())
		{
			$userDw = XenForo_DataWriter::create('XenForo_DataWriter_User');
			$userDw->setExistingData($user['user_id'], true);
			$userDw->set('receive_admin_email', 0);
			$userDw->save();

			return $this->responseMessage(new XenForo_Phrase('your_email_notification_selections_have_been_updated'));
		}
		else
		{
			$viewParams = array(
				'user' => $user,
				'confirmKey' => $confirmKey
			);

			return $this->responseView('XenForo_ViewPublic_EmailStop_MailingList', 'email_stop_mailing_list', $viewParams);
		}
	}
}