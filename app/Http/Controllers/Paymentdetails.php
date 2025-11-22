<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class Paymentdetails extends Controller
{
    public function success(Request $request)
    {
        // Convert entire payload to array
        $data = $request->all();

        // Always save raw JSON
        $rawPayload = json_encode($data);

        // APPROVED or DECLINED
        $approved = $data['approved'] ?? false;
        $status = $approved ? 'APPROVED' : 'DECLINED';

        // Extract correct fields based on approval
        $fields = $approved
            ? $data['paymentReturnFields']['approveFields'] ?? []
            : $data['paymentReturnFields']['declineFields'] ?? [];

        // Convert array of name/value pairs into associative array
        $custom = [];
        foreach ($fields as $f) {
            $custom[$f['name']] = $f['value'];
        }

        // Save into DB
        $payment = Transaction::create([
            'name' => $custom['name'] ?? null,
            'comment' => $data['responseFields']['ssl_result_message'] ?? null,
            'org_id' => $custom['org_id'] ?? null,
            'paymentmethod' => $custom['paymentmethod'] ?? null,
            'purpose' => $custom['purpose'] ?? null,
            'amount' => $custom['ssl_amount'] ?? $data['amount'] ?? null,
            'txn_id' => $custom['ssl_txn_id'] ?? null,
            'status' => $status,
            'is_approved' => $approved ? 1 : 0,
            'raw_payload' => $rawPayload
        ]);

        // Return JSON response
        return response()->json([
            'success' => true,
            'message' => 'Payment callback processed',
            'data' => $payment
        ]);
    }

}
