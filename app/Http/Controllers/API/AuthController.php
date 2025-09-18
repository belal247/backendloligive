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

class AuthController extends Controller
{
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
                ->orWhere(function($query) use ($request) {
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
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]
        ], 200);
    }
    
    public function logout(Request $request){
        $user = $request->user();
        $user->tokens()->delete();

        return response()->json([
            'status' => true,
            'user' => $user,
            'message' => 'You logged Out Successfully',
        ],200);
    }
}
