<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../libs/phirehose/Phirehose.php';
require_once __DIR__ . '/../libs/phirehose/OauthPhirehose.php';

define('ROOT_DIR', substr(__DIR__, 0, -4));

class TweetPull extends OauthPhirehose
{
	const MAX_QUEUE_LENGTH = 1000;
	private $_Redis = null;
	private $_walllist = array();
	private $_queueLength = 0;

	public function __construct($username, $password, $method, $format = self::FORMAT_JSON)
	{
		$this->_Redis = new Redis;
		$this->_Redis->connect('127.0.0.1');

		$this->avgPeriod = 120;
		$this->filterUpdMin = 60;
		parent::__construct($username, $password, $method, $format);
	}

	public function __destruct()
	{
	}

	// send twitter status to zmq:
	public function enqueueStatus($status)
	{
		if($this->_queueLength < self::MAX_QUEUE_LENGTH)
		{
			$this->_queueLength = $this->_Redis->lPush('jitt_tweetqueue', $status);
		}
	}

	// check for changed tracks (new/removed walls) here:
	public function checkFilterPredicates()
	{
		$this->_walllist = array();
		$tmp = $this->_Redis->hGetAll('jiwalls');
		foreach($tmp as $wallid => $clientCount)
		{
			if($clientCount > 0)
			{
				$this->_walllist[$wallid] = $wallid;
			}
		}
		if(empty($this->_walllist) || !isset($this->_walllist['jitt']))
		{
			$this->_walllist['jitt'] = 'jitt';
		}
		$this->setTrack($this->_walllist);
	}

	protected function statusUpdate()
	{
		$this->filterCheckSpent = $this->idlePeriod = $this->maxIdlePeriod = 0;
	}
}

$JittliConfig = new JittliConfig;

// The OAuth credentials you received when registering your app at Twitter
define("TWITTER_CONSUMER_KEY", $JittliConfig->twitterConsumerKey);
define("TWITTER_CONSUMER_SECRET", $JittliConfig->twitterConsumerSecret);

// The OAuth data for the twitter account
define("OAUTH_TOKEN", $JittliConfig->twitterOauthToken);
define("OAUTH_SECRET", $JittliConfig->twitterOauthSecret);

// Start reading:
$TweetPull = new TweetPull(OAUTH_TOKEN, OAUTH_SECRET, Phirehose::METHOD_FILTER);
$TweetPull->setTrack(array('jitt'));
$TweetPull->consume();