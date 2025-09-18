<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Feature;

class FeatureController extends Controller
{
    public function getFeatures(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer'
        ]);

        // Check if user exists
        $user = User::where('id', $request->user_id)->first();
        if (!$user) {
            return response()->json([
                'code' => 404,
                'message' => 'User not found with the provided user_id.'
            ], 404);
        }

        // Get features for the user
        $features = Feature::where('user_id', $request->user_id)->first();

        if (!$features) {
            return response()->json([
                'code' => 404,
                'message' => 'No features found for this user.'
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Features retrieved successfully.',
            'data' => $features
        ], 200);
    }
    
    public function store(Request $request)
    {
        if ($request->all() == []) {
            # code...
            return response()->json([
            'REQUESTS' => $request->all()], 200);
        }
        $request->validate([
            'user_id'           => 'required',
            'bank_logo'         => 'nullable|boolean',
            'chip'              => 'nullable|boolean',
            'mag_strip'         => 'nullable|boolean',
            'sig_strip'         => 'nullable|boolean',
            'hologram'          => 'nullable|boolean',
            'customer_service'  => 'nullable|boolean',
            'symmetry'          => 'nullable|boolean',
        ]);


        // Check if user_id exists in users table
        $user = User::where('id', $request->user_id)->first();
        if (!$user) {
            return response()->json([
                'code'    => 404,
                'message' => 'User not found with the provided user_id.'
            ], 404);
        }

        // Create or update Feature based on user_id
        $detail = Feature::updateOrCreate(
            ['user_id' => $request->user_id], // Search criteria
            $request->only(['bank_logo', 'chip',  'mag_strip', 'sig_strip', 'hologram', 'customer_service', 'symmetry']) // Data to update
        );

        return response()->json([
            'code'    => 200,
            'message' => 'Features saved successfully.',
            'data'    => $detail
        ], 200);
    }
    public function storeFeature(Request $request)
    {
        if (!$request->has('user_id')) {
            return response()->json(['error' => 'user_id is missing in the request.',
            'REQUEST' => $request->all()], 200);
        }
        $request->validate([
            'user_id'           => 'required',
            'bank_logo'         => 'nullable|boolean',
            'chip'              => 'nullable|boolean',
            'mag_strip'         => 'nullable|boolean',
            'sig_strip'         => 'nullable|boolean',
            'hologram'          => 'nullable|boolean',
            'customer_service'  => 'nullable|boolean',
            'symmetry'          => 'nullable|boolean',
        ]);


        // Check if user_id exists in users table
        $user = User::where('id', $request->user_id)->first();
        if (!$user) {
            return response()->json([
                'code'    => 404,
                'message' => 'User not found with the provided user_id.'
            ], 404);
        }

        // Create or update Feature based on user_id
        $detail = Feature::updateOrCreate(
            ['user_id' => $request->user_id], // Search criteria
            $request->only(['bank_logo', 'chip',  'mag_strip', 'sig_strip', 'hologram', 'customer_service', 'symmetry']) // Data to update
        );

        return response()->json([
            'code'    => 200,
            'message' => 'Features saved successfully.',
            'data'    => $detail
        ], 200);
    }
}
