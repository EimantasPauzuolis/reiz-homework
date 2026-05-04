<?php

namespace App\Actions;

use App\Data\Job;
use App\Enums\JobStatus;
use App\Jobs\ScrapeUrlJob;
use App\Repositories\JobRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Str;

final class CreateJob
{
    public function __construct(
        private readonly JobRepositoryInterface $jobsRepository,
        private readonly Dispatcher $bus,
    ) {}

    /**
     * @param  array<int,string>    $urls
     * @param  array<string,string> $selectors
     */
    public function execute(
        array $urls,
        array $selectors,
        ?string $container = null,
        bool $renderJs = false,
    ): Job {
        $job = new Job(
            id: (string) Str::uuid(),
            status: JobStatus::Pending,
            urls: array_values(array_unique($urls)),
            selectors: $selectors,
            results: [],
            errors: [],
            createdAt: CarbonImmutable::now(),
            container: $container,
            renderJs: $renderJs,
        );

        $this->jobsRepository->save($job);

        foreach ($job->urls as $url) {
            $this->bus->dispatch(new ScrapeUrlJob(
                jobId: $job->id,
                url: $url,
                selectors: $selectors,
                container: $container,
                renderJs: $renderJs,
            ));
        }

        return $job;
    }
}
