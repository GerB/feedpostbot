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
 * Simple RSS reader ACP module info.
 */
class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\ger\simplerss\acp\main_module',
			'title'		=> 'ACP_SIMPLERSS_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'ACP_SIMPLERSS_TITLE',
					'auth'	=> 'ext_ger/simplerss && acl_a_board',
					'cat'	=> array('ACP_SIMPLERSS_TITLE')
				),
			),
		);
	}
}
