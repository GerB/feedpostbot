<?php
/**
 *
 * Add config item for locking fetch process
 *
 * @copyright (c) 2017, Ger, https://github.com/GerB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ger\feedpostbot\migrations;

class config_lock extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['feedpostbot_locked']);
	}

	static public function depends_on()
	{
		return array('\ger\feedpostbot\migrations\install_feedpostbot');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('feedpostbot_locked', 0, true)),
		);
	}
}