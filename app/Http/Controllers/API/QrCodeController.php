<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class QrCodeController extends Controller
{
    /**
     * Generate QR code based on org_key_id and sub_domain_url
     */
    public function generate(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'org_key_id' => 'required|string|max:255',
            'sub_domain_url' => 'required|url|max:255'
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
            $subDomainUrl = $request->input('sub_domain_url');

            // Get the authenticated user
            //$user = Auth::user();
            $user = User::where('org_key_id', $orgKeyId)
                ->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Update user record with QR code details
            $user->update([
                'org_key_id' => $orgKeyId,
                'sub_domain_url' => $subDomainUrl
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sub Domain add successfully',
                'data' => [
                    'user_id' => $user->id,
                    'org_key_id' => $user->org_key_id,
                    'sub_domain_url' => $user->sub_domain_url
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add sub domain',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
