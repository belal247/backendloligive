<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\SuperAdminDoc;
use App\Models\User;
use App\Models\BusinessProfile;
use App\Models\CardScan;
use App\Models\OldSubscription;
use App\Models\Subscription;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;


class SuperAdminDocController extends Controller
{
    public function getEnterpriseUsersWithSubBusinesses(Request $request)
    {
        try {
            // Get all enterprise users with their business profiles
            $enterpriseUsers = User::with('businessProfile')
                ->where('role', 'ENTERPRISE_USER')
                ->get();

            // Format the response with their sub-businesses
            $formattedEnterpriseUsers = $enterpriseUsers->map(function ($enterpriseUser) {
                // Get all sub-businesses for this enterprise user
                $subBusinesses = User::with('businessProfile')
                    ->where('parent_id', $enterpriseUser->merchant_id)
                    ->where('role', 'SUB_BUSINESS')
                    ->get()
                    ->map(function ($user) {
                        return [
                            'merchant_id' => $user->merchant_id,
                            'email' => $user->email,
                            'aes_key' => $user->aes_key, // Added aes_key
                            'business_verified' => $user->business_verified, // Added business_verified
                            'created_at' => $user->created_at,
                            'business_profile' => $user->businessProfile ? [
                                'business_name' => $user->businessProfile->business_name,
                                'business_registration_number' => $user->businessProfile->business_registration_number,
                                'street' => $user->businessProfile->street,
                                'street_line2' => $user->businessProfile->street_line2,
                                'city' => $user->businessProfile->city,
                                'state' => $user->businessProfile->state,
                                'zip_code' => $user->businessProfile->zip_code,
                                'country' => $user->businessProfile->country,
                                'account_holder_first_name' => $user->businessProfile->account_holder_first_name,
                                'account_holder_last_name' => $user->businessProfile->account_holder_last_name,
                                'account_holder_email' => $user->businessProfile->account_holder_email,
                                'account_holder_date_of_birth' => $user->businessProfile->account_holder_date_of_birth,
                                'account_holder_id_type' => $user->businessProfile->account_holder_id_type,
                                'account_holder_id_number' => $user->businessProfile->account_holder_id_number,
                                'registration_document_url' => $user->businessProfile->registration_document_path ? asset('storage/'.$user->businessProfile->registration_document_path) : null,
                                'account_holder_id_document_url' => $user->businessProfile->account_holder_id_document_path ? asset('storage/'.$user->businessProfile->account_holder_id_document_path) : null,
                            ] : null
                        ];
                    });

                return [
                    'enterprise_user' => [
                        'merchant_id' => $enterpriseUser->merchant_id,
                        'email' => $enterpriseUser->email,
                        'aes_key' => $enterpriseUser->aes_key, // Added aes_key
                        'business_verified' => $enterpriseUser->business_verified, // Added business_verified
                        'created_at' => $enterpriseUser->created_at,
                        'business_profile' => $enterpriseUser->businessProfile ? [
                            'business_name' => $enterpriseUser->businessProfile->business_name,
                            'business_registration_number' => $enterpriseUser->businessProfile->business_registration_number,
                            'street' => $enterpriseUser->businessProfile->street,
                            'street_line2' => $enterpriseUser->businessProfile->street_line2,
                            'city' => $enterpriseUser->businessProfile->city,
                            'state' => $enterpriseUser->businessProfile->state,
                            'zip_code' => $enterpriseUser->businessProfile->zip_code,
                            'country' => $enterpriseUser->businessProfile->country,
                            'account_holder_first_name' => $enterpriseUser->businessProfile->account_holder_first_name,
                            'account_holder_last_name' => $enterpriseUser->businessProfile->account_holder_last_name,
                            'account_holder_email' => $enterpriseUser->businessProfile->account_holder_email,
                            'account_holder_date_of_birth' => $enterpriseUser->businessProfile->account_holder_date_of_birth,
                            'account_holder_id_type' => $enterpriseUser->businessProfile->account_holder_id_type,
                            'account_holder_id_number' => $enterpriseUser->businessProfile->account_holder_id_number,
                            'registration_document_url' => $enterpriseUser->businessProfile->registration_document_path ? asset('storage/'.$enterpriseUser->businessProfile->registration_document_path) : null,
                            'account_holder_id_document_url' => $enterpriseUser->businessProfile->account_holder_id_document_path ? asset('storage/'.$enterpriseUser->businessProfile->account_holder_id_document_path) : null,
                        ] : null
                    ],
                    'sub_businesses' => $subBusinesses,
                    'sub_businesses_count' => $subBusinesses->count()
                ];
            });

            return response()->json([
                'message' => 'Enterprise users with their sub-businesses retrieved successfully',
                'data' => $formattedEnterpriseUsers,
                'total_enterprise_users' => $enterpriseUsers->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve enterprise users with sub-businesses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function get_sub_businesses(Request $request)
    {
        try {
            $validated = $request->validate([
                'parent_id' => 'required|exists:users,merchant_id'
            ]);

            $parentUser = User::where('merchant_id', $validated['parent_id'])
                            ->firstOrFail();

            // Get all sub-businesses with their business profiles
            $subBusinesses = User::with('businessProfile')
                                ->where('parent_id', $parentUser->merchant_id)
                                ->where('role', 'SUB_BUSINESS')
                                ->get();

            // Format the response
            $formattedSubBusinesses = $subBusinesses->map(function ($user) {
                return [
                        'merchant_id' => $user->merchant_id,
                        'email' => $user->email,
                        'created_at' => $user->created_at,
                        'business_profile' => $user->businessProfile ? [
                        'business_name' => $user->businessProfile->business_name,
                        'business_registration_number' => $user->businessProfile->business_registration_number,
                        'street' => $user->businessProfile->street,
                        'street_line2' => $user->businessProfile->street_line2,
                        'city' => $user->businessProfile->city,
                        'state' => $user->businessProfile->state,
                        'zip_code' => $user->businessProfile->zip_code,
                        'country' => $user->businessProfile->country,
                        'account_holder_first_name' => $user->businessProfile->account_holder_first_name,
                        'account_holder_last_name' => $user->businessProfile->account_holder_last_name,
                        'account_holder_email' => $user->businessProfile->account_holder_email,
                        'account_holder_date_of_birth' => $user->businessProfile->account_holder_date_of_birth,
                        'account_holder_id_type' => $user->businessProfile->account_holder_id_type,
                        'account_holder_id_number' => $user->businessProfile->account_holder_id_number,
                        'registration_document_url' => $user->businessProfile->registration_document_path ? asset('storage/'.$user->businessProfile->registration_document_path) : null,
                        'account_holder_id_document_url' => $user->businessProfile->account_holder_id_document_path  ? asset('storage/'.$user->businessProfile->account_holder_id_document_path) : null,
                    ] : null
                ];
            });

            return response()->json([
                'message' => 'Sub-businesses retrieved successfully',
                'data' => [
                    'parent_id' => $parentUser->merchant_id,
                    'sub_businesses' => $formattedSubBusinesses
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Parent user not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve sub-businesses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sub_business_store(Request $request)
    {
        try {
            $validated = $request->validate([
                'parent_id' => 'required|exists:users,merchant_id',
                'sub_businesses' => 'required|array|min:1',
                'sub_businesses.*.sub_b_name' => 'required|string|max:255',
                'sub_businesses.*.sub_b_email' => 'required|email',
                'sub_businesses.*.sub_b_reg_no' => 'required|string',
                'sub_businesses.*.sub_b_street' => 'required|string',
                'sub_businesses.*.sub_b_street_line2' => 'nullable|string',
                'sub_businesses.*.sub_b_city' => 'required|string',
                'sub_businesses.*.sub_b_state' => 'required|string',
                'sub_businesses.*.sub_b_zip_code' => 'required|string',
                'sub_businesses.*.sub_b_country' => 'required|string',
                'sub_businesses.*.account_holder_first_name' => 'required|string',
                'sub_businesses.*.account_holder_last_name' => 'required|string',
                'sub_businesses.*.account_holder_email' => 'required|email',
                'sub_businesses.*.account_holder_date_of_birth' => 'required|date',
                'sub_businesses.*.account_holder_street' => 'required|string',
                'sub_businesses.*.account_holder_street_line2' => 'nullable|string',
                'sub_businesses.*.account_holder_city' => 'required|string',
                'sub_businesses.*.account_holder_state' => 'required|string',
                'sub_businesses.*.account_holder_zip_code' => 'nullable|string',
                'sub_businesses.*.account_holder_country' => 'required|string',
                'sub_businesses.*.account_holder_id_type' => 'required|string',
                'sub_businesses.*.account_holder_id_number' => 'required|string',
                'sub_businesses.*.registration_document' => 'required|file',
                'sub_businesses.*.account_holder_id_document' => 'required|file'
            ]);

            DB::beginTransaction();

            $parentUser = User::where('merchant_id', $validated['parent_id'])->firstOrFail();
            $parentUser->update(['role' => 'ENTERPRISE_USER']);

            $createdSubBusinesses = [];
            foreach ($validated['sub_businesses'] as $subBusiness) {
                $aesKey = Str::random(16);

                $user = User::create([
                    'email' => $subBusiness['sub_b_email'],
                    'aes_key' => $aesKey,
                    'role' => 'SUB_BUSINESS',
                    'parent_id' => $parentUser->merchant_id,
                    'business_verified' => 'APPROVED' // Set business_verified to APPROVED
                ]);

                //$merchantId = 'mer' . str_pad($user->id, 6, '0', STR_PAD_LEFT);

                // Generate new format merchant_id (16 characters with numbers and alphabets chars)
                $numbers = '0123456789';
                //$specialChars = '@#$%&*!?';
                $alphaChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $merchantId = '';
                
                // Generate 14 random numbers
                for ($i = 0; $i < 14; $i++) {
                    $merchantId .= $numbers[rand(0, strlen($numbers) - 1)];
                }
                
                // Insert 2 special characters at random positions
                $positions = array_rand(range(0, 15), 2);
                foreach ($positions as $pos) {
                    $merchantId = substr_replace(
                        $merchantId, 
                        $alphaChars[rand(0, strlen($alphaChars) - 1)], 
                        $pos, 
                        0
                    );
                }
                
                // Ensure exactly 16 characters
                $merchantId = substr($merchantId, 0, 16);

                $user->update(['merchant_id' => $merchantId]);

                $registrationDocPath = $subBusiness['registration_document']->store('business_documents', 'public');
                $idDocPath = $subBusiness['account_holder_id_document']->store('id_documents', 'public');

                $businessProfile = BusinessProfile::create([
                    'user_id' => $user->id,
                    'business_name' => $subBusiness['sub_b_name'],
                    'business_registration_number' => $subBusiness['sub_b_reg_no'],
                    'street' => $subBusiness['sub_b_street'],
                    'street_line2' => $subBusiness['sub_b_street_line2'] ?? null,
                    'city' => $subBusiness['sub_b_city'],
                    'state' => $subBusiness['sub_b_state'],
                    'zip_code' => $subBusiness['sub_b_zip_code'],
                    'country' => $subBusiness['sub_b_country'],
                    'account_holder_first_name' => $subBusiness['account_holder_first_name'],
                    'account_holder_last_name' => $subBusiness['account_holder_last_name'],
                    'account_holder_email' => $subBusiness['account_holder_email'],
                    'account_holder_date_of_birth' => $subBusiness['account_holder_date_of_birth'],
                    'account_holder_street' => $subBusiness['account_holder_street'],
                    'account_holder_street_line2' => $subBusiness['account_holder_street_line2'] ?? null,
                    'account_holder_city' => $subBusiness['account_holder_city'],
                    'account_holder_state' => $subBusiness['account_holder_state'],
                    'account_holder_zip_code' => $subBusiness['account_holder_zip_code'] ?? null,
                    'account_holder_country' => $subBusiness['account_holder_country'],
                    'account_holder_id_type' => $subBusiness['account_holder_id_type'],
                    'account_holder_id_number' => $subBusiness['account_holder_id_number'],
                    'registration_document_path' => $registrationDocPath,
                    'account_holder_id_document_path' => $idDocPath,
                ]);

                $createdSubBusinesses[] = [
                    'user' => $user->fresh(),
                    'business_profile' => $businessProfile
                ];
            }

            DB::commit();

            return response()->json([
                'message' => 'Sub-businesses created successfully and parent role updated',
                'data' => [
                    'parent_id' => $validated['parent_id'],
                    'sub_businesses' => $createdSubBusinesses
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Parent user not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create sub-businesses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request)
    {
        $request->validate([
            'merchant_id' => 'required|exists:users,merchant_id'
        ]);

        // Find the user by merchant_id and load their business profile
        $user = User::where('merchant_id', $request->merchant_id)->first();
        
        if (!$user || !$user->businessProfile) {
            return response()->json([
                'message' => 'Business profile not found'
            ], 404);
        }

        return response()->json([
            'data' => $user->businessProfile
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'display_name' => 'required|string|max:255',
            'display_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = [
            'user_id' => $request->user_id,
            'display_name' => $request->display_name,
        ];

        // Handle logo upload
        if ($request->hasFile('display_logo')) {
            // Delete old logo if exists
            $existingProfile = BusinessProfile::where('user_id', $request->user_id)->first();
            if ($existingProfile && $existingProfile->display_logo) {
                // Convert the URL back to storage path for deletion
                $oldLogoPath = str_replace('https://admin.cardnest.io/storage/', '', $existingProfile->display_logo);
                Storage::disk('public')->delete($oldLogoPath);
            }
            
            $path = $request->file('display_logo')->store('business_logos', 'public');
            $data['display_logo'] = 'https://admin.cardnest.io/storage/' . $path;
        }

        // Update or create the business profile
        $businessProfile = BusinessProfile::updateOrCreate(
            ['user_id' => $request->user_id],
            $data
        );

        return response()->json([
            'message' => 'Business profile ' . ($businessProfile->wasRecentlyCreated ? 'created' : 'updated') . ' successfully',
            'data' => $businessProfile,
        ], 200);
    }
    
    public function grantSubadminAccess(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admin_email'   => 'required',
            'user_email'    => 'required',
            'role'          => 'required|string', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'Validation Error',
                'errors'    => $validator->errors()
            ], 422);
        }
        $admin = User::where('email', $request->admin_email)->first();
        if ($admin) {
            # code...
            $user = User::where('email', $request->user_email)->first();
        
            if (!$user) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'User not found with this email',
                    'code'      => 404
                ], 404);
            }
        
            $user->role = $request->role;
            $user->save();
        
            return response()->json([
                'status'  => true,
                'message' => 'Role updated successfully',
                'data'    => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'role'    => $user->role
                ]
            ]);
        }
        else {
            return response()->json([
                'status'  => false,
                'message' => 'You have no rights to do changes.',
                'code'    => 404
            ]);
        }
    }

    public function uploadDocumentation(Request $request)
    {
            // Custom validation with messages
            $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'required|in:PDF,DOCX,IMAGE',
            'fileName'    => 'required|string|max:255',
            'fileType'    => 'required|string|max:100',
            'fileBase'    => ['required', 'string', 'regex:/^[A-Za-z0-9+\/=]+$/']
            ], [
            'type.in'            => 'Type must be one of: PDF, DOCX, IMAGE.',
            'fileBase.regex'     => 'The fileBase must be a valid base64 encoded string.',
            ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Decode and store file
            $fileData = base64_decode($request->fileBase);

            if ($fileData === false) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid base64 string.'
                ], 400);
            }

            $uniqueName = uniqid() . '_' . preg_replace('/[^A-Za-z0-9_\.\-]/', '_', $request->fileName);
            $path = 'documents/' . $uniqueName;

            Storage::disk('public')->put($path, $fileData);

            // Save to DB
            $doc = SuperAdminDoc::create([
                'title'       => $request->title,
                'description' => $request->description,
                'type'        => $request->type,
                'file_path'   => $path,
                'file_type'   => $request->fileType,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Documentation uploaded successfully.',
                'data'    => $doc
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'An error occurred while uploading documentation.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function getDocumentation(Request $request)
    {
        $docs = SuperAdminDoc::all();

        if ($docs->isEmpty()) {
            return response()->json([
                'status'  => 'not_found',
                'message' => 'No documentation found.',
                'data'    => []
            ], 404);
        }

        // Add file_url for each document
        $docs->transform(function ($doc) {
            $doc->file_url = asset('storage/' . ltrim($doc->file_path, '/'));
            return $doc;
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Documentation list retrieved.',
            'data'    => $docs
        ], 200);
    }

    public function accessAllScans(Request $request)
    {
        $scan = CardScan::select(
                            'card_scans.user_id',
                            'card_scans.merchant_id',
                            'card_scans.merchant_key',
                            'card_scans.card_number_masked',
                            'card_scans.status',
                            'business_profiles.business_name'
                        )
                        ->leftJoin('business_profiles', 'card_scans.user_id', '=', 'business_profiles.user_id')
                        ->get();

        return response()->json([
            'status' => true,
            'message' => 'All Card Scans records retrieved successfully',
            'data' => $scan
        ], 200);
    }
    
    public function accessAllOldSubscriptions(Request $request)
    {
        // Get old subscriptions
        $oldSubscriptions = OldSubscription::select(
            'user_id', 
            'merchant_id', 
            'package_id',
            'api_calls_limit', 
            'api_calls_used', 
            'overage_calls', 
            'subscription_date',
            'renewal_date', 
            'is_blocked',
            'custom_package',
            'custom_price',
            'custom_calls_used',
            'custom_status',
            'custom_api_count'
        )->get();

        // Get current subscriptions
        $subscriptions = Subscription::select(
            'user_id', 
            'merchant_id', 
            'package_id',
            'api_calls_limit', 
            'api_calls_used', 
            'overage_calls', 
            'subscription_date',
            'renewal_date', 
            'is_blocked',
            'custom_package',
            'custom_price',
            'custom_calls_used',
            'custom_status',
            'custom_api_count'
        )->get();

        // Combine both collections
        $allSubscriptions = $oldSubscriptions->concat($subscriptions);

        return response()->json([
            'status' => true,
            'message' => 'All subscription records retrieved successfully',
            'data' => $allSubscriptions,
            'metadata' => [
                'old_subscriptions_count' => $oldSubscriptions->count(),
                'current_subscriptions_count' => $subscriptions->count(),
                'total_records' => $allSubscriptions->count()
            ]
        ], 200);
    }
}
