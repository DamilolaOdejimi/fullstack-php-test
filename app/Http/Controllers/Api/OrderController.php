<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Interfaces\StatusCode;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Interfaces\BatchStatuses;
use App\Jobs\BatchAndProcessHmoOrdersJob;
use App\Models\Batch;
use App\Models\HmoProvider;
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
            'batch_date' => [
                'required', 'date', 'date_format:"M Y"',
                'before_or_equal:' . now()->subMonth()->endOfMonth()->toDateString()
            ],
            'hmo_provider_id' => ['required', Rule::exists('hmo_providers')->where(function ($query) {
                return $query->where('status', true);
            })]
        ]);
		if ($validator->fails()) {
            return response()->json([
                'status' => StatusCode::VALIDATION,
                'message' => 'Validation failed',
                'data' => $validator->getMessageBag()->toArray(),
            ], StatusCode::VALIDATION);
        }

        $existingBatch = Batch::where('date', $request->batch_date)->where('status', BatchStatuses::OPEN)->first();
        if($existingBatch){
            return response()->json([
                'status' => StatusCode::BAD_REQUEST,
                'message' => 'Invalid batch date used. Batch already exists and is closed!',
                'data' => [],
            ], StatusCode::VALIDATION);
        }

        // Check batching type of HMO
        $hmoProviderInstance = HmoProvider::with('hmo')->find($request->hmo_provider_id);
        $providerName = $hmoProviderInstance->provider()->name;

        // Create batch
        $batch = Batch::create([
            'hmo_provider_id' => $hmoProviderInstance->id,
            'label' => "$providerName $request->batch_date",
            'date' => $request->batch_date
        ]);


        // Fire a job to update orders with batch ID
        BatchAndProcessHmoOrdersJob::dispatch($batch, $hmoProviderInstance);
        $response = [
            'status' => StatusCode::OK,
            'message' => 'Successful',
            'data' => [],
        ];
        return response()->json($response, StatusCode::OK);
    }
}
