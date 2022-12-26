<?php
return [
    'disks' => [
	   'gcs' => [
            'driver' => 'gcs',
            'key_file_path' => env('GOOGLE_CLOUD_KEY_FILE', null), 
            'key_file' => [], 
            'project_id' => env('GOOGLE_CLOUD_PROJECT_ID', 'your-project-id'), 
            'bucket' => env('GOOGLE_CLOUD_STORAGE_BUCKET', 'your-bucket'),
            'path_prefix' => env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX', ''), 
            'storage_api_uri' => env('GOOGLE_CLOUD_STORAGE_API_URI', null), 
            'apiEndpoint' => env('GOOGLE_CLOUD_STORAGE_API_ENDPOINT', null), 
            'visibility' => 'public', 
            'metadata' => ['cacheControl'=> 'public,max-age=86400'], 
        ]
    ]
]
?>