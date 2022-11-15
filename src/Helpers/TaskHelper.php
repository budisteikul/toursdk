<?php
namespace budisteikul\toursdk\Helpers;
use Google\Cloud\Tasks\V2\CloudTasksClient;
use Google\Cloud\Tasks\V2\HttpMethod;
use Google\Cloud\Tasks\V2\HttpRequest;
use Google\Cloud\Tasks\V2\Task;
use Google\Cloud\Tasks\V2\Queue;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class TaskHelper {

	public static function delete($json)
	{
		$data = json_decode($json);
		$queue_id = $data->queue_id;
        $project = env("TASK_PROJECT_ID");
        $location = env("TASK_LOCATION_ID");

        $client = new CloudTasksClient();
        $queueName = $client->queueName($project, $location, $queue_id);
        $client->deleteQueue($queueName);
	}

	public static function create($payload)
	{
		
		$queue_id = Uuid::uuid4()->toString();
		$project = env("TASK_PROJECT_ID");
		$location = env("TASK_LOCATION_ID");

		$payload->queue_id = $queue_id;
		
		$client = new CloudTasksClient();
		$queueName = $client->queueName($project, $location, $queue_id);
		$locationName = $client->locationName($project, $location);

		$httpRequest = new HttpRequest();
		$httpRequest->setUrl(env("TASK_URL"));
		$httpRequest->setHttpMethod(HttpMethod::POST);
		if (isset($payload)) {
    		$httpRequest->setBody(json_encode($payload));
		}

		$queue = new Queue([
			'name' => $queueName
		]);
		$queue->setName($queueName);
		$client->createQueue($locationName, $queue);

		$task = new Task();
		$task->setHttpRequest($httpRequest);
		$response = $client->createTask($queueName, $task);

		return $response;
	}

}
?>