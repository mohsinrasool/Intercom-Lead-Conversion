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

		$this->db = null;
		if(DB_LOGGING)
			$this->db = new Database(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
	}

	public function fetchAndConvert()
	{


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

				if( empty($lead->email) )
					continue;
				else {
					$isEmptyPage = false;
					try {

						$convertData = array('contact'=>array('user_id'=>$lead->user_id),"user" => array( "email" => $lead->email ));
						$response = $this->client->leads->convertLead($convertData);

					} catch(\Exception $ex) {

						// record exception in database
						if( !empty($this->db) ) {
							$record = array(
								'id' => $lead->id,
								'user_id' => $lead->user_id,
								'email' => $lead->email,
								'name' => $lead->name,
								'created_at' => $lead->created_at,
								'anonymous' => 0,
								'response' => $ex->getMessage().json_encode($response),
							);
							$this->db->saveRecord($record, DB_TABLE);
						}

						$errorCount++;

						echo "Exception for: ". $lead->email." at page # ". $currentPage."\n <br/>";
						echo $ex->getMessage();
						continue;

					}
				}

				// record response in database
				if( !empty($this->db) ) {
					$record = array(
						'id' => $lead->id,
						'user_id' => $lead->user_id,
						'email' => $lead->email,
						'name' => $lead->name,
						'created_at' => $lead->created_at,
						'anonymous' => 1,
						'response' => json_encode($response),
					);
					$this->db->saveRecord($record, DB_TABLE);
				}

			}

			// break if there are repeated exceptions
			if($errorCount > 10)
				break;

			// Do not parse all the pages **required because created_since API parameter is not working
			if($emptyPages++ > 5)
				break;

		} while(isset($leads->pages) && $currentPage < $leads->pages->total_pages);
	}


} // END BodyRockConversion class



$conv = new BodyRockLeadConversion();
$conv->fetchAndConvert();

?>

</body>
</html>

