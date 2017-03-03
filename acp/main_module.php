<?php
/**
 *
 * Simple RSS reader. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Ger, https://github.com/GerB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ger\simplerss\acp;

/**
 * Simple RSS reader ACP module.
 */
class main_module
{
	public $u_action;

	public function main($id, $mode)
	{
		global $request, $template, $user, $phpbb_container;
		$config_text = $phpbb_container->get('config_text');
		$simplerss = $phpbb_container->get('ger.simplerss.classes.driver');

		$this->tpl_name		 = 'acp_simplerss_body';
		$this->page_title	 = $user->lang('ACP_SIMPLERSS_TITLE');
		add_form_key('ger/simplerss');

		// Fetch current feeds
		$current_state = $simplerss->current_state;
		if ($request->is_set_post('run_all'))
		{
			$simplerss->fetch_all();
			trigger_error($user->lang('ACP_SIMPLERSS_SETTING_SAVED').adm_back_link($this->u_action));

		}
		else if ($request->is_set_post('submit'))
		{
			if (!check_form_key('ger/simplerss'))
			{
				trigger_error('FORM_INVALID');
			}

			// Is a new category added?
			if ($request->is_set_post('add_feed'))
			{
				$url = $request->variable('add_feed', '', true);
				if (!$this->validate_feed($current_state, $url))
				{
					trigger_error('L_FEED_URL_INVALID' . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$current_state[] = array(
					'url' => $url,
					'name' => $url,
					'forum_id' => 0,
					'user_id' => $user->data['user_id'],
					'timeout' => 3,
					'latest' => array(
						'link' => '',
						'pubDate' => '',
						'guid' => '',
					),
				);
				$config_text->set('ger_simple_rss_current_state', json_encode($current_state));
			}
			else
			{
				foreach ($current_state as $id => $source)
				{
					$url = $request->variable($id.'_url', '');
					if (!$this->validate_feed($current_state, $url, $id))
					{
						trigger_error('L_FEED_URL_INVALID');
					}
					$new_state[$id] = array(
						'url' => $url,
						'name' => $request->variable($id.'_name', ''),
						'forum_id' => $request->variable($id.'_forum_id', 0),
						'user_id' => $request->variable($id.'_user_id', $user->data['user_id']),
						'timeout' => $request->variable($id.'_timeout', 3),
						'latest' => $source['latest'],
					);
				}
				$config_text->set('ger_simple_rss_current_state', json_encode($new_state));
			}
			trigger_error($user->lang('ACP_SIMPLERSS_SETTING_SAVED').adm_back_link($this->u_action));
		}
		else if ($request->variable('action', '') == 'delete')
		{
			$id = $request->variable('id', 0);
			unset($current_state[$id]);
			$config_text->set('ger_simple_rss_current_state', json_encode($current_state));
		}

		// List current
		if (!empty($current_state))
		{
			foreach ($current_state as $id => $source)
			{
				$template->assign_block_vars('feeds', array(
					'ID'		=> $id,
					'URL'		=> $source['url'],
					'NAME'		=> $source['name'],
					'U_DELETE'	=> $this->u_action . "&amp;action=delete&amp;id=".$id,
					'S_FORUMS'	=> make_forum_select($source['forum_id'], false, false, false, false),
					'USER_ID'	=> $source['user_id'],
					'TIMEOUT'	=> $source['timeout'],
				));
			}
		}

		// Check for MB string
		if (!function_exists('mb_detect_encoding')) {
			$template->assign_vars(array(
				'NO_MB_STRING' => true,
			));
		}

		$template->assign_vars(array(
			'U_ADD_ACTION' => $this->u_action . "&amp;action=add",
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
//		var_dump($current_state, $url, $id);
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