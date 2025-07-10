<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => Str::title(str_replace('_', ' ', $this->status)),
            'due_date' => Carbon::parse($this->due_date)->format('d-M-Y'),
            'project_id' => (int) $this->project_id,
        ];
    }
}
