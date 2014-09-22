<?php
class TweetPush
{
	const WALL_UPDATE_INTERVAL = 60;

	private $_Redis = null;
	private $_zmqSocket = null;
	private $_lastWallUpdate = 0;
	private $_walllist = array();

	public function __construct()
	{
		$this->_Redis = new Redis;
		$this->_Redis->connect('127.0.0.1');

		$context = new ZMQContext();
		$this->_zmqSocket = $context->getSocket(ZMQ::SOCKET_PUSH, 'jittliwall');
		$this->_zmqSocket->connect("tcp://localhost:5555");

		$this->_updateWallist();
	}

	public function __destruct()
	{

	}

	public function run()
	{
		while(true)
		{
			$tweets = $this->_Redis->lRange('jitt_tweetqueue', 0, 9);
			$this->_Redis->del('jitt_tweetqueue');
			$tweetsMaxIndex = count($tweets) - 1;
			for($i = $tweetsMaxIndex; $i >= 0; $i--)
			{
				$tweetData = json_decode($tweets[$i], true);
				if(is_array($tweetData) && isset($tweetData['user']['screen_name']) && !empty($tweetData['entities']['hashtags']))
				{
					$hashTags = array();
					foreach($tweetData['entities']['hashtags'] as $htData)
					{
						$hashTags[] = $htData['text'];
					}
					$hashTags = implode('|', $hashTags);
					foreach($this->_walllist as $wallname)
					{
						if(stripos($hashTags, $wallname) !== false)
						{
							$pushData = array(
								'action' => 'newTweet',
								'actionData' => array(
									'wallname' => $wallname,
									'tweetData' => $tweetData,
								),
							);
							$this->_zmqSocket->send(json_encode($pushData));
							break;
						}
					}
					unset($tweetData, $hashTags);
				}
			}
			$this->_updateWallist();
			usleep(300000);
		}
	}

	private function _updateWallist()
	{
		if(time() - $this->_lastWallUpdate < self::WALL_UPDATE_INTERVAL)
		{
			return true;
		}

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
		return true;
	}
}

$TweetPush = new TweetPush;
$TweetPush->run();