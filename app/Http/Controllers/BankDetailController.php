<?php

namespace App\Http\Controllers;
use App\Models\BankDetail;
use Illuminate\Http\Request;

class BankDetailController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|integer',              // for update
            'org_id' => 'nullable|integer',
            'bank_name' => 'nullable|string',
            'account_no' => 'nullable|string',
            'account_holder_name' => 'nullable|string',
            'iban' => 'nullable|string',
            'branch_address' => 'nullable|string',
            'zelle_name' => 'nullable|string',
            'zelle_email' => 'nullable|email',
            'zelle_phone' => 'nullable|string',
            'isZelle' => 'nullable|boolean',
        ]);

        if ($request->org_id) {
            // UPDATE
            $bankDetail = BankDetail::find($request->org_id);

            if (!$bankDetail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Record not found'
                ], 404);
            }

            $bankDetail->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Bank details updated successfully',
                'data' => $bankDetail
            ]);
        } else {
            // CREATE
            $bankDetail = BankDetail::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Bank details saved successfully',
                'data' => $bankDetail
            ]);
        }
    }

    public function show($org_id)
    {
        $data = BankDetail::where('org_id', $org_id)->first();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function index()
    {
        $data = BankDetail::orderBy('id', 'DESC')->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
