<?php

/**
 *
 * Feed post bot main class
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
	protected $language;
	protected $auth;
	protected $db;
	protected $log;
	protected $phpbb_root_path;
	protected $php_ext;
	protected $phpbb_dispatcher;
	public $current_state;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config						$config					Config object
	 * @param \phpbb\config\db_text						$config_text			Config text object
	 * @param \phpbb\request\request_interface			$request				Request object
	 * @param \phpbb\user								$user					User object
	 * @param \phpbb\language\language					$language				Language object
	 * @param \phpbb\auth\auth							$auth					Auth object
	 * @param \phpbb\db\driver\driver_interface			$db						DB object
	 * @param string									$phpbb_root_path
	 * @param string									$php_ext
	 * @param \phpbb\event\dispatcher					$phpbb_dispatcher
	 */
	public function __construct(\phpbb\config\config $config,  \phpbb\config\db_text $config_text, \phpbb\user $user, \phpbb\language\language $language, \phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\log\log $log, $phpbb_root_path, $php_ext, \phpbb\event\dispatcher $phpbb_dispatcher)
	{
		$this->config = $config;
		$this->config_text = $config_text;
		$this->user = $user;
		$this->language = $language;
		$this->auth = $auth;
		$this->db = $db;
		$this->log = $log;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
	}

	/**
	 * Set and return current state
	 */
	public function init_current_state()
	{
		$ct = $this->config_text->get('ger_feedpostbot_current_state');
		if (empty($ct) || $ct === 'null')
		{
			$this->current_state = false;
		}
		else
		{
            $this->current_state = json_decode($ct, true);
            $this->check_state_parameters();
        }
        return $this->current_state;
	}

    /**
     * Make sure we have all parameters set
     */
    private function check_state_parameters()
    {
        $new_state = [];
        foreach($this->current_state as $id => $source)
        {
            if (isset($source['append_link']))
			{
				$new_state[$id] = $source;
			}
            else
            {
				$new = $source;
				$new['append_link'] = 1;
				$new_state[$id] = $new;
            }
        }
        $this->current_state = $new_state;
        $this->config_text->set('ger_feedpostbot_current_state', json_encode($new_state));
    }
    
	/**
	 * Fetch all feeds
	 * This is called by the cron handler
     * @return int
	 */
	public function fetch_all()
	{
        if (empty($this->current_state)) 
        {
            $this->init_current_state();
        }
        $lock = (int) $this->config['feedpostbot_locked'];
        if ($lock > 0)
        {
            return 0;
        }
        $counter = 0;
        $active_user = $this->user->data['user_id'];
		if (empty($this->current_state))
		{
			return 0;
		}
        if (!$this->config->set_atomic('feedpostbot_locked', 0, time(), false))
        {
            return 0;
        }
		foreach($this->current_state as $id => $source)
		{
			// Only proceed if not disabled in ACP
			if (!empty($source['forum_id']))
			{
				$counter += $this->fetch_items($this->parse_feed($source['url'], $source['type'], $source['timeout']), $id);
			}
		}
		$this->config_text->set('ger_feedpostbot_current_state', json_encode($this->current_state));
        $this->switch_user($active_user);
        $this->config->set('feedpostbot_locked', 0, false);
        return $counter;
	}

    
    /**
     * Get content through curl or fallback to file_get_contents
     * @param string $url
     * @param int $timeout
     * @param bool $useragent_override
     * @param bool $force_file_get_contents
     * @return string with content data or false
     */
    private function get_content($url, $timeout = 10, $useragent_override = false, $force_file_get_contents = false)
    {
        $url= html_entity_decode($url);
        if (!function_exists('curl_init') || $force_file_get_contents) 
        {
            $opts['http']['timout'] = (int) $timeout;
            $context = stream_context_create($opts);
            $data = @file_get_contents($url, false, $context); // Suppress errors
        }
        else
        {
            $curl = curl_init($url);

            curl_setopt($curl, CURLOPT_FAILONERROR, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
            if ($useragent_override)
            {
                curl_setopt($curl, CURLOPT_USERAGENT, 'Googlebot/2.1 (+http://www.google.com/bot.html)' );
            }
            $data = curl_exec($curl);
            curl_close($curl);  
            if (empty($data)) 
            {
                // Try it posing as Google
                if (!$useragent_override) 
                {
                    return $this->get_content($url, $timeout, true);
                }
                return $this->get_content($url, $timeout, false, true);
            }
        }

        return $data;
    }
    
	/**
	 * Parse a feed
	 * @param string $url
	 * @param string $type
	 * @param int $timeout
	 * @return boolean
	 */
	private function parse_feed($url, $type, $timeout = 3)
	{
        // Don't throw errors but log them instead
        libxml_use_internal_errors(true);

        $data = $this->get_content($url, $timeout);
        if (!$data)
		{
			$this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'FPB_LOG_FEED_TIMEOUT', time(), array($url . ' (' . $timeout . ' s)'));
			return false;
		}
		else
		{
			$method = 'parse_'.$type;
			return $this->$method($data, $url);
		}
	}

    /**
	 * Autodetect feed type
	 */
    public function detect_feed_type($url)
    {
        $data = $this->get_content($url);

        if (!empty($data))
        {
			// Determine feed type and proceed accordingly
			if ((stripos($data, 'application/atom+xml')!== false) || preg_match('/xmlns="(.+?)Atom"/i', $data))
			{
				return 'atom';
			}
			else if (stripos($data, '<rdf:RDF') !== false)
			{
				return 'rdf';
			}
			else
			{
				return 'rss';
			}
        }
        return false;
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
            $this->log_xml_error($url);
			return false;
		}
        $ns = $content->getNamespaces(true);

		foreach($content->entry as $item)
		{
			$append = array(
				'guid' => $this->prop_to_string($item->id),
				'title' => strip_tags($this->prop_to_string($item->title)),
				'link' => $this->prop_to_string($item->link->attributes()->href),
				'description' =>  $this->get_item_description($item, $ns),
				'pubDate' => empty($item->updated) ? 0 : $this->prop_to_string($item->updated),
			);
                    
            /**
            * Modify the fetched ATOM item before it's added to the return list
            *
            * @event ger.feedpostbot.parse_atom_append
            * @var  object item  item as found in source
            * @var  array  append Array of properties to be send to the post_message function
            * @since 1.0.1
            */
            $vars = array('item', 'append');
            extract($this->phpbb_dispatcher->trigger_event('ger.feedpostbot.parse_atom_append', compact($vars)));      

            // Add it to the list
            $return[] = $append;
		}

		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'FPB_LOG_FEED_FETCHED', time(), array($url));
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
            $this->log_xml_error($url);
			return false;
		}
        $ns = $content->getNamespaces(true);
        
		foreach($content->item as $item)
		{            
			$append = array(
				'title' => $this->prop_to_string($item->title),
				'link' => $this->prop_to_string($item->link),
				'description' =>  $this->get_item_description($item, $ns),
				'pubDate' => empty($item->date) ? ( empty($content->channel->date) ? 0 : $this->prop_to_string($content->channel->date) ) : $this->prop_to_string($item->date), // Fallback galore
			);

            /**
            * Modify the fetched RDF item before it's added to the return list
            *
            * @event ger.feedpostbot.parse_rdf_append
            * @var  object item  item as found in source
            * @var  array  append Array of properties to be send to the post_message function
            * @since 1.0.1
            */
            $vars = array('item', 'append');
            extract($this->phpbb_dispatcher->trigger_event('ger.feedpostbot.parse_rdf_append', compact($vars)));      

            // Add it to the list
            $return[] = $append;            
		}
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'FPB_LOG_FEED_FETCHED', time(), array($url));
		return $return;
	}

	/**
	 * Parse the RSS source into relevant info
	 * @param string $data	valid RSS XML string
	 * @return array
	 */
	private function parse_rss($data, $url)
	{
		$content = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
		if ($content === false)
		{
            $this->log_xml_error($url);
			return false;
		}
        $ns = $content->getNamespaces(true);

		foreach($content->channel->item as $item)
		{
            $append = array(
				'guid' => $this->prop_to_string($item->guid),
				'title' => $this->prop_to_string($item->title),
				'link' => $this->prop_to_string($item->link),
				'description' =>  $this->get_item_description($item, $ns),
				'pubDate' => $this->prop_to_string($item->pubDate),
			);
        
            /**
            * Modify the fetched RSS item before it's added to the return list
            *
            * @event ger.feedpostbot.parse_rss_append
            * @var  object item  item as found in source
            * @var  array  append Array of properties to be send to the post_message function
            * @since 1.0.1
            */
            $vars = array('item', 'append');
            extract($this->phpbb_dispatcher->trigger_event('ger.feedpostbot.parse_rss_append', compact($vars)));      

            // Add it to the list
            $return[] = $append;
		}
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'FPB_LOG_FEED_FETCHED', time(), array($url));
		return $return;
		
	}

    /**
     * Get some description with fallbacks for fallbacks
     * @param object $item
     * @param object $ns
     * @return string
     */
    private function get_item_description($item, $ns = null)
    {
        if ( (!empty($ns['content'])) && $item->children($ns['content'])->encoded) 
        {   
            return $this->prop_to_string($item->children($ns['content'])->encoded);
        }
        if (!empty($item->description))
        {
            return $this->prop_to_string($item->description);
        }
        if (!empty($item->content))
        {
            return $this->prop_to_string($item->content);
        }
        if (!empty($item->summary))
        {
            return $this->prop_to_string($item->summary);
        }
        if (!empty($item->title))
        {
            return $this->prop_to_string($item->title);
        }
        // Still here?
        return '';
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

        $to_post = array();
		foreach($items as $item)
		{
			if ($this->is_handled($item, $this->current_state[$source_id]['latest']))
			{
				// We've had this one and all below
				$this->current_state[$source_id]['latest'] = $new_latest;
				break;
			}
			else
			{
				$to_post[] = $item;
			}
		}
		if (!empty($to_post))
		{
            $this->switch_user($this->current_state[$source_id]['user_id']);
            
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
		if (!empty($item['guid']) && ($this->prop_to_string($item['guid']) == $current['guid']))
		{
			return true;
		}
		else if (($item['pubDate'] == $current['pubDate']) && ($item['link'] == $current['link']))
		{
			return true;
		}
		else if ($item['link'] == $current['link'])
		{
			return true;
		}
		else if (!empty($item['pubDate']) && (strtotime($item['pubDate']) < strtotime($current['pubDate']))) 
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
			include($this->phpbb_root_path . 'includes/functions_content.' . $this->php_ext);
		}
		if (!function_exists('submit_post'))
		{
			include($this->phpbb_root_path . 'includes/functions_posting.' . $this->php_ext);
		}
        $source = $this->current_state[$source_id];
        
		// Make sure we have UTF-8 and handle HTML
		$description = $rss_item['description'];
		$title = $this->clean_title($rss_item['title']);
		if (!empty($source['prefix']))
		{
			$title = trim($source['prefix']) . ' ' . $title;
		}

		// Only show excerpt of feed if a text limit is given, but make it nice
		if (!empty($source['textlimit']))
		{
			$post_text = $this->html2bbcode($this->closetags($this->character_limiter($description, $source['textlimit'])));
            if (!empty($source['append_link']))
            {
                $post_text .= "\n\n" . '[url=' . $rss_item['link'] . ']' . $this->user->lang('FPB_READ_MORE') . '[/url]';
            }
		}
		else
		{
			$post_text = $this->html2bbcode($description);
            if (!empty($source['append_link']))
            {
                $post_text .= "\n\n" . $this->user->lang('FPB_SOURCE') . ' [url]' .  $rss_item['link'] . '[/url]';
            }
		}

        if (is_numeric($source['forum_id']))
        {
            // Prep posting
            $poll = $uid = $bitfield = $options = '';
            $allow_bbcode = $allow_urls = $allow_smilies = true;
            generate_text_for_storage($post_text, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);

            $data = array(
                // General Posting Settings
                'forum_id'			 => $source['forum_id'], // The forum ID in which the post will be placed. (int)
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
                'bbcode_uid'		 => $uid, // Value created from the generate_text_for_storage() function.    
                // Other Options
                'post_edit_locked'	 => 0, // Disallow post editing? 1 = Yes, 0 = No
                'topic_title'		 => $title,
                'notify_set'		 => true, // (bool)
                'notify'			 => true, // (bool)
                'post_time'			 => empty($source['curdate']) ? strtotime($rss_item['pubDate']) : 0, // Set a specific time, use 0 to let submit_post() take care of getting the proper time (int)
                'forum_name'		 => $this->get_forum_name($source['forum_id']), // For identifying the name of the forum in a notification email. (string)    // Indexing
                'enable_indexing'	 => true, // Allow indexing the post? (bool)    // 3.0.6
            );
        }
        // Maybe an extension handles the content other than by posting
        $do_post = true;
        
        /**
         * Modify the post data array before post is submitted
         *
         * @event ger.feedpostbot.submit_post_before
         * @var  array  data  Data array send to the submit_post function
         * @var  array  rss_item  Complete feed item as fetched by parse_{method}
         * @var  array  source      source settings
         * @var  string  title      topic title
         * @var  bool   do_post     set to false if you don't want to post
         * @since 1.0.1
         */
        $vars = array('data', 'rss_item', 'source', 'title', 'do_post');
        extract($this->phpbb_dispatcher->trigger_event('ger.feedpostbot.submit_post_before', compact($vars)));
        if ($do_post)
        {
            return submit_post('post', $title, $this->user->data['username'], POST_NORMAL, $poll, $data);
        }
        return true;
	}

	/**
	 * Make sure we have a string
	 * @param mixed $prop
	 * @return string
	 */
	private function prop_to_string($prop)
	{
        if (empty($prop))
        {
            return '';
        }
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
     * Ditch emojis from title
     * @param string $string
     * @return string
     */
    private function clean_title($string)
    {
         return trim(preg_replace('/[\x{10000}-\x{10FFFF}]/u', " ", $string));
    }
    
	/**
	 * Switch to the RSS source user
	 * @param int $new_user_id
	 * @return bool
	 */
	private function switch_user($new_user_id)
	{
        if ($this->user->data['user_id'] == $new_user_id)
        {
            $this->language->add_lang('info_acp_feedpostbot', 'ger/feedpostbot');
            return true;
        }
        $cur_lang = $this->user->data['user_lang'];
		
        $sql = 'SELECT *
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . (int) $new_user_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
        $row['is_registered'] = true;
        $this->user->data = array_merge($this->user->data, $row);
        $this->user->timezone = $row['user_timezone'];
        
        if ($cur_lang != $row['user_lang'])
        {
            $this->language->set_user_language($row['user_lang'], true);
        }
		$this->auth->acl($this->user->data);
        $this->language->add_lang('info_acp_feedpostbot', 'ger/feedpostbot');
		return true;
	}

	/**
	 * Get forum name by id (for notifications)
	 * @param int $id
	 * @return string
	 */
	public function get_forum_name($id)
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
	public function character_limiter($str, $n = 300, $end_char = '...')
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
	public function closetags($html)
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
	public function html2bbcode($html_string)
	{ 
		$convert = array(
            "/[\r\n]+/" => " ",
			"/\<ul(.*?)\>(.*?)\<\/ul\>/is" => "[list]$2[/list]",
			"/\<ol(.*?)\>(.*?)\<\/ol\>/is" => "[list]$2[/list]",
			"/\<b(.*?)\>(.*?)\<\/b\>/is" => "[b]$2[/b]",
			"/\<i(.*?)\>(.*?)\<\/i\>/is" => "[i]$2[/i]",
			"/\<u(.*?)\>(.*?)\<\/u\>/is" => "[u]$2[/u]",
			"/\<li(.*?)\>(.*?)\<\/li\>/is" => "[*]$2",
			'/\<img(.*?) src=["\']?([^"\'>]+)["\']?(.*?)\>/is' => "\n[img]$2[/img]\n",
			"/\<div(.*?)\>(.*?)\<\/div\>/is" => "$2",
			"/\<p(.*?)\>(.*?)\<\/p\>/is" => "\n$2\n",
			"/[\s]*\<br(.*?)\>[\s]*/is" => "\n",
			"/\<strong(.*?)\>(.*?)\<\/strong\>/is" => "[b]$2[/b]",
            '/<a(.+?)href=["\']?([^"\'>]+)["\']?(.*?)>(.*?)\<\/a\>/is' => "[url=$2]$4[/url]",
			'/\<iframe (.*?)src=["\']?([^"\'>]+)["\']?(.*?)<\/iframe\>/is' => "\n$2\n",
			'/\n{3,}/s' => "\n\n",
		);

        /**
        * Modify the fetched RSS item before it's added to the return list
        *
        * @event ger.feedpostbot.html2bbcode_convert
        * @var  array   $convert  regex array
        * @var  string  $html_string input string
        * @since 1.0.12
        */
        $vars = array('item', 'append');
        extract($this->phpbb_dispatcher->trigger_event('ger.feedpostbot.html2bbcode_convert', compact($vars)));   
        
		// Replace main stuff and strip anything else
		return strip_tags(preg_replace(array_keys($convert), array_values($convert), $html_string));
	}
    
    /**
     * Log xml error messages and clear
     * @param string $url
     * @return void
     */
    private function log_xml_error($url)
    {
        // Create a simple list of found errors
        $xml_errors = '';
        foreach( libxml_get_errors() as $error ) 
        {
            $xml_errors .= $error->message . '\n';
        }
        $this->log->add('critical', $this->user->data['user_id'], $this->user->ip, 'FPB_LOG_FEED_ERROR', time(), array($url, $xml_errors));
        
        // Clear libxml error buffer
        libxml_clear_errors();
        return;
    }
}