<?php

namespace App\Jobs;

use App\Interfaces\BatchingTypes;
use App\Interfaces\ProcessStatusTypes;
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
     * @var int
     */
    protected $hmoProviderId;

    /**
     * @var object
     */
    protected $batch;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(object $batch, int $hmoProviderId)
    {
        $this->batch = $batch;
        $this->hmoProviderId = $hmoProviderId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $hmoProvider = HmoProvider::find($this->hmoProviderId)->hmo;
        $groupField = null;
        switch ($hmoProvider->hmo->batching_type) {
            case BatchingTypes::SENT_DATE:
                $groupField = 'created_at';
                break;

            default:
                $groupField = 'encounter_date';
                break;
        }

        $hmoProvider->orders()->where($groupField, '>=', \Carbon\CarbonImmutable::parse($this->batch->date)->startOfMonth())
            ->where($groupField, '<=', \Carbon\CarbonImmutable::parse($this->batch->date)->endOfMonth())
            ->chunk(200, function ($orders) {
                foreach ($orders as $order) {
                    // Fire a job to Mimick processing for each order
                    ProcessSingleOrderJob::dispatch($order);
                }
            });
    }
}
