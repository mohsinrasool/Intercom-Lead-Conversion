<?php

include 'vendor/autoload.php';

use Intercom\IntercomClient;

/**
 * undocumented class
 *
 * @package default
 * @author
 **/
class BodyRockLeadConversion
{

	/**
	 * Personal Access Token
	 *
	 * @var string
	 **/
	var $PAT = 'dG9rOmNkNDBhZjAyX2Q1OWJfNGQzY184YWZlX2U2MmY4ZjVkYWIyZDoxOjA=';


	/**
	 * undocumented function
	 *
	 * @return void
	 * @author
	 **/
	function __construct()
	{
		$this->client = new IntercomClient($this->PAT, null);

	}

	public function run($value='')
	{
		print_r($this->client->leads->getLeads([]));
		// $users = $this->client->users->getUsers([]);
		// print_r($users);
	}

} // END BodyRockConversion class



$conv = new BodyRockLeadConversion();
$conv->run();

?>
