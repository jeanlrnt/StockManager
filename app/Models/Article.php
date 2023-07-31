<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory, HasUuids, HasTimestamps;

    protected $fillable = [
        'title',
        'slug',
        'customer_id',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id', 'id');
    }
}
