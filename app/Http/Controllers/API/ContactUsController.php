<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactUsSubmission;

class ContactUsController extends Controller
{
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'companyname' => 'required|string|max:255',
            'contactname' => 'required|string|max:255',
            'businessemail' => 'required|email|max:255',
            'phoneno' => 'required|string|max:255',
            'businesstype' => 'required|string|max:255',
            'expected_monthly_income' => 'required|string|max:255',
            'currentpayment_provider' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Store the submission in database
        $submission = ContactSubmission::create([
            'company_name' => $request->companyname,
            'contact_name' => $request->contactname,
            'business_email' => $request->businessemail,
            'phone_no' => $request->phoneno,
            'business_type' => $request->businesstype,
            'expected_monthly_income' => $request->expected_monthly_income,
            'current_payment_provider' => $request->currentpayment_provider,
            'description' => $request->description,
        ]);

        // Send email (uncomment when ready)
        // Mail::to('support@cardnest.io')->send(new ContactUsSubmission($submission));

        return response()->json([
            'message' => 'Thank you for contacting us! We will get back to you soon.',
            'data' => $submission
        ], 201);
    }
}