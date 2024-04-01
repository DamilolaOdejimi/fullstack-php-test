<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Interfaces\StatusCode;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Interfaces\BatchingTypes;
use App\Interfaces\BatchStatuses;
use App\Jobs\BatchAndProcessHmoOrdersJob;
use App\Models\Batch;
use App\Models\Hmo;
use App\Models\HmoProvider;
use App\Models\Order;
use App\Models\Provider;
use Illuminate\Support\Facades\Validator;

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

        switch ($hmoProvider->hmo->batching_type) {
            case BatchingTypes::SENT_DATE:
                $groupField = 'created_at';
                $groupDate = now()->format("M Y");
                break;

            default:
                $groupField = 'encounter_date';
                $carbonEncounterDate = Carbon\Carbon::parse($request->encounter_date);
                $groupDate = $carbonEncounterDate->format("M Y");
                break;
        }

        $existingBatch = Batch::where('hmo_provider_id', $hmoProvider->id)
            ->where('date', $groupDate)
            ->where('status', BatchStatuses::CLOSED)->first();

        if($existingBatch){
            return response()->json([
                'status' => StatusCode::BAD_REQUEST,
                'message' => 'Invalid batch date used. Batch already exists and is closed!',
                'data' => [],
            ], StatusCode::VALIDATION);
        }


        // Create batch
        $batch = Batch::firstOrcreate([
            'hmo_provider_id' => $hmoProvider->id,
            'label' => $hmoProvider->provider()->name . " " . $groupDate,
            'date' => $groupDate->format("M Y")
        ]);

        Order::createMultiple($request, $hmoProvider->id, $batch->id);

        // Fire a job to update orders with batch ID
        BatchAndProcessHmoOrdersJob::dispatch($batch, $hmoProvider->id, $groupField);
        $response = [
            'status' => StatusCode::OK,
            'message' => 'Successful',
            'data' => [],
        ];
        return response()->json($response, StatusCode::OK);
    }
}
