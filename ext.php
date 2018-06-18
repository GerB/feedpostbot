<?php

namespace ger\feedpostbot;

class ext extends \phpbb\extension\base
{
    public function is_enableable()
    {
		if (!function_exists('simplexml_load_string'))
		{
			$user = $this->container->get('user');
			$user->add_lang_ext('ger/feedpostbot', 'info_acp_feedpostbot');
			trigger_error($user->lang('FPB_REQUIRE_SIMPLEXML'), E_USER_WARNING);
		}
		return true;
    }
}
