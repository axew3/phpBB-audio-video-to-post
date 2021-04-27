<?php
/**
 *
 * Audio Player and Recorder on Posts. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2021, axew3, http://www.axew3.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'W3AUDIOVIDEOTOPOST_STARTREC'	=> 'Start recording',
	'W3AUDIOVIDEOTOPOST_STOPREC' => 'Stop recording',
	'W3AUDIOVIDEOTOPOST_ONREC' => 'Recording',
	'W3AUDIOVIDEOTOPOST_DELREC' => 'Delete',
	'W3AUDIOVIDEOTOPOST_ANPAC' => 'attach name (optional)',
	'W3AUDIOVIDEOTOPOST_EXPAC' => 'Only letters, numbers and - (hyphen)',	
	'W3AUDIOVIDEOTOPOST_ATTACHA' => 'Attach audio',
	'W3AUDIOVIDEOTOPOST_ATTACHV' => 'Attach video',
));
