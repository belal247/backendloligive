<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DefaultPackage;

class PackageController extends Controller
{
    public function index()
    {
        $packages = DefaultPackage::select('id', 'package_name', 'monthly_limit', 'overage_rate','package_price', 'package_period','package_description')->get();

        return response()->json([
            'status' => true,
            'message' => 'Packages retrieved successfully',
            'data' => $packages
        ], 200);
    }
    public function show(Request $request, $id)
    {
        try {
            $package = DefaultPackage::select(
                'id',
                'package_name',
                'monthly_limit',
                'overage_rate',
                'package_price',
                'package_period',
                'package_description'
            )->findOrFail($id);

            return response()->json([
                'status'  => true,
                'message' => 'Package retrieved successfully',
                'data'    => $package
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Package not found'
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve package',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'monthly_limit'         => 'sometimes|required|integer|min:0',
            'overage_rate'          => 'sometimes|required|numeric|min:0',
            'package_price'         => 'nullable|numeric|min:0',
            'package_period'        => 'nullable|string|max:50',
            'package_description'   => 'nullable|string|max:255',
        ]);

        try {
            $package = DefaultPackage::findOrFail($id);
            
            $package->update($validated);

            return response()->json([
                'status'  => true,
                'message' => 'Package updated successfully',
                'data'    => $package
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Package not found'
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to update package',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    public function store(Request $request)
    {
        $request->validate([
            'package_name'          => 'required|string',
            'monthly_limit'         => 'required|integer|min:0',
            'overage_rate'          => 'required|numeric|min:0',
            'package_price'         => 'nullable|numeric|min:0',
            'package_period'        => 'nullable|string|max:50',
            'package_description'   => 'nullable|string|max:255',
        ]);

        $package = DefaultPackage::create($request->only([
            'package_name',
            'monthly_limit',
            'overage_rate',
            'package_price',
            'package_period',
            'package_description'
        ]));

        return response()->json([
            'status'  => true,
            'message' => 'Package created successfully',
            'data'    => $package
        ]);
    }

}
