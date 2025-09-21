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
            'purpose_reason' => 'required|string|max:500',
            'comment' => 'nullable|string|max:1000'
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

            // Verify if org_key_id exists in users table (optional)
            $userExists = User::where('org_key_id', $orgKeyId)->exists();
            
            if (!$userExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Organization key not found'
                ], 404);
            }

            // Create transaction
            $transaction = Transaction::create([
                'org_key_id' => $orgKeyId,
                'amount' => $amount,
                'name' => $name,
                'purpose_reason' => $purposeReason,
                'comment' => $comment
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaction created successfully',
                'data' => [
                    'org_key_id' => $transaction->org_key_id,
                    'amount' => $transaction->amount,
                    'name' => $transaction->name,
                    'purpose_reason' => $transaction->purpose_reason,
                    'comment' => $transaction->comment,
                    'created_at' => $transaction->created_at,
                    'id' => $transaction->id
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
}