<?php

class XenForo_Install_Upgrade_1052031 extends XenForo_Install_Upgrade_Abstract
{
	public function getVersionName()
	{
		return '1.5.20 Beta 1';
	}

	public function step1()
	{
		$db = $this->_getDb();

		$privacyUrl = $db->fetchOne('
			SELECT option_value
			FROM xf_option
			WHERE option_id = ?
		', 'privacyPolicyUrl');

		$newValue = array(
			'type' => 'default',
			'custom' => false
		);

		if ($privacyUrl)
		{
			$newValue['type'] = 'custom';
			$newValue['custom'] = $privacyUrl;
		}

		$this->executeUpgradeQuery('
			UPDATE xf_option
			SET option_value = ?
			WHERE option_id = ?
		', array(serialize($newValue), 'privacyPolicyUrl'));
	}

	public function step2()
	{
		$this->executeUpgradeQuery("
			ALTER TABLE `xf_user`
			ADD `privacy_policy_accepted` INT(10) UNSIGNED NOT NULL DEFAULT 0,
			ADD `terms_accepted` INT(10) UNSIGNED NOT NULL DEFAULT 0
		");
	}

	public function step3()
	{
		$this->executeUpgradeQuery("
			ALTER TABLE `xf_user_change_log`
			ADD `protected` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0
		");

		// protect any existing receive_admin_email logs
		$this->executeUpgradeQuery("
			UPDATE xf_user_change_log
			SET protected = 1
			WHERE field = 'receive_admin_email'
		");
	}

	public function step4()
	{
		$this->executeUpgradeQuery("
			ALTER TABLE `xf_notice`
			MODIFY COLUMN `notice_type` VARCHAR(25) NOT NULL DEFAULT 'block',
			MODIFY COLUMN `display_style` VARCHAR(25) NOT NULL DEFAULT ''
		");
	}

	public function step5()
	{
		$dupePage = $this->_getDb()->fetchRow("
			SELECT *
			FROM xf_help_page 
			WHERE page_name = 'privacy-policy'
		");

		if (!$dupePage)
		{
			return;
		}

		$updates = array();

		if ($dupePage['page_name'] == 'privacy-policy')
		{
			$updates['page_name'] = 'privacy-policy-old';
		}

		$this->_getDb()->update('xf_help_page', $updates, 'page_id = ' . $this->_getDb()->quote($dupePage['page_id']));
	}
}