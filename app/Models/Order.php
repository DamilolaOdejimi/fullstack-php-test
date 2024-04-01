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

    public static function createMultiple(object $data, int $hmoProviderId, int $batchId)
    {
        $data = collect($data)->map(function($order) use($data, $hmoProviderId, $batchId) {
            $order->order_number = (string) \Str::uuid();
            $order->encounter_date = $data->encounter_date;
            $order->hmo_provider_id = $hmoProviderId;
            $order->items = json_encode($data->orders);
            $order->total_amount = $data->order_total;
            $order->batch_id = $batchId;
        })->toArray();
        
        DB::table('orders')->insert($data);
    }
}
