<?php

namespace App\Services\Scraping;

final class ScraperFactory
{
    public function __construct(
        private readonly ScraperInterface $staticScraper,
        private readonly ScraperInterface $browser,
    ) {}

    public function resolve(bool $renderJs): ScraperInterface
    {
        return $renderJs ? $this->browser : $this->staticScraper;
    }
}
