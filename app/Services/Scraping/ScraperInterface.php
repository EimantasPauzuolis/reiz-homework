<?php

namespace App\Services\Scraping;

interface ScraperInterface
{
    /**
     * @param  array<string,string> $selectors
     * @return array<string,?string>|array<int,array<string,?string>>
     */
    public function scrape(string $url, array $selectors, ?string $container = null): array;
}
