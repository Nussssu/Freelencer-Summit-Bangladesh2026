<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Admin-editable lead emails: admin_notification and user_confirmation. Bodies are Markdown with {placeholders}. */
class EmailTemplate extends Model
{
    protected $guarded = ['id'];

    public const PLACEHOLDERS = ['{name}', '{email}', '{type}', '{reference}', '{fields}', '{site}'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }
}
