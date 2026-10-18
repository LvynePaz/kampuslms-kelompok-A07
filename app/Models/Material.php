<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'uploaded_by',
        'title',
        'description',
        'type',
        'file_path',
        'original_name',
        'file_size',
        'mime_type',
        'external_url',
    ];

    protected $appends = [
        'formatted_file_size',
    ];

    /**
     * Hapus berkas fisik saat record materi dihapus dari database.
     */
    protected static function booted(): void
    {
        static::deleting(function (Material $material) {
            if ($material->file_path) {
                if (Storage::disk('local')->exists($material->file_path)) {
                    Storage::disk('local')->delete($material->file_path);
                }
                if (Storage::disk('public')->exists($material->file_path)) {
                    Storage::disk('public')->delete($material->file_path);
                }
            }
        });
    }

    public function getFormattedFileSizeAttribute(): ?string
    {
        if (empty($this->file_path) || $this->file_size === null) {
            return null;
        }

        $bytes = (int) $this->file_size;

        return $bytes >= 1024 * 1024
            ? number_format($bytes / (1024 * 1024), 1) . ' MB'
            : number_format($bytes / 1024, 1) . ' KB';
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
