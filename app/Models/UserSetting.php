<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    protected $fillable = [
        'user_id',
        'default_font',
        'default_font_size',
        'auto_save',
        'auto_save_interval',
        'sidebar_collapsed',
        'files_view',
        'documents_per_page',
        'email_notifications',
    ];

    protected function casts(): array
    {
        return [
            'default_font_size' => 'integer',
            'auto_save' => 'boolean',
            'auto_save_interval' => 'integer',
            'sidebar_collapsed' => 'boolean',
            'documents_per_page' => 'integer',
            'email_notifications' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
