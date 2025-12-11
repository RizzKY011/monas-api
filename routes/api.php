<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController; 
use App\Http\Controllers\CelenganController;
use App\Http\Controllers\CelenganTransactionController;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);

Route::post('/register', [AuthController::class, 'register']);

Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/profile', [AuthController::class, 'updateProfile']);
    Route::get('/celengan', [CelenganController::class, 'index']);
    Route::post('/celengan', [CelenganController::class, 'store']);
    Route::get('/celengan/{id}', [CelenganController::class, 'show']);
    Route::put('/celengan/{id}', [CelenganController::class, 'update']);
    Route::delete('/celengan/{id}', [CelenganController::class, 'destroy']);
    Route::get('/celengan/{id}/transactions', [CelenganTransactionController::class, 'index']);
    Route::post('/celengan/{id}/transactions', [CelenganTransactionController::class, 'store']);
});


Route::get('/image-proxy/{folder}/{filename}', function ($folder, $filename) {
    $path = "{$folder}/{$filename}";

    if (!Storage::disk('public')->exists($path)) {
        return response()->json([
            'message' => 'Image not found', 
            'path_checked' => $path
        ], 404);
    }

    $file = Storage::disk('public')->get($path);
    $type = Storage::disk('public')->mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);
    $response->header("Access-Control-Allow-Origin", "*");

    return $response;
});