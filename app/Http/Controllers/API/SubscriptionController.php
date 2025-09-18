<?php

namespace App\Http\Controllers\API;

use Square\SquareClient;
use Square\Environments;
use Square\Payments\Requests\CreatePaymentRequest;
use Square\Types\Money;
use Square\Types\Currency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Models\User;
use App\Models\AchPaymentInfo;
use App\Models\Subscription;
use App\Models\OldSubscription;
use App\Models\DefaultPackage;
use App\Models\CustomPackagePricing;
use Carbon\Carbon;
use App\Mail\subscriptionemail;
use Illuminate\Support\Facades\Mail;

class SubscriptionController extends Controller
{
    public function updateByMerchantId(Request $request)
    {
        try {
            // Validate merchant_id from URL parameter
            $merchantValidator = Validator::make(['merchant_id' => $request->merchant_id], [
                'merchant_id' => [
                    'required',
                    'string',
                    'exists:users,merchant_id'
                ]
            ]);

            if ($merchantValidator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid merchant ID',
                    'errors' => $merchantValidator->errors()
                ], 422);
            }

            // Validate request data (all fields optional)
            $validator = Validator::make($request->all(), [
                'subscription_package_price' => 'nullable|numeric|min:0',
                'over_limit_price' => 'nullable|numeric|min:0',
                'subscription_amount' => 'nullable|integer|min:0',
                'price_per_scan' => 'nullable|numeric|min:0',
            ], [
                'subscription_package_price.numeric' => 'Subscription package price must be a number',
                'over_limit_price.numeric' => 'Over-limit price must be a number',
                'subscription_amount.integer' => 'Subscription amount must be an integer',
                'price_per_scan.numeric' => 'Price per scan must be a number',
                '*.min' => 'Values cannot be negative',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find user by merchant_id
            $user = User::where('merchant_id', $request->merchant_id)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found with the provided merchant ID',
                    'data' => null
                ], 404);
            }

            // Update only the provided fields
            $updateData = array_filter($request->only([
                'subscription_package_price',
                'over_limit_price',
                'subscription_amount',
                'price_per_scan'
            ]), function ($value) {
                return $value !== null;
            });

            if (empty($updateData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid fields provided for update',
                    'data' => null
                ], 400);
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Subscription details updated successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'merchant_id' => $user->merchant_id,
                    'subscription_package_price' => $user->subscription_package_price,
                    'over_limit_price' => $user->over_limit_price,
                    'subscription_amount' => $user->subscription_amount,
                    'price_per_scan' => $user->price_per_scan,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update subscription details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getByMerchantId(string $merchantId)
    {
        try {
            // Validate merchant_id from URL parameter
            $validator = Validator::make(['merchant_id' => $merchantId], [
                'merchant_id' => [
                    'required',
                    'string',
                    'exists:users,merchant_id'
                ]
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid merchant ID',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::where('merchant_id', $merchantId)
                ->select([
                    'id',
                    'name',
                    'email',
                    'merchant_id',
                    'subscription_package_price',
                    'over_limit_price',
                    'subscription_amount',
                    'price_per_scan'
                ])
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'User subscription details retrieved successfully',
                'data' => $user
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subscription details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAchPayments(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get merchant_id from request
        $merchantId = $request->merchant_id;

        // Retrieve ACH payments for the merchant
        $achPayments = AchPaymentInfo::where('merchant_id', $merchantId)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($achPayments->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No ACH payments found for the specified merchant'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $achPayments
        ], 200);
    }

    public function storeAchPayment(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'ach_string' => 'required|string|max:5000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Sanitize inputs
        $merchantId = $request->merchant_id;
        $achString = trim($request->ach_string);

        // Check for duplicate within last 5 minutes
        $recentDuplicate = AchPaymentInfo::where('merchant_id', $merchantId)
            ->where('ach_string', $achString)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();

        if ($recentDuplicate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Duplicate ACH payment detected within the last 5 minutes'
            ], 422);
        }

        DB::beginTransaction();

        try {

            $client = new SquareClient(
                token: env('SQUARE_ACCESS_TOKEN'),
                options: [
                    'baseUrl' => Environments::Sandbox->value,
                ],
            );
            // Assuming $achString contains the JSON string you shared
            $payload = json_decode($achString, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON in $achString');
            }
            $response = $client->payments->create(
                new CreatePaymentRequest([
                     'sourceId' => $payload['sourceId'],
                     'idempotencyKey' => $payload['idempotencyKey'],
                    'amountMoney' => new Money([
                        'amount' => $payload['amountMoney']['amount'],
                        'currency' => Currency::Usd->value,
                    ]),
                    'locationId' => $payload['locationId'],
                ]),
            );

            // Create new ACH payment record
            $achPayment = AchPaymentInfo::create([
                'merchant_id' => $merchantId,
                'ach_string' => $achString,
                'ach_payment_api_response' => $response

            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'ACH payment information saved successfully',
                'data' => [
                    'id' => $achPayment->id,
                    'merchant_id' => $request->merchant_id,
                    'created_at' => $achPayment->created_at
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save ACH payment information'
            ], 500);
        }
    }

    public function getCustomPackagePricing(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find the pricing by package_id
        $pricing = CustomPackagePricing::where('package_id', $request->package_id)->first();

        if (!$pricing) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pricing not found for the specified package'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $pricing
        ], 200);
    }
    
    public function customStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
             'merchant_id'       => 'required|string',
             'package_id'        => 'required|exists:default_packages,id',
             'subscription_date' => 'required|date',
             'custom_api_count'  => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('merchant_id', $request->merchant_id)->first();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found for this merchant_id',
                'code'    => 404
            ]);
        }

        $profile = User::where('merchant_id', $request->merchant_id)
                    ->where('business_verified', 'APPROVED')
                    ->whereNotNull('merchant_id')
                    ->first();

        if (!$profile) {
            return response()->json([
                'status'  => true,
                'message' => 'User Business Profile is not in Approved state',
                'code'    => 200
            ]);
        }

        $package = DefaultPackage::find($request->package_id);
        $baseLimit = $package ? $package->monthly_limit : 0;

        $subscriptionDate = Carbon::parse($request->subscription_date);
        $renewalDate = $subscriptionDate->copy()->addMonth();

        // Check if this is a custom package (package_id = 3)
        if ($request->package_id == 3) {
            // Get custom pricing for this merchant
            $customPricing = CustomPackagePricing::where('package_id', 3)
                ->first();

            if (!$customPricing || empty($customPricing->business_user_per_custom_api_cost)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Custom pricing not configured for this merchant',
                    'code'    => 400
                ]);
            }

            if (empty($request->custom_api_count)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'API count is required for custom packages',
                    'code'    => 400
                ]);
            }





            // Use the original subscription creation logic
            /* $subscription = Subscription::where('merchant_id', $request->merchant_id)->first();

            if ($subscription) 
            {
                $previousLimit = $subscription->api_calls_limit ?? 0;
                $usedCalls = $subscription->api_calls_used ?? 0;
                $adjustment = $previousLimit - $usedCalls;
                $finalLimit = max(0, $baseLimit + $adjustment);

                // Archive old subscription
                OldSubscription::create([
                    'user_id'            => $subscription->user_id,
                    'merchant_id'        => $subscription->merchant_id,
                    'package_id'         => $subscription->package_id,
                    'api_calls_limit'    => $subscription->api_calls_limit,
                    'api_calls_used'     => $subscription->api_calls_used,
                    'overage_calls'      => $subscription->overage_calls,
                    'subscription_date'  => $subscription->subscription_date,
                    'renewal_date'       => $subscription->renewal_date,
                    'is_blocked'         => $subscription->is_blocked,
                    'custom_package'     => $subscription->custom_package,
                    'custom_price'       => $subscription->custom_price,
                    'custom_calls_used'  => $subscription->custom_calls_used,
                    'custom_status'      => $subscription->custom_status,
                    'custom_api_count'   => $subscription->custom_api_count ?? null,
                ]);

                // Update subscription
                $subscription->update([
                    'package_id'        => $request->package_id,
                    'api_calls_limit'   => $finalLimit,
                    'api_calls_used'    => 0,
                    'overage_calls'     => 0,
                    'subscription_date' => $subscriptionDate,
                    'renewal_date'      => $renewalDate,
                    'is_blocked'       => false,
                    'custom_package'   => true,
                    'custom_price'     => $customPricing->business_user_per_custom_api_cost * $request->custom_api_count,
                    'custom_calls_used' => 0,
                    'custom_status'     => 'inactive',
                    'custom_api_count'  => $request->custom_api_count,
                ]);
            } 
            else 
            {
                // Create new subscription
                $subscription = Subscription::create([
                    'merchant_id'       => $request->merchant_id,
                    'user_id'          => $user->id,
                    'package_id'       => $request->package_id,
                    'api_calls_limit'   => $baseLimit,
                    'api_calls_used'   => 0,
                    'overage_calls'     => 0,
                    'subscription_date' => $subscriptionDate,
                    'renewal_date'     => $renewalDate,
                    'is_blocked'      => false,
                    'custom_package'  => true,
                    'custom_price'    => $customPricing->business_user_per_custom_api_cost * $request->custom_api_count,
                    'custom_calls_used' => 0,
                    'custom_status'    => 'inactive',
                    'custom_api_count' => $request->custom_api_count,
                ]);
            } */

            return response()->json([
                'status'  => true,
                //'message' => 'Custom subscription created successfully',
                'message' => 'Show custom subscription calculation',
                'code'    => 200,
                'data'    => [
                    //'subscription' => $subscription,
                    'merchant_id' => $request->merchant_id,
                    'per_api_price' => $customPricing->business_user_per_custom_api_cost
                ]
            ]);
        }
    }

    public function CustomPackagePricing(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|integer',
            'business_user_per_custom_api_cost' => 'nullable|numeric|min:0',
            'enterprise_user_per_custom_api_cost' => 'nullable|numeric|min:0',
            'sub_business_fee' => 'nullable|numeric|min:0',
            'billing_cycle' => 'nullable|string|in:monthly,quarterly,yearly' // assuming specific allowed values
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create or update the pricing
        $pricing = CustomPackagePricing::updateOrCreate(
            ['package_id' => $request->package_id],
            $request->only([
                'business_user_per_custom_api_cost',
                'enterprise_user_per_custom_api_cost',
                'sub_business_fee',
                'billing_cycle'
            ])
        );

        return response()->json([
            'status' => 'success',
            'data' => $pricing
        ], 201);
    }

    public function CustomSubscriptions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'merchant_id'       => 'required|string',
            'package_id'        => 'required',
            'subscription_date' => 'required|date',
            'custom_package'    => 'required|string',
            'custom_price'      => 'required',
            'custom_calls_used' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
                'code'    => 422
            ]);
        }

        // Fetch user with merchant_id
        $user = DB::table('users')
            ->where('users.merchant_id', $request->merchant_id)
            ->first();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found for this merchant_id',
                'code'    => 404
            ]);
        }
        $profile = User::where('merchant_id', $request->merchant_id)
                    ->where('business_verified', 'APPROVED')
                    ->whereNotNull('merchant_id')
                    ->first();

        if (!$profile) {
            return response()->json([
                'status'  => true,
                'message' => 'User Business Profile is not in Approved state',
                'code'    => 200
            ]);
        }
        $subscriptionDate   = Carbon::parse($request->subscription_date);
        $renewalDate        = $subscriptionDate->copy()->addMonth();
        $customCallsLimit   = (int) $request->custom_calls_used;

        // Handle old subscription
        $existing = Subscription::where('merchant_id', $request->merchant_id)->first();

        if ($existing) {
            // Move to old_subscriptions
            OldSubscription::create([
                'user_id'            => $existing->user_id,
                'merchant_id'        => $existing->merchant_id,
                'package_id'         => $existing->package_id,
                'api_calls_used'     => $existing->api_calls_used,
                'overage_calls'      => $existing->overage_calls,
                'subscription_date'  => $existing->subscription_date,
                'renewal_date'       => $existing->renewal_date,
                'is_blocked'         => $existing->is_blocked,
                'custom_package'     => $existing->custom_package,
                'custom_price'       => $existing->custom_price,
                'custom_calls_used'  => $existing->custom_calls_used,
                'custom_status'      => $existing->custom_status,
                'api_calls_limit'    => $existing->api_calls_limit,
            ]);

            // Adjust for pending or overage
            $pending = max(0, $existing->api_calls_limit - $existing->api_calls_used);
            $overage = max(0, $existing->api_calls_used - $existing->api_calls_limit);
            $customCallsLimit = max(0, $customCallsLimit + $pending - $overage);

            $existing->delete();
        }

        // Create custom subscription
        $subscription = Subscription::create([
            'merchant_id'       => $request->merchant_id,
            'user_id'           => $user->id,
            'package_id'        => $request->package_id,
            'api_calls_used'    => 0,
            'overage_calls'     => 0,
            'subscription_date' => $subscriptionDate,
            'renewal_date'      => $renewalDate,
            'custom_package'    => $request->custom_package,
            'custom_price'      => $request->custom_price,
            'custom_calls_used' => $request->custom_calls_used,
            'custom_status'     => 'inactive',
            'api_calls_limit'   => $customCallsLimit,
            'is_blocked'        => false
        ]);

        return response()->json([
            'status'    => true,
            'message'   => 'Custom subscription created successfully',
            'code'      => 200,
            'data'      => $subscription
        ]);
    }

    public function CustomSubscriptions_old(Request $request)
    {
        $request->validate([
            'merchant_id'           => 'required|string',
            'package_id'            => 'required',
            'subscription_date'     => 'required|date',
            'custom_package'        => 'required',
            'custom_price'          => 'required',
            'custom_calls_used'     => 'required',

        ]);
        $user = DB::table('users')->where('merchant_id', $request->merchant_id)->first();
        
        // Add one month to subscription_date for renewal_date
        $subscriptionDate = Carbon::parse($request->subscription_date);
        $renewalDate = $subscriptionDate->copy()->addMonth();

        $subscription = Subscription::create([
            'merchant_id'       => $request->merchant_id,
            'user_id'           => $user->id,
            'package_id'        => $request->package_id,
            'api_calls_used'    => 0,
            'overage_calls'     => 0,
            'renewal_date'      => $renewalDate,
            'subscription_date' => $subscriptionDate,
            'custom_package'    => $request->custom_package,
            'custom_price'      => $request->custom_price,
            'custom_calls_used' => $request->custom_calls_used,
            'custom_status'     => 'inactive',
            'is_blocked'        => false
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Subscription created successfully',
            'code' => 200
        ]);
    }

    public function GetByUserIDorMerchantID(Request $request)
    {
        $userId         = $request->user_id; 
        $merchant_id    = $request->merchant_id; 

        $query = Subscription::with('package')->where('user_id', $userId)->orwhere('merchant_id', $merchant_id);

        $subscription = $query->latest()->first();

        if (!$subscription) {
            return response()->json([
                'status'    => false,
                'code'      => 404,
                'message'   => 'No subscription found for this merchant.'
            ], 404);
        }

        return response()->json([
            'status'        => true,
            'code'          => 200,
            'message'       => 'Subscription retrieved successfully',
            'data'          => $subscription
        ],200);
    }

    // Subscription store or update 
    public function old_store(Request $request)
    {
        $request->validate([
            'merchant_id'       => 'required|string',
            'package_id'        => 'required|exists:default_packages,id',
            'subscription_date' => 'required|date',
        ]);

        $user = User::where('merchant_id', $request->merchant_id)->first();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found for this merchant_id',
                'code'    => 404
            ]);
        }

        // Add one month to subscription_date for renewal_date
        $subscriptionDate = Carbon::parse($request->subscription_date);
        $renewalDate = $subscriptionDate->copy()->addMonth();

        $subscription = Subscription::create([
            'merchant_id'       => $request->merchant_id,
            'user_id'           => $user->id,
            'package_id'        => $request->package_id,
            'api_calls_used'    => 0,
            'overage_calls'     => 0,
            'subscription_date' => $subscriptionDate,
            'renewal_date'      => $renewalDate,
            'is_blocked'        => false
        ]);

        return response()->json([
            'status'    => true,
            'message'   => 'Subscription created successfully',
            'code'      => 200,
            'data'      => $subscription
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'merchant_id'       => 'required|string',
            'package_id'        => 'required|exists:default_packages,id',
            'subscription_date' => 'required|date',
            'custom_api_count'  => 'nullable|integer|min:1', // Optional, only for package 3
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('merchant_id', $request->merchant_id)->first();
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found for this merchant_id',
                'code'    => 404
            ]);
        }

        /* $profile = User::where('merchant_id', $request->merchant_id)
                    ->where('business_verified', 'APPROVED')
                    ->whereNotNull('merchant_id')
                    ->first();
        if (!$profile) {
            return response()->json([
                'status'  => true,
                'message' => 'User Business Profile is not in Approved state',
                'code'    => 200
            ]);
        } */

        $package = DefaultPackage::find($request->package_id);
        $baseLimit = $package ? $package->monthly_limit : 0;

        // Handle custom API count only for package 3
        $isPackage3 = ($request->package_id == 3);
        $customApiCount = $isPackage3 && $request->has('custom_api_count') 
                        ? $request->custom_api_count 
                        : null;

        $subscriptionDate = Carbon::parse($request->subscription_date);
        $renewalDate = $subscriptionDate->copy()->addMonth();

        $subscription = Subscription::where('merchant_id', $request->merchant_id)->first();

        if ($subscription) {
            $previousLimit = $subscription->api_calls_limit ?? 0;
            $usedCalls = $subscription->api_calls_used ?? 0;

            // Calculate adjustment: remaining or overused
            $adjustment = $previousLimit - $usedCalls;

            // Final new limit: base + adjustment (can subtract if overused)
            $finalLimit = max(0, $baseLimit + $adjustment);

            // Archive old subscription
            $oldSubscriptionData = [
                'user_id'            => $subscription->user_id,
                'merchant_id'        => $subscription->merchant_id,
                'package_id'         => $subscription->package_id,
                'api_calls_limit'    => $subscription->api_calls_limit,
                'api_calls_used'     => $subscription->api_calls_used,
                'overage_calls'     => $subscription->overage_calls,
                'subscription_date'  => $subscription->subscription_date,
                'renewal_date'       => $subscription->renewal_date,
                'is_blocked'        => $subscription->is_blocked,
                'custom_package'    => $subscription->custom_package,
                'custom_price'      => $subscription->custom_price,
                'custom_calls_used' => $subscription->custom_calls_used,
                'custom_status'      => $subscription->custom_status,
            ];

            // Add custom_api_count to archive if it exists for package 3
            if ($isPackage3 && $subscription->custom_api_count) {
                $oldSubscriptionData['custom_api_count'] = $subscription->custom_api_count;
            }

            OldSubscription::create($oldSubscriptionData);

            // Prepare update data
            $updateData = [
                'package_id'        => $request->package_id,
                'api_calls_limit'   => $finalLimit,
                'api_calls_used'    => 0,
                'overage_calls'     => 0,
                'subscription_date' => $subscriptionDate,
                'renewal_date'      => $renewalDate,
                'is_blocked'        => false,
            ];

            // Add custom_api_count only for package 3 if provided
            if ($isPackage3 && $customApiCount) {
                $updateData['custom_api_count'] = $customApiCount;
            }

            $subscription->update($updateData);
        } else {
            // Prepare new subscription data
            $newSubscriptionData = [
                'merchant_id'       => $request->merchant_id,
                'user_id'          => $user->id,
                'package_id'       => $request->package_id,
                'api_calls_limit'   => $baseLimit,
                'api_calls_used'   => 0,
                'overage_calls'     => 0,
                'subscription_date' => $subscriptionDate,
                'renewal_date'      => $renewalDate,
                'is_blocked'       => false,
            ];

            // Add custom_api_count only for package 3 if provided
            if ($isPackage3 && $customApiCount) {
                $newSubscriptionData['custom_api_count'] = $customApiCount;
            }

            $subscription = Subscription::create($newSubscriptionData);
        }

        // MAIL CODE
        // Find the user with their current business profile
        $userbusinessProfile = User::with('businessProfile')->find($user->id);
        
        $accountHolderFirstName = 'Customer';
        if ($userbusinessProfile->businessProfile && !empty($userbusinessProfile->businessProfile->account_holder_first_name)) {
            $accountHolderFirstName = $user->businessProfile->account_holder_first_name;
        }
        
        $package_name = $package ? $package->package_name : 'No Name';
        
        if ($isPackage3 && $customApiCount) 
        {
            $monthly_limit = $customApiCount;
            
            // Find the pricing by package_id
            $pricing = CustomPackagePricing::where('package_id', $request->package_id)->first();
            $package_price = $pricing->business_user_per_custom_api_cost * $customApiCount;
        }
        else
        {
            $monthly_limit = $package ? $package->monthly_limit : 0;
            $package_price = $package ? $package->package_price : 0.00;
        }
        $renewalDate = $renewalDate ? $renewalDate : '0000-00-00 00:00:00';

        $mail_plan = [
            'customer_name' => $accountHolderFirstName,
            'package_name' => $package_name,
            'monthly_limit' => $monthly_limit,
            'package_price' => $package_price,
            'renewal_date' => $renewalDate
        ];

        $toEmail = $user->email;
        $message = "subscription";
        $subject = "Thank You for Your Subscription – Welcome to CardNest!";

        $request = Mail::to($toEmail)->send(new subscriptionemail($message, $subject, $mail_plan));
        // MAIL CODE  

        return response()->json([
            'status'  => true,
            'message' => 'Subscription saved successfully',
            'code'    => 200,
            'data'    => $subscription
        ]);
    }

}