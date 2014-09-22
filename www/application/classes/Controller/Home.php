<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Home extends Controller_Common
{
	public $template = 'home';
	
	public function action_index()
	{		
		$this->template->meta_title = 'jitt.li - Your realtime twitterwall.';
		$this->template->meta_keywords = 'twitterwall twitter wall live realtime websockets';
		$this->template->meta_description = 'Realtime twitterwall using websockets.';
		
		// redirect to wall if form is submitted:
		$wallname = $this->request->post('wallname');		
		if(!empty($wallname) && strlen($wallname) > 2)
		{
			$this->redirect('/wall/' . $wallname, 301);			
		}
		
		$ModelHome = new Model_Home;						
		//$this->template->recentWalls = $ModelHome->getRecentWalls();
		$this->template->recentWalls = false;
		$this->template->statsData = $ModelHome->getStatsData();
	}
}