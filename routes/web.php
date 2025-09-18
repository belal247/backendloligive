<?php

use App\Http\Controllers\EmailController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

Route::get('send-email', [EmailController::class, 'sendEmail']);

Route::get('/', function () {
    return view('welcome');
});


Route::get('/logtest', function () {
    \Log::debug("Log test at " . now());
    return "Logged!";
});

Route::get('/logs', function () {
    $logFile = storage_path('logs/laravel.log');
    if (!file_exists($logFile)) {
        return 'Log file does not exist.';
    }
    return nl2br(e(file_get_contents($logFile)));
});

Route::get('/test-db2', function () {
    
       $db = DB::connection();
       return $db;
});

Route::get('/test-db', function () {
    try {
        DB::connection()->getPdo();
        Log::debug('✅ Database connected successfully.');
        return '✅ Database connected!';
    } catch (\Exception $e) {
        Log::error('❌ DB connection failed: ' . $e->getMessage());
        return '❌ DB connection failed: ' . $e->getMessage();
    }
});

Route::get('/db-info', function () {
    try {
        $connection = DB::connection();
        $pdo = $connection->getPdo();
        $dbName = $connection->getDatabaseName();

        Log::debug('✅ DB connected successfully at ' . now(), [
            'database' => $dbName,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Database connected successfully',
            'database' => $dbName,
        ]);
    } catch (\Exception $e) {
        Log::error('❌ DB connection failed at ' . now() . ': ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Database connection failed',
            'error' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/ssl-check', function () {
    $path = '/etc/ssl/certs/ca-certificates.crt';

    if (!file_exists($path)) {
        return "❌ File does not exist.";
    }

    $permissions = substr(sprintf('%o', fileperms($path)), -4);
    $readable = is_readable($path) ? 'Yes' : 'No';
    $owner = posix_getpwuid(fileowner($path))['name'] ?? 'Unknown';

    return response()->json([
        'exists' => true,
        'readable' => $readable,
        'permissions' => $permissions,
        'owner' => $owner,
    ]);
});


Route::get('/db-info2', function () {
    try {
        $connection = DB::connection();
        $pdo = $connection->getPdo();

        return response()->json([
            'status' => 'success',
            'message' => 'Database connected successfully',
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'ssl_ca' => env('MYSQL_ATTR_SSL_CA'),
            'dsn' => $pdo->getAttribute(\PDO::ATTR_CONNECTION_STATUS),
        ]);
    } catch (\Exception $e) {
        
        return response()->json([
            'status' => 'error',
            'message' => 'Database connection failed',
            'error' => $e->getMessage(),
            'error2' => $e,
             'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'ssl_ca' => env('MYSQL_ATTR_SSL_CA'),
        ], 500);
    }
});