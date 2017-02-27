<?php
/**
 *
 * Simple RSS reader. An extension for the phpBB Forum Software package.
 * [Dutch]
 *
 * @copyright (c) 2017, Ger, https://github.com/GerB
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
	'ACP_FORUM_ID'					=> 'Feed forum',
	'ACP_FORUM_ID_EXPLAIN'			=> 'Forum waar nieuwe RSS berichten in geplaatst worden.',
	'ACP_SETTINGS_EXPLAIN'			=> 'Je kunt RSS feeds toevoegne met onderstaand formulier. Begin met het toevoegen van een RSS URL. Als je feeds toegevoegd hebt, wordt een tabel met deze parameters getoond:',
	'ACP_SIMPLERSS_SETTING_SAVED'	=> 'Simple RSS instellingen opgeslagen',
	'ACP_SIMPLERSS_TITLE'			=> 'Simpele RSS reader',
	'ADD_FEED'						=> 'URL toevoegen',
	'FETCH_ALL_FEEDS'				=> 'Verwerk alle feeds handmatig',
	'FEED_URL'						=> 'Feed URL',
	'FEED_URL_EXPLAIN'				=> 'De URL van de RSS feed, bijv. <code>https://www.phpbb.com/feeds/rss/</code>. Iedere feed URL moet uniek zijn.',
	'FEED_URL_INVALID'				=> 'Ongeldige RSS feed URL. Dit kan komen doordat deze reeds in de lijst staat of omdat de URL een onjuist format heeft.',
	'NAME'							=> 'Feed naam',
	'NAME_EXPLAIN'					=> 'Alleen bedoeld voor jezelf ter identificatie van de feed.',
	'USER_ID'						=> 'Feed gebruiker id',
	'USER_ID_EXPLAIN'				=> 'De id van de gebruiker op wiens naam de nieuwe RSS berichten geplaatst worden.',
));
