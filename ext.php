<?php

namespace ger\simplerss;

class ext extends \phpbb\extension\base
{
    public function is_enableable()
    {
		if (!function_exists('simplexml_load_string'))
		{
			$user = $this->container->get('user');
			$user->add_lang_ext('ger/simplerss', 'info_acp_simplerss');
			trigger_error($user->lang('REQUIRE_SIMPLEXML'), E_USER_WARNING);
		}
		return ;
    }
}
