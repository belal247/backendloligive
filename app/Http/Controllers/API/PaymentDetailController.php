<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\PaymentDetail;

class PaymentDetailController extends Controller
{
    public function storeDetails(Request $request)
    {
        // Validation rules
        $request->validate([
            'merchant_id'       => 'required|string',
            'email'            => 'required|email', // Added email validation
            'billing_address'  => 'required|string',
            'city'             => 'required|string',
            'zipcode'         => 'required|string',
            'country'         => 'required|string',
            
            // Optional fields validation
            'payment_method'   => 'nullable|string',
            'payment_result'   => 'nullable|array',
            'payment_result.locationId' => 'nullable|string',
            'payment_result.sourceId' => 'nullable|string',
            'payment_result.idempotencyKey' => 'nullable|string',
            'payment_result.paymentType' => 'nullable|string|in:ach,credit_card,other', // Added enum validation
            'payment_result.amount' => 'nullable|numeric', // Changed to numeric validation
        ]);

        // Check if merchant exists
        $user = User::where('merchant_id', $request->merchant_id)->first();
        
        if (!$user) {
            return response()->json([
                'code'    => 404,
                'message' => 'Merchant not found with the provided merchant_id.'
            ], 404);
        }

        // Prepare data for saving
        $data = $request->only([
            'email', 
            'billing_address', 
            'city', 
            'zipcode', 
            'country',
            'payment_method',
            'payment_result'
        ]);

        // Handle empty payment_result if needed
        if (empty($data['payment_result'])) {
            $data['payment_result'] = null;
        }

        // Update or create record
        $detail = PaymentDetail::updateOrCreate(
            ['merchant_id' => $request->merchant_id], // Search criteria
            $data // Data to update/create
        );

        // Return success response
        return response()->json([
            'code'    => 200,
            'message' => 'Payment detail saved successfully.',
            'data'    => $detail
        ], 200);
    }
}