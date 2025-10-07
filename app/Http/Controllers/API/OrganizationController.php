<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Company;
use App\Models\User;
use App\Models\BusinessProfile;

class OrganizationController extends Controller
{
    /**
     * Get all organizations with related users and business profiles
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Get all users with their related data (excluding transactions)
            $users = User::with(['businessProfile', 'company'])
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'org_key_id' => $user->org_key_id,
                        'email' => $user->email,
                        'country_code' => $user->country_code,
                        'country_name' => $user->country_name,
                        'phone_no' => $user->phone_no,
                        'organization_verified' => $user->organization_verified,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                        'company' => $user->company ? [
                            'name' => $user->company->name,
                            'alias' => $user->company->alias,
                            'logo' => $user->company->logo,
                            'description' => $user->company->description,
                            'video' => $user->company->video,
                            'purpose_reason' => $user->company->purpose_reason,
                            'location' => $user->company->location,
                        ] : null,
                        'business_profile' => $user->businessProfile ? [
                            'organization_name' => $user->businessProfile->organization_name,
                            'organization_registration_number' => $user->businessProfile->organization_registration_number,
                            'street' => $user->businessProfile->street,
                            'street_line2' => $user->businessProfile->street_line2,
                            'city' => $user->businessProfile->city,
                            'state' => $user->businessProfile->state,
                            'zip_code' => $user->businessProfile->zip_code,
                            'country' => $user->businessProfile->country,
                            'email' => $user->businessProfile->email,
                            'account_holder_first_name' => $user->businessProfile->account_holder_first_name,
                            'account_holder_last_name' => $user->businessProfile->account_holder_last_name,
                            'account_holder_email' => $user->businessProfile->account_holder_email,
                            'account_holder_date_of_birth' => $user->businessProfile->account_holder_date_of_birth,
                            'account_holder_street' => $user->businessProfile->account_holder_street,
                            'account_holder_street_line2' => $user->businessProfile->account_holder_street_line2,
                            'account_holder_city' => $user->businessProfile->account_holder_city,
                            'account_holder_state' => $user->businessProfile->account_holder_state,
                            'account_holder_zip_code' => $user->businessProfile->account_holder_zip_code,
                            'account_holder_country' => $user->businessProfile->account_holder_country,
                            'account_holder_id_type' => $user->businessProfile->account_holder_id_type,
                            'account_holder_id_number' => $user->businessProfile->account_holder_id_number,
                            'account_holder_id_document_path' => $user->businessProfile->account_holder_id_document_path,
                            'registration_document_path' => $user->businessProfile->registration_document_path,
                        ] : null,
                        'has_company' => !is_null($user->company),
                        'has_business_profile' => !is_null($user->businessProfile),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $users,
                'message' => 'All users retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
