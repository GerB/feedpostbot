<?php
/**
 *
 * Move cron frequency to config item instead of hardcoded
 *
 * @copyright (c) 2017, Ger, https://github.com/GerB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ger\feedpostbot\migrations;

class cron_frequency_config extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['feedpostbot_cron_frequency']);
	}

	static public function depends_on()
	{
		return array('\ger\feedpostbot\migrations\install_feedpostbot');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('feedpostbot_cron_frequency', 1800)),
		);
	}
}


