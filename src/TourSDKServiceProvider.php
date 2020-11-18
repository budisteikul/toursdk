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
        $this->loadViewsFrom(__DIR__.'/views', 'toursdk');
        $this->loadMigrationsFrom(__DIR__.'/migrations/2020_11_17_133006_create_categories_table.php');
		$this->loadMigrationsFrom(__DIR__.'/migrations/2020_11_17_222702_create_products_table.php');
		$this->loadMigrationsFrom(__DIR__.'/migrations/2020_11_17_225332_create_category_product_table.php');
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
    }
}
