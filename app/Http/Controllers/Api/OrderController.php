<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Interfaces\StatusCode;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
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
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Batch and process .
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function processOrders(Request $request)
    {
        // Validate batch date & provider
		$validator = Validator::make($request->all(), [
            'provider_name' => ['required', 'string', 'exists:providers,name'],
            'hmo_code' => ['required', 'string', 'exists:hmos,code'],
            'encounter_date' => [
                'required', 'date', 'date_format:"Y-m-d"',
                'before_or_equal:' . now()->subMonth()->endOfMonth()->toDateString()
            ],
            'orders' => ['required', 'array', 'min:1'],
            'orders.*.name' => ['string', 'required'],
            'orders.*.quantity' => ['integer', 'required'],
            'orders.*.unit_price' => ['float', 'required'],
            'orders.*.amount' => ['float', 'required'],
            'order_total' => ['float', 'required']
        ]);
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

        $encounterDate = Carbon\Carbon::parse($request->encounter_date);

        $existingBatch = Batch::where('hmo_provider_id', $hmoProvider->id)
            ->where('date', $encounterDate->format("M Y"))
            ->where('status', BatchStatuses::OPEN)->first();

        if($existingBatch){
            return response()->json([
                'status' => StatusCode::BAD_REQUEST,
                'message' => 'Invalid batch date used. Batch already exists and is closed!',
                'data' => [],
            ], StatusCode::VALIDATION);
        }

        // Create batch
        $batch = Batch::create([
            'hmo_provider_id' => $hmoProvider->id,
            'label' => $hmoProvider->provider()->name . " " . $encounterDate->format("M Y"),
            'date' => $encounterDate->format("M Y")
        ]);


        Order::createMultiple($request, $hmoProvider->id, $batch->id);

        // Fire a job to update orders with batch ID
        BatchAndProcessHmoOrdersJob::dispatch($batch, $hmoProvider->id);
        $response = [
            'status' => StatusCode::OK,
            'message' => 'Successful',
            'data' => [],
        ];
        return response()->json($response, StatusCode::OK);
    }
}
