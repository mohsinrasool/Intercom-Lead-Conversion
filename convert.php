<html>
<head>
</head>
<body>
<?php

include 'vendor/autoload.php';
include 'config.php';
include 'db_layer.php';

set_time_limit(0);

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
	var $PAT = PAT;


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


		$currentPage = 0;
		$errorCount = 0;
		$emptyPages = 0;
		$isEmptyPage = true;
		do {
			$currentPage++;

			try {
				$leads = $this->client->leads->getLeads(['created_since'=>'1','page'=>$currentPage]);
			}
			catch(\Exception $ex) {
				echo "Current Page: ". $currentPage."\n <br/>";
				echo $ex->getMessage();
				break;
			}


			foreach ($leads->contacts as $lead) {

				$response = null;

				if(empty($lead->email))
					continue;
				else if($db->isExists('cron_log','email',$lead->email, ' anonymous = 0 ')){
					// skip if an exception was occured for this email
					continue;

				} else {
					$isEmptyPage = false;
					try {

						$convertData = array('contact'=>array('user_id'=>$lead->user_id),"user" => array( "email" => $lead->email ));
						$response = $this->client->leads->convertLead($convertData);

					} catch(\Exception $ex) {

						$record = array(
							'id' => $lead->id,
							'user_id' => $lead->user_id,
							'email' => $lead->email,
							'name' => $lead->name,
							'created_at' => $lead->created_at,
							'anonymous' => 0,
							'response' => $ex->getMessage().json_encode($response),
						);
						$db->saveRecord($record, 'cron_log');

						$errorCount++;

						echo "Exception for: ". $lead->email." at page # ". $currentPage."\n <br/>";
						echo $ex->getMessage();
						continue;

					}
				}

				$record = array(
					'id' => $lead->id,
					'user_id' => $lead->user_id,
					'email' => $lead->email,
					'name' => $lead->name,
					'created_at' => $lead->created_at,
					'anonymous' => 1,
					'response' => json_encode($response),
				);
				$db->saveRecord($record, 'cron_log');
				// break;
			}
			if($errorCount > 10)
				break;
			if($emptyPages++ > 5)
				break;
			// break;
		} while(isset($leads->pages) && $currentPage < $leads->pages->total_pages);
	}

	public function convertFromDB($numThreads = 5)
	{
		$db = new Database('localhost','root','','bodyrock_intercom');

		$dbLeads = $db->getResults('*','leads', 'contact_id="" AND error IS NULL');

		if(!$dbLeads) {
			echo 'No lead available to convert.';
		}

		for ($i=0; $i < $db->numRecords($dbLeads); $i++) {

			$contact = null;
			$dbLead = $db->getObject($dbLeads);

			// $leads = $this->client->leads->getLeads(['email'=>$dbLead->email]);

			// if(empty($leads->contacts))
			// 	continue;

			// echo "<h1>Leads</h1>";
			// print_r($dbLead);
			// foreach($leads->contacts as $lead) {
			// echo "<p>";
			// echo "Lead: ".$dbLead->email." ";

				// if($contact == null)
				try {
					$contact = $this->client->users->getUsers(['email'=>$dbLead->email]);
				} catch(\Exception $ex) {
					echo "CONTACT ERROR:".$ex->getMessage()."</p>";
					$db->query("update leads set error='".$ex->getMessage()."' where id='".$dbLead->id."'; ");
					// exit;
					continue;
				}

				// echo "<h1>Contact</h1>";
				// print_r($contact);

				$convertData = array("contact" => array("user_id" => $dbLead->id));
				if(!empty($contact)) {
					$convertData['user'] = array("user_id" => $contact->id);
					// echo "Contact: ".$contact->id." ";
				}

				// echo "Conversion: ".json_encode($convertData)." ";

				// echo "<h1>Convert Data</h1>";
				// print_r($convertData);
				try {

					$response = $this->client->leads->convertLead($convertData);

					if(!empty($response->id)) {
						$db->query("update leads set contact_id='".$response->id."' where id='".$dbLead->id."'; ");
						// echo '--CONVERTED!--';
					}

					$db->query("update leads set response='".json_encode($response)."', request='".json_encode($convertData)."' where id='".$dbLead->id."'; ");
				} catch(\Exception $ex) {
					// echo "<h1>Conversion Response</h1>";
					// print_r($response);
					echo "CONVERSION ERROR:".$ex->getMessage()."</p>";
					$db->query("update leads set error='".json_encode($ex->getMessage())."', request='".json_encode($convertData)."' where id='".$dbLead->id."'; ");
					// exit;
				}
			// echo "</p>";
			// flush();
			// exit;
			// exit;
			if($i >= 1000)
				exit;
			// }
		}
	}

} // END BodyRockConversion class



$conv = new BodyRockLeadConversion();
$conv->fetchAndConvert();

?>

</body>
</html>

