<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BussinessLineController;
use App\Http\Controllers\CenterController;
use App\Http\Controllers\CenterSalaryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ObjectiveAndResultController;
use App\Http\Controllers\ShareTypeController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\YearController;
use App\Models\Center;
use App\Models\CenterSalary;
use Illuminate\Support\Facades\Route;

//AUTH
Route::post('/login', [AuthController::class, 'login']);
Route::middleware(['force.json', 'auth:sanctum'])->group(function(){
    //Logout route
    Route::post('/logout', [AuthController::class, 'logout']);
    //User routes
    Route::controller(UserController::class)->prefix("users/")->group(function () {
        Route::get('/', 'viewAll');
        Route::get('/{id}', 'view');
        Route::post('/', 'create'); //super_admin
        Route::put('/{id}', 'update'); //super_admin
        Route::put('/{id}/update-password', 'updatePassword');
        Route::delete('/{id}', 'delete'); //super_admin
    });

    //Invoice routes
    Route::controller(InvoiceController::class)->prefix("invoices/")->group(function () {
        Route::get('/', 'viewAll');
        Route::get('/odoo', 'viewOdooInvoices');
        Route::get('/getTotalMonth', 'getTotalThisMonth');
        Route::get('/getNumberToAdd', 'getNumberOfInvoicesToAdd');
        Route::post('/resetInvoice/{id}', 'resetInvoice'); //super_admin
        Route::get('/{id}', 'view');
        Route::post('/allNewOdoo', 'addAllNewOdooInvoices');
        Route::post('/', 'create'); //super_admin
        Route::put('/{id}', 'update'); //super_admin
        Route::delete('/{id}', 'delete'); //super_admin
    });

    //Supplier routes
    Route::controller(SupplierController::class)->prefix("suppliers/")->group(function () {
        Route::get('/', 'viewAll');
        Route::get('/{id}', 'view');
        Route::post('/', 'create'); //super_admin
        Route::put('/{id}', 'update'); //super_admin
        Route::delete('/{id}', 'delete'); //super_admin
        Route::post('/updateCentersOnInvoices', 'updateCentersOnInvoices'); //super_admin
    });

    //Center routes
    Route::controller(CenterController::class)->prefix("centers/")->group(function () {
        Route::get('/', 'viewAll');
        Route::get('/{id}', 'view');
        Route::post('/', 'create'); //super_admin
        Route::put('/{id}', 'update'); //super_admin
        Route::delete('/{id}', 'delete'); //super_admin
    });

    //Share types routes
    Route::controller(ShareTypeController::class)->prefix("share-types/")->group(function () {
        Route::get('/', 'viewAll');
        Route::get('/{id}', 'view');
        Route::post('/', 'create'); //super_admin
        Route::put('/{id}', 'update'); //super_admin
        Route::delete('/{id}', 'delete'); //super_admin
    });

    //Business line routes
    Route::controller(BussinessLineController::class)->prefix("business-lines/")->group(function () {
        Route::get('/', 'viewAll');
        Route::get('/{id}', 'view');
        Route::post('/', 'create'); //super_admin
        Route::put('/{id}', 'update'); //super_admin
        Route::delete('/{id}', 'delete'); //super_admin
    });

    //Objective and Result routes
    Route::controller(ObjectiveAndResultController::class)->prefix("objetives/")->group(function () {
        Route::get('/', 'viewYearlyObjectivesAndResults');
        Route::post('/', 'create'); //super_admin
        Route::put('/{id}', 'update'); //super_admin
        Route::delete('/{id}', 'delete'); //super_admin
    });

    Route::controller(YearController::class)->prefix("years/")->group(function () {
        Route::get('/', 'viewAll');
        Route::get('/currentYear', 'viewCurrentYear');
        Route::get('/{id}', 'view');
        Route::post('/', 'create'); //super_admin
        Route::put('/{id}', 'update'); //super_admin
        Route::delete('/{id}', 'delete'); //super_admin
    });

    //    Route::controller(CenterSalaryController::class)->prefix("center-salaries/")->group(function () {
    //    Route::get('/', 'viewAll'); // List all salaries with optional filters
    //    Route::get('/byCenterAndYear', 'viewByCenterAndYear'); // View salaries by center and year
    //    Route::post('/', 'create'); // Create a new salary record
    //    Route::put('/{id}', 'update'); // Update an existing salary record
    //    Route::delete('/{id}', 'delete'); // Delete a salary record
    //});

    });



Route::fallback(function (){
    abort(404, 'API resource not found');
});


