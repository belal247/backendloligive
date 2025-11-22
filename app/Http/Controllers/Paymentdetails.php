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

        // Pick the correct fields based on approved status
        $fields = [];
        if (isset($data['paymentReturnFields'])) {
            if (!empty($data['paymentReturnFields']['approveFields']) && $approved) {
                $fields = $data['paymentReturnFields']['approveFields'];
            } elseif (!empty($data['paymentReturnFields']['declineFields'])) {
                $fields = $data['paymentReturnFields']['declineFields'];
            }
        }

        $custom = [];
        foreach ($fields as $f) {
            $custom[$f['name']] = $f['value'];
        }

        // Save transaction
        try {
            $payment = Transaction::create([
                'name' => $custom['name'] ?? null,
                'comment' => $custom['ssl_result_message'] ?? $data['responseFields']['ssl_result_message'] ?? null,
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
            return response()->json([
                'success' => false,
                'message' => "Server error: " . $e->getMessage()
            ]);
        }
    }




}
