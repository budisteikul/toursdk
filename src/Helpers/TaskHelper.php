<?php
namespace budisteikul\toursdk\Helpers;
use Google\Cloud\Tasks\V2\CloudTasksClient;
use Google\Cloud\Tasks\V2\HttpMethod;
use Google\Cloud\Tasks\V2\HttpRequest;
use Google\Cloud\Tasks\V2\Task;

class TaskHelper {

	
	public function create($payload)
	{
		$client = new CloudTasksClient();
		$queueName = $client->queueName(env("TASK_PROJECT_ID"), env("TASK_LOCATION_ID"), env("TASK_QUEUE_ID"));

		$httpRequest = new HttpRequest();
		$httpRequest->setUrl(env("TASK_URL"));
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