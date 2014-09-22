<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Wall extends Controller_Common
{
	public $template = 'wall';
	
	public function action_index()
	{
		// redirect to home if wallname is invalid:
		$wallname = $this->request->param('wallname');
		if(empty($wallname) || strlen($wallname) < 3)
		{
			$this->redirect('/', 301);
		}
		$Model_Wall = new Model_Wall;
		$Model_Wall->logHit($wallname);
		$search = array('&', '<', '>', '"', '\'', '/');
		$replace = array('&amp;', '&lt;', '&gt;', '&quot;', '&#x27;', '&#x2F;');
		$wallnameOut = str_replace($search, $replace, $wallname);	
		
		
		/*
		 * 
		if($_SERVER['REMOTE_ADDR'] === '46.246.121.229')
		{
			$ModelWall = new Model_Wall;
			var_dump($ModelWall->getLatestTweets($wallname));
		}
		 * 
		 */
		
		$this->template->wallname = $wallnameOut;		
		$this->template->meta_title = '#' . $wallnameOut . ' Twitterwall | jitt.li';
		$this->template->meta_keywords = 'twitterwall twitter wall live realtime websockets';
		$this->template->meta_description = 'Shows all ' . $wallnameOut . ' tweets updated in realtime.';		
	}
}