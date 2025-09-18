<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BillingLog;

class BillingLogController extends Controller
{ 
    public function GetByUserIDorMerchantID(Request $request)
    {
        $user_id        = $request->user_id;
        $merchant_id    = $request->merchant_id;

        $logs = BillingLog::where('merchant_id', $merchant_id)->orwhere('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        if ($logs->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No billing logs found for this merchant.',
                'code' => 404
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Billing logs retrieved successfully',
            'code' => 200
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'user_id'           => 'required|exists:users,id',
            'merchant_id'       => 'required|string',
            'period_start'      => 'required|date',
            'period_end'        => 'required|date',
            'base_calls'        => 'required|integer|min:0',
            'overage_calls'     => 'required|integer|min:0',
            'overage_charge'    => 'required|numeric|min:0',
            'due_date'          => 'required|date',
            'is_paid'           => 'boolean',
        ]);

        $log = BillingLog::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Billing log created successfully',
            'code' => 200
        ]);
    }
}
