<?php

namespace App\Repositories;

use App\Data\Job;

interface JobRepositoryInterface
{
    public function save(Job $job): void;

    public function find(string $id): ?Job;

    public function delete(string $id): bool;

    /** @param  array<string,?string>|array<int,array<string,?string>> $data */
    public function recordResult(string $id, string $url, array $data): void;

    public function recordError(string $id, string $url, string $message): void;
}
