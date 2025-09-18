<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\CardScan;
use App\Models\Subscription;
use App\Models\OldSubscription;
use App\Models\BillingLog;

class CardScanController extends Controller
{
    public function updateCardScan(Request $request)
    {
        // Validate request parameters
        $validator = Validator::make($request->all(), [
            'merchant_id'        => 'required|string',
            'merchant_key'       => 'required|string',
            'card_number_masked' => 'required|string',
            'status'             => 'required|in:success,failure',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Find the user by merchant ID
        $user = User::where('merchant_id', $request->merchant_id)->first();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'code'    => 404,
                'message' => 'No user found for this merchant.'
            ], 404);
        }

        // Handle trial users
        if ($user->on_trial) {
            if (now()->gt($user->trial_ends_at) || $user->trial_calls_remaining <= 0) {
                $user->on_trial = false;
                $user->save();

                return response()->json([
                    'status'  => false,
                    'code'    => 403,
                    'message' => 'Trial expired or API limit exceeded.'
                ], 403);
            }

            $user->decrement('trial_calls_remaining');

            $scan = CardScan::create([
                'user_id'            => $user->id,
                'merchant_id'        => $request->merchant_id,
                'merchant_key'       => $request->merchant_key,
                'card_number_masked' => $request->card_number_masked,
                'status'             => $request->status,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Card scan saved and trial usage updated.',
                'data'    => $scan
            ], 200);
        }

        // Handle subscription users
        $subscription = Subscription::where('merchant_id', $request->merchant_id)
            ->latest()
            ->first();

        if (!$subscription) {
            return response()->json([
                'status'  => false,
                'code'    => 403,
                'message' => 'No active subscription found.'
            ], 403);
        }

        // Check billing overdue > 30 days
        $billing = BillingLog::where('merchant_id', $request->merchant_id)->latest()->first();

        if ($billing && !$billing->is_paid && now()->gt($billing->due_date) && now()->diffInDays($billing->due_date, false) > 30) {
            return response()->json([
                'status'  => false,
                'message' => 'Subscription blocked due to unpaid invoice.'
            ], 403);
        }

        // Track subscription usage
        $limit = $subscription->api_calls_limit;
        $subscription->api_calls_used += 1;

        if ($subscription->api_calls_used > $limit) {
            $subscription->overage_calls += 1;
        }

        $subscription->save();

        $scan = CardScan::create([
            'user_id'            => $user->id,
            'merchant_id'        => $request->merchant_id,
            'merchant_key'       => $request->merchant_key,
            'card_number_masked' => $request->card_number_masked,
            'status'             => $request->status,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Card scan saved and subscription usage updated.',
            'data'    => $scan
        ], 200);
    }

    public function getCardScans(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'           => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation Error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = User::where('merchant_id', $request->id)->first();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'code'    => 404,
                'message' => 'No user found for this merchant.'
            ], 404);
        }

        $subscription = Subscription::where('merchant_id', $request->id)
            ->latest()
            ->first();

        // ✅ Subscription exists
        if ($subscription) {
            // ✅ Get the card scan
            $scan = CardScan::where('merchant_id', $request->id)
            ->latest()
            ->get();
    
            return response()->json([
                'status'  => true,
                'message' => 'Retrieve Card Scans record against merchant_id.',
                'data'    => $scan
            ], 200);
        }
        else {
            return response()->json([
                'status'  => false,
                'message' => 'There is no subscription purchased.',
                'data'    => NULL
            ], 400);
        }

    }

    public function getOldSubscriptions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('merchant_id', $request->id)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'message' => 'No user found for this merchant.'
            ], 404);
        }

        // Get both old and current subscriptions
        $oldSubscriptions = OldSubscription::where('merchant_id', $request->id)
            ->latest()
            ->get();

        $subscriptions = Subscription::where('merchant_id', $request->id)
            ->latest()
            ->get();

        // Combine both collections
        $allSubscriptions = $oldSubscriptions->concat($subscriptions)->sortByDesc('created_at');

        return response()->json([
            'status' => true,
            'message' => 'Retrieved all subscription records against merchant_id.',
            'data' => $allSubscriptions,
            'metadata' => [
                'total_old_subscriptions' => $oldSubscriptions->count(),
                'total_current_subscriptions' => $subscriptions->count(),
                'total_all_subscriptions' => $allSubscriptions->count()
            ]
        ], 200);
    }
}
