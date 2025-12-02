<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Transaction;
use App\Models\User;

class TransactionController extends Controller
{
    public function generateReport(Request $request): JsonResponse
    {
        // Check if it's a report request
        if ($request->has(['start_date', 'end_date'])) {
            // Validate report request
            $validator = Validator::make($request->all(), [
                'org_key_id' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed for report request',
                    'errors' => $validator->errors()
                ], 422);
            }

            try {
                $orgKeyId = $request->input('org_key_id');
                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');

                // Verify if org_key_id exists in users table
                $userExists = User::where('org_key_id', $orgKeyId)->exists();

                if (!$userExists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Organization key not found'
                    ], 404);
                }

                // Generate report
                $transactions = Transaction::where('org_key_id', $orgKeyId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'desc')
                    ->get();

                $summary = [
                    'total_transactions' => $transactions->count(),
                    'total_amount' => $transactions->sum('amount'),
                    'average_amount' => $transactions->avg('amount'),
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'Report generated successfully',
                    'data' => [
                        'summary' => $summary,
                        'transactions' => $transactions->map(function ($transaction) {
                            return [
                                'id' => $transaction->id,
                                'org_key_id' => $transaction->org_key_id,
                                'amount' => $transaction->amount,
                                'name' => $transaction->name,
                                'purpose_reason' => $transaction->purpose_reason,
                                'comment' => $transaction->comment,
                                'created_at' => $transaction->created_at
                            ];
                        })
                    ]
                ], 200);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate report',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
    }


    /**
     * Create a new transaction
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'org_key_id' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'name' => 'required|string|max:255',
            'purpose_reason' => 'required|string|max:255',
            'comment' => 'nullable|string',
            'payment_method' => 'required|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $orgKeyId = $request->input('org_key_id');
            $amount = $request->input('amount');
            $name = $request->input('name');
            $purposeReason = $request->input('purpose_reason');
            $comment = $request->input('comment');
            $paymentMethod = $request->input('payment_method');

            // Verify if org_key_id exists in users table
            $userExists = User::where('org_key_id', $orgKeyId)->exists();

            if (!$userExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Organization key not found'
                ], 404);
            }

            // Generate unique TID in format TXN1001, TXN1002, etc.
            $lastTransaction = Transaction::orderBy('id', 'desc')->first();
            $nextId = $lastTransaction ? $lastTransaction->id + 1 : 1;
            $tid = 'TXN' . $nextId;

            // Check if TID already exists (though very unlikely)
            while (Transaction::where('tid', $tid)->exists()) {
                $nextId++;
                $tid = 'TXN' . $nextId;
            }

            // Calculate bank fee (2.5% of amount)
            $bankFee = round($amount * 0.025, 2);
            $amountReceived = round($amount - $bankFee, 2);

            // Create transaction
            $transaction = Transaction::create([
                'org_key_id' => $orgKeyId,
                'tid' => $tid,
                'name' => $name,
                'amount' => $amount,
                'bank_fee' => $bankFee,
                'amount_received' => $amountReceived,
                'payment_method' => $paymentMethod,
                'purpose_reason' => $purposeReason,
                'comment' => $comment
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaction created successfully',
                'data' => [
                    'org_key_id' => $transaction->org_key_id,
                    'tid' => $transaction->tid,
                    'name' => $transaction->name,
                    'amount' => $transaction->amount,
                    'bankFee' => $transaction->bank_fee,
                    'amountReceived' => $transaction->amount_received,
                    'paymentMethod' => $transaction->payment_method,
                    'purpose_reason' => $transaction->purpose_reason,
                    'comment' => $transaction->comment,
                    'created_at' => $transaction->created_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific transaction by ID
     */
    public function show(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'org_key_id' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $orgKeyId = $request->input('org_key_id');

            $transaction = Transaction::where('org_key_id', $orgKeyId)->get();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaction retrieved successfully',
                'data' => $transaction
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function all(Request $request): JsonResponse
    {
        try {
            // Load user and business profile
            $transactions = Transaction::with(['user.businessProfile'])->get();

            if ($transactions->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No transactions found',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'All transactions retrieved successfully',
                'data' => [
                    'count' => $transactions->count(),
                    'transactions' => $transactions
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}