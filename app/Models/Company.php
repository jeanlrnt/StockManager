<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $table = 'companies';

    protected $with = ['address'];

    protected $fillable = [
        'name',
        'phone',
        'email',
        'website',
        'industry',
        'number_of_employees',
        'annual_revenue',
        'description',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * @return MorphOne
     */
    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function customer(): morphOne
    {
        return $this->morphOne(Customer::class, 'customerable');
    }

    public function provider(): morphOne
    {
        return $this->morphOne(Provider::class, 'providerable');
    }

}
