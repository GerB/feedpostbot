<?php
/**
 *
 * Simple RSS reader. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Ger, https://github.com/GerB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ger\simplerss\migrations;

class install_simplerss extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['simplerss_cron_last_run']);
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v314');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('simplerss_cron_last_run', 0)),
			array('config_text.add', array('ger_cmbb_rightbar_html', '<h3>cmBB is the best! :)</h3>' . "\n" . '<p>Cats are cute</p>')),
			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_SIMPLERSS_TITLE'
			)),
			array('module.add', array(
				'acp',
				'ACP_SIMPLERSS_TITLE',
				array(
					'module_basename'	=> '\ger\simplerss\acp\main_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}


