<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Common extends Controller_Template
{	
	public function before()
	{
		parent::before();
		
		// load global template stuff...
	}
	
	public function after()
	{
		parent::after();
	}	
}