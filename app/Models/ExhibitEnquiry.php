<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExhibitEnquiry extends Model
{
    protected $fillable = [
        'company_name', 'website', 'industry', 'head_office',
        'full_name', 'designation', 'email', 'phone', 'utm',
    ];

    protected function casts(): array
    {
        return ['utm' => 'array'];
    }
}
