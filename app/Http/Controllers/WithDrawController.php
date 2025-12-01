<?php

namespace App\Http\Controllers;
use App\Models\BankDetail;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

use App\Mail\WithdrawalRequestMail;
use Illuminate\Support\Facades\Mail;

class WithDrawController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'org_id' => 'required|string',
            'amount' => 'required|numeric'
        ]);

        $bank = BankDetail::where('org_id', $data['org_id'])->first();

        if (!$bank) {
            return response()->json([
                'success' => false,
                'message' => 'Bank details not found for this org_id'
            ], 404);
        }

        $saveData = [
            'org_id' => $data['org_id'],
            'amount' => $data['amount'],
            'isZelle' => $bank->isZelle,
            'withdrawal_status' => 0 // default
        ];

        if ($bank->isZelle) {
            $saveData['zelle_name'] = $bank->zelle_name;
            $saveData['zelle_email'] = $bank->zelle_email;
            $saveData['zelle_phone'] = $bank->zelle_phone;
        } else {
            $saveData['bank_name'] = $bank->bank_name;
            $saveData['account_no'] = $bank->account_no;
            $saveData['account_holder_name'] = $bank->account_holder_name;
            $saveData['iban'] = $bank->iban;
            $saveData['branch_address'] = $bank->branch_address;
        }

        $withdrawal = Withdrawal::create($saveData);

        Mail::to('support@lolligive.com')->send(new WithdrawalRequestMail($withdrawal));

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal request created successfully',
            'data' => $withdrawal
        ]);
    }

    public function index()
    {
        $data = Withdrawal::orderBy('id', 'DESC')->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function getByOrgId($org_id)
    {
        // Fetch withdrawals by org_id in descending order
        $data = Withdrawal::where('org_id', $org_id)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }


    public function updateWithdrawalStatus(Request $request)
    {
        $data = $request->validate(['org_id' => 'required|string', 'id' => 'required|integer', 'withdrawal_status' => 'required|integer',]);// Find the withdrawal record by org_id and id
        $withdrawal = Withdrawal::where('org_id', $data['org_id'])
            ->where('id', $data['id'])
            ->first();

        // If record not found
        if (!$withdrawal) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found for this org_id and id.'
            ]);
        }

        // Update the withdrawal status
        $withdrawal->update([
            'withdrawal_status' => $data['withdrawal_status']
        ]);

        // Return response
        return response()->json([
            'success' => true,
            'message' => 'Withdrawal status updated successfully.',
            'data' => $withdrawal
        ]);
    }

}
