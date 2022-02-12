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
        $this->publishes([ __DIR__.'/publish/img' => public_path('img'),], 'budisteikul');
        
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
		
        $this->loadMigrationsFrom(__DIR__.'/migrations/2022_02_12_110221_create_vendors_table.php');
        
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
    }
}
