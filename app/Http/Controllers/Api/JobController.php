<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateJob;
use App\Repositories\JobRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobRequest;
use App\Http\Resources\JobResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class JobController extends Controller
{
    public function __construct(
        private readonly CreateJob $createJob,
        private readonly JobRepositoryInterface $jobsRepository,
    ) {}

    public function store(StoreJobRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $job = $this->createJob->execute(
            urls: $validated['urls'],
            selectors: $validated['selectors'],
            container: $validated['container'] ?? null,
            renderJs: (bool) ($validated['render_js'] ?? false),
        );

        return JobResource::make($job)
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function show(string $id): JsonResource
    {
        $job = $this->jobsRepository->find($id)
            ?? throw new NotFoundHttpException("Job {$id} not found.");

        return JobResource::make($job);
    }

    public function destroy(string $id): Response
    {
        return $this->jobsRepository->delete($id)
            ? response()->noContent()
            : throw new NotFoundHttpException("Job {$id} not found.");
    }
}
