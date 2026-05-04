<?php

namespace App\Services\Scraping;

use Symfony\Component\Panther\Client;
use Throwable;

final class PantherScraper implements ScraperInterface
{
    use ExtractsRecords;

    public function __construct(
        private readonly string $seleniumHost,
        private readonly string $waitFor = 'body',
        private readonly int $timeoutSeconds = 30,
    ) {}

    public function scrape(string $url, array $selectors, ?string $container = null): array
    {
        $client = Client::createSeleniumClient($this->seleniumHost);

        try {
            $client->request('GET', $url);
            $crawler = $client->waitFor($container ?? $this->waitFor, $this->timeoutSeconds);

            return $this->extract($crawler, $selectors, $container);
        } catch (Throwable $e) {
            throw new ScrapeException("Failed to scrape {$url}: {$e->getMessage()}", 0, $e);
        } finally {
            $client->quit();
        }
    }
}
