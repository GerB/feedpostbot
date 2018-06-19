<?php
/**
 *
 * Feed post bot. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Ger, https://github.com/GerB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ger\feedpostbot\acp;

/**
 * Feed post bot ACP module.
 */
class main_module
{
	public $u_action;

	public function main($id, $mode)
	{
		global $request, $template, $user, $phpbb_container, $config;
		$config_text = $phpbb_container->get('config_text');
		$feedpostbot = $phpbb_container->get('ger.feedpostbot.classes.driver');
		$phpbb_dispatcher = $phpbb_container->get('dispatcher');

        $this->tpl_name		 = 'acp_feedpostbot_body';
		$this->page_title	 = $user->lang('FPB_ACP_FEEDPOSTBOT_TITLE');
		add_form_key('ger/feedpostbot');

		// Fetch current feeds
        $feedpostbot->init_current_state();
		$current_state = $feedpostbot->current_state;
		if ($request->is_set_post('run_all'))
		{
			$fetched = $feedpostbot->fetch_all();
            if ($fetched > 0)
            {
                $message = $user->lang('FPB_ACP_FETCHED_ITEMS', $fetched);
            }
            else 
            {
                $message = $user->lang('FPB_ACP_NO_FETCHED_ITEMS');
            }
			trigger_error($message.adm_back_link($this->u_action));
		}
        // Set main config. Might be expanded in the future
		else if ($request->is_set_post('set_config'))
        {
			if (!check_form_key('ger/feedpostbot'))
			{
				trigger_error('FORM_INVALID');
			}
            $config->set('feedpostbot_cron_frequency', $request->variable('cron_frequency', 0));
            trigger_error($user->lang('FPB_ACP_FEEDPOSTBOT_SETTING_SAVED').adm_back_link($this->u_action));
        }
		else if ($request->is_set_post('reset_lock'))
        {
			if (!check_form_key('ger/feedpostbot'))
			{
				trigger_error('FORM_INVALID');
			}
            $config->set('feedpostbot_locked', 0, 1);
            trigger_error($user->lang('FPB_ACP_FEEDPOSTBOT_SETTING_SAVED').adm_back_link($this->u_action));
        }
		else if ($request->is_set_post('submit'))
		{
			if (!check_form_key('ger/feedpostbot'))
			{
				trigger_error('FORM_INVALID');
			}

			// Is a new feed added?
			if ($request->is_set_post('add_feed'))
			{
				$url = $request->variable('add_feed', '', true);
				if (!$this->validate_feed($current_state, $url))
				{
					trigger_error($user->lang('FPB_FEED_URL_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
				}
				$type = $feedpostbot->detect_feed_type($url);
				if ($type === false)
				{
					trigger_error($user->lang('FPB_FEED_URL_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
				}
				$current_state[] = array(
					'url' => $url,
					'type' => $type,
					'prefix' => '',
					'forum_id' => 0,
					'user_id' => $user->data['user_id'],
					'textlimit' => 0,
					'timeout' => 3,
					'curdate' => 0,
					'append_link' => 1,
					'latest' => array(
						'link' => '',
						'pubDate' => '',
						'guid' => '',
					),
				);
				$config_text->set('ger_feedpostbot_current_state', json_encode($current_state));
			}
			else
			{
                $new_state = [];
				foreach ($current_state as $id => $source)
				{
					$url = $request->variable($id.'_url', '', true);
					if (!$this->validate_feed($current_state, $url, $id))
					{
						trigger_error('FPB_FEED_URL_INVALID');
					}
					$new_state[$id] = array(
						'url' => $url,
						'type' => $request->variable($id.'_type', $current_state[$id]['type']),
						'prefix' => $request->variable($id.'_prefix', '', true),
						'forum_id' => $request->variable($id.'_forum_id', ''),
						'user_id' => $request->variable($id.'_user_id', $user->data['user_id']),
						'textlimit' => $request->variable($id.'_textlimit', 0),
						'timeout' => $request->variable($id.'_timeout', 3),
						'curdate' => strlen($request->variable($id . '_curdate', '')) > 0 ? 1 : 0,
						'append_link' => strlen($request->variable($id . '_append_link', '')) > 0 ? 1 : 0,
						'latest' => $source['latest'],
					);
				}
				$config_text->set('ger_feedpostbot_current_state', json_encode($new_state));
			}
			trigger_error($user->lang('FPB_ACP_FEEDPOSTBOT_SETTING_SAVED').adm_back_link($this->u_action));
		}
		else if ($request->variable('action', '') == 'delete')
		{
            if (confirm_box(true))
            {
                $id = $request->variable('id', 0);
                unset($current_state[$id]);
                $config_text->set('ger_feedpostbot_current_state', json_encode($current_state));               
            }
            else
            {
                // Confirm
                confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
                    'id'        => $request->variable('id', 0),
                    'action'    => 'delete',
                )));
            }
		}

		// List current
		if (!empty($current_state))
		{
			foreach ($current_state as $id => $source)
			{
                $block_vars = array(
					'ID'		=> $id,
					'URL'		=> $source['url'],
					'TYPE'		=> $source['type'],
					'PREFIX'	=> $source['prefix'],
					'U_DELETE'	=> $this->u_action . "&amp;action=delete&amp;id=".$id,
					'S_FORUMS'	=> make_forum_select($source['forum_id'], false, false, false, false),
					'USER_ID'	=> $source['user_id'],
					'TEXTLIMIT'	=> $source['textlimit'],
					'TIMEOUT'	=> $source['timeout'],
					'S_CURDATE'	=> empty($source['curdate']) ? false : true,
					'S_APPEND_LINK'	=> empty($source['append_link']) ? false : true,
				);
                
                /**
                 * Modify the post data array before post is submitted
                 *
                 * @event ger.feedpostbot.acp_override_feed_block_vars
                 * @var  array  block_vars  default available block_vars
                 * @var  array  source      source settings from DB
                 * @var  bool   id          source id
                 * @since 1.0.14
                 */
                $vars = array('block_vars', 'source', 'id');
                extract($phpbb_dispatcher->trigger_event('ger.feedpostbot.acp_override_feed_block_vars', compact($vars)));

                $template->assign_block_vars('feeds', $block_vars);
			}
		}

		$template->assign_vars(array(
			'FPB_LOCKED'        => $config['feedpostbot_locked'],
			'CRON_FREQUENCY'    => $config['feedpostbot_cron_frequency'],
			'U_ADD_ACTION'      => $this->u_action . "&amp;action=add",
		));
	}

	/**
	 * Check if provided feed url is unique and a valid url scheme
	 * @param array $current_state
	 * @param string $url
	 * @param int $id
	 */
	private function validate_feed($current_state, $url, $id = null)
	{
		if (filter_var($url, FILTER_VALIDATE_URL) === false)
		{
			return false;
		}
		if (is_array($current_state))
		{
			foreach ($current_state as $source_id => $source)
			{
				if (($url == $source['url']) && ($id != $source_id))
				{
					return false;
				}
			}
		}
		return true;
	}
}