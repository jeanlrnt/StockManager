<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Provider extends Model
{
    use HasFactory, HasUuids, HasTimestamps;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address_id',
    ];

    /**
     * Get the provider's address.
     */
    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    /**
     * Get the provider's articles.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'provider_id', 'id');
    }
}
