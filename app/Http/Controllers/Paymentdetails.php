<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class Paymentdetails extends Controller
{
    public function success(Request $request)
    {
        $data = $request->all();
        $rawPayload = json_encode($data);

        $approved = $data['approved'] ?? false;
        $status = $approved ? 'APPROVED' : 'DECLINED';

        $fields = $data['paymentReturnFields']['approveFields'] ?? [];
        if (empty($fields)) {
            $fields = $data['paymentReturnFields']['declineFields'] ?? [];
        }

        $custom = [];
        foreach ($fields as $f) {
            if (isset($f['name'], $f['value'])) {
                $custom[$f['name']] = $f['value'];
            }
        }

        try {
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

            return response()->json([
                'success' => true,
                'message' => "Callback processed",
                'data' => $payment
            ]);
        } catch (\Exception $e) {
            // Log the exact error
            \Log::error('Transaction save failed: ' . $e->getMessage(), ['payload' => $data]);
            return response()->json([
                'success' => false,
                'message' => 'Server error: could not save transaction'
            ], 500);
        }
    }



}
