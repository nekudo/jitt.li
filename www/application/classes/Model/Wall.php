<?php
include_once DOCROOT . '../libs/class.tweet_helper.php';

class Model_Wall extends Model_Database
{	
	public function getLatestTweets($hashtag)
	{		
		$hashtag = urlencode($hashtag);		
		$searchquery = 'https://api.twitter.com/1.1/search/tweets.json?q=' . $hashtag . '&count=10&include_entities=true&result_type=recent';
		$curl = curl_init($searchquery);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_NOBODY, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 5);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($curl, CURLOPT_USERAGENT, 'jitt.li curl request');
		curl_setopt($curl, CURLOPT_REFERER, 'http://jitt.li');
		$result = curl_exec($curl);		
		curl_close($curl);
		$tweets = json_decode($result, true);		
		unset($result);		
				
		if(empty($tweets))
		{
			return false;
		}
		
		$preparedTweets = null;
		foreach(array_keys($tweets['results']) as $key)
		{
			$preparedTweets[$key] = TweetHelper::prepareTweet($tweets['results'][$key]);				
		}		
		unset($tweets);
		return $preparedTweets;
	}

	/**
	 * Logs a hit/request of a wall to database.
	 * @param $wallname The name of the requested wall.
	 * @return bool True if hit was logged false otherwise.
	 */
	public function logHit($wallname)
	{
		if(empty($wallname))
		{
			return false;
		}

		$this->_db->query(Database::INSERT, "INSERT INTO stats_wallhits (wallname,hits) VALUES(" . $this->_db->quote($wallname) . ",1) ON DUPLICATE KEY UPDATE hits = hits + 1");
		return true;
	}
}