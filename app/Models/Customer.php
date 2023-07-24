<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    use HasFactory, HasUuids, HasTimestamps;

    protected $table = 'customer';
    protected $with = ['address'];
    protected $hidden = ['address_id', 'created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'company_name',
        'email',
        'phone',
        'address_id',
    ];

    /**
     * A customer has one address
     * @return HasOne
     */
    public function address(): HasOne
    {
        return $this->hasOne(Address::class, 'id', 'address_id');
    }
}
