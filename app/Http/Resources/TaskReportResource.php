<?php

namespace App\Http\Resources;

use App\Models\TaskReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TaskReport */
class TaskReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{id:int,status:string,download_url:?string,created_at:?string}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'download_url' => $this->status === 'completed' ? route('reports.download', $this->resource) : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
