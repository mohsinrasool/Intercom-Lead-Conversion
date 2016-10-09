<?php

include 'vendor/autoload.php';
include 'db_layer.php';

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

	public function fetchAndConvert()
	{
		$db = new Database('localhost','root','','bodyrock_intercom');

		$totalPages = null;
		$currentPage = 1;
		// $leads = $this->client->leads->getLeads(['created_since'=>'2']);
		// $leads = $this->client->leads->getLeads(['segment_id'=>'57f7c6d08bc828fa6fe2a963']);
		$leads = $this->client->leads->getLeads(['email' => 'nbuckram@gmail.com']);
		print_r($leads);


		foreach ($leads->contacts as $lead) {
			if(empty($lead->email))
				continue;

			$record = array(
				'id' => $lead->id,
				'user_id' => $lead->user_id,
				'email' => $lead->email,
				'name' => $lead->name,
				'created_at' => $lead->created_at,
				'anonymous' => $lead->anonymous,
				);
			$db->saveRecord($record, 'cron_log');
		}
	}

	public function convertFromDB()
	{
		$db = new Database('localhost','root','','bodyrock_intercom');

		$dbLeads = $db->getResults('*','leads', array('contact_id'=>''));

		if(!$dbLeads) {
			echo 'No lead available to convert.';
		}

		for ($i=0; $i < $db->numRecords($dbLeads); $i++) {

			$contact = null;
			$dbLead = $db->getObject($dbLeads);

			// $leads = $this->client->leads->getLeads(['email'=>$dbLead->email]);

			// if(empty($leads->contacts))
			// 	continue;

			echo "<h1>Leads</h1>";
			print_r($dbLead);
			// foreach($leads->contacts as $lead) {

				// if($contact == null)
					$contact = $this->client->users->getUsers(['email'=>$dbLead->email]);

				echo "<h1>Contact</h1>";
				print_r($contact);

				$convertData = array("contact" => array("user_id" => $dbLead->user_id));
				if(!empty($contact))
					$convertData['user'] = array("user_id" => $contact->user_id);

				echo "<h1>Convert Data</h1>";
				print_r($convertData);
				try {

					// $response = $this->client->leads->convertLead($convertData);

					// if(!empty($response->id)) {
					// 	$db->query("update leads set contact_id='".$response->id."' where id='".$dbLead->id."'; ");
					// }

				}catch(\Exception $ex) {

				}
				echo "<h1>Conversion Response</h1>";
				// print_r($response);
				exit;
			// }
		}
	}

} // END BodyRockConversion class



$conv = new BodyRockLeadConversion();
$conv->convertFromDB();

?>
