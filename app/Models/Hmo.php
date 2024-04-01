<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hmo extends Model
{
    protected $fillable = ['name', 'code', 'hmo_email', 'batching_type', 'status'];

    protected $casts = [
        'status' => 'boolean'
    ];

    /**
     * Get Providers of HMO through hmo_providers table
     */
    public function providers()
    {
        return $this->belongsToMany(Provider::class, HmoProvider::class, 'hmo_id', 'provider_id')
            ->withPivot('status');
    }

    /**
     * Get HMO Providers Pivot Table through hmo_providers table
     */
    public function hmoProviders()
    {
        return $this->hasMany(HmoProvider::class);
    }

    /**
     * Get HMO Providers through hmo_providers table
     */
    public function activeProviders()
    {
        return $this->belongsToMany(Provider::class, HmoProvider::class, 'hmo_id', 'provider_id')
            ->withPivot('status')->wherePivot('status', true);
    }

    /**
     *
     */
    public function batches()
    {
        return $this->hasManyThrough(Batch::class, HmoProvider::class);
    }

    /**
     *
     */
    public function orders()
    {
        return $this->hasManyThrough(Order::class, HmoProvider::class);
    }
}
