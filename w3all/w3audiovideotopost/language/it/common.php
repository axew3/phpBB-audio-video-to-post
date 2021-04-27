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
	'W3AUDIOVIDEOTOPOST_STARTREC' => 'Inzia registrazione',
	'W3AUDIOVIDEOTOPOST_STOPREC' => 'Ferma registrazione',
	'W3AUDIOVIDEOTOPOST_ONREC' => 'Registrando',
	'W3AUDIOVIDEOTOPOST_DELREC' => 'Cancella',
	'W3AUDIOVIDEOTOPOST_ANPAC' => 'nome allegato (opzionale)',
	'W3AUDIOVIDEOTOPOST_EXPAC' => 'Solo letter, numeri e - (trattino)',	
	'W3AUDIOVIDEOTOPOST_ATTACHA' => 'Allega audio',
	'W3AUDIOVIDEOTOPOST_ATTACHV' => 'Allega video',
));
