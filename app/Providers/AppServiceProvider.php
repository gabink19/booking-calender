<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $settings = Setting::all();
            $settArr = [];
            foreach ($settings as $setting) {
                $settArr[$setting->key_name] = $setting->value;
            }
            $view->with('settings', $settArr);
        });
    }
}
