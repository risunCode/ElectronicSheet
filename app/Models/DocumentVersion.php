<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'document_id',
        'version_number',
        'content',
        'content_json',
        'word_count',
        'change_summary',
        'created_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'content_json' => 'array',
            'version_number' => 'integer',
            'word_count' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
