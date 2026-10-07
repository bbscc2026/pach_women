<?php

namespace App\Providers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Setting;
use App\Support\Cart;
use App\Support\StoredFiles;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(Cart::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Values saved in Admin → Site settings override config/shop.php.
        Setting::applyToConfig();

        // Delete replaced/removed images from storage.
        StoredFiles::cleanUp(Category::class, 'image');
        StoredFiles::cleanUp(Banner::class, 'image');
    }
}
