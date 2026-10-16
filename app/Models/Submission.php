<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'user_id',
        'file_path',
        'original_name',
        'file_size',
        'note',
        'submitted_at',
        'is_late',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Submission $submission) {
            if (empty($submission->file_path)) {
                return;
            }

            $disk = Storage::disk('local');
            if ($disk->exists($submission->file_path) && ! $disk->delete($submission->file_path)) {
                throw new RuntimeException("Failed to delete submission file [{$submission->file_path}].");
            }
        });
    }

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'is_late' => 'boolean',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function grade(): HasOne
    {
        return $this->hasOne(Grade::class);
    }
}
