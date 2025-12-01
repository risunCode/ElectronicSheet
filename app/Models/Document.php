<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'type',
        'status',
        'storage_path',
        'page_size',
        'page_orientation',
        'owner_id',
        'folder_id',
        'template_id',
        'file_id',
        'content',
        'content_json',
        'word_count',
        'page_count',
        'last_edited_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'content_json' => 'array',
            'word_count' => 'integer',
            'page_count' => 'integer',
            'last_edited_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($document) {
            if (empty($document->uuid)) {
                $document->uuid = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->orderBy('version_number', 'desc');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'document_tags');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'loggable');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    // Helper methods
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getLatestVersion()
    {
        return $this->versions()->first();
    }

    public function createVersion(string $changeSummary = null): DocumentVersion
    {
        $latestVersion = $this->getLatestVersion();
        $newVersionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;

        return $this->versions()->create([
            'version_number' => $newVersionNumber,
            'content' => $this->content,
            'content_json' => $this->content_json,
            'word_count' => $this->word_count,
            'change_summary' => $changeSummary,
            'created_by' => auth()->id(),
        ]);
    }
}
