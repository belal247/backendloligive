<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Feature;
use App\Models\BusinessProfile;
use App\Models\ScanSession;
use App\Models\Subscription;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ScanController extends Controller
{
    public function getmerchantscanInfo(Request $request)
    {  
        $validator = Validator::make($request->all(), [
            'scanId' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 422,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $scanRecord = User::leftJoin('business_profiles', 'users.id', '=', 'business_profiles.user_id')
                    ->leftJoin('scan_sessions', 'scan_sessions.merchant_id', '=', 'users.merchant_id')
                    ->where('scan_sessions.scan_id', $request->scanId)
                    ->select([
                        'scan_sessions.scan_id as scanId',
                        'users.merchant_id',
                        'business_profiles.display_name',
                        'business_profiles.display_logo'
                    ])
                    ->first();

        if (!$scanRecord) {
            return response()->json([
                'status'  => false,
                'code'    => 404,
                'message' => 'No record exists against this scanID.'
            ], 404);
        }

        // Format the logo URL
        $logoUrl = null;
        if ($scanRecord->display_logo) {
            $logoUrl = asset('storage/' . $scanRecord->display_logo);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => [
                'scanId' => $scanRecord->scanId,
                'merchant_id' => $scanRecord->merchant_id,
                'display_name' => $scanRecord->display_name,
                'display_logo' => $logoUrl
            ]
        ]);
    }
    public function getmerchantDisplayInfo(Request $request)
    {  
        $validator = Validator::make($request->all(), [
            'merchantId' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 422,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $merchantRecord = User::leftJoin('business_profiles', 'users.id', '=', 'business_profiles.user_id')
                    ->where('users.merchant_id', $request->merchantId)
                    ->select([
                        'users.merchant_id',
                        'business_profiles.display_name',
                        'business_profiles.display_logo'
                    ])
                    ->first();

        if (!$merchantRecord) {
            return response()->json([
                'status'  => false,
                'code'    => 404,
                'message' => 'No record exists against this scanID.'
            ], 404);
        }

        // Format the logo URL
        $logoUrl = null;
        if ($merchantRecord->display_logo) {
            $logoUrl = asset('storage/' . $merchantRecord->display_logo);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => [
                'merchant_id' => $merchantRecord->merchant_id,
                'display_name' => $merchantRecord->display_name,
                'display_logo' => $logoUrl
            ]
        ]);
    }
    public function updateMerchantScanInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'merchant_id' => ['required'],
            'display_name' => ['sometimes', 'string', 'max:255'],
            'display_logo' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'], // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 422,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('merchant_id', $request->merchant_id)->first();
            $businessProfile = BusinessProfile::where('user_id', $user->id)->first();

            $updateData = [];
            
            if ($request->has('display_name')) {
                $updateData['display_name'] = $request->display_name;
            }

            if ($request->hasFile('display_logo')) {
                // Delete old logo if exists
                if ($businessProfile->display_logo) {
                    $oldLogoPath = public_path('storage/businesslogo/' . basename($businessProfile->display_logo));
                    if (file_exists($oldLogoPath)) {
                        unlink($oldLogoPath);
                    }
                }
                
                // Store new logo in the specified directory
                $logoFile = $request->file('display_logo');
                $logoName = 'logo_' . $request->merchant_id . '_' . time() . '.' . $logoFile->getClientOriginalExtension();
                
                // Create directory if it doesn't exist
                if (!file_exists(public_path('storage/businesslogo'))) {
                    mkdir(public_path('storage/businesslogo'), 0755, true);
                }
                
                // Move the file to the desired location
                $logoFile->move(public_path('storage/businesslogo'), $logoName);
                
                $updateData['display_logo'] = 'businesslogo/' . $logoName;
            }

            if (!empty($updateData)) {
                $businessProfile->update($updateData);
            }

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'Merchant display information updated successfully.',
                'data' => [
                    'display_name' => $businessProfile->display_name,
                    'display_logo' => $businessProfile->display_logo ? asset('storage/' . $businessProfile->display_logo) : null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'code'    => 500,
                'message' => 'Failed to update merchant display information.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function updateMerchantDisplayInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'merchant_id' => ['required'],
            'display_name' => ['required', 'string', 'max:255'],
            'display_logo' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('merchant_id', $request->merchant_id)->firstOrFail();
            $businessProfile = BusinessProfile::where('user_id', $user->id)->firstOrFail();

            $updateData = [
                'display_name' => $request->display_name,
            ];

            // Handle logo - delete existing if not provided in request
            if (!$request->has('display_logo')) {
                // If logo field is not present in request at all, delete existing logo
                if ($businessProfile->display_logo) {
                    $oldLogoPath = public_path('storage/' . $businessProfile->display_logo);
                    if (file_exists($oldLogoPath)) {
                        unlink($oldLogoPath);
                    }
                }
                $updateData['display_logo'] = null;
            } 
            // Handle when logo is explicitly set to null
            elseif ($request->has('display_logo') && is_null($request->display_logo)) {
                if ($businessProfile->display_logo) {
                    $oldLogoPath = public_path('storage/' . $businessProfile->display_logo);
                    if (file_exists($oldLogoPath)) {
                        unlink($oldLogoPath);
                    }
                }
                $updateData['display_logo'] = null;
            }
            // Handle new logo upload
            elseif ($request->hasFile('display_logo')) {
                // Delete old logo if exists
                if ($businessProfile->display_logo) {
                    $oldLogoPath = public_path('storage/' . $businessProfile->display_logo);
                    if (file_exists($oldLogoPath)) {
                        unlink($oldLogoPath);
                    }
                }
                
                // Store new logo
                $logoFile = $request->file('display_logo');
                $logoName = 'logo_' . $request->merchant_id . '_' . time() . '.' . $logoFile->getClientOriginalExtension();
                
                // Ensure directory exists
                if (!file_exists(public_path('storage/businesslogo'))) {
                    mkdir(public_path('storage/businesslogo'), 0755, true);
                }
                
                $logoFile->move(public_path('storage/businesslogo'), $logoName);
                $updateData['display_logo'] = 'businesslogo/' . $logoName;
            }

            $businessProfile->update($updateData);

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'Merchant display information updated successfully.',
                'data' => [
                    'display_name' => $businessProfile->display_name,
                    'display_logo' => $businessProfile->display_logo ? asset('storage/' . $businessProfile->display_logo) : null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'message' => 'Failed to update merchant display information.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function generateTokenOld(Request $request)
    {
         
        $validator = Validator::make($request->all(), [
            'merchantId'        => ['required', 'string', 'exists:users,merchant_id'],
            'merchantcontact'   => ['required', 'string'],
            'isMobile'          => ['required', 'in:true,false'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 422,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()
            ], 422);
        }
        $merchant = User::leftJoin('business_profiles', 'users.id', '=', 'business_profiles.user_id')
        ->where('users.merchant_id', $request->merchantId)
        ->where(function ($q) use ($request) {
            $q->where('users.email', $request->merchantcontact)
            ->orWhere('users.phone_no', $request->merchantcontact);
        })
        ->first();
        $user        = User::where('merchant_id', $request->merchantId)->first();
        $query = Subscription::where('merchant_id', $request->merchantId);
        $subscription = $query->latest()->first();
        if (!$subscription) {
            $trail = User::where('merchant_id', $request->merchantId)->first();
            if (!$trail->on_trial) {
                return response()->json([
                    'status'  => false,
                    'code'    => 403,
                    'message' => 'No active subscription or trial.'
                ], 403);
            }
            if (now()->gt($trail->trial_ends_at) || $trail->trial_calls_remaining <= 0) {
                $trail->on_trial = false;
                $trail->save();
                return response()->json([
                    'status'  => false,
                    'code'    => 403,
                    'message' => 'Trial expired or API limit exceeded.'
                ], 403);
            }
        }
        $query2 = Subscription::where('merchant_id', $request->merchantId);
        // Get the latest subscription
        $subscriptiontill = $query2->latest()->first();

        if (Carbon::now()->gt(Carbon::parse($subscriptiontill->renewal_date))) {
            return response()->json([
                'status'  => false,
                'code'    => 404,
                'message' => 'Subscription expired for this merchant.'
            ], 404);
        }

        else{

        $rawFeatures = Feature::select('bank_logo', 'chip', 'mag_strip', 'sig_strip', 'hologram', 'customer_service', 'symmetry')
        ->where('user_id', $user->id)
        ->first();
        
        $features = null;

        if ($rawFeatures) {
            $features = [
                'bank_logo'        => (bool) $rawFeatures->bank_logo,
                'chip'             => (bool) $rawFeatures->chip,
                'mag_strip'        => (bool) $rawFeatures->mag_strip,
                'sig_strip'        => (bool) $rawFeatures->sig_strip,
                'hologram'         => (bool) $rawFeatures->hologram,
                'customer_service' => (bool) $rawFeatures->customer_service,
                'symmetry'         => (bool) $rawFeatures->symmetry,
            ];
        }

        if (!$merchant) {
            return response()->json(['message' => 'Invalid merchant credentials'], 404);
        }
        $business_name = $merchant->business_name;
        $scanId     = Str::uuid()->toString();        
        $session    = ScanSession::create([
            'scan_id'           => $scanId,
            'merchant_id'       => $merchant->merchant_id,
            'merchant_contact'  => $request->merchantcontact,
            'isMobile'          => $request->isMobile === 'true' ? 'mobile' : 'web',
            'tries'             => 0,
            'encryption_key'    => $merchant->aes_key,
        ]);

        $customClaims = [
            'scan_id'           => $scanId,
            'merchant_id'       => $merchant->merchant_id,
            'encryption_key'    => $merchant->aes_key,
            'features'          => $features
        ];

        $jwtToken       = JWTAuth::customClaims($customClaims)->fromUser($merchant);
        $encryptedToken = $jwtToken; 
        $response = [
            'authToken' => $encryptedToken,
        ];

        if ($request->isMobile === 'false') {

            $response['scanID']     = $scanId;
            $response['scanURL']    = "https://auth.cardnest.io/" . rawurlencode($business_name) . "/{$scanId}";
            
        } else {
            $response['scanID']     = "";
            $response['scanURL']    = "";
        }

        return response()->json($response);
        }

    }
    public function generateToken(Request $request)
    {
        // 1. Input Validation
        $validator = Validator::make($request->all(), [
            'merchantId'        => ['required', 'string', 'exists:users,merchant_id'],
            'merchantcontact'   => ['required', 'string'],
            'isMobile'          => ['required', 'in:true,false'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 422,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()
            ], 422);
        }

        // 2. Fetch Merchant
        $merchant = User::leftJoin('business_profiles', 'users.id', '=', 'business_profiles.user_id')
            ->where('users.merchant_id', $request->merchantId)
            ->where(function ($q) use ($request) {
                $q->where('users.email', $request->merchantcontact)
                ->orWhere('users.phone_no', $request->merchantcontact);
            })
            ->first();

        if (!$merchant) {
            return response()->json(['message' => 'Invalid merchant credentials'], 404);
        }

        // 3. Debug Logging (Check ACTUAL values)
        \Log::info('Trial Status Check', [
            'merchant_id' => $merchant->merchant_id,
            'on_trial' => $merchant->on_trial, // tinyint(1): 0 or 1
            'trial_ends_at' => $merchant->trial_ends_at, // timestamp (nullable)
            'trial_calls_remaining' => $merchant->trial_calls_remaining, // int
            'current_time' => now(),
        ]);

        // 4. Check Access (Fixed logic for tinyint(1) and nullable timestamp)
        $hasValidAccess = false;

        // A. First check subscription (if exists)
        $subscription = Subscription::where('merchant_id', $request->merchantId)
            ->latest()
            ->first();

        if ($subscription && now()->lte($subscription->renewal_date)) {
            $hasValidAccess = true;
        }
        // B. Fallback to trial check (PROPERLY handles tinyint(1) and null trial_ends_at)
        elseif ($merchant->on_trial == 1) { // Use == for tinyint(1)
            // Check if trial end date exists and is in future
            $trialActive = $merchant->trial_ends_at 
                ? now()->lte($merchant->trial_ends_at)
                : false; // If null, treat as expired

            if ($trialActive && $merchant->trial_calls_remaining > 0) {
                $hasValidAccess = true;
                $merchant->decrement('trial_calls_remaining');
            } else {
                // Trial expired
                $merchant->on_trial = 0;
                $merchant->save();
                return response()->json([
                    'status'  => false,
                    'code'    => 403,
                    'message' => 'Trial expired or API limit exceeded.'
                ], 403);
            }
        }

        // 5. Final denial if no valid access
        if (!$hasValidAccess) {
            return response()->json([
                'status'  => false,
                'code'    => 403,
                'message' => 'No active subscription or valid trial.'
            ], 403);
        }

        // 6. Generate Token (Only reached if access is valid)
        $features = Feature::where('user_id', $merchant->id)
            ->first([
                'bank_logo', 'chip', 'mag_strip', 
                'sig_strip', 'hologram', 'customer_service', 'symmetry'
            ]);

        $scanId = Str::uuid()->toString();
        ScanSession::create([
            'scan_id'           => $scanId,
            'merchant_id'       => $merchant->merchant_id,
            'merchant_contact'  => $request->merchantcontact,
            'isMobile'          => $request->isMobile === 'true' ? 'mobile' : 'web',
            'tries'             => 0,
            'encryption_key'    => $merchant->aes_key,
        ]);

        $response = [
            'authToken' => JWTAuth::customClaims([
                'scan_id'        => $scanId,
                'merchant_id'    => $merchant->merchant_id,
                'encryption_key' => $merchant->aes_key,
                'features'      => $features ? $features->toArray() : null,
            ])->fromUser($merchant),
        ];

        if ($request->isMobile === 'false') {
            $response['scanID'] = $scanId;
            $response['scanURL'] = "https://auth.cardnest.io/" . rawurlencode($merchant->business_name) . "/{$scanId}";
        }

        return response()->json($response);
    }
    public function createScanToken(Request $request)
    {
        $request->validate([
            'scanId' => 'required|string',
        ]);

        $session = ScanSession::where('scan_id', $request->scanId)->first();

        if (!$session) {
            return response()->json(['message' => 'Invalid scan ID'], 404);
        }

        $merchant = User::where('merchant_id', $session->merchant_id)->first();

        if (!$merchant) {
            return response()->json(['message' => 'Merchant not found'], 404);
        }

        $encodedKey = $session->encryption_key;

        $customClaims = [
            'scan_id'                => $session->scan_id,
            'merchant_id'            => $session->merchant_id,
            'merchant_contact'       => $session->merchant_contact,
            'isMobile'               => $session->isMobile,
            'encryption_key'         => $encodedKey,
            'iat'                    => now()->timestamp,
        ];

        $jwtToken       = JWTAuth::customClaims($customClaims)->fromUser($merchant);
        $encryptedToken = $jwtToken; 
        return response()->json([
            'authToken' => $encryptedToken,
        ]);
    }

    public function submitEncryptedData(Request $request)
    {
        $request->validate([
            'scanId'         => 'required|string',
            'encrypted_data' => 'required|string',
        ]);

        $session = ScanSession::where('scan_id', $request->scanId)->first();

        if (!$session) {
            return response()->json(['message' => 'Invalid scan ID'], 404);
        }

        $session->encrypted_data    = $request->encrypted_data;
        $session->scanned_at        = now(); 
        $session->save();

        return response()->json([
            'message' => 'Scanned data submitted successfully.',
        ]);
    }
    public function getEncryptedData(Request $request)
    {
         
        $session = ScanSession::select('id','scan_id', 'merchant_id', 'encrypted_data')->where('scan_id', $request->scanId)->first();

        if (!$session) {
            return response()->json(['message' => 'Invalid scan ID'], 404);
        }


        return response()->json([
            'message' => 'Scanned data retrieved successfully.',
            'data' => $session
        ]);
    }
    public function decodeToken(Request $request)
    {
        $request->validate([
            'authToken' => 'required|string'
        ]);

        try {
            
            // 2. Then verify and decode the JWT
            $payload = JWTAuth::setToken($decryptedToken)->getPayload();
            
            // 3. Extract the custom claims
            $customClaims = [
                'scan_id' => $payload->get('scan_id'),
                'merchant_id' => $payload->get('merchant_id'),
                'encryption_key' => $payload->get('encryption_key'),
            ];
            
            return response()->json([
                'success' => true,
                'payload' => $customClaims
            ]);
            
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return response()->json(['error' => 'Token decryption failed'], 400);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Token parsing failed'], 400);
        }
    }

}

