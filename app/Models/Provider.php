<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $fillable = ['name', 'code', 'location', 'provider_type', 'status'];

    /**
     * Get HMO Providers through hmo_providers table
     */
    public function hmos()
    {
        return $this->belongsToMany(Hmo::class, HmoProvider::class, 'provider_id', 'hmo_id')
            ->withPivot('status');
    }

    // hmo_providers
    public function hmoProviders()
    {
        return $this->hasMany(HmoProvider::class);
    }

    // provider type
    public function type()
    {
        return $this->belongsTo(ProviderType::class);
    }
}
