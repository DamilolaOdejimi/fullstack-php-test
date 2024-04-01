<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $fillable = ['label', 'hmo_provider_id', 'status'];

    // hmo_provider
    public function hmoProvider()
    {
        return $this->belongsTo(HmoProvider::class);
    }

    // hmo
    public function hmo()
    {
        return $this->hmoProvider()->hmo();
    }

    // provider
    public function provider()
    {
        return $this->hmoProvider()->provider();
    }

    // provider
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
