<?php

class XenForo_ViewAdmin_DataPortability_Export extends XenForo_ViewAdmin_Base
{
	public function renderXml()
	{
		$this->setDownloadFileName('user.xml');
		return $this->_params['xml']->saveXml();
	}
}