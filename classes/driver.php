<?php

/**
 *
 * Feed post bot
 *
 * @copyright (c) 2017 Ger Bruinsma
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ger\feedpostbot\classes;

class driver
{
	protected $config;
	protected $config_text;
	protected $user;
	protected $auth;
	protected $db;
	protected $log;
	protected $phpbb_root_path;
	public $current_state;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config						$config					Config object
	 * @param \phpbb\config\db_text						$config_text			Config text object
	 * @param \phpbb\request\request_interface			$request				Request object
	 * @param \phpbb\user								$user					User object
	 * @param \phpbb\auth\auth							$auth					Auth object
	 * @param \phpbb\dbal								$db						DB object
	 * @param string									$phpbb_root_path
	 */
	public function __construct(\phpbb\config\config $config,  \phpbb\config\db_text $config_text, \phpbb\user $user, \phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\log\log $log, $phpbb_root_path)
	{
		$this->config = $config;
		$this->config_text = $config_text;
		$this->user = $user;
		$this->auth = $auth;
		$this->db = $db;
		$this->log = $log;
		$this->phpbb_root_path = $phpbb_root_path;

		$this->init_current_state();
	}

	/**
	 * Set and return current state
	 */
	public function init_current_state()
	{
		$ct = $this->config_text->get('ger_feedpostbot_current_state');
		if (empty($ct))
		{
			$this->current_state = false;
		}
		else
		{
			// Legacy check for serialize function, instantly convert to JSON
			$sertest = @unserialize($ct);
			if ($sertest === false)
			{
				$this->current_state = json_decode($ct, true);
			}
			else
			{
				$this->config_text->set('ger_feedpostbot_current_state', json_encode($sertest));
				return $sertest;
			}
		}
		return $this->current_state;
	}

	/**
	 * Fetch all feeds
	 * This is called by the cron handler
	 */
	public function fetch_all()
	{
		if (empty($this->current_state))
		{
			return;
		}

		foreach($this->current_state as $id => $source)
		{
			// Only proceed if not disabled in ACP
			if ($source['forum_id'] > 0)
			{
				$this->fetch_items($this->parse_feed($source['url'], $source['timeout']), $id);
			}
		}
		$this->config_text->set('ger_feedpostbot_current_state', json_encode($this->current_state));
	}

	/**
	 * Parse a feed
	 * @param string $url
	 * @param int $timeout
	 * @return boolean
	 */
	private function parse_feed($url, $timeout = 3)
	{
		$opts['http']['timout'] = (int) $timeout;
		$context = stream_context_create($opts);
		$data = @file_get_contents($url, false, $context); // Suppress errors
		if (!$data)
		{
			$this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'LOG_FEED_TIMEOUT', time(), array($url . ' (' . $timeout . ' s)'));
			return false;
		}
		else
		{
			// Determine feed type and proceed accordingly
			if ((stripos($data, 'application/atom+xml')!== false) || preg_match('/feed xmlns="(.+?)Atom"/i', $data))
			{
				return $this->parse_atom($data, $url);
			}
			else if (stripos($data, '<rdf:RDF') !== false)
			{
				return $this->parse_rdf($data, $url);
			}
			else
			{
				return $this->parse_rss($data, $url);
			}
		}
	}

	/**
	 * Parse the atom source into relevant info
	 * @param string $data	valid ATOM XML string
	 * @return array
	 */
	private function parse_atom($data, $url)
	{
		$content = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
		if ($content === false)
		{
			return false;
		}

		foreach($content->entry as $item)
		{
			$return[] = array(
				'guid' => $this->prop_to_string($item->id),
				'title' => $this->prop_to_string($item->title),
				'link' => $this->prop_to_string($item->link->attributes()->href),
				'description' =>  empty($item->content) ? ( empty($item->summary) ? $this->prop_to_string($item->title) : $this->prop_to_string($item->summary) ) : $this->prop_to_string($item->content),
				'pubDate' => empty($item->updated) ? 0 : $this->prop_to_string($item->updated),
			);
		}

		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FEED_FETCHED', time(), array($url));
		return $return;
	}

	/**
	 * Parse the RDF source into relevant info
	 * @param string $data	valid RDF XML string
	 * @return array
	 */
	private function parse_rdf($data, $url)
	{
		// RDF default hasn't dates. Most use a DC or SY namespace but SimpleXML doesn't handle those
		$find = array('dc:date>', 'sy:date>');

		$content = simplexml_load_string(str_replace($find, 'date>', $data), 'SimpleXMLElement', LIBXML_NOCDATA);
		if ($content === false)
		{
			return false;
		}

		foreach($content->item as $item)
		{
			$return[] = array(
				'title' => $this->prop_to_string($item->title),
				'link' => $this->prop_to_string($item->link),
				'description' =>  empty($item->description) ? $this->prop_to_string($item->title) : $this->prop_to_string($item->description),
				'pubDate' => empty($item->date) ? ( empty($content->channel->date) ? 0 : $this->prop_to_string($content->channel->date) ) : $this->prop_to_string($item->date), // Fallback galore
			);
		}
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FEED_FETCHED', time(), array($url));
		return $return;
	}

	/**
	 * Parse the RSS source into relevant info
	 * @param string $data	valid RSS XML string
	 * @return array
	 */
	private function parse_rss($data, $url)
	{
		$content = simplexml_load_string(($data), 'SimpleXMLElement', LIBXML_NOCDATA);
		if ($content === false)
		{
			return false;
		}
		foreach($content->channel->item as $item)
		{
			$return[] = array(
				'guid' => $this->prop_to_string($item->guid),
				'title' => $this->prop_to_string($item->title),
				'link' => $this->prop_to_string($item->link),
				'description' =>  empty($item->description) ? $this->prop_to_string($item->title) : $this->prop_to_string($item->description),
				'pubDate' => $this->prop_to_string($item->pubDate),
			);
		}
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FEED_FETCHED', time(), array($url));
		return $return;
		
	}

	/**
	 * Fetch the new content in feed
	 * 
	 * @param array $items
	 * @param int $source_id
	 * @return int
	 */
	public function fetch_items($items, $source_id)
	{
		$posted = 0;
		if (empty($items))
		{
			return $posted;
		}

		$new_latest = array(
			'link' => $this->prop_to_string($items[0]['link']),
			'pubDate' => $this->prop_to_string($items[0]['pubDate']),
			'guid' => empty($items[0]['guid']) ? '' : $items[0]['guid'],
		);
		$this->switch_user($this->current_state[$source_id]['user_id']);
		$to_post = array();
		foreach($items as $item)
		{
			if ($this->is_handled($item, $this->current_state[$source_id]['latest']))
			{
				// We've had this one and all below
				$this->current_state[$source_id]['latest'] = $new_latest;
				return;
			}
			else
			{
				$to_post[] = $item;
			}
		}
		if (!empty($to_post))
		{
			// Reverse array to make sure that the latest item is also the newest
			$to_post = array_reverse($to_post);
			foreach($to_post as $item)
			{
				$this->post_message($item, $source_id);
				$posted++;
			}
		}

		$this->current_state[$source_id]['latest'] = $new_latest;
		return $posted;
	}

	/**
	 * Check if this is the latest item
	 * Use guid if available, fallback to pubDate & link
	 * @param object $item
	 * @param array $current
	 * @return bool
	 */
	private function is_handled($item, $current)
	{
		if ( (empty($current['link'])) && (empty($current['pubDate'])) && (empty($current['guid'])) )
		{
			return false;
		}
		if (isset($item['guid']) && ($this->prop_to_string($item['guid']) == $current['guid']))
		{
			return true;
		}
		else if (($item['pubDate'] == $current['pubDate']) && ($item['link'] == $current['link']))
		{
			return true;
		}
		return false;
	}

	/**
	 * Create a topic for new RSS item
	 *
	 * @param array $rss_item
	 * @param int $source_id
	 * @return string
	 */
	private function post_message($rss_item, $source_id)
	{
		if (empty($rss_item))
		{
			return false;
		}
		if (!function_exists('generate_text_for_storage'))
		{
			include($this->phpbb_root_path . 'includes/functions_content.php');
		}
		if (!function_exists('submit_post'))
		{
			include($this->phpbb_root_path . 'includes/functions_posting.php');
		}

		// Make sure we have UTF-8 and handle HTML
		$description = $rss_item['description'];
		$title = $rss_item['title'];

		// Only show excerpt of feed if a text limit is given, but make it nice
		if (!empty($this->current_state[$source_id]['textlimit']))
		{
			$post_text = $this->html2bbcode($this->closetags($this->character_limiter($description, $this->current_state[$source_id]['textlimit'])));
			$post_text .= "\n\n" . '[url=' . $rss_item['link'] . ']' . $this->user->lang('READ_MORE') . '[/url]';
		}
		else
		{
			$post_text = $this->html2bbcode($description) . "\n\n" . $rss_item['link'];
		}

		$poll = $uid = $bitfield = $options = '';
		$allow_bbcode = $allow_urls = $allow_smilies = true;
		generate_text_for_storage($post_text, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);

		$data = array(
			// General Posting Settings
			'forum_id'			 => $this->current_state[$source_id]['forum_id'], // The forum ID in which the post will be placed. (int)
			'topic_id'			 => 0, // Post a new topic or in an existing one? Set to 0 to create a new one, if not, specify your topic ID here instead.
			'icon_id'			 => false, // The Icon ID in which the post will be displayed with on the viewforum, set to false for icon_id. (int)
			// Defining Post Options
			'enable_bbcode'		 => true, // Enable BBcode in this post. (bool)
			'enable_smilies'	 => true, // Enabe smilies in this post. (bool)
			'enable_urls'		 => true, // Enable self-parsing URL links in this post. (bool)
			'enable_sig'		 => true, // Enable the signature of the poster to be displayed in the post. (bool)
			// Message Body
			'message'			 => $post_text, // Your text you wish to have submitted. It should pass through generate_text_for_storage() before this. (string)
			'message_md5'		 => md5($post_text), // The md5 hash of your message
			// Values from generate_text_for_storage()
			'bbcode_bitfield'	 => $bitfield, // Value created from the generate_text_for_storage() function.
			'bbcode_uid'		 => $uid, // Value created from the generate_text_for_storage() function.    // Other Options
			'post_edit_locked'	 => 0, // Disallow post editing? 1 = Yes, 0 = No
			'topic_title'		 => $title,
			'notify_set'		 => true, // (bool)
			'notify'			 => true, // (bool)
			'post_time'			 => empty($this->current_state[$source_id]['curdate']) ? strtotime($rss_item['pubDate']) : 0, // Set a specific time, use 0 to let submit_post() take care of getting the proper time (int)
			'forum_name'		 => $this->get_forum_name($this->current_state[$source_id]['forum_id']), // For identifying the name of the forum in a notification email. (string)    // Indexing
			'enable_indexing'	 => true, // Allow indexing the post? (bool)    // 3.0.6
			'force_visibility'	 => true, // 3.1.x: Allow the post to be submitted without going into unapproved queue, or make it be deleted (replaces force_approved_state)
		);

		return submit_post('post', $title, $this->user->data['username'], POST_NORMAL, $poll, $data);
	}

	/**
	 * Make sure we have a string
	 * @param mixed $prop
	 * @return string
	 */
	private function prop_to_string($prop)
	{
		if (!is_string($prop))
		{
			// Most probaly a SimpleXMLElement
			$prop_ary = (array) $prop;
			$prop = $prop_ary[0];
		}
		$prop = (string) $prop;
		return html_entity_decode($prop);
	}

	/**
	 * Switch to the RSS source user
	 * @param int $new_user_id
	 * @return bool
	 */
	private function switch_user($new_user_id)
	{
		$sql = 'SELECT *
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . (int) $new_user_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$row['is_registered'] = true;
		$this->db->sql_freeresult($result);
		$this->user->data = array_merge($this->user->data, $row);
		$this->auth->acl($this->user->data);

		return true;
	}

	/**
	 * Get forum name by id (for notifications)
	 * @param int $id
	 * @return string
	 */
	private function get_forum_name($id)
	{
		$sql = 'SELECT forum_name
				FROM ' . FORUMS_TABLE . '
				WHERE forum_id = ' . (int) $id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		return empty($row['forum_name']) ? '' : $row['forum_name'];
	}

	/**
	 * Elegant word wrap
	 * @param string $str
	 * @param int $n
	 * @param string $end_char
	 * @return string
	 */
	private function character_limiter($str, $n = 300, $end_char = '...')
	{
		if (strlen($str) < $n)
		{
			return $str;
		}

		$str = preg_replace("/\s+/", ' ', str_replace(array(
			"\r\n",
			"\r",
			"\n"), ' ', $str));

		if (strlen($str) <= $n)
		{
			return $str;
		}

		$out = "";
		foreach (explode(' ', trim($str)) as $val)
		{
			$out .= $val . ' ';

			if (strlen($out) >= $n)
			{
				$out = trim($out);
				return (strlen($out) == strlen($str)) ? $out : $out . $end_char;
			}
		}
	}

	/**
	 * Close open HTML tags
	 * @param string $html
	 * @return string
	 */
	private function closetags($html)
	{
		// put all opened tags into an array
		preg_match_all("#<([a-z]+)( .*)?(?!/)>#iU", $html, $result);
		$openedtags = $result[1];

		// put all closed tags into an array
		preg_match_all("#</([a-z]+)>#iU", $html, $result);
		$closedtags = $result[1];
		$len_opened = count($openedtags);

		// all tags are closed
		if (count($closedtags) == $len_opened)
		{
			return $html;
		}

		$openedtags = array_reverse($openedtags);
		// close tags
		for ($i = 0; $i < $len_opened; $i++)
		{
			if (!in_array($openedtags[$i], $closedtags))
			{
				$html .= "</" . $openedtags[$i] . ">";
			}
			else
			{
				unset($closedtags[array_search($openedtags[$i], $closedtags)]);
			}
		}
		return $html;
	}


	/**
	 * Simple HTML to BBcode conversion
	 * @param string $html_string
	 * @return string
	 */
	private function html2bbcode($html_string)
	{
		$convert = array(
			"/\<ul(.*?)\>(.*?)\<\/ul\>/is" => "[list]$2[/list]",
			"/\<ol(.*?)\>(.*?)\<\/ol\>/is" => "[list]$2[/list]",
			"/\<b(.*?)\>(.*?)\<\/b\>/is" => "[b]$2[/b]",
			"/\<i(.*?)\>(.*?)\<\/i\>/is" => "[i]$2[/i]",
			"/\<u(.*?)\>(.*?)\<\/u\>/is" => "[u]$2[/u]",
			"/\<li(.*?)\>(.*?)\<\/li\>/is" => "[*]$2",
			"/\<img(.*?) src=\"(.*?)\" (.*?)\>/is" => "[img]$2[/img]",
			"/\<div(.*?)\>(.*?)\<\/div\>/is" => "$2",
			"/\<br(.*?)\>/is" => "\n",
			"/\<strong(.*?)\>(.*?)\<\/strong\>/is" => "[b]$2[/b]",
			"/\<a (.*?)href=\"(.*?)\"(.*?)\>(.*?)\<\/a\>/is" => "[url=$2]$4[/url]",
		);

		// Replace main stuff and strip anything else
		return strip_tags(preg_replace(array_keys($convert), array_values($convert), $html_string));
	}
}