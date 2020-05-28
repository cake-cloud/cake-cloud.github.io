<?php

$startTime = microtime(true);
$fileDir = dirname(__FILE__);

require($fileDir . '/library/XenForo/Autoloader.php');
XenForo_Autoloader::getInstance()->setupAutoloader($fileDir . '/library');

XenForo_Application::initialize($fileDir . '/library', $fileDir);
XenForo_Application::set('page_start_time', $startTime);

$dependencies = new XenForo_Dependencies_Public();
$dependencies->preLoadData();

$counter = isset($_GET['c']) && is_string($_GET['c']) ? intval($_GET['c']) : 0;

$renderer = new XenForo_SitemapRenderer();
$renderer->outputSitemap($counter);