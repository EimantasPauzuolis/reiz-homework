<?php

namespace App\Repositories;

use App\Data\Job;
use App\Enums\JobStatus;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Redis\Connection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class RedisJobRepository implements JobRepositoryInterface
{
    private const RESULT_PREFIX = 'result:';

    private const ERROR_PREFIX = 'error:';

    public function __construct(private readonly Connection $redis) {}

    public function save(Job $job): void
    {
        $key = $this->key($job->id);
        $this->redis->del($key);
        $this->redis->hset($key, 'meta', json_encode([
            'id' => $job->id,
            'urls' => $job->urls,
            'selectors' => $job->selectors,
            'container' => $job->container,
            'render_js' => $job->renderJs,
            'created_at' => $job->createdAt->toIso8601String(),
        ], JSON_THROW_ON_ERROR));
    }

    public function find(string $id): ?Job
    {
        $hash = $this->redis->hgetall($this->key($id));
        if (! Arr::has($hash, 'meta')) {
            return null;
        }

        $meta = json_decode(Arr::get($hash, 'meta'), true, 512, JSON_THROW_ON_ERROR);
        $results = [];
        $errors = [];

        foreach ($hash as $field => $value) {
            if (Str::startsWith($field, self::RESULT_PREFIX)) {
                $results[Str::after($field, self::RESULT_PREFIX)]
                    = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            } elseif (Str::startsWith($field, self::ERROR_PREFIX)) {
                $errors[Str::after($field, self::ERROR_PREFIX)] = $value;
            }
        }

        $completedAtRaw = Arr::get($hash, 'completed_at');

        return new Job(
            id: Arr::get($meta, 'id'),
            status: $this->deriveStatus(count(Arr::get($meta, 'urls')), $results, $errors),
            urls: Arr::get($meta, 'urls'),
            selectors: Arr::get($meta, 'selectors'),
            results: $results,
            errors: $errors,
            createdAt: CarbonImmutable::parse(Arr::get($meta, 'created_at')),
            completedAt: $completedAtRaw !== null ? CarbonImmutable::parse($completedAtRaw) : null,
            container: Arr::get($meta, 'container'),
            renderJs: (bool) Arr::get($meta, 'render_js', false),
        );
    }

    public function delete(string $id): bool
    {
        return (bool) $this->redis->del($this->key($id));
    }

    public function recordResult(string $id, string $url, array $data): void
    {
        $this->redis->hset(
            $this->key($id),
            self::RESULT_PREFIX.$url,
            json_encode($data, JSON_THROW_ON_ERROR),
        );
        $this->stampCompletedIfFinished($id);
    }

    public function recordError(string $id, string $url, string $message): void
    {
        $this->redis->hset($this->key($id), self::ERROR_PREFIX.$url, $message);
        $this->stampCompletedIfFinished($id);
    }

    private function stampCompletedIfFinished(string $id): void
    {
        $key = $this->key($id);
        $hash = $this->redis->hgetall($key);
        if (! Arr::has($hash, 'meta')) {
            return;
        }

        $meta = json_decode(Arr::get($hash, 'meta'), true, 512, JSON_THROW_ON_ERROR);
        $reported = 0;
        foreach ($hash as $field => $_) {
            if (Str::startsWith($field, [self::RESULT_PREFIX, self::ERROR_PREFIX])) {
                $reported++;
            }
        }

        if ($reported >= count(Arr::get($meta, 'urls'))) {
            $this->redis->hsetnx($key, 'completed_at', CarbonImmutable::now()->toIso8601String());
        }
    }

    /**
     * @param  array<string,array<string,?string>|array<int,array<string,?string>>> $results
     * @param  array<string,string>                                                  $errors
     */
    private function deriveStatus(int $totalUrls, array $results, array $errors): JobStatus
    {
        $reported = count($results) + count($errors);

        return match (true) {
            $reported === 0 => JobStatus::Pending,
            $reported < $totalUrls => JobStatus::Running,
            count($results) === 0 => JobStatus::Failed,
            count($errors) === 0 => JobStatus::Completed,
            default => JobStatus::PartiallyCompleted,
        };
    }

    private function key(string $id): string
    {
        return "job:{$id}";
    }
}
