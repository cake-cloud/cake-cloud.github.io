<?php

class XenForo_ViewPublic_NewsFeed_View extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$this->_params['newsFeed'] = XenForo_ViewPublic_Helper_NewsFeed::getTemplates(
			$this,
			$this->_params['newsFeed'],
			$this->_params['newsFeedHandlers']
		);
		if (!$this->_params['newsFeed'])
		{
			$this->_params['feedEnds'] = true;
		}
	}

	public function renderJson()
	{
		$output = $this->_renderer->getDefaultOutputArray(get_class($this), $this->_params, 'news_feed');
		$output['oldestItemId'] = $this->_params['oldestItemId'];
		$output['feedEnds'] = $this->_params['feedEnds'];

		return XenForo_ViewRenderer_Json::jsonEncodeForOutput($output);
	}
}