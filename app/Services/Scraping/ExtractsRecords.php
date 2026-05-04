<?php

namespace App\Services\Scraping;

use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

trait ExtractsRecords
{
    /**
     * @param  array<string,string> $selectors
     * @return array<string,?string>|array<int,array<string,?string>>
     */
    private function extract(Crawler $crawler, array $selectors, ?string $container): array
    {
        if ($container === null) {
            return $this->extractRecord($crawler, $selectors);
        }

        $records = [];
        $crawler->filter($container)->each(function (Crawler $node) use ($selectors, &$records) {
            $records[] = $this->extractRecord($node, $selectors);
        });

        return $records;
    }

    /**
     * @param  array<string,string> $selectors
     * @return array<string,?string>
     */
    private function extractRecord(Crawler $scope, array $selectors): array
    {
        $out = [];

        foreach ($selectors as $name => $expr) {
            [$css, $attr] = $this->splitSelector($expr);
            $node = $scope->filter($css);

            if ($node->count() === 0) {
                $out[$name] = null;

                continue;
            }

            $first = $node->first();
            $out[$name] = $attr !== null
                ? $first->attr($attr)
                : trim($first->text());
        }

        return $out;
    }

    /** @return array{0:string,1:?string} */
    private function splitSelector(string $expr): array
    {
        if (! Str::contains($expr, '@')) {
            return [trim($expr), null];
        }

        return [
            trim(Str::beforeLast($expr, '@')),
            trim(Str::afterLast($expr, '@')),
        ];
    }
}
