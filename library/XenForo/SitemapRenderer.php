<?php

class XenForo_SitemapRenderer
{
	public function outputSitemap($counter = 0)
	{
		header('X-Robots-Tag: noindex');

		/** @var XenForo_Model_Sitemap $sitemapModel */
		$sitemapModel = XenForo_Model::create('XenForo_Model_Sitemap');
		$sitemap = $sitemapModel->getCurrentSitemap();
		if (!$sitemap)
		{
			header('Content-Type: text/plain; charset=utf-8', true, 404);
			echo 'no sitemap';
			exit;
		}

		$counter = intval($counter);
		if ($counter <= 0)
		{
			if ($sitemap['file_count'] > 1)
			{
				header('Content-Type: application/xml; charset=utf-8');
				header('Content-Disposition: inline; filename="sitemap-index.xml"');
				echo $this->buildSitemapIndex($sitemap);
				exit;
			}

			$counter = 1;
		}

		$fileName = $sitemapModel->getSitemapFileName($sitemap['sitemap_id'], $counter, $sitemap['is_compressed']);
		if (file_exists($fileName))
		{
			$outputFileName = $sitemap['file_count'] > 1 ? 'sitemap-' . $counter . '.xml' : 'sitemap.xml';

			header('Content-Type: application/xml; charset=utf-8');
			header('Content-Disposition: inline; filename="' . $outputFileName . '"');

			if ($sitemap['is_compressed'])
			{
				header('Content-Encoding: gzip');
			}

			readfile($fileName);
		}
		else
		{
			header('Content-Type: text/plain; charset=utf-8', true, 404);
			echo 'invalid sitemap file';
		}
	}

	public function buildSitemapIndex(array $sitemap)
	{
		$output = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
			. '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		$options = XenForo_Application::getOptions();
		$boardUrl = $options->boardUrl;

		if ($options->useFriendlyUrls)
		{
			$sitemapBase = $boardUrl . '/sitemap-%d.xml';
		}
		else
		{
			$sitemapBase = $boardUrl . '/sitemap.php?c=%d';
		}

		for ($i = 1; $i <= $sitemap['file_count']; $i++)
		{
			$url = sprintf($sitemapBase, $i);
			$output .= "\t" . '<sitemap>'
				. '<loc>' . htmlspecialchars($url) . '</loc>'
				. '<lastmod>' . gmdate(DateTime::W3C, $sitemap['complete_date']) . '</lastmod>'
				. '</sitemap>' . "\n";
		}

		$output .= '</sitemapindex>';

		return $output;
	}
}