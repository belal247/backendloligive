<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\BusinessProfile;
use App\Models\TempOtp;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
//use App\Mail\welcomemail;
//use App\Mail\adminemail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class AuthController extends Controller
{
    public function processPayment(Request $request)
    {
        // Validate that token is present in the request
        $request->validate([
            'ssl_txn_auth_token' => 'required|string'
        ]);
        $url = 'https://hpp.na.elavonpayments.com/hosted-payments';

        // Get the token from POST request
        $authToken = $request->input('ssl_txn_auth_token');

        $response = Http::asForm()
            ->withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Cookie' => '_abck=5775A11AD4D5FAA5981B1D2FDA6B50C3~-1~YAAQL54QAjlqRTKaAQAA0pltVQ6GjsYKPE9756B56TufA/I+w2mKeNZRtR16E7M2k86r5Mx7+Qe1+LK1OGcmQmkdymwD9HlVaP8UOnl0Nx88HqOyjEesxxncvYPTQZQy9bDadZ5y6/uLAsfPAqInhJAG6nG8iADODUWEfsIpj+oXf1kr6GMMEHspGMQRd4pSDMMeapes+1zLemjp/qFntex9M4xEIRUeFdCbOVtjJbcObkAUv/hGqO35wBg3KWl0rcuAyrdN9r9QrXmyTnMFfAg3vEp9H6SVYQMsqSAiVGEOZ5Bp88wYdYiOqs523rufT2DfBowXTEzuaTdhfTK6ImFGc/6FYHWKF0GOawtW64HFlf7+XCftd1NoMRQ6Hr7eOWjwGhOMs4tCqB7TGEXpiPvqk+wzV9hJVMjyQhRw92FE/qMc7wbLD2RHdIUR+DAWQw2v80IlOy8qBg==~-1~-1~-1~-1~-1; ak_bmsc=7BDB7FD21E86A3C46E15D171BE58DAB3~000000000000000000000000000000~YAAQL54QAikmRV6aAQAAQIfnZR18jI0szRvQ4keFEsYDJlYpocIvKZ4rwdf2NCQbPGtUleew4gcoV0wtRWXdcBiS84K4aCWeOgHE+agz9hIG9Eit/Ui4E+AxPr3M3OhmjQc4RkHOPo+YERZm0vReZOajSrKQjXSeS8APbvIfRMNUQPGrI4sbpqueuz9UHhcJVuUXzl6GRyv/9DYqmKmraTgzmAB8vBeu1pzoq4U51xj5cJ1tlZK5G7+8We4yQbx7Q7wg0BgiYJDEng7D0a1Xmw1JVCHO/r8gtb77XUAHUGmw+4JiKzAaM90GIuxHGVRFqyO1EKzRPRpHEb0MuTNJEMtEbSQ34NpT4nn1WRw8qa1v50su+TCf/g=='
            ])
            ->post($url, [
                'ssl_txn_auth_token' => $authToken
            ]);

        //return $response->body();
        return response($response->body())->header('Content-Type', 'text/html');
    }

    public function getTransactionToken(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.50',
        ], [
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Minimum amount must be 0.50.',
        ]);
        try {
            $response = Http::withOptions([
                'max_redirects' => 10,
                'timeout' => 0,
                'version' => CURL_HTTP_VERSION_1_1,
            ])
                ->withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Cookie' => '_abck=5775A11AD4D5FAA5981B1D2FDA6B50C3~-1~YAAQL54QAjlqRTKaAQAA0pltVQ6GjsYKPE9756B56TufA/I+w2mKeNZRtR16E7M2k86r5Mx7+Qe1+LK1OGcmQmkdymwD9HlVaP8UOnl0Nx88HqOyjEesxxncvYPTQZQy9bDadZ5y6/uLAsfPAqInhJAG6nG8iADODUWEfsIpj+oXf1kr6GMMEHspGMQRd4pSDMMeapes+1zLemjp/qFntex9M4xEIRUeFdCbOVtjJbcObkAUv/hGqO35wBg3KWl0rcuAyrdN9r9QrXmyTnMFfAg3vEp9H6SVYQMsqSAiVGEOZ5Bp88wYdYiOqs523rufT2DfBowXTEzuaTdhfTK6ImFGc/6FYHWKF0GOawtW64HFlf7+XCftd1NoMRQ6Hr7eOWjwGhOMs4tCqB7TGEXpiPvqk+wzV9hJVMjyQhRw92FE/qMc7wbLD2RHdIUR+DAWQw2v80IlOy8qBg==~-1~-1~-1~-1~-1'
                ])
                ->asForm()
                ->post('https://hpp.na.elavonpayments.com/hosted-payments/transaction_token', [
                    'ssl_account_id' => '2693813',
                    'ssl_user_id' => '8045256156web',
                    'ssl_pin' => 'WVVN6XVVOOF92M73QP4GPV2CJRVMON907KCR3Z2NUZCEEG4PDIR7TJEGNR9VL4VW',
                    'ssl_transaction_type' => 'ccsale',
                    'ssl_amount' => $request->amount,
                    'ssl_get_token' => 'Y'
                ]);

            // Convert the response (key=value lines) to an array
            parse_str($response->body(), $data);

            // Check if the token exists and return JSON
            if (isset($data['ssl_txn_auth_token'])) {
                return response()->json([
                    'ssl_txn_auth_token' => $data['ssl_txn_auth_token']
                ]);
            } else {
                return response()->json([
                    'error' => 'Token not found in response',
                    'response' => $data
                ], 400);
            }

        } catch (RequestException $e) {
            // Handle exception or log error
            return response()->json(['error' => 'Request failed: ' . $e->getMessage()], 500);
        }
    }

    public function getConvergePayIp()
    {
        try {
            $response = Http::withOptions([
                'max_redirects' => 10,
                'timeout' => 0,
                'version' => CURL_HTTP_VERSION_1_1,
            ])
                ->withHeaders([
                    'Cookie' => '_abck=5775A11AD4D5FAA5981B1D2FDA6B50C3~-1~YAAQL54QAjlqRTKaAQAA0pltVQ6GjsYKPE9756B56TufA/I+w2mKeNZRtR16E7M2k86r5Mx7+Qe1+LK1OGcmQmkdymwD9HlVaP8UOnl0Nx88HqOyjEesxxncvYPTQZQy9bDadZ5y6/uLAsfPAqInhJAG6nG8iADODUWEfsIpj+oXf1kr6GMMEHspGMQRd4pSDMMeapes+1zLemjp/qFntex9M4xEIRUeFdCbOVtjJbcObkAUv/hGqO35wBg3KWl0rcuAyrdN9r9QrXmyTnMFfAg3vEp9H6SVYQMsqSAiVGEOZ5Bp88wYdYiOqs523rufT2DfBowXTEzuaTdhfTK6ImFGc/6FYHWKF0GOawtW64HFlf7+XCftd1NoMRQ6Hr7eOWjwGhOMs4tCqB7TGEXpiPvqk+wzV9hJVMjyQhRw92FE/qMc7wbLD2RHdIUR+DAWQw2v80IlOy8qBg==~-1~-1~-1~-1~-1; bm_sz=895FD4BE24132C45D2B8E81DDE7ED360~YAAQL54QAjpqRTKaAQAA0pltVR1IFCJmz0G2p0U0dVJh7JplxktuGZBo1+iR3eIz0zSZF7IUwnAuekR2SmrAGwkGjw6G48fM9ouRqpHUpFVacabBdVOnU0CDDJzQGenxX2F/k8ep5A/I6E52w4Z5Q3J/Lx/KCIBeWsSpdTJa7lCV2L0NQp8wgGHHOIP1ht1ec97A8gw6hmskqVQYq0n4EB1VNMDNoqubSAWFIxIdZiEPyrB90Tn1TxWtEF+Vfx/rHoSupjR5sJ2a9/aviEvT7xBgADToYdBC6wvM+Cc6SeHeIzmPRRSXFjwg0atJ7AASz2e5vIvG40ZOSceVh/H8eG543/Fs+9TNDg385874Y1n6eQWW~4471089~4276785; convergeprod=!Yf5Q9cZ2tWYYNk9PBpR37Q1fu5rwegTgmNDdJmogB7ACASY+CMfC5ueT+tdXVN6/8B/jHuxJV8m4Ag=='
                ])
                ->get('https://www.convergepay.com/hosted-payments/myip');

            return $response->body();

        } catch (RequestException $e) {
            // Handle exception or log error
            return response()->json(['error' => 'Request failed: ' . $e->getMessage()], 500);
        }
    }

    public function assignLeader(Request $request)
    {
        try {

            // Find organisation by org_key_id
            $organisation = Company::where('org_key_id', $request->org_key)->first();

            if (!$organisation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Organization not found'
                ], 404);
            }

            $phoneEmail = $request->phone_email;
            $countryCode = $request->country_code;
            $isEmail = filter_var($phoneEmail, FILTER_VALIDATE_EMAIL);

            // Find existing user by email or phone
            $user = null;
            if ($isEmail) {
                $user = User::where('email', $phoneEmail)->first();
            } else {
                // For phone, check with country code
                $user = User::where('phone', $phoneEmail)
                    ->where('country_code', $countryCode)
                    ->first();
            }

            if ($user) {
                // Update existing user role to LEADER
                $user->where('id', $user->id)
                    ->update(['role' => 'LEADER']);

                $user->where('org_key_id', $request->org_key)
                    ->update(['leader_id' => $user->id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Existing user updated as LEADER and assigned to organization',
                    'data' => [
                        'user' => [
                            'id' => $user->id,
                            'email' => $user->email,
                            'phone' => $user->phone,
                            'country_code' => $user->country_code,
                            'role' => $user->role,
                        ]
                    ]
                ], 200);
            }

            // Create new user if not exists
            $userData = [
                'role' => 'LEADER',
                'country_code' => $countryCode,
            ];

            if ($isEmail) {
                $userData['email'] = $phoneEmail;
            } else {
                $userData['phone'] = $phoneEmail;
            }

            $user = User::create($userData);

            $user->where('org_key_id', $request->org_key)
                ->update(['leader_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'New LEADER created and assigned to organization',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'country_code' => $user->country_code,
                        'role' => $user->role,
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign leader',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function checkUserExists(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'nullable|email',
            'phone_no' => 'nullable|string',
        ]);

        $email = $request->input('email');
        $phone = $request->input('phone_no');

        $exists = false;
        $field = null;
        $message = null;

        // Check if both email and phone are provided
        if ($email && $phone) {
            $userByEmail = User::where('email', $email)->first();
            $userByPhone = User::where('phone_no', $phone)->first();

            if ($userByEmail && $userByPhone) {
                return response()->json([
                    'exists' => true,
                    'field' => 'both',
                    'message' => 'User already exists with this email and phone number'
                ]);
            } elseif ($userByEmail) {
                return response()->json([
                    'exists' => true,
                    'field' => 'email',
                    'message' => 'User already exists with this email'
                ]);
            } elseif ($userByPhone) {
                return response()->json([
                    'exists' => true,
                    'field' => 'phone_no',
                    'message' => 'User already exists with this phone number'
                ]);
            }
        } elseif ($email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                return response()->json([
                    'exists' => true,
                    'field' => 'email',
                    'message' => 'User already exists with this email'
                ]);
            }
        } elseif ($phone) {
            $user = User::where('phone_no', $phone)->first();
            if ($user) {
                return response()->json([
                    'exists' => true,
                    'field' => 'phone_no',
                    'message' => 'User already exists with this phone number'
                ]);
            }
        }

        return response()->json([
            'exists' => false,
            'message' => 'No user found with these credentials'
        ]);
    }

    public function signup(Request $request)
    {
        // Validate input
        $validateUser = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
                'country_code' => 'required',
                'phone_no' => 'required',
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validateUser->errors()->all()
            ], 401);
        }

        // Generate a random 128-bit (16-byte) AES key
        $aesKey = Str::random(16);

        // Check if email exists
        $emailExists = User::where('email', $request->email)->exists();

        if ($emailExists) {
            return response()->json([
                'status' => false,
                'message' => 'Email already taken',
                'errors' => ['email' => 'This email is already registered']
            ], 409);
        }

        // Check if phone exists
        $phoneExists = User::where('country_code', $request->country_code)
            ->where('phone_no', $request->phone_no)
            ->exists();

        if ($phoneExists) {
            return response()->json([
                'status' => false,
                'message' => 'Phone number already taken',
                'errors' => ['phone' => 'This phone number is already registered']
            ], 409);
        }

        // Check if user exists
        $user = User::where('email', $request->email)
            ->orWhere(function ($query) use ($request) {
                $query->where('country_code', $request->country_code)
                    ->where('phone_no', $request->phone_no);
            })
            ->first();

        if (!$user) {
            // Generate org_key_id first
            $numbers = '0123456789';
            $alphaChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $orgKeyId = '';

            // Generate 14 random numbers
            for ($i = 0; $i < 14; $i++) {
                $orgKeyId .= $numbers[rand(0, strlen($numbers) - 1)];
            }

            // Insert 2 alpha characters at random positions
            $positions = array_rand(range(0, 15), 2);
            foreach ($positions as $pos) {
                $orgKeyId = substr_replace(
                    $orgKeyId,
                    $alphaChars[rand(0, strlen($alphaChars) - 1)],
                    $pos,
                    0
                );
            }

            // Ensure exactly 16 characters
            $orgKeyId = substr($orgKeyId, 0, 16);

            // Create new user
            $user = User::create([
                'org_key_id' => $orgKeyId,
                'email' => $request->email,
                'country_code' => $request->country_code,
                'phone_no' => $request->phone_no,
                'aes_key' => $aesKey,
                'organization_verified' => 'INCOMPLETE PROFILE'
            ]);
        } else {
            // Update organization_verified if user exists
            $user->update([
                'organization_verified' => 'INCOMPLETE PROFILE'
            ]);
        }

        // Refresh user data
        $user->refresh();

        // Prepare the response with the new structure
        $userResponse = [
            'org_key_id' => $user->org_key_id,
            'email' => $user->email,
            'country_code' => $user->country_code,
            'phone_no' => $user->phone_no,
            'organization_verified' => $user->organization_verified,
            'verification_reason' => $user->verification_reason,
            'role' => $user->role,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at
        ];

        return response()->json([
            'status' => true,
            'message' => $user->wasRecentlyCreated ? 'User Created Successfully.' : 'User details updated.',
            'user' => $userResponse
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|numeric|digits:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find the user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Find the OTP record
        $tempOtp = TempOtp::where('user_id', $user->id)
            ->where('otp', $request->otp)
            ->first();

        // Check if OTP exists
        if (!$tempOtp) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP'
            ], 401);
        }

        // Check if OTP is expired
        if (now()->gt($tempOtp->otp_expiry_time)) {
            return response()->json([
                'status' => false,
                'message' => 'OTP has expired'
            ], 401);
        }

        // OTP is valid - update verification status and delete OTP
        $user->update(['otp_verified' => true]);
        $tempOtp->delete();

        return response()->json([
            'status' => true,
            'message' => 'OTP verified successfully',
            'user' => $user->fresh() // Return fresh instance with updated verification status
        ], 200);
    }

    public function resetOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find the user
        $user = User::where('email', $request->email)->first();

        // Generate new 6-digit OTP
        //$newOtp = rand(100000, 999999);
        $newOtp = 123456;
        $newExpiry = now()->addMinutes(15);

        // Update or create OTP record
        $tempOtp = TempOtp::updateOrCreate(
            ['user_id' => $user->id],
            [
                'otp' => $newOtp,
                'otp_expiry_time' => $newExpiry,
                'updated_at' => now()
            ]
        );

        // In production: Send the new OTP via SMS/email
        // $user->notify(new OtpNotification($newOtp));

        return response()->json([
            'status' => true,
            'message' => 'New OTP generated successfully',
            'otp' => $newOtp, // Remove in production
            'otp_expiry_time' => $newExpiry->format('Y-m-d H:i:s') // Remove in production
        ], 200);
    }

    public function login(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'country_code' => 'required|string|max:5',
            'login_input' => 'required|string', // Can be email or phone
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Determine if input is email or phone
        $isEmail = filter_var($request->login_input, FILTER_VALIDATE_EMAIL);
        $field = $isEmail ? 'email' : 'phone_no';

        // Find user based on provided credentials
        $user = User::where('country_code', $request->country_code)
            ->where($field, $request->login_input)
            ->first();

        // Check if user exists
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
                'errors' => ['auth' => 'The provided credentials are incorrect']
            ], 404);
        }

        // Generate org_key_id if not set
        if (empty($user->org_key_id)) {
            $numbers = '0123456789';
            $alphaChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $orgKeyId = '';

            // Generate 14 random numbers
            for ($i = 0; $i < 14; $i++) {
                $orgKeyId .= $numbers[rand(0, strlen($numbers) - 1)];
            }

            // Insert 2 alpha characters at random positions
            $positions = array_rand(range(0, 15), 2);
            foreach ($positions as $pos) {
                $orgKeyId = substr_replace(
                    $orgKeyId,
                    $alphaChars[rand(0, strlen($alphaChars) - 1)],
                    $pos,
                    0
                );
            }

            // Ensure exactly 16 characters
            $orgKeyId = substr($orgKeyId, 0, 16);

            $user->update(['org_key_id' => $orgKeyId]);
            $user->refresh();
        }

        // Create custom JWT claims
        $customClaims = [
            'sub' => $user->id,
            'org_key_id' => $user->org_key_id,
            'iat' => now()->timestamp,
            'exp' => now()->addMinutes(auth()->factory()->getTTL())->timestamp,
            'jti' => Str::random(20),
        ];

        // Generate token
        $token = auth()->claims($customClaims)->login($user);

        if (!$token) {
            try {
                $payload = auth()->factory()->make($customClaims);
                $token = auth()->manager()->encode($payload)->get();
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token generation failed',
                    'errors' => ['auth' => 'Could not generate authentication token']
                ], 500);
            }
        }

        // If user is LEADER, get org_key_ids of all users under them
        $leaderOrgKeys = [];
        if (strtoupper($user->role) === 'LEADER') {
            $leaderOrgKeys = User::where('leader_id', $user->id)
                ->whereNotNull('org_key_id')
                ->pluck('org_key_id')
                ->toArray();
        }

        // Prepare response
        $response = [
            'status' => true,
            'message' => 'Login successful',
            'user_id' => $user->id,
            'org_key_id' => $user->org_key_id,
            'JWT_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => [
                'org_key_id' => $user->org_key_id,
                'email' => $user->email,
                'country_code' => $user->country_code,
                'phone_no' => $user->phone_no,
                'organization_verified' => $user->organization_verified,
                'verification_reason' => $user->verification_reason,
                'role' => $user->role,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]
        ];

        // Add team org keys if user is LEADER
        if (!empty($leaderOrgKeys)) {
            $response['team_org_key_ids'] = $leaderOrgKeys;
        }

        return response()->json($response, 200);
    }

    public function login_old(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'country_code' => 'required|string|max:5',
            'login_input' => 'required|string', // Can be email or phone
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Determine if input is email or phone
        $isEmail = filter_var($request->login_input, FILTER_VALIDATE_EMAIL);
        $field = $isEmail ? 'email' : 'phone_no';

        // Find user based on provided credentials
        $user = User::where('country_code', $request->country_code)
            ->where($field, $request->login_input)
            ->first();

        // Check if user exists
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
                'errors' => ['auth' => 'The provided credentials are incorrect']
            ], 404);
        }

        // Check if org_key_id is null and generate it if needed
        if (empty($user->org_key_id)) {
            $numbers = '0123456789';
            $alphaChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $orgKeyId = '';

            // Generate 14 random numbers
            for ($i = 0; $i < 14; $i++) {
                $orgKeyId .= $numbers[rand(0, strlen($numbers) - 1)];
            }

            // Insert 2 alpha characters at random positions
            $positions = array_rand(range(0, 15), 2);
            foreach ($positions as $pos) {
                $orgKeyId = substr_replace(
                    $orgKeyId,
                    $alphaChars[rand(0, strlen($alphaChars) - 1)],
                    $pos,
                    0
                );
            }

            // Ensure exactly 16 characters
            $orgKeyId = substr($orgKeyId, 0, 16);

            $user->update(['org_key_id' => $orgKeyId]);
            $user->refresh(); // Refresh to get the updated org_key_id
        }

        // Manually create the JWT payload to ensure all claims are properly set
        $customClaims = [
            'sub' => $user->id, // This is the REQUIRED subject claim
            'org_key_id' => $user->org_key_id,
            'iat' => now()->timestamp, // Issued at
            'exp' => now()->addMinutes(auth()->factory()->getTTL())->timestamp, // Expiration
            'jti' => Str::random(20), // JWT ID
        ];

        // Generate token with custom claims
        $token = auth()->claims($customClaims)->login($user);

        // Alternative approach if the above still fails - use the payload factory directly
        if (!$token) {
            try {
                $payload = auth()->factory()->make($customClaims);
                $token = auth()->manager()->encode($payload)->get();
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token generation failed',
                    'errors' => ['auth' => 'Could not generate authentication token']
                ], 500);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'user_id' => $user->id,
            'org_key_id' => $user->org_key_id,
            'JWT_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => [
                'org_key_id' => $user->org_key_id,
                'email' => $user->email,
                'country_code' => $user->country_code,
                'phone_no' => $user->phone_no,
                'organization_verified' => $user->organization_verified,
                'verification_reason' => $user->verification_reason,
                'role' => $user->role,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();

        return response()->json([
            'status' => true,
            'user' => $user,
            'message' => 'You logged Out Successfully',
        ], 200);
    }
}
