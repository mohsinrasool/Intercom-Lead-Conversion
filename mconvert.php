<html>
<head>
</head>
<body  onload="window.location.reload();">
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

	function make_seed()
	{
	  list($usec, $sec) = explode(' ', microtime());
	  return (float) $sec + ((float) $usec * 100000);
	}

	public function convertFromDB($numThreads = 5)
	{
		$db = new Database('localhost','root','','bodyrock_intercom');

		// $response = $this->client->leads->convertLead(array('contact'=>array('user_id'=>'1c9c0591-7ff1-498e-a581-87e7d6105f13'),"user" => array( "email" => "na3105ta@googlemail.com" )));
		// print_r($response);
		// exit;



		// $contact = $this->client->users->getUsers(['email'=>'allen4454@gmail.com']);
		// var_dump($contact);
		// exit;
		while (1)
		{

			srand(time());

			$id = mt_rand(0, 100442) + mt_rand(1, 88442);
			// $id = 0;

			// echo ($id);
			// exit;
			// if(empty($_GET['sort']))
			// 	return;

			$db->query("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;");
			$dbLeads = $db->getResults('*','leads_part2', ' in_process = 0 ',' rand() ', 0, 1);
			// $dbLeads = $db->getResults('*','leads_part2', ' email =  "sherrys@cinci.rr.com" and in_process = 0 ',NULL, 0, 1);
			$db->query("COMMIT;");

			// print_r($db->numRecords($dbLeads));
			if($db->numRecords($dbLeads) == 0) {

				// $dbLeads = $db->getResults('*','leads_part2', ' in_process = 0 ',NULL, 0, 1);
				// if($db->numRecords($dbLeads) == 0) {
					echo 'x';
					continue;
				// }
				// else
			}

			$dbLead = $db->getObject($dbLeads);
			// print_r($dbLead);
			// print_r($db->numRecords($dbLeads));
			// return;
			$db->query("update leads_part2 set in_process='1' where id='".$dbLead->id."'; ");

			// for ($i=0; $i < $db->numRecords($dbLeads); $i++) {

				$contact = null;

				// $leads = $this->client->leads->getLeads(['email'=>$dbLead->email]);

				// if(empty($leads->contacts))
				// 	continue;

				// echo "<h1>Leads</h1>";
				// print_r($dbLead);
				// foreach($leads->contacts as $lead) {
				// echo "<p>";
				// echo "Lead: ".$dbLead->email." ";

					// if($contact == null)
					// try {
					// 	$contact = $this->client->users->getUsers(['email'=>$dbLead->email]);
					// } catch(\Exception $ex) {

					// 	if(stripos($ex->getMessage(), '404 Not Found') === false ) {
					// 		echo "CONTACT ERROR:".$ex->getMessage()."</p>";
					// 		// $db->query("update leads_part2 set error='".$ex->getMessage()."', in_process='2'  where nid='".$dbLead->nid."'; ");
					// 		// exit;
					// 		continue;
					// 	}
					// 	else{
					// 		// $db->query("update leads_part2 set adjustment='404'  where nid='".$dbLead->nid."'; ");
					// 	}
					// }

					// echo "<h1>User</h1>";
					// print_r($contact);

					$convertData = array('contact'=>array('user_id'=>$dbLead->id),"user" => array( "email" => $dbLead->email ));

					// echo "Conversion: ".json_encode($convertData)." ";

					// echo "<h1>Convert Data</h1>";
					// print_r($convertData);
					// exit;
					try {

						$response = $this->client->leads->convertLead($convertData);

						if(!empty($response->id)) {
							$db->query("update leads_part2 set contact_id='".$response->id."', in_process='3'  where id='".$dbLead->id."'; ");
							echo '.';
						}

						$db->query("update leads_part2 set response='".json_encode($response)."', request='".json_encode($convertData)."', in_process='3'  where id='".$dbLead->id."'; ");
					} catch(\Exception $ex) {
						echo "CONVERSION ERROR:".$ex->getMessage()."</p>";
						$db->query("update leads_part2 set error='".json_encode($ex->getMessage())."', request='".json_encode($convertData)."', in_process='3'  where id='".$dbLead->id."'; ");
						// exit;
					}
					// echo "<h1>Conversion Response</h1>";
					// print_r($response);
				// echo "</p>";
				// flush();
				// exit;
				// exit;
				// if($i >= 1000)
				// 	exit;
				// }
				// exit;

		}
	}

} // END BodyRockConversion class


// class AsyncWebRequest extends Thread {
//     public $url;
//     public $data;

//     public function __construct($url) {
//         $this->url = $url;
//     }

//     public function run() {
//         if (($url = $this->url)) {

//             $this->data = file_get_contents($url);
//         } else
//             printf("Thread #%lu was not provided a URL\n", $this->getThreadId());
//     }
// }

$conv = new BodyRockLeadConversion();
$conv->convertFromDB();

?>


<script type="text/javascript">



</script>

</body>
</html>

