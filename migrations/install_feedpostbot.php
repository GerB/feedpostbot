<?php
/**
 *
 * Simple RSS reader. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Ger, https://github.com/GerB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ger\feedpostbot\migrations;

class install_feedpostbot extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['feedpostbot_cron_last_run']);
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v314');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('feedpostbot_cron_last_run', 0)),
			array('config_text.add', array('ger_feedpostbot_current_state', '')),
			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'FPB_ACP_FEEDPOSTBOT_TITLE'
			)),
			array('module.add', array(
				'acp',
				'FPB_ACP_FEEDPOSTBOT_TITLE',
				array(
					'module_basename'	=> '\ger\feedpostbot\acp\main_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}


