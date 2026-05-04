<?php

use App\Jobs\ScrapeUrlJob;
use App\Repositories\JobRepositoryInterface;
use Illuminate\Support\Facades\Bus;

it('creates a job and dispatches one queue job per URL', function () {
    Bus::fake();

    $payload = [
        'urls' => ['https://example.com', 'https://example.org'],
        'selectors' => ['title' => 'h1', 'desc' => 'meta[name="description"]'],
    ];

    $response = $this->postJson('/api/jobs', $payload);

    $response->assertStatus(202)
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonCount(2, 'data.urls');

    $id = $response->json('data.id');
    expect(app(JobRepositoryInterface::class)->find($id))->not->toBeNull();

    Bus::assertDispatchedTimes(ScrapeUrlJob::class, 2);
});

it('rejects missing urls', function () {
    $this->postJson('/api/jobs', ['selectors' => ['title' => 'h1']])
        ->assertStatus(422)
        ->assertJsonValidationErrors('urls');
});

it('rejects non-URL entries', function () {
    $this->postJson('/api/jobs', [
        'urls' => ['not-a-url'],
        'selectors' => ['title' => 'h1'],
    ])->assertStatus(422)->assertJsonValidationErrors('urls.0');
});

it('persists container and render_js on the created job', function () {
    Bus::fake();

    $payload = [
        'urls' => ['https://shop.test/listing'],
        'container' => 'article.product',
        'render_js' => true,
        'selectors' => ['title' => 'h3@title', 'price' => '.price'],
    ];

    $response = $this->postJson('/api/jobs', $payload);

    $response->assertStatus(202)
        ->assertJsonPath('data.container', 'article.product')
        ->assertJsonPath('data.render_js', true);

    $loaded = app(JobRepositoryInterface::class)->find($response->json('data.id'));
    expect($loaded->container)->toBe('article.product')
        ->and($loaded->renderJs)->toBeTrue();
});
