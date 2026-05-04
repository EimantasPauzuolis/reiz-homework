<?php

namespace App\Jobs;

use App\Repositories\JobRepositoryInterface;
use App\Services\Scraping\ScrapeException;
use App\Services\Scraping\ScraperFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class ScrapeUrlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 5;

    /** @param  array<string,string> $selectors */
    public function __construct(
        public readonly string $jobId,
        public readonly string $url,
        public readonly array $selectors,
        public readonly ?string $container = null,
        public readonly bool $renderJs = false,
    ) {}

    public function handle(ScraperFactory $scrapers, JobRepositoryInterface $jobsRepository): void
    {
        try {
            $data = $scrapers->resolve($this->renderJs)
                ->scrape($this->url, $this->selectors, $this->container);

            $jobsRepository->recordResult($this->jobId, $this->url, $data);
        } catch (ScrapeException $e) {
            $jobsRepository->recordError($this->jobId, $this->url, $e->getMessage());
        }
    }

    public function failed(Throwable $e): void
    {
        app(JobRepositoryInterface::class)
            ->recordError($this->jobId, $this->url, $e->getMessage());
    }
}
