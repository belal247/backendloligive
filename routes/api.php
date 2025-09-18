<?php

use App\Http\Controllers\API\ContactUsController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\CardScanController;
use App\Http\Controllers\API\PackageController;
use App\Http\Controllers\API\SubscriptionController;
use App\Http\Controllers\API\BillingLogController;
use App\Http\Controllers\API\BusinessProfileController;
use App\Http\Controllers\API\ScanController;
use App\Http\Controllers\API\PaymentDetailController;
use App\Http\Controllers\API\FeatureController;
use App\Http\Controllers\API\SuperAdminDocController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::post('contact-us', [ContactUsController::class, 'store']);
Route::get('/check-storage', function () {
    // Check if storage link exists
    $storageLinkExists = file_exists(public_path('storage'));
    
    // Get all directories in the storage/app/public folder
    $directories = Storage::disk('public')->allDirectories();
    
    return response()->json([
        'storage_link_exists' => $storageLinkExists,
        'directories' => $directories,
        'message' => $storageLinkExists 
            ? 'Storage link exists and here are the directories'
            : 'Storage link does not exist. Run php artisan storage:link'
    ]);
});
Route::post('signup', [AuthController::class, 'signup']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('reset-otp', [AuthController::class, 'resetOtp']);
Route::post('login', [AuthController::class, 'login']);
Route::post('check-user-exists', [AuthController::class, 'checkUserExists']);

// Business Profile routes
Route::prefix('business-profile')->group(function () {
    Route::post('/', [BusinessProfileController::class, 'store']);
    Route::get('/', [BusinessProfileController::class, 'index']); // List documents for review
    Route::get('/approved', [BusinessProfileController::class, 'business_verified_approved']); // List documents for Approved Business
    Route::post('/decision', [BusinessProfileController::class, 'updateStatus']); // Approve/Reject
    Route::get('/business-verification-status', [BusinessProfileController::class, 'getStatus']);
});

Route::middleware('auth:sanctum')->group(function(){
	Route::post('logout', [AuthController::class, 'logout']);
	Route::apiResource('posts', PostController::class);
});


// Package API's
Route::get('/Packages', [PackageController::class, 'index']);
Route::post('/Packages/Update/{id}', [PackageController::class, 'update'])->where('id', '[0-9]+');
Route::get('/Packages/Show/{id}', [PackageController::class, 'show']);
//Route::post('/Packages', [PackageController::class, 'store']);

// Subscription API's
Route::get('/Subscriptions/GetByUserIDorMerchantID', [SubscriptionController::class, 'GetByUserIDorMerchantID']);
Route::post('/Subscriptions', [SubscriptionController::class, 'store']);
Route::post('/Custom/Subscriptions', [SubscriptionController::class, 'CustomSubscriptions']);
Route::post('/Subscriptions/customPackagePricing', [SubscriptionController::class, 'customPackagePricing']);
Route::post('/Subscriptions/customStore', [SubscriptionController::class, 'customStore']);
Route::get('/Subscriptions/customPackagePricing', [SubscriptionController::class, 'getCustomPackagePricing']);
Route::post('/Subscriptions/storeAchPayment', [SubscriptionController::class, 'storeAchPayment']);
Route::get('/Subscriptions/getAchPayments', [SubscriptionController::class, 'getAchPayments']);
Route::post('/Subscriptions/merchant', [SubscriptionController::class, 'updateByMerchantId']);

// BillingLogs API's
Route::get('/BillingLogs/GetByUserIDorMerchantID', [BillingLogController::class, 'GetByUserIDorMerchantID']);
Route::post('/BillingLogs', [BillingLogController::class, 'store']);

// Card Scan API's
Route::post('/UpdateCardScan', [CardScanController::class, 'UpdateCardScan']);
Route::get('/merchant/getCardScans', [CardScanController::class, 'getCardScans']);
Route::get('/merchant/getOldSubscriptions', [CardScanController::class, 'getOldSubscriptions']);

// SCAN Merchant Generate Token API's
Route::get('/getmerchantscanInfo', [ScanController::class, 'getmerchantscanInfo']);
Route::get('/getmerchantDisplayInfo', [ScanController::class, 'getmerchantDisplayInfo']);
Route::post('/updateMerchantScanInfo', [ScanController::class, 'updateMerchantScanInfo']);
Route::post('/updateMerchantDisplayInfo', [ScanController::class, 'updateMerchantDisplayInfo']);
Route::post('/merchantscan/generateToken', [ScanController::class, 'generateToken']);
Route::post('/scan/token', [ScanController::class, 'createScanToken']);
Route::post('/scan/submitEncryptedData', [ScanController::class, 'submitEncryptedData']);
Route::post('/scan/getEncryptedData', [ScanController::class, 'getEncryptedData']);
Route::post('/scan/decodeToken', [ScanController::class, 'decodeToken']);
// Feature API's
Route::post('/scan/storeFeature', [FeatureController::class, 'storeFeature']);
Route::post('/feature/store', [FeatureController::class, 'store']);
Route::get('/feature/get', [FeatureController::class, 'getFeatures']);
// Payment API's
Route::post('/payment/storeDetails', [PaymentDetailController::class, 'storeDetails']);
// SuperAdmin API's
Route::post('/superadmin/uploadDocumentation', [SuperAdminDocController::class, 'uploadDocumentation']);
Route::get('/superadmin/getDocumentation', [SuperAdminDocController::class, 'getDocumentation']);
Route::post('/superadmin/grant-subadmin-access', [SuperAdminDocController::class, 'grantSubadminAccess']);
Route::get('/superadmin/access-all-scans', [SuperAdminDocController::class, 'accessAllScans']);
Route::get('/superadmin/access-all-old-subscriptions', [SuperAdminDocController::class, 'accessAllOldSubscriptions']);
Route::post('/superadmin/store', [SuperAdminDocController::class, 'store']);
Route::get('/superadmin/show', [SuperAdminDocController::class, 'show']);
Route::post('/superadmin/sub-business-store', [SuperAdminDocController::class, 'sub_business_store']);
Route::get('/superadmin/get-sub-business', [SuperAdminDocController::class, 'get_sub_businesses']);
Route::get('/superadmin/get-enterprise-sub-business', [SuperAdminDocController::class, 'getEnterpriseUsersWithSubBusinesses']);