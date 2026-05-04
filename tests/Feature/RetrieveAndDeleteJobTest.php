<?php

use App\Data\Job;
use App\Enums\JobStatus;
use App\Repositories\JobRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

it('returns 404 for unknown job', function () {
    $this->getJson('/api/jobs/'.Str::uuid())->assertStatus(404);
});

it('returns the job snapshot', function () {
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
    $repo->recordResult($job->id, 'https://x.test', ['t' => 'Hello']);

    $response = $this->getJson("/api/jobs/{$job->id}");

    $response->assertOk()
        ->assertJsonPath('data.status', 'completed');

    expect($response->json('data.results')['https://x.test']['t'])->toBe('Hello');
});

it('deletes a job', function () {
    $repo = app(JobRepositoryInterface::class);
    $job = new Job(
        id: (string) Str::uuid(),
        status: JobStatus::Pending,
        urls: [],
        selectors: [],
        results: [],
        errors: [],
        createdAt: CarbonImmutable::now(),
    );
    $repo->save($job);

    $this->deleteJson("/api/jobs/{$job->id}")->assertNoContent();
    expect($repo->find($job->id))->toBeNull();
});
