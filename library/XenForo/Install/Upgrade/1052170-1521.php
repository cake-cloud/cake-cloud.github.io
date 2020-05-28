<?php

class XenForo_Install_Upgrade_1052170 extends XenForo_Install_Upgrade_Abstract
{
	public function getVersionName()
	{
		return '1.5.21';
	}

	public function step1()
	{
		// this is being run again as these changes were not in the installer in 1.5.20
		$this->executeUpgradeQuery("
			ALTER TABLE `xf_notice`
			MODIFY COLUMN `notice_type` VARCHAR(25) NOT NULL DEFAULT 'block',
			MODIFY COLUMN `display_style` VARCHAR(25) NOT NULL DEFAULT ''
		");
	}
}