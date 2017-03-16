<?php
/**
 *
 * Simple feed reader. An extension for the phpBB Forum Software package.
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
	'ACP_FORUM_ID_EXPLAIN'			=> 'Forum waar nieuwe feed berichten in geplaatst worden.',
	'ACP_SETTINGS_EXPLAIN'			=> 'Je kunt RSS, ATOM en RDF feeds toevoegen met onderstaand formulier. Begin met het toevoegen van een feed URL. Als je feeds toegevoegd hebt, wordt een tabel met deze parameters getoond:',
	'ACP_FEEDPOSTBOT_SETTING_SAVED'	=> 'Feed post bot instellingen opgeslagen',
	'ACP_FEEDPOSTBOT_TITLE'			=> 'Feed post bot',
	'ADD_FEED'						=> 'Feed toevoegen',
	'CURDATE'						=> 'Lokale datum/tijd',
	'CURDATE_EXPLAIN'				=> 'Vink aan om moment van verwerken als berichttijd op te slaan. Laat uit om de publicatiedatum van de feed als berichttijd op te slaan.',
	'FETCH_ALL_FEEDS'				=> 'Verwerk alle feeds handmatig',
	'FEED_URL'						=> 'Feed URL',
	'FEED_URL_EXPLAIN'				=> 'De URL van de feed, bijv. <code>https://www.phpbb.com/feeds/rss/</code>. Iedere feed URL moet uniek zijn.',
	'FEED_URL_INVALID'				=> 'Ongeldige feed URL. Dit kan komen doordat deze reeds in de lijst staat of omdat de URL een onjuist format heeft.',
	'LOG_FEED_FETCHED'				=> 'Feed verwerkt<br />» %s',
	'LOG_FEED_TIMEOUT'				=> 'Feed timeout bereikt<br />» %s',
	'NAME'							=> 'Feed naam',
	'NAME_EXPLAIN'					=> 'Alleen bedoeld voor jezelf ter identificatie van de feed.',
	'NO_FEEDS'						=> 'Er zijn nog geed feeds ingevoerd.',
	'READ_MORE'						=> 'Lees meer',
	'REQUIRE_SIMPLEXML'				=> 'De PHP <a href="http://php.net/manual/en/book.simplexml.php">SimpleXML extensie</a> is niet beschikbaar op de server. De extensie heeft dit nodig om feeds te lezen en kan daarom niet geïnstalleerd worden.',
	'TEXTLIMIT'						=> 'Tekstlimiet',
	'TEXTLIMIT_EXPLAIN'				=> 'De ruwe tekst van de feed zal gelimiteerd worden tot het opgegeven aantal tekens. Woorden worden niet afgekapt en eventuele HTML-fouten die hierdoor ontstaan worden gerepareerd alvorens de BBcode conversie plaatsvind. Er wordt een "Lees meer" link toegevoegd. <br> Stel op 0 in om deze functie uit te schakelen.',
	'TIMEOUT'						=> 'Time-out',
	'TIMEOUT_EXPLAIN'				=> 'De tijd die gewacht wordt op antwoord van de feed URL. Indien deze tijd verstreken is zonder antwoord wordt het verzoek afgebroken.',
	'USER_ID'						=> 'Feed gebruiker id',
	'USER_ID_EXPLAIN'				=> 'De id van de gebruiker op wiens naam de nieuwe berichten geplaatst worden.',
));
