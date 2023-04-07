<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RequisitionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('api')->get('/user', function (Request $request) {
    auth()->user();
    return $request->user();
});

Route::post('auth/login', [LoginController::class, 'login']);

Route::group(['middleware' => ['api', 'auth', 'role:employee']], function ($router) {

    Route::post('/auth/logout', [LoginController::class, 'logout']);
    Route::post('/auth/refresh', [LoginController::class, 'refresh']);

    Route::get('requisitions/my-requisition', [RequisitionController::class, 'myRequisition']);

    Route::apiResources([
        'users' => UserController::class,
        'items' => ItemController::class,
        'suppliers' => SupplierController::class,
        'stocks' => StockController::class,
        'requisitions' => RequisitionController::class,
    ]);

    Route::patch('requisitions/{requisition}/approve', [RequisitionController::class, 'approve']);
    Route::patch('requisitions/{requisition}/issue', [RequisitionController::class, 'issue']);

});