<?php

use App\Services\Scraping\StaticHtmlScraper;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
    $this->scraper = new StaticHtmlScraper(app(HttpFactory::class));
});

it('extracts text and attributes from a single page', function () {
    Http::fake([
        'https://x.test/*' => Http::response('<html><body>
            <h1>The Title</h1>
            <a class="cta" href="/buy">Buy now</a>
            <meta name="description" content="A great page">
        </body></html>'),
    ]);

    $result = $this->scraper->scrape('https://x.test/page', [
        'title' => 'h1',
        'cta' => 'a.cta@href',
        'desc' => 'meta[name=description]@content',
        'absent' => '.nope',
    ]);

    expect($result)->toBe([
        'title' => 'The Title',
        'cta' => '/buy',
        'desc' => 'A great page',
        'absent' => null,
    ]);
});

it('returns one record per container node when container is set', function () {
    Http::fake([
        'https://shop.test/*' => Http::response('<html><body>
            <article class="product"><h3 title="A">a</h3><span class="price">£1</span></article>
            <article class="product"><h3 title="B">b</h3><span class="price">£2</span></article>
            <article class="product"><h3 title="C">c</h3><span class="price">£3</span></article>
        </body></html>'),
    ]);

    $result = $this->scraper->scrape(
        url: 'https://shop.test/list',
        selectors: ['title' => 'h3@title', 'price' => '.price'],
        container: 'article.product',
    );

    expect($result)->toBe([
        ['title' => 'A', 'price' => '£1'],
        ['title' => 'B', 'price' => '£2'],
        ['title' => 'C', 'price' => '£3'],
    ]);
});

it('returns an empty list when container matches nothing', function () {
    Http::fake(['*' => Http::response('<html><body><p>nope</p></body></html>')]);

    expect($this->scraper->scrape(
        url: 'https://x.test/empty',
        selectors: ['t' => 'h1'],
        container: '.product',
    ))->toBe([]);
});
