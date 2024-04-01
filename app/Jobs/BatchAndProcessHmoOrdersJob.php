<?php

namespace App\Jobs;

use App\Interfaces\ProcessStatusTypes;
use App\Models\HmoProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BatchAndProcessHmoOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    protected $hmoProviderId;

    /**
     * @var object
     */
    protected $batch;

    /**
     * @var string
     */
    protected $groupField;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(object $batch, int $hmoProviderId, string $groupField)
    {
        $this->batch = $batch;
        $this->hmoProviderId = $hmoProviderId;
        $this->groupField = $groupField;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $hmoProvider = HmoProvider::find($this->hmoProviderId);

        $hmoProvider->orders()->where($this->groupField, '>=', \Carbon\CarbonImmutable::parse($this->batch->date)->startOfMonth())
            ->where($this->groupField, '<=', \Carbon\CarbonImmutable::parse($this->batch->date)->endOfMonth())
            ->chunk(10, function ($orders) {
                foreach ($orders as $order) {
                    try {
                        // Dummy function
                        $this->processOrder();

                        $order->process_status = ProcessStatusTypes::PROCESSED;
                        $order->save();
                    } catch (\Exception $ex) {
                        \Log::error("Unable to process order - " . $order->order_number, $ex);
                        $order->process_status = ProcessStatusTypes::FAILED;
                        $order->save();
                    }
                }
            });
    }


    protected function processOrder() : bool
    {
        // A dummy of the actual processsing of the order
        return true;
    }
}
