<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class File extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'original_name',
        'extension',
        'mime_type',
        'size',
        'path',
        'disk',
        'folder_id',
        'owner_id',
        'thumbnail_path',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'metadata' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($file) {
            if (empty($file->uuid)) {
                $file->uuid = (string) Str::uuid();
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function isImage(): bool
    {
        return in_array($this->extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
    }

    public function isDocument(): bool
    {
        return in_array($this->extension, ['doc', 'docx', 'pdf', 'txt', 'rtf']);
    }

    public function isSpreadsheet(): bool
    {
        return in_array($this->extension, ['xls', 'xlsx', 'csv']);
    }

    public function isPresentation(): bool
    {
        return in_array($this->extension, ['ppt', 'pptx']);
    }
}
