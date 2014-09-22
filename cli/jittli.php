<?php
/**
 * jitt.li management script.
 * Call from commandline to start|stop|restart|keepalive the scripts.
 *
 * @author Simon Samtleben <support@lemmingzshadow.net>
 */

require_once __DIR__ . '/config.php';

class Jittli
{
	protected $scripts;
	/**
	 * @var JittliConfig
	 */
	protected $config;

	private $procInfo;

	public function __construct()
	{
		$this->config = new JittliConfig;
		$this->init();
		$this->loadProcessInfo();
	}

	/**
	 * Defined used scripts in this method.
	 */
	public function init()
	{
		$this->scripts = array(
			'pull_tweets.php' => 'php '.$this->config->cliPath.'pull_tweets.php',
			'push_tweets.php' => 'php '.$this->config->cliPath.'push_tweets.php',
			'jittli_server.php' => 'php '.$this->config->cliPath.'jittli_server.php',
		);
	}

	/**
	 * This method will start all defined scripts if not already running.
	 *
	 * @return bool True if scripts were started false if already running.
	 */
	public function start()
	{
		if(!empty($this->procInfo))
		{
			echo "Script already running. Please use restart option." . PHP_EOL;
			return false;
		}
		foreach($this->scripts as $script => $startupCmd)
		{
			exec(escapeshellcmd($startupCmd) . ' > /dev/null 2>&1 &');
		}
		echo "Scripts started." . PHP_EOL;
		return true;
	}

	/**
	 * This method will kill all running scrips.
	 *
	 * @return bool true
	 */
	public function stop()
	{
		if(empty($this->procInfo))
		{
			echo "Scripts not running." . PHP_EOL;
			return true;
		}
		foreach($this->procInfo as $script => $pid)
		{
			exec(escapeshellcmd('kill ' . $pid));
		}
		echo "Scripts stopped." . PHP_EOL;
		return true;
	}

	/**
	 * This method will stop and than restart all scripts.
	 *
	 * @return bool true
	 */
	public function restart()
	{
		$this->stop();
		for($i = 0; $i < 5; $i++)
		{
			usleep(100000);
			echo '.';
		}
		echo PHP_EOL;
		$this->start();
		return true;
	}

	/**
	 * This method will check if all scrits are running and start them if one is not running.
	 *
	 * @return bool true
	 */
	public function keepalive()
	{
		foreach($this->scripts as $script => $startupCmd)
		{
			if(!isset($this->procInfo[$script]))
			{
				exec(escapeshellcmd($startupCmd) . ' > /dev/null 2>&1 &');
			}
		}
		return true;
	}

	/**
	 * This method parses processlist output to get PIDs of running scripts.
	 *
	 * @return void
	 */
	private function loadProcessInfo()
	{
		$cliOutput = array();
		exec('ps x | grep jitt', $cliOutput);
		foreach($cliOutput as $i => $line)
		{
			$line = trim($line);
			$procInfo = preg_split('#\s+#', $line);
			$pid = $procInfo[0];
			$command = $procInfo[5];
			foreach($this->scripts as $script => $startCommand)
			{
				if(strpos($command, $script) !== false)
				{
					$this->procInfo[$script] = $pid;
				}
			}
		}
	}
}


if(empty($argv))
{
	exit('Script can only be run in cli mode.' . PHP_EOL);
}
if(empty($argv[1]))
{
	exit('Please provide a parameter. Valid parameters are: start|stop|restart|keepalive' . PHP_EOL);
}
switch($argv[1])
{
	case 'start':
		$Jittli = new Jittli;
		$Jittli->start();
	break;

	case 'stop':
		$Jittli = new Jittli;
		$Jittli->stop();
	break;

	case 'restart':
		$Jittli = new Jittli;
		$Jittli->restart();
	break;

	case 'keepalive':
		$Jittli = new Jittli;
		$Jittli->keepalive();
	break;

	default:
		exit('Invalid parameter. Valid parameters are: start|stop|restart|keepalive' . PHP_EOL);
	break;
}