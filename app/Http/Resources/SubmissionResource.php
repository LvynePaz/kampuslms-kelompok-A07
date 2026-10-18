<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assignment_id' => $this->assignment_id,
            'user_id' => $this->user_id,
            'original_name' => $this->original_name,
            'file_size' => $this->file_size,
            'file_size_human' => $this->file_size === null
                ? null
                : ((int) $this->file_size >= 1024 * 1024
                    ? number_format($this->file_size / (1024 * 1024), 1) . ' MB'
                    : number_format($this->file_size / 1024, 1) . ' KB'),
            'note' => $this->note,
            'submitted_at' => $this->submitted_at,
            'is_late' => $this->is_late,
            'student' => UserResource::make($this->whenLoaded('student')),
            'grade' => GradeResource::make($this->whenLoaded('grade')),
        ];
    }
}
