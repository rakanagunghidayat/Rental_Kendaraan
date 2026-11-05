<?php

namespace App\Providers;

use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

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
        View::composer('*', function (ViewInstance $view): void {
            $request = request();

            if (! $request->attributes->has(WebsiteSetting::class)) {
                $request->attributes->set(WebsiteSetting::class, WebsiteSetting::current());
            }

            $view->with('websiteSettings', $request->attributes->get(WebsiteSetting::class));
        });
    }
}
