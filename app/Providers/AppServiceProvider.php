<?php

namespace App\Providers;

use App\Repositories\JobRepositoryInterface;
use App\Repositories\RedisJobRepository;
use App\Services\Scraping\PantherScraper;
use App\Services\Scraping\ScraperFactory;
use App\Services\Scraping\StaticHtmlScraper;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            JobRepositoryInterface::class,
            fn () => new RedisJobRepository(Redis::connection()),
        );

        $this->app->singleton(ScraperFactory::class, function ($app) {
            return new ScraperFactory(
                new StaticHtmlScraper($app->make(HttpFactory::class)),
                new PantherScraper(
                    seleniumHost: config('scraping.selenium_host'),
                    waitFor: config('scraping.wait_for_selector'),
                    timeoutSeconds: (int) config('scraping.request_timeout'),
                ),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
