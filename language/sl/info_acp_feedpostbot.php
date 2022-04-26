<?php
/**
 *
 * Feed post bot. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Ger, https://github.com/GerB
 * @license GNU General Public License, version 2 (GPL-2.0)
 * Slovenian Translation - Marko K.(max, max-ima,...)
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
	'FPB_ACP_FORUM_ID'					=> 'Vir novic Forum',
	'FPB_ACP_FORUM_ID_EXPLAIN'			=> 'Forum za objavo novih elementov vira.',
	'FPB_ACP_SETTINGS_EXPLAIN'			=> 'S spodnjim obrazcem lahko dodate vire RSS, ATOM ali RDF. Začnite z objavo URL-ja vira. Ko vnesete vire, najdete tabelo s temi parametri:',
	'FPB_ACP_FEEDPOSTBOT_SETTING_SAVED'	=> 'Nastavitve bota za objavo vira so shranjene',
	'FPB_ACP_FEEDPOSTBOT_TITLE'			=> 'Vir novic robot/<br>Feed post bot',
    'FPB_ACP_FETCHED_ITEMS'             => array(
		1	=> 'Vsi viri so pridobljeni; %d nova objava je ustvarjena',
		2	=> 'Pridobljeni vsi viri: %d novih objav je ustvarjeno',
	),
    'FPB_ACP_NO_FETCHED_ITEMS'          => 'Ni (novih) elementov za pridobitev',
	'FPB_ADD_FEED'						=> 'Dodajte vir',
	'FPB_APPEND_LINK'					=> 'Dodaj povezavo',
	'FPB_APPEND_LINK_EXPLAIN'			=> 'Dodajte povezavo do vira elementa vira',
    'FPB_CRON_FREQUENCY'				=> 'Interval za samodejno obdelavo podajanja (sekunde). 0, da onemogočite samodejno pridobivanje.',
	'FPB_CURDATE'						=> 'Lokalni datum/čas',
	'FPB_CURDATE_EXPLAIN'				=> 'Označite, če želite uporabiti čas pridobivanja vira kot čas objave. Počistite polje, če želite uporabiti vir PubDate kot čas objave.',
	'FPB_FETCH_ALL_FEEDS'				=> 'Ročno pridobite vse vire',
	'FPB_FEED_TYPE'						=> 'Vrsta vira',
	'FPB_FEED_TYPE_EXPLAIN'				=> 'Viri so lahko ATOM, RDF ali RSS. Ko prvič vnesete vir, se vrsta samodejno zazna. Če vir ne vrne nobenega elementa za objavo, poskusite to spremeniti.',
	'FPB_FEED_URL'						=> 'URL vira',
	'FPB_FEED_URL_EXPLAIN'				=> 'URL dejanskega vira, npr. <code>https://www.phpbb.com/feeds/rss/</code>. Vsak URL vira mora biti edinstven',
	'FPB_FEED_URL_INVALID'				=> 'Neveljaven URL vira. To je lahko posledica dvojnika na vašem seznamu virov ali preprosto URL, ki ne ustreza specifikacijam',
    'FPB_FEEDS'                         => 'Viri',
    'FPB_LOCKED_EXPLAIN'                => 'Obdelava vira se je začela, vendar ni končana in se zato ne more znova začeti. Če se to ponavlja, lahko postopek zapustite s klikom na ta gumb',
	'FPB_LOG_FEED_ERROR'				=> 'Napaka XML v viru vira<br />» %s',
	'FPB_LOG_FEED_FETCHED'				=> 'Vir je pridobljen<br />» %s',
	'FPB_LOG_FEED_TIMEOUT'				=> 'Časovna omejitev vira je dosežena<br />» %s',
	'FPB_PREFIX'						=> 'Predpona teme',
	'FPB_PREFIX_EXPLAIN'				=> 'Svojim temam lahko dodate predpono, npr. “[phpBB RSS]”. Pustite prazno brez predpone.',
	'FPB_NO_FEEDS'						=> 'Ni še nobenih virov.',
	'FPB_READ_MORE'						=> 'Preberi več',
	'FPB_REQUIRE_SIMPLEXML'				=> 'PHP <a href="http://php.net/manual/en/book.simplexml.php">Razširitev SimpleXML</a> ni na voljo na strežniku. Razširitev to potrebuje za branje virov in je zato ni mogoče namestiti.',
	'FPB_SOURCE'						=> 'Izvor:',
	'FPB_TEXTLIMIT'						=> 'Omejitev besedila',
	'FPB_TEXTLIMIT_EXPLAIN'				=> 'Besedilo vira je omejeno na dano število znakov. Upoštevajte, da se ta vrednost uporabi za neobdelano besedilo vira in besede bodo ostale nedotaknjene. Nato bo vsak pokvarjen HTML iz vira popravljen in pretvorjen v BBcode in dodana je povezava z “Preberi več”. Omejitev je torej le spodbuda za nastalo besedilo objave. <br> Nastavite na 0, da onemogočite omejevanje besedila.',
	'FPB_TIMEOUT'						=> 'Časovna omejitev',
	'FPB_TIMEOUT_EXPLAIN'				=> 'Časovna omejitev za zahtevo po URL-ju vira. Če je ta čas minil brez pridobivanja vsebine vira, se zahteva prekliče.',
    'FPB_TYPE_ATOM'						=> 'ATOM',
	'FPB_TYPE_RDF'						=> 'RDF',
	'FPB_TYPE_RSS'						=> 'RSS',
    'FPB_USER_ID'						=> 'ID uporabnika vira',
	'FPB_USER_ID_EXPLAIN'				=> 'ID uporabnika, ki bo uporabljen za objavo novih elementov.',
));
