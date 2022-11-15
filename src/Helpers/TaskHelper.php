<?php
namespace budisteikul\toursdk\Helpers;
use Google\Cloud\Tasks\V2\CloudTasksClient;
use Google\Cloud\Tasks\V2\HttpMethod;
use Google\Cloud\Tasks\V2\HttpRequest;
use Google\Cloud\Tasks\V2\Task;

class TaskHelper {

	public function __construct() {
		$this->task = new \stdClass();
		$this->tw->projectId = env("TASK_PROJECT_ID");
    	$this->tw->locationId = env("TASK_LOCATION_ID");
    	$this->tw->queueId = env("TASK_QUEUE_ID");
    	$this->tw->url = env("TASK_URL");
	}

	public function createTask($payload)
	{
		$client = new CloudTasksClient();
		$queueName = $client->queueName($this->tw->projectId, $this->tw->locationId, $this->tw->queueId);

		$httpRequest = new HttpRequest();
		$httpRequest->setUrl($this->tw->url);
		$httpRequest->setHttpMethod(HttpMethod::POST);
		if (isset($payload)) {
    		$httpRequest->setBody($payload);
		}

		$task = new Task();
		$task->setHttpRequest($httpRequest);
		$response = $client->createTask($queueName, $task);
	}

}
?>