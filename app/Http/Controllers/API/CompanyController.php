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
            'name' => 'required|string|max:255',
            'org_key_id' => 'required|string|max:255',
            'alias' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120', // 5MB max
            'description' => 'nullable|string',
            'video' => 'nullable|url',
            'purpose_reason' => 'nullable|array',
            'purpose_reason.*' => 'string|max:500',
            'location' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $logoPath = null;
            $action = 'created'; // Track whether we're creating or updating
            
            // Handle logo upload with custom filename
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $logoPath = $file->storeAs('logos', $fileName, 'public');
            }

            // Check if company exists with the same org_key_id
            $company = Company::where('org_key_id', $request->org_key_id)->first();

            if ($company) {
                // Update existing company
                $action = 'updated';
                
                // Delete old logo if new logo is uploaded
                if ($request->hasFile('logo') && $company->logo) {
                    Storage::disk('public')->delete($company->logo);
                }

                $updateData = [
                    'name' => $request->name,
                    'alias' => $request->alias,
                    'description' => $request->description,
                    'video' => $request->video,
                    'purpose_reason' => $request->purpose_reason,
                    'location' => $request->location,
                ];

                // Only update logo path if a new logo was uploaded
                if ($request->hasFile('logo')) {
                    $updateData['logo'] = $logoPath;
                }

                $company->update($updateData);

            } else {
                // Create new company
                $company = Company::create([
                    'org_key_id' => $request->org_key_id,
                    'name' => $request->name,
                    'alias' => $request->alias,
                    'logo' => $logoPath,
                    'description' => $request->description,
                    'video' => $request->video,
                    'purpose_reason' => $request->purpose_reason,
                    'location' => $request->location,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Company {$action} successfully.",
                'data' => [
                    'org_key_id' => $company->org_key_id,
                    'name' => $company->name,
                    'alias' => $company->alias,
                    'logo_url' => $company->logo ? Storage::disk('public')->url($company->logo) : null,
                    'description' => $company->description,
                    'video' => $company->video,
                    'purpose_reason' => $company->purpose_reason,
                    'location' => $company->location,
                    'created_at' => $company->created_at->toISOString(),
                    'updated_at' => $company->updated_at->toISOString(),
                ]
            ], $action === 'created' ? 201 : 200);

        } catch (\Exception $e) {
            // Delete uploaded file if operation fails
            if (isset($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process company.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get company by org_key_id
     */
    public function show($org_key_id): JsonResponse
    {
        try {
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
                    'org_key_id' => $company->org_key_id,
                    'name' => $company->name,
                    'alias' => $company->alias,
                    'logo_url' => $company->logo ? Storage::disk('public')->url($company->logo) : null,
                    'description' => $company->description,
                    'video' => $company->video,
                    'purpose_reason' => $company->purpose_reason,
                    'location' => $company->location,
                    'created_at' => $company->created_at->toISOString(),
                    'updated_at' => $company->updated_at->toISOString(),
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