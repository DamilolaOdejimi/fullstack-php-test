<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'encounter_date', 'hmo_provider_id', 'items', 'total_amount',
        'batch_id', 'process_status'
    ];

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

    // batch
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
