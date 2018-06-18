<?php
/**
 *
 * Feed post bot. An extension for the phpBB Forum Software package.
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
	'FPB_ACP_FORUM_ID'					=> 'Feed forum',
	'FPB_ACP_FORUM_ID_EXPLAIN'			=> 'Forum waar nieuwe feed berichten in geplaatst worden.',
	'FPB_ACP_SETTINGS_EXPLAIN'			=> 'Je kunt RSS, ATOM en RDF feeds toevoegen met onderstaand formulier. Begin met het toevoegen van een feed URL. Als je feeds toegevoegd hebt, wordt een tabel met deze parameters getoond:',
	'FPB_ACP_FEEDPOSTBOT_SETTING_SAVED'	=> 'Feed post bot instellingen opgeslagen',
	'FPB_ACP_FEEDPOSTBOT_TITLE'			=> 'Feed post bot',
	'FPB_ACP_FETCHED_ITEMS'             => array(
		1	=> 'Alle feeds verwerkt; %d nieuw bericht geplaatst.',
		2	=> 'Alle feeds verwerkt; %d nieuwe berichten geplaatst.',
	),
    'FPB_ACP_NO_FETCHED_ITEMS'          => 'Geen (nieuwe) items om te verwerken',
	'FPB_ADD_FEED'						=> 'Feed toevoegen',
	'FPB_APPEND_LINK'					=> 'Link toevoegen',
	'FPB_APPEND_LINK_EXPLAIN'			=> 'Voeg een link naar de bron van het feedbericht toe.',    
	'FPB_CRON_FREQUENCY'				=> 'Interval voor het automatisch ophalen van feeds (in seconden). 0 om dit uit te schakelen.',
	'FPB_CURDATE'						=> 'Lokale datum/tijd',
	'FPB_CURDATE_EXPLAIN'				=> 'Vink aan om moment van verwerken als berichttijd op te slaan. Laat uit om de publicatiedatum van de feed als berichttijd op te slaan.',
	'FPB_FETCH_ALL_FEEDS'				=> 'Verwerk alle feeds handmatig',
	'FPB_FEED_TYPE'						=> 'Feed type',
	'FPB_FEED_TYPE_EXPLAIN'				=> 'Feeds kunnen van het type ATOM, RDF of RSS zijn. Bij het toevoegen van een feed wordt dit automatisch herkend. Indien er geen berichten gevonden worden kan het helpen om dit aan te passen.',
	'FPB_FEED_URL'						=> 'Feed URL',
	'FPB_FEED_URL_EXPLAIN'				=> 'De URL van de feed, bijv. <code>https://www.phpbb.com/feeds/rss/</code>. Iedere feed URL moet uniek zijn.',
	'FPB_FEED_URL_INVALID'				=> 'Ongeldige feed URL. Dit kan komen doordat deze reeds in de lijst staat of omdat de URL een onjuist format heeft.',
	'FPB_FEEDS'                         => 'Feeds',
	'FPB_LOCKED_EXPLAIN'                => 'Het verwerken van feeds is gestart maar niet voltooid en kan daarom niet nogmaals gestart worden. Indien deze melding blijft bestaan, kun je het proces middels deze knop vrijgeven',
	'FPB_LOG_FEED_ERROR'				=> 'XML fout in feed bron<br />» %s',
	'FPB_LOG_FEED_FETCHED'				=> 'Feed verwerkt<br />» %s',
	'FPB_LOG_FEED_TIMEOUT'				=> 'Feed timeout bereikt<br />» %s',
	'FPB_PREFIX'						=> 'Onderwerp prefix',
	'FPB_PREFIX_EXPLAIN'				=> 'Je kunt ervoor kiezen om een prefix voor de onderwerptitel te plaatsen, bijvoorbeeld “[phpBB RSS]”. Laat leeg om geen prefix te gebruiken.',
	'FPB_NO_FEEDS'						=> 'Er zijn nog geed feeds ingevoerd.',
	'FPB_READ_MORE'						=> 'Lees meer',
	'FPB_REQUIRE_SIMPLEXML'				=> 'De PHP <a href="http://php.net/manual/en/book.simplexml.php">SimpleXML extensie</a> is niet beschikbaar op de server. De extensie heeft dit nodig om feeds te lezen en kan daarom niet geïnstalleerd worden.',
	'FPB_SOURCE'						=> 'Bron:',
	'FPB_TEXTLIMIT'						=> 'Tekstlimiet',
	'FPB_TEXTLIMIT_EXPLAIN'				=> 'De ruwe tekst van de feed zal gelimiteerd worden tot het opgegeven aantal tekens. Woorden worden niet afgekapt en eventuele HTML-fouten die hierdoor ontstaan worden gerepareerd alvorens de BBcode conversie plaatsvind. Er wordt een “Lees meer” link toegevoegd. <br> Stel op 0 in om deze functie uit te schakelen.',
	'FPB_TIMEOUT'						=> 'Time-out',
	'FPB_TIMEOUT_EXPLAIN'				=> 'De tijd die gewacht wordt op antwoord van de feed URL. Indien deze tijd verstreken is zonder antwoord wordt het verzoek afgebroken.',
	'FPB_TYPE_ATOM'						=> 'ATOM',
	'FPB_TYPE_RDF'						=> 'RDF',
	'FPB_TYPE_RSS'						=> 'RSS',
	'FPB_USER_ID'						=> 'Feed gebruiker id',
	'FPB_USER_ID_EXPLAIN'				=> 'De id van de gebruiker op wiens naam de nieuwe berichten geplaatst worden.',
));
