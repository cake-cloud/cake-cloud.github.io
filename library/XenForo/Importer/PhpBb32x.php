<?php

class XenForo_Importer_PhpBb32x extends XenForo_Importer_PhpBb31x
{
	public static function getName()
	{
		return 'phpBB 3.2.x (Beta)';
	}

	protected function _sanitizeBbCode($string, $bbCodeUid = null, $strip = false)
	{
		// TODO: XF2 notes: Emoji are represented in the DB in <EMOJI> tags with their seq ID and HTML entity
		// TODO: We can likely simplify the sanitization of the XML markup with: html_entity_decode(strip_tags($xml), ENT_QUOTES, 'UTF-8');

		$start = substr($string, 0, 3);
		if ($start != '<r>' && $start != '<t>')
		{
			// treat this as legacy content
			return parent::_sanitizeBbCode($string, $bbCodeUid, $strip);
		}

		// 3.2 style content
		$this->_strip32BbCode = $strip;

		// should be wrapped in <r>...</r> or <t>...</t>
		$end = substr($string, -4);
		if ($end == '</r>' || $end == '</t>')
		{
			$string = substr($string, 3, -4);
		}

		$string = preg_replace('#<br\s*/?>(\r?\n)*#i', "\n", $string);

		do
		{
			$oldString = $string;
			$string = preg_replace_callback(
				'#<([A-Z0-9_]+)(\s+[^>]*)?>(<s>.*</e>)</\\1>#sU',
				array($this, '_sanitize32RegexReplace'),
				$string
			);
		}
		while ($oldString != $string);

		// <LI> tags don't have a matching <e> part
		do
		{
			$oldString = $string;
			$string = preg_replace(
				'#<([A-Z0-9_]+)(\s+[^>]*)?><s>(.*)</s>(.*)</\\1>#sU',
				$strip ? '$4' : '$3$4',
				$string
			);
		}
		while ($oldString != $string);

		// covers any remaining bits and emoticons
		do
		{
			$oldString = $string;
			$string = preg_replace(
				'#<([A-Z0-9_]+|s|e|r|t)(\s+[^>]*)?>(.*)</\\1>#sU',
				'$3',
				$string
			);
		}
		while ($oldString != $string);

		$string = preg_replace('#<!--.*-->#siU', '', $string);
		$string = str_replace('[/*]', '', $string);
		$string = $this->_convertToUtf8($string, true);

		do
		{
			$previousString = $string;

			// Handles quotes with arguments
			$string = preg_replace(
				'#\[quote=(?:"|)(.+)(?:"|)(?:\s?(?:post_id|time|user_id)=[^\]]+|)\]#siU',
				'[quote="$1"]',
				$string
			);

			// don't handle converting [attachment] tags - just strip them
			$string = preg_replace(
				'#\[attachment=[^]]+\].*\[/attachment\]#siU',
				'',
				$string
			);

			// size tags need mapping
			$string = preg_replace_callback(
				'#\[(size)="?([^\]]*)"?\](.*)\[/size\]#siU',
				array($this, '_handleBbCodeSizeCallback'),
				$string
			);

			// align tags need mapping
			$string = preg_replace(
				'#\[align="?(left|center|right)"?\](.*)\[/align\]#siU',
				'[$1]$3[/$1]',
				$string
			);
		}
		while ($string != $previousString);

		$this->_strip32BbCode = false;

		return $string;
	}

	protected $_strip32BbCode = false;

	protected function _sanitize32RegexReplace(array $match)
	{
		$content = $match[3];
		$content = preg_replace('#^<s>(.*)</s>#sU', $this->_strip32BbCode ? '' : '$1', $content);

		// This is a bit odd, but we want to strip the last <e>...</e> off only.
		// Start matches are fast and we can more easily ensure that we match the last <e> directly.
		// So flip the string and have the regex match the string backwards.
		$content = strrev($content);
		$content = preg_replace('#^>e/<(.*)>e<#sU', $this->_strip32BbCode ? '' : '$1', $content);
		$content = strrev($content);

		return $content;
	}
}