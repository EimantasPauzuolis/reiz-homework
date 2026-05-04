<?php

namespace App\Services\Scraping;

use Illuminate\Http\Client\Factory as HttpFactory;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

final class StaticHtmlScraper implements ScraperInterface
{
    use ExtractsRecords;

    public function __construct(private readonly HttpFactory $http) {}

    public function scrape(string $url, array $selectors, ?string $container = null): array
    {
        try {
            $html = $this->http->timeout(30)->get($url)->throw()->body();
        } catch (Throwable $e) {
            throw new ScrapeException("Failed to fetch {$url}: {$e->getMessage()}", 0, $e);
        }

        return $this->extract(new Crawler($html, $url), $selectors, $container);
    }
}
