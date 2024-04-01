<?php

namespace App\Jobs;

use App\Interfaces\BatchingTypes;
use App\Interfaces\ProcessStatusTypes;
use App\Models\Batch;
use App\Models\HmoProvider;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessSingleOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var object
     */
    protected $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Object $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /**
         * A dummy of the actual processsing of the order
         */

        $orderInstance = Order::find($this->order->id);
        try {
            $this->process();

            $orderInstance->process_status = ProcessStatusTypes::PROCESSED;
            $orderInstance->save();
        } catch (\Exception $ex) {
            \Log::error("Unable to process order - " . $orderInstance->order_number, $ex);
            $orderInstance->process_status = ProcessStatusTypes::FAILED;
            $orderInstance->save();
        }
    }

    function process() : bool
    {
        // A dummy of the actual processsing of the order

        return true;
    }
}
