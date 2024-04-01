<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HmoProvider extends Model
{
    protected $fillable = ['hmo_id', 'provider_id', 'status'];

    // hmo
    public function hmo()
    {
        return $this->belongsTo(Hmo::class);
    }

    // provider
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    // batches
    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    // orders
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
