<?php
namespace Jittli;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Twitterwall implements WampServerInterface
{
	private $_Redis = null;
	private $_rate = 0; // tweets per second
	private $_tweetCount = 0;
	protected $subscriptions = array();
	
	public function __construct($Redis)
	{
		$this->_Redis = $Redis;
		$this->_Redis->del('jiwalls');
	}

	public function onSubscribe(ConnectionInterface $conn, $Topic)
	{
        // add wall to a storage array:
        if (!array_key_exists($Topic->getId(), $this->subscriptions))
		{
            $this->subscriptions[$Topic->getId()] = $Topic;
        }
		$this->_updateWalllist();
		$this->_actionUpdateServerinfo();
    }

	/**
	 * This method is called whenever a message is pushed into the server using ZMQ.
	 * @param type $dataEncoded Json Encode data passed by ZMQ-	 
	 */
    public function onApiEvent($dataEncoded)
	{
        $eventData = json_decode($dataEncoded, true);
		$action = $eventData['action'];
		$actionData = $eventData['actionData'];
		switch($action)
		{
			case 'newTweet':
				$this->_actionNewTweet($actionData);
			break;
		
			case 'serverEvents':
				$topicId = 'serverEvents';
				if (!array_key_exists($topicId, $this->subscriptions))
				{
					return;
				}
				$Topic = $this->subscriptions[$topicId];
				$Topic->broadcast($actionData);
			break;
			
			default:
				// unknown action
			break;
		}
    }
	
	/**
	 * This method is called periodically every 60 seconds.
	 */
	public function onEveryMinute()
	{
		$this->_rate = round($this->_tweetCount / 60, 2);
		$this->_Redis->hSet('jiStats', 'lastMinute', $this->_tweetCount);
		$this->_tweetCount = 0;
		$this->_updateWalllist();
		$this->_actionUpdateServerinfo();
	}
   
    public function onUnSubscribe(ConnectionInterface $conn, $Topic)
	{
    }
    public function onOpen(ConnectionInterface $conn) {
    }
    public function onClose(ConnectionInterface $conn) {
		$this->_updateWalllist();
		$this->_actionUpdateServerinfo();
    }
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->close();
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {
    }
	
	private function _actionNewTweet($actionData)
	{
		$this->_tweetCount++;
		$wallname = $actionData['wallname'];
		$topicId = 'wall_' . $wallname;
		$tweetData = TweetHelper::prepareTweet($actionData['tweetData']);		

        // check if any client is connected to wall:
        if (!array_key_exists($topicId, $this->subscriptions))
		{
            return;
        }
        $Topic = $this->subscriptions[$topicId];
        $Topic->broadcast(array(
			'wallname' => $wallname,
			'tweet' => $tweetData
		));
	}
	
	private function _actionUpdateServerinfo()
	{
		if(!array_key_exists('serverEvents', $this->subscriptions))
		{
            return;
        }
		$connectedClients = count($this->subscriptions['serverEvents']);
		$activeWalls = 0;
		foreach($this->subscriptions as $topicId => $Topic)
		{
			if(strpos($topicId, 'wall_') !== false && count($Topic) > 0)
			{
				$activeWalls++;
			}
		}	
		$Topic = $this->subscriptions['serverEvents'];
		$Topic->broadcast(array(
			'action' => 'updateServerinfo',
			'actionData' => array(
				'connectedClients' => $connectedClients,
				'activeWalls' => $activeWalls,
				'tweetRate' => $this->_rate,
			)
		));
	}


	private function _updateWalllist()
	{
		// update client count:
		foreach($this->subscriptions as $topicId => $Topic)
		{
			if(strpos($topicId, 'wall_') === false)
			{
				continue;
			}
			$clientCount = count($this->subscriptions[$topicId]);
			$wallname = str_replace('wall_', '', $topicId);
			$this->_Redis->hSet('jiwalls', $wallname, $clientCount);
		}
		
		// remove unused walls:
		$redisWalls = $this->_Redis->hGetAll('jiwalls');
		foreach($redisWalls as $wallname => $curCount)
		{
			$topicId = 'wall_' . $wallname;
			if(!isset($this->subscriptions[$topicId]) || $curCount == 0)
			{
				$this->_Redis->hDel('jiwalls', $wallname);
			}			
		}
		
		return true;
	}
}