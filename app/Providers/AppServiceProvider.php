<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

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
            $user = Auth::user();

            $siteSettings = $user
                ? Setting::where('user_id', $user->id)->first()
                : Setting::whereNull('user_id')->first(); // fallback

            $view->with('siteSettings', $siteSettings);
        });

    }
}
