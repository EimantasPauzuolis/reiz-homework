<?php

use App\Data\Job;
use App\Enums\JobStatus;
use App\Jobs\ScrapeUrlJob;
use App\Repositories\JobRepositoryInterface;
use App\Services\Scraping\ScrapeException;
use App\Services\Scraping\ScraperFactory;
use App\Services\Scraping\ScraperInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

it('records a result on success', function () {
    $repo = app(JobRepositoryInterface::class);
    $job = new Job(
        id: (string) Str::uuid(),
        status: JobStatus::Pending,
        urls: ['https://x.test'],
        selectors: ['t' => 'h1'],
        results: [],
        errors: [],
        createdAt: CarbonImmutable::now(),
    );
    $repo->save($job);

    $scraper = \Mockery::mock(ScraperInterface::class);
    $scraper->shouldReceive('scrape')
        ->once()
        ->with('https://x.test', ['t' => 'h1'], null)
        ->andReturn(['t' => 'Title']);

    $factory = new ScraperFactory(staticScraper: $scraper, browser: $scraper);

    (new ScrapeUrlJob($job->id, 'https://x.test', ['t' => 'h1']))
        ->handle($factory, $repo);

    expect($repo->find($job->id)->results['https://x.test'])->toBe(['t' => 'Title']);
});

it('records an error when the scraper throws', function () {
    $repo = app(JobRepositoryInterface::class);
    $job = new Job(
        id: (string) Str::uuid(),
        status: JobStatus::Pending,
        urls: ['https://x.test'],
        selectors: ['t' => 'h1'],
        results: [],
        errors: [],
        createdAt: CarbonImmutable::now(),
    );
    $repo->save($job);

    $scraper = \Mockery::mock(ScraperInterface::class);
    $scraper->shouldReceive('scrape')
        ->once()
        ->andThrow(new ScrapeException('boom'));

    $factory = new ScraperFactory(staticScraper: $scraper, browser: $scraper);

    (new ScrapeUrlJob($job->id, 'https://x.test', ['t' => 'h1']))
        ->handle($factory, $repo);

    expect($repo->find($job->id)->errors['https://x.test'])->toBe('boom');
});

it('persists a list of records when container is set', function () {
    $repo = app(JobRepositoryInterface::class);
    $job = new Job(
        id: (string) Str::uuid(),
        status: JobStatus::Pending,
        urls: ['https://shop.test'],
        selectors: ['title' => 'h3@title'],
        results: [],
        errors: [],
        createdAt: CarbonImmutable::now(),
        container: 'article.product',
        renderJs: false,
    );
    $repo->save($job);

    $scraper = \Mockery::mock(ScraperInterface::class);
    $scraper->shouldReceive('scrape')
        ->once()
        ->with('https://shop.test', ['title' => 'h3@title'], 'article.product')
        ->andReturn([
            ['title' => 'Item A'],
            ['title' => 'Item B'],
        ]);

    $factory = new ScraperFactory(staticScraper: $scraper, browser: $scraper);

    (new ScrapeUrlJob(
        jobId: $job->id,
        url: 'https://shop.test',
        selectors: ['title' => 'h3@title'],
        container: 'article.product',
        renderJs: false,
    ))->handle($factory, $repo);

    expect($repo->find($job->id)->results['https://shop.test'])
        ->toBe([['title' => 'Item A'], ['title' => 'Item B']]);
});
