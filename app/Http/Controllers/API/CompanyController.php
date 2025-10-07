<?php
// app/Http/Controllers/CompanyController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'org_key_id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'alias' => 'required|string|max:255',
            'logo' => 'nullable|url|max:2000',
            'mainImage' => 'nullable|url|max:2000',
            'welcomeText' => 'nullable|string|max:1000',
            'testimonyText' => 'nullable|string|max:1000',
            'aboutUsText' => 'nullable|string|max:2000',
            'aboutUsImage' => 'nullable|url|max:2000',
            'donationMessage' => 'nullable|string|max:1000',
            'videoUrl' => 'nullable|url|max:2000',
            'contactInfo' => 'nullable|array',
            'contactInfo.address' => 'nullable|string|max:500',
            'contactInfo.phone' => 'nullable|string|max:20',
            'contactInfo.email' => 'nullable|email|max:255',
            'purpose_reason' => 'nullable|array',
            'purpose_reason.*' => 'string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $action = 'created'; // Track whether we're creating or updating
            
            // Check if company exists with the same orgId
            $company = Company::where('org_key_id', $request->org_key_id)->first();

            if ($company) {
                // Update existing company
                $action = 'updated';
                
                $updateData = [
                    'name' => $request->name,
                    'alias' => $request->alias,
                    'logo' => $request->logo,
                    'main_image' => $request->mainImage,
                    'welcome_text' => $request->welcomeText,
                    'testimony_text' => $request->testimonyText,
                    'about_us_text' => $request->aboutUsText,
                    'about_us_image' => $request->aboutUsImage,
                    'donation_message' => $request->donationMessage,
                    'video_url' => $request->videoUrl,
                    'contact_info' => $request->contactInfo,
                    'purpose_reason' => $request->purpose_reason,
                ];

                $company->update($updateData);

            } else {
                // Create new company
                $company = Company::create([
                    'org_key_id' => $request->org_key_id,
                    'name' => $request->name,
                    'alias' => $request->alias,
                    'logo' => $request->logo,
                    'main_image' => $request->mainImage,
                    'welcome_text' => $request->welcomeText,
                    'testimony_text' => $request->testimonyText,
                    'about_us_text' => $request->aboutUsText,
                    'about_us_image' => $request->aboutUsImage,
                    'donation_message' => $request->donationMessage,
                    'video_url' => $request->videoUrl,
                    'contact_info' => $request->contactInfo,
                    'purpose_reason' => $request->purpose_reason,
                ]);
            }

            // Refresh the company instance to get updated data
            $company->refresh();

            return response()->json([
                'success' => true,
                'message' => "Company {$action} successfully.",
                'data' => [
                    'org_key_id' => $company->org_key_id,
                    'name' => $company->name,
                    'alias' => $company->alias,
                    'logo' => $company->logo,
                    'mainImage' => $company->main_image,
                    'welcomeText' => $company->welcome_text,
                    'testimonyText' => $company->testimony_text,
                    'aboutUsText' => $company->about_us_text,
                    'aboutUsImage' => $company->about_us_image,
                    'donationMessage' => $company->donation_message,
                    'videoUrl' => $company->video_url,
                    'contactInfo' => $company->contact_info,
                    'purpose_reason' => $company->purpose_reason,
                    'createdAt' => $company->created_at->toISOString(),
                    'updatedAt' => $company->updated_at->toISOString(),
                ]
            ], $action === 'created' ? 201 : 200);

        } catch (\Exception $e) {
            \Log::error('Company store error: ' . $e->getMessage(), [
                'org_key_id' => $request->org_key_id,
                'exception' => $e
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process company.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get company by org_key_id
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'org_key_id' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $org_key_id = $request->input('org_key_id');
            
            // Find company by org_key_id
            $company = Company::where('org_key_id', $org_key_id)->first();

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Company retrieved successfully.',
                'data' => [
                    'orgId' => $company->org_key_id,
                    'name' => $company->name,
                    'alias' => $company->alias,
                    'logo' => $company->logo,
                    'mainImage' => $company->main_image,
                    'welcomeText' => $company->welcome_text,
                    'testimonyText' => $company->testimony_text,
                    'aboutUsText' => $company->about_us_text,
                    'aboutUsImage' => $company->about_us_image,
                    'donationMessage' => $company->donation_message,
                    'videoUrl' => $company->video_url,
                    'contactInfo' => $company->contact_info,
                    'purpose_reason' => $company->purpose_reason,
                    'updatedAt' => $company->updated_at->toISOString(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve company.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get company by alias
     */
    public function showAlias(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'alias' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $alias = $request->input('alias');
            
            // Find company by org_key_id
            $company = Company::where('alias', $alias)->first();

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Company retrieved successfully.',
                'data' => [
                    'orgId' => $company->org_key_id,
                    'name' => $company->name,
                    'alias' => $company->alias,
                    'logo' => $company->logo,
                    'mainImage' => $company->main_image,
                    'welcomeText' => $company->welcome_text,
                    'testimonyText' => $company->testimony_text,
                    'aboutUsText' => $company->about_us_text,
                    'aboutUsImage' => $company->about_us_image,
                    'donationMessage' => $company->donation_message,
                    'videoUrl' => $company->video_url,
                    'contactInfo' => $company->contact_info,
                    'purpose_reason' => $company->purpose_reason,
                    'updatedAt' => $company->updated_at->toISOString(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve company.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}