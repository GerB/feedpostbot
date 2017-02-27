<?php
/**
 *
 * Simple RSS reader. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Ger, https://github.com/GerB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ger\simplerss\cron\task;

/**
 * Simple RSS reader cron task.
 */
class simplerss_cron extends \phpbb\cron\task\base
{
	/**
	 * How often we run the cron (in seconds).
	 * @var int
	 */
//	protected $cron_frequency = 86400; // 24 hours
	protected $cron_frequency = 1; // 24 hours

	/** @var \phpbb\config\config */
	protected $config;
	
	/** @var \ger\simplerss\classes\driver */
	protected $simplerss;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config $config Config object
	 */
	public function __construct(\phpbb\config\config $config,  \ger\simplerss\classes\driver $simplerss)
	{
		$this->config = $config;
		$this->simplerss = $simplerss;
	}

	/**
	 * Runs this cron task.
	 *
	 * @return void
	 */
	public function run()
	{
		// Run your cron actions here...
		$this->simplerss->fetch_all();

		// Update the cron task run time here if it hasn't
		// already been done by your cron actions.
		$this->config->set('simplerss_cron_last_run', time(), false);
	}

	/**
	 * Returns whether this cron task can run, given current board configuration.
	 *
	 * For example, a cron task that prunes forums can only run when
	 * forum pruning is enabled.
	 *
	 * @return bool
	 */
	public function is_runnable()
	{
		return true;
	}

	/**
	 * Returns whether this cron task should run now, because enough time
	 * has passed since it was last run.
	 *
	 * @return bool
	 */
	public function should_run()
	{
//		return true;
		return $this->config['simplerss_cron_last_run'] < (time() - $this->cron_frequency);
	}
}
