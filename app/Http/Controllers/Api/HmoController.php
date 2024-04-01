<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\StatusCode;
use App\Models\Hmo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HmoController extends Controller
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
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        // Validation
        $providerId = $request->query('provider_id') ?? null;
        $validator = Validator::make($request->only('provider'), ['providerId' => 'nullable|exists:providers,id']);
		if ($validator->fails()) {
            return response()->json([
                'status' => StatusCode::VALIDATION,
                'message' => 'Validation failed',
                'data' => $validator->getMessageBag()->toArray(),
            ], StatusCode::VALIDATION);
        }

        $providerId = $request->providerId ?? null;
        $hmos = Hmo::when($providerId, function($query) use ($providerId) {
            $query->whereHas('hmoProviders', function($query) use ($providerId){
                $query->where('provider_id', $providerId)
                    ->where('status', true);
            });
        })->get();
        
        $response = [
            'status' => StatusCode::OK,
            'message' => 'Successful',
            'data' => $hmos,
        ];
        return response()->json($response, StatusCode::OK);
    }
}
