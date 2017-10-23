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
 * Feed post bot ACP module info.
 */
class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\ger\feedpostbot\acp\main_module',
			'title'		=> 'FPB_ACP_FEEDPOSTBOT_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'FPB_ACP_FEEDPOSTBOT_TITLE',
					'auth'	=> 'ext_ger/feedpostbot && acl_a_board',
					'cat'	=> array('FPB_ACP_FEEDPOSTBOT_TITLE')
				),
			),
		);
	}
}
