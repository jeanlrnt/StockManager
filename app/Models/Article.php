<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use LaravelArchivable\Archivable;

class Article extends Model
{
    use HasFactory, HasUuids, HasTimestamps, SoftDeletes, Archivable;

    protected $fillable = [
        'title',
        'slug',
        'provider',
    ];

    protected $hidden = [
        'slug',
        'provider',
        'created_at',
        'updated_at',
        'deleted_at',
        'archived_at'
    ];

    /**
     * @return BelongsTo
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider', 'id');
    }
}
