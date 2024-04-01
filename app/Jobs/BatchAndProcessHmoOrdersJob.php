<?php

namespace App\Jobs;

use App\Interfaces\BatchingTypes;
use App\Models\Batch;
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
     * @var App\Models\HmoProvider
     */
    protected $hmoProvider;

    /**
     * @var App\Models\Batch
     */
    protected $batch;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Batch $batch, HmoProvider $hmoProvider)
    {
        $this->batch = $batch;
        $this->hmoProvider = $hmoProvider;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $hmo = $this->hmoProvider->hmo;
        $groupField = null;
        switch ($hmo->batching_type) {
            case BatchingTypes::SENT_DATE:
                $groupField = 'created_at';
                break;

            default:
                $groupField = 'encounter_date';
                break;
        }

        $this->hmoProvider->orders()->where($groupField, '>=', \Carbon\CarbonImmutable::parse($this->batch->date)->startOfMonth())
            ->where($groupField, '<=', \Carbon\CarbonImmutable::parse($this->batch->date)->endOfMonth())
            ->chunk(200, function ($orders) {
                foreach ($orders as $order) {
                    $order->batch_id = $this->batch->id;

                    // Fire a job to Mimick processing for each order
                }
            });

        // Mark batch as processed within last job

    }
}
