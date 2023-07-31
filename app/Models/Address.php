<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Address extends Model
{
    use HasFactory, HasUuids, HasTimestamps;

    protected $table = 'address';
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        'id',
        'street',
        'street_complement',
        'city',
        'zip_code',
        'country',
    ];

    /**
     * Get the owning addressable model.
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

}
