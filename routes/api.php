<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
});

Route::get('/debug-config', function () {
    return response()->json([
        'base_path' => base_path(),
        'env_file' => app()->environmentFilePath(),

        'config_database' => config('database.default'),
        'config_session' => config('session.driver'),

        'env_db_connection' => getenv('DB_CONNECTION'),
        'env_session_driver' => getenv('SESSION_DRIVER'),

        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? null,
        'php_sapi' => PHP_SAPI,
    ]);
});