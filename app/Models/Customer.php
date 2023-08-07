<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, HasUuids, HasTimestamps, SoftDeletes;

    protected $table = 'customer';
    protected $with = ['customerable'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        'first_name',
        'last_name',
        'company_name',
        'email',
        'phone',
    ];

    /**
     * A customer is a person or a company
     * @return MorphTo
     */
    public function customerable(): MorphTo
    {
        return $this->morphTo();
    }
}
