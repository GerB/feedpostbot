<?php

/**
 *
 * Simple RSS reader
 *
 * @copyright (c) 2017 Ger Bruinsma
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ger\simplerss\classes;

class driver
{
	protected $config;
	protected $config_text;
	protected $user;
	protected $auth;
	protected $db;
	protected $log;
	protected $phpbb_root_path;
	protected $current_state;
	protected $encoding = '';

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

		$this->current_state = unserialize($this->config_text->get('ger_simple_rss_current_state'));
	}

	/**
	 * Parse the RSS source into relevant info
	 * @param string $url
	 * @return boolean
	 */
	public function parse_rss($url)
	{
		$content = $this->read_rss($url);
		if ($content === false)
		{
			return false;
		}
		else
		{
			foreach($content->channel->item as $item)
			{
				$return[] = $item;
			}
			return $return;
		}
	}

	/**
	 * Fetch all RSS items.
	 * This is called by the cron handler
	 */
	public function fetch_all()
	{
		foreach($this->current_state as $id => $source)
		{
			if ($source['forum_id'] > 0)
			{
				$this->fetch_items($this->parse_rss($source['url']), $id);
			}
		}
		$this->config_text->set('ger_simple_rss_current_state', serialize($this->current_state));
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
			'link' => $this->prop_to_string($items[0]->link),
			'pubDate' => $this->prop_to_string($items[0]->pubDate),
			'guid' => isset($items[0]->guid) ? $this->prop_to_string($items[0]->guid) : '',
		);
		$this->switch_user($this->current_state[$source_id]['user_id']);

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
//		var_dump($this->prop_to_string($item->guid), $current['guid']);

		if (isset($item->guid) && ($this->prop_to_string($item->guid) == $current['guid']))
		{
			return true;
		}
		else if (($item->pubDate == $current['pubDate']) && ($item->link == $current['link']))
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
		if (empty($source_id) || empty($rss_item))
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
		$description = $this->prop_to_string($rss_item->description);
		$title = $this->prop_to_string($rss_item->title);
		$post_text = $this->html2bbcode($description) . "\n\n" . $this->prop_to_string($rss_item->link);

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
			'post_time'			 => strtotime($rss_item->pubDate), // Set a specific time, use 0 to let submit_post() take care of getting the proper time (int)
			'forum_name'		 => $this->get_forum_name($this->current_state[$source_id]['forum_id']), // For identifying the name of the forum in a notification email. (string)    // Indexing
			'enable_indexing'	 => true, // Allow indexing the post? (bool)    // 3.0.6
			'force_visibility'	 => true, // 3.1.x: Allow the post to be submitted without going into unapproved queue, or make it be deleted (replaces force_approved_state)
		);

		return submit_post('post', $title, $this->user->data['username'], POST_NORMAL, $poll, $data);
	}

	/**
	 * Make sure a property is an UTF-8 encoded string
	 * @param mixed $prop
	 * @return string
	 */
	private function prop_to_string($prop)
	{
		if (!is_string($prop))
		{
			// Most probaly a SimpleXMLElement
			$prop_ary = (array) $prop;
			$prop = (string) $prop_ary[0];
		}
		if (empty($this->encoding))
		{
			// Couldn't detect the encoding from stream
			$this->encoding = @mb_detect_encoding($prop, mb_detect_order(), true);
		}
		if (strtoupper($this->encoding) != 'UTF-8')
		{
			if (!function_exists('utf8_recode'))
			{
				include($this->phpbb_root_path . 'includes/utf/utf_tools.php');
			}
			$prop = utf8_recode($prop, $this->encoding);
		}
		return $prop;
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
	 * Simple HTML to BBcode conversion
	 * @param string $html_string
	 * @return string
	 */
	private function html2bbcode($html_string)
	{
		$convert = array(
			"/\<b(.*?)\>(.*?)\<\/b\>/is" => "[b]$2[/b]",
			"/\<i(.*?)\>(.*?)\<\/i\>/is" => "[i]$2[/i]",
			"/\<u(.*?)\>(.*?)\<\/u\>/is" => "[u]$2[/u]",
			"/\<ul(.*?)\>(.*?)\<\/ul\>/is" => "[list]$2[/list]",
			"/\<ol(.*?)\>(.*?)\<\/ol\>/is" => "[list]$2[/list]",
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

	/**
	 * Read RSS URL into SimpleXML object
	 * Ensures no timeouts occur by timing out after 3 seconds
	 * @param string $url
	 * @param int $timeout
	 */
	private function read_rss($url, $timeout = 3)
	{
		$opts['http']['timout'] = (int) $timeout;
		$context = stream_context_create($opts);
		$data = file_get_contents($url, false, $context);
		if (!$data)
		{
			$this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'FEED_TIMEOUT', time(), array($url . ' (' . $timeout . ')'));
			return false;
		}
		else
		{
			// Try fetching the encoding
			preg_match('/encoding="(.+?)"/is', $data, $matches);
			if (!empty($matches[1]))
			{
				$this->encoding = empty($matches[1]) ? '' : $matches[1];
			}
			return simplexml_load_string($data);
		}
	}
}