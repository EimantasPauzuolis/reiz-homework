<?php

namespace App\Http\Resources;

use App\Data\Job;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Job */
class JobResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'urls' => $this->urls,
            'selectors' => $this->selectors,
            'container' => $this->container,
            'render_js' => $this->renderJs,
            'results' => $this->results,
            'errors' => $this->errors,
            'created_at' => $this->createdAt->toIso8601String(),
            'completed_at' => $this->completedAt?->toIso8601String(),
        ];
    }
}
