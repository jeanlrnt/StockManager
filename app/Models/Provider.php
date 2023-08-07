<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use LaravelArchivable\Archivable;

class Provider extends Model
{
    use HasFactory, HasUuids, HasTimestamps, SoftDeletes, Archivable;

    protected $table = 'providers';
    protected $with = ['providerable'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at', 'archived_at'];

    protected $fillable = [
        'name',
        'email',
        'phone'
    ];

    /**
     * Get the provider's articles.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'provider_id', 'id');
    }

    /**
     * A customer is a company
     * @return MorphTo
     */
    public function providerable(): MorphTo
    {
        return $this->morphTo();
    }
}
