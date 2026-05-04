<?php

use App\Repositories\JobRepositoryInterface;
use App\Repositories\RedisJobRepository;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

beforeEach(function () {
    Redis::connection('testing')->flushdb();

    app()->instance(
        JobRepositoryInterface::class,
        new RedisJobRepository(Redis::connection('testing')),
    );
});

afterEach(fn () => \Mockery::close());
