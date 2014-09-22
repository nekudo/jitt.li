<?php
namespace Jittli;

class TweetHelper
{
	public static function prepareTweet($tweetData)
	{
		if(!is_array($tweetData))
		{
			$tweetData = json_decode($tweetData, true);
		}
		if(empty($tweetData))
		{
			return false;
		}			
		
		$preparedTweet = array();
		if(isset($tweetData['user']))
		{
			$preparedTweet['user_nick'] = $tweetData['user']['screen_name'];
			$preparedTweet['user_name'] = $tweetData['user']['name'];
			$preparedTweet['user_pic'] = $tweetData['user']['profile_image_url'];
		}
		else
		{
			$preparedTweet['user_nick'] = $tweetData['from_user'];
			$preparedTweet['user_name'] = $tweetData['from_user_name'];
			$preparedTweet['user_pic'] = $tweetData['profile_image_url'];
		}
		$preparedTweet['text'] = $tweetData['text'];
		$preparedTweet['date'] = date("Y-m-d H:i:s", strtotime($tweetData['created_at']));
		
		// replace plaintext urls with links:
		if(isset($tweetData['entities']['urls']))
		{
			foreach(array_keys($tweetData['entities']['urls']) as $i)
			{
				$preparedTweet['text'] = str_replace($tweetData['entities']['urls'][$i]['url'], '<a href="'.$tweetData['entities']['urls'][$i]['url'].'" target="_blank">'. $tweetData['entities']['urls'][$i]['url'].'</a>', $preparedTweet['text']);
			}
		}
		// replace nicknames with links:
		if(isset($tweetData['entities']['user_mentions']))
		{
			foreach(array_keys($tweetData['entities']['user_mentions']) as $i)
			{
				$preparedTweet['text'] = str_replace('@'.$tweetData['entities']['user_mentions'][$i]['screen_name'], '@<a href="https://twitter.com/#!/'.$tweetData['entities']['user_mentions'][$i]['screen_name'].'" target="_blank">'.$tweetData['entities']['user_mentions'][$i]['screen_name'].'</a>', $preparedTweet['text']);
			}
		}
		// replace hashtags with links:
		if(isset($tweetData['entities']['hashtags']))
		{
			foreach(array_keys($tweetData['entities']['hashtags']) as $i)
			{				
				$preparedTweet['text'] =  str_replace('#'.$tweetData['entities']['hashtags'][$i]['text'], '#<a href="https://twitter.com/#!/search?q=%23'.$tweetData['entities']['hashtags'][$i]['text'].'" target="_blank">'.$tweetData['entities']['hashtags'][$i]['text'].'</a>', $preparedTweet['text']);
			}
		}
		unset($tweetData);
		
		return $preparedTweet;
	}
}