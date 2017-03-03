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
	'ACP_SETTINGS_EXPLAIN'			=> 'Je kunt RSS feeds toevoegen met onderstaand formulier. Begin met het toevoegen van een RSS URL. Als je feeds toegevoegd hebt, wordt een tabel met deze parameters getoond:',
	'ACP_SIMPLERSS_SETTING_SAVED'	=> 'Simple RSS instellingen opgeslagen',
	'ACP_SIMPLERSS_TITLE'			=> 'Simpele RSS reader',
	'ADD_FEED'						=> 'Feed toevoegen',
	'FETCH_ALL_FEEDS'				=> 'Verwerk alle feeds handmatig',
	'FEED_URL'						=> 'Feed URL',
	'FEED_URL_EXPLAIN'				=> 'De URL van de RSS feed, bijv. <code>https://www.phpbb.com/feeds/rss/</code>. Iedere feed URL moet uniek zijn.',
	'FEED_URL_INVALID'				=> 'Ongeldige RSS feed URL. Dit kan komen doordat deze reeds in de lijst staat of omdat de URL een onjuist format heeft.',
	'NAME'							=> 'Feed naam',
	'NAME_EXPLAIN'					=> 'Alleen bedoeld voor jezelf ter identificatie van de feed.',
	'NO_FEEDS'						=> 'Er zijn nog geed feeds ingevoerd.',
	'NO_MB_STRING'					=> 'De PHP <a href="http://php.net/manual/en/book.mbstring.php">mbstring extensie</a> is niet beschikbaar op de server. Simple RSS zal werken, maar door verschillen in encoding van de RSS feeds en het forum kunnen sommige berichten rare teksten bevatten.',
	'READ_MORE'						=> 'Lees meer',
	'REQUIRE_SIMPLEXML'				=> 'De PHP <a href="http://php.net/manual/en/book.simplexml.php">SimpleXML extensie</a> is niet beschikbaar op de server. Simple RSS heeft dit nodig om RSS feeds te lezen en kan daarom niet geïnstalleerd worden.',
	'TEXTLIMIT'						=> 'Tekstlimiet',
	'TEXTLIMIT_EXPLAIN'				=> 'De ruwe tekst van de feed zal gelimiteerd worden tot het opgegeven aantal tekens. Woorden worden niet afgekapt en eventuele HTML-fouten die hierdoor ontstaan worden gerepareerd alvorens de BBcode conversie plaatsvind. Er wordt een "Lees meer" link toegevoegd. <br> Stel op 0 in om deze functie uit te schakelen.',
	'TIMEOUT'						=> 'Time-out',
	'TIMEOUT_EXPLAIN'				=> 'De tijd die gewacht wordt op antwoord van de feed URL. Indien deze tijd verstreken is zonder antwoord wordt het verzoek afgebroken.',
	'USER_ID'						=> 'Feed gebruiker id',
	'USER_ID_EXPLAIN'				=> 'De id van de gebruiker op wiens naam de nieuwe RSS berichten geplaatst worden.',
));
