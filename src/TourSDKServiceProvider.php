<?php

namespace budisteikul\toursdk;

use Illuminate\Support\ServiceProvider;

class TourSDKServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->loadViewsFrom(__DIR__.'/views', 'toursdk');
        $this->loadMigrationsFrom(__DIR__.'/migrations/2020_11_17_133006_create_categories_table.php');
		$this->loadMigrationsFrom(__DIR__.'/migrations/2020_11_17_222702_create_products_table.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations/2020_11_18_151603_create_images_table.php');
		$this->loadMigrationsFrom(__DIR__.'/migrations/2020_11_19_041300_create_channels_table.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations/2020_11_22_160052_create_reviews_table.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations/2020_11_25_125733_create_pages_table.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations/2020_11_19_141154_create_shoppingcarts_table.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations/2020_11_19_141233_create_shoppingcart_products_table.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations/2020_11_19_141242_create_shoppingcart_product_details_table.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations/2020_11_19_141252_create_shoppingcart_questions_table.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations/2020_11_19_141259_create_shoppingcart_question_options_table.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations/2020_11_19_141311_create_shoppingcart_payments_table.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations/2022_04_12_195049_create_vouchers_table.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations/2022_04_13_194552_create_vouchers_products_table.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations/2022_12_04_011639_create_close_outs_table.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations/2022_12_23_220624_create_settings_table.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations/2023_12_07_135250_create_shoppingcart_cancellations_table.php');
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
    }

    protected function registerConfig()
    {
        
        
        

        app()->config["filesystems.disks.gcs"] = [
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
        ];


    }
}
