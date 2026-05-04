<?php

namespace App\Data;

use App\Enums\JobStatus;
use Carbon\CarbonImmutable;

final class Job
{
    /**
     * @param  array<int,string>                                                    $urls
     * @param  array<string,string>                                                 $selectors
     * @param  array<string,array<string,?string>|array<int,array<string,?string>>> $results
     * @param  array<string,string>                                                 $errors
     */
    public function __construct(
        public readonly string $id,
        public readonly JobStatus $status,
        public readonly array $urls,
        public readonly array $selectors,
        public readonly array $results,
        public readonly array $errors,
        public readonly CarbonImmutable $createdAt,
        public readonly ?CarbonImmutable $completedAt = null,
        public readonly ?string $container = null,
        public readonly bool $renderJs = false,
    ) {}
}
