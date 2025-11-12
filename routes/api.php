<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserImportController;
use App\Http\Controllers\WardController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\AccountController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LoadExcelsController;
use App\Http\Controllers\InsertExcel;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/create-user', [AccountController::class, 'createUser']);
Route::post('/login', [LoginController::class, 'signIn'])->withoutMiddleware([VerifyCsrfToken::class]);


Route::group([
    'middleware' => 'checkWardProvince', 
], function () {
    Route::post('/import-users', [UserImportController::class, 'import']);
    Route::post('/import-users-queue', [UserImportController::class, 'importQueue']);
    Route::delete('/delete-queue/{session_id}', [UserImportController::class, 'delete']);
    Route::get('/import-result/{session}', [UserImportController::class, 'getResult']);

});

// Group middleware cho role phuong
Route::group([
    'middleware' => 'checkWardProvince:phuong',
], function () {
    Route::post('/get-wards/{province_id}', [WardController::class, 'getWard']);
    Route::post('/insert-wards', [WardController::class, 'sumTotalWard']);
    Route::post('/update-wards/{wardId}/{namDieuTra}', [WardController::class, 'update']);
    Route::get('/export-wards', [WardController::class, 'export']);
    Route::get('/show-wards', [WardController::class, 'index']);
    Route::post('/load-wards', [LoadExcelsController::class, 'load']);
    Route::post('/wards-cell-insert', [InsertExcel::class, 'insert']);
});

// Group middleware cho role tinh
Route::group([
    'middleware' => 'checkWardProvince:tinh',
], function () {
    Route::post('/get-provinces', [ProvinceController::class, 'getProvince']);
    Route::post('/insert-provinces', [ProvinceController::class, 'province']);
    Route::get('/export-provinces', [ProvinceController::class, 'export']);
    Route::get('/show-provinces', [ProvinceController::class, 'index']);
});