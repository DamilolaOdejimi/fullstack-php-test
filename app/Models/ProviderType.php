<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderType extends Model
{
    protected $fillable = ['name', 'code'];

    // providers
    public function providers()
    {
        return $this->hasMany(Provider::class);
    }
}