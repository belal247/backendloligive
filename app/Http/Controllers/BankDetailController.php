<?php

namespace App\Http\Controllers;
use App\Models\BankDetail;
use Illuminate\Http\Request;

class BankDetailController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'org_id' => 'required|string',   // Make org_id required for both create & update
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

        // Try to find existing record by org_id
        $bankDetail = BankDetail::where('org_id', $request->org_id)->first();

        if ($bankDetail) {
            // UPDATE existing record
            $bankDetail->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Bank details updated successfully',
                'data' => $bankDetail
            ]);
        } else {
            // CREATE new record
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
        $data = BankDetail::with(['user.businessProfile'])
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

}
