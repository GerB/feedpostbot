<?php
/**
 *
 * Feed post bot. An extension for the phpBB Forum Software package.
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
	'ACP_FORUM_ID_EXPLAIN'			=> 'The forum to post the new feed items in.',
	'ACP_SETTINGS_EXPLAIN'			=> 'You can add RSS, ATOM or RDF feeds using the form below. Start with posting a feed URL. When you have entered feeds, you find a table with these parameters:',
	'ACP_SFEEDPOSTBOT_SETTING_SAVED'	=> 'Feed post bot settings saved',
	'ACP_FEEDPOSTBOT_TITLE'			=> 'Feed post bot',
	'ADD_FEED'						=> 'Add feed',
	'APPEND_LINK'					=> 'Append link',
	'APPEND_LINK_EXPLAIN'			=> 'Append a link to the source of the feed item',
    'CRON_FREQUENCY'				=> 'Interval for processing feeds (seconds)',
	'CURDATE'						=> 'Local date/time',
	'CURDATE_EXPLAIN'				=> 'Check to use the feed fetch time as post time. Uncheck to use the feed PubDate as post time.',
	'FETCH_ALL_FEEDS'				=> 'Fetch all feeds manually',
	'FEED_TYPE'						=> 'Feed type',
	'FEED_TYPE_EXPLAIN'				=> 'Feeds can be ATOM, RDF or RSS. Upon entering a feed for the first time, the type is autodetected. If the feed doesn\'t return any items to post, try to change this.',
	'FEED_URL'						=> 'Feed URL',
	'FEED_URL_EXPLAIN'				=> 'The URL to the actual feed, e.g. <code>https://www.phpbb.com/feeds/rss/</code>. Each feed URL should be unique',
	'FEED_URL_INVALID'				=> 'Invalid feed URL. This may be the result of a duplicate in your feed list or simply an URL that does not meet the specifications',
    'FEEDS'                         => 'Feeds',
	'LOG_FEED_FETCHED'				=> 'Feed fetched<br />» %s',
	'LOG_FEED_TIMEOUT'				=> 'Feed timeout reached<br />» %s',
	'PREFIX'						=> 'Topic prefix',
	'PREFIX_EXPLAIN'				=> 'You can choose to add a prefix to your topics, eg. "[phpBB RSS]". Leave empty for no prefix.',
	'NO_FEEDS'						=> 'There are no feeds yet.',
	'READ_MORE'						=> 'Read more',
	'REQUIRE_SIMPLEXML'				=> 'The PHP <a href="http://php.net/manual/en/book.simplexml.php">SimpleXML extension</a> is not available on the server. The extension needs this to read the feeds and therefore cannot be installed.',
	'TEXTLIMIT'						=> 'Text limit',
	'TEXTLIMIT_EXPLAIN'				=> 'The feed text is limited to given number of characters. Note that this value is applied to the raw feed text and words will be kept intact. Afterwards any broken HTML from the feed will be mended and converted to BBcode and a link with "Read more" is appended. The limit is therefore only an incidation for the resulting post text. <br> Set to 0 to disable text limiting.',
	'TIMEOUT'						=> 'Timeout',
	'TIMEOUT_EXPLAIN'				=> 'Timeout for requesting the Feed URL. If this time has passed without retrieving the feed content, the request is cancelled.',
	'USER_ID'						=> 'Feed user id',
	'USER_ID_EXPLAIN'				=> 'The id of the user that will be used to post new items.',
));
