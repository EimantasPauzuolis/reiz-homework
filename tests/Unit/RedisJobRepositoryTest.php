<?php

use App\Data\Job;
use App\Enums\JobStatus;
use App\Repositories\JobRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

it('saves, finds and deletes', function () {
    $repo = app(JobRepositoryInterface::class);
    $job = new Job(
        id: (string) Str::uuid(),
        status: JobStatus::Pending,
        urls: ['u'],
        selectors: ['s' => 'h1'],
        results: [],
        errors: [],
        createdAt: CarbonImmutable::now(),
    );

    $repo->save($job);
    expect($repo->find($job->id))->not->toBeNull();
    expect($repo->delete($job->id))->toBeTrue();
    expect($repo->find($job->id))->toBeNull();
});

it('marks the job complete after the last URL is recorded', function () {
    $repo = app(JobRepositoryInterface::class);
    $job = new Job(
        id: (string) Str::uuid(),
        status: JobStatus::Pending,
        urls: ['a', 'b'],
        selectors: ['s' => 'h1'],
        results: [],
        errors: [],
        createdAt: CarbonImmutable::now(),
    );
    $repo->save($job);
    $repo->recordResult($job->id, 'a', ['s' => 'A']);
    expect($repo->find($job->id)->status)->toBe(JobStatus::Running);

    $repo->recordResult($job->id, 'b', ['s' => 'B']);
    expect($repo->find($job->id)->status)->toBe(JobStatus::Completed);
});
