<?php

namespace App\Http\Controllers\Api;

use App\Models\Hmo;
use App\Models\Batch;
use App\Models\Order;
use App\Models\Provider;
use App\Models\HmoProvider;
use Illuminate\Http\Request;
use App\Interfaces\StatusCode;
use App\Interfaces\BatchingTypes;
use App\Interfaces\BatchStatuses;
use App\Http\Controllers\Controller;
use App\Interfaces\ProcessStatusTypes;
use App\Jobs\BatchAndProcessHmoOrdersJob;
use Illuminate\Support\Facades\Validator;
use App\Notifications\HmoOrderNotification;
use Illuminate\Support\Facades\Notification;

class OrderController extends Controller
{
    /**
     * Batch and process .
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function processOrders(Request $request)
    {
        $rules = [
            'provider_name' => ['required', 'string', 'exists:providers,name'],
            'hmo_code' => ['required', 'string', 'exists:hmos,code'],
            'encounter_date' => [
                'required', 'date', 'date_format:"Y-m-d"',
                'before_or_equal:' . now()->subMonth()->endOfMonth()->toDateString()
            ],
            'order' => ['required', 'array', 'min:1'],
            'order.*.name' => ['string', 'required'],
            'order.*.quantity' => ['integer', 'required'],
            'order.*.unit_price' => ['float', 'required'],
            'order.*.amount' => ['float', 'required'],
            'order_total' => ['float', 'required']
        ];

        // Validate batch date & provider
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
            return response()->json([
                'status' => StatusCode::VALIDATION,
                'message' => 'Validation failed',
                'data' => $validator->getMessageBag()->toArray(),
            ], StatusCode::VALIDATION);
        }

        // Check if HMO is connected to Provider
        $hmo = Hmo::where('code', $request->hmo_code)->first();
        $provider = Provider::where('name', $request->provider_name)->first();
        $hmoProvider = HmoProvider::where('hmo_id', $hmo->id)->where('provider_id', $provider->id)->first();
        if($hmoProvider){
            return response()->json([
                'status' => StatusCode::BAD_REQUEST,
                'message' => 'Provider is not registered with HMO',
                'data' => [],
            ], StatusCode::BAD_REQUEST);
        }

        $batch = $this->setUpBatch($hmoProvider, $request->encounter_date);

        // Process the order
        $order = $this->processOrder($request, $hmoProvider->id, $batch->id);

        Notification::route('mail', $hmo->hmo_email)->notify(new HmoOrderNotification([
            'hmo_name' => $hmo->name,
            'provider_name' => $provider->name,
            'order_number' => $order->order_number
        ]));

        $response = [
            'status' => StatusCode::OK,
            'message' => 'Successful',
            'data' => [],
        ];

        return response()->json($response, StatusCode::OK);
    }


    private function setUpBatch(HmoProvider $hmoProvider, string $encounterDate) {
        // Determine how batching will be done based on preference of HMO
        switch ($hmoProvider->hmo->batching_type) {
            case BatchingTypes::SENT_DATE:
                $groupDate = now()->format("M Y");
                break;

            default:
                $carbonEncounterDate = Carbon\Carbon::parse($encounterDate);
                $groupDate = $carbonEncounterDate->format("M Y");
                break;
        }

        // Exit if batch date/label being used has been closed before hand
        // This feature is to allow multiple order entries be created against a batch
        // and to prevent additional orders when the batch is 'closed'
        $closedBatch = Batch::where('hmo_provider_id', $hmoProvider->id)
            ->where('date', $groupDate)
            ->where('status', BatchStatuses::CLOSED)->first();

        if($closedBatch){
            return response()->json([
                'status' => StatusCode::BAD_REQUEST,
                'message' => 'Invalid batch date used. Batch already exists and is closed!',
                'data' => [],
            ], StatusCode::VALIDATION);
        }

        // Create new batch
        $batch = Batch::firstOrcreate([
            'hmo_provider_id' => $hmoProvider->id,
            'label' => $hmoProvider->provider()->name . " " . $groupDate,
            'date' => $groupDate->format("M Y")
        ]);

        return $batch;
    }

    private function processOrder(object $data, int $hmoProviderId, int $batchId) {
        $order = Order::create([
            'order_number' => (string) \Str::uuid(),
            'encounter_date' => $data->encounter_date,
            'hmo_provider_id' => $hmoProviderId,
            'items' => json_encode($data->orders),
            'total_amount' => $data->order_total,
            'batch_id' => $batchId,
        ]);

        // Dummy function - Trigger hypothetical order processing job
        // ProcessHmoOrdersJob::dispatch($batch, $hmoProvider->id, $groupField);

        return $order;
    }
}
