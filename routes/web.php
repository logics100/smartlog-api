<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\WebAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SmartLog Web Portal
|--------------------------------------------------------------------------
|
| Browser interface for Lecturer, HOD and ICT Admin.
| The Flutter mobile API remains separate in routes/api.php.
|
*/

Route::get('/', function () {
    return redirect()->route('web.login');
});

Route::middleware('guest')->group(function () {

    Route::get(
        '/login',
        [WebAuthController::class, 'showLogin']
    )->name('web.login');

    Route::post(
        '/login',
        [WebAuthController::class, 'login']
    )->name('web.login.submit');
});

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/logout',
        [WebAuthController::class, 'logout']
    )->name('web.logout');


    /*
    |--------------------------------------------------------------------------
    | Lecturer Web Portal
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/lecturer/dashboard',
        [DashboardController::class, 'lecturer']
    )->name('web.lecturer.dashboard');

    Route::get(
        '/lecturer/units',
        [DashboardController::class, 'lecturerUnits']
    )->name('web.lecturer.units');

    Route::get(
        '/lecturer/units/{unitId}/students',
        [DashboardController::class, 'lecturerUnitStudents']
    )->name('web.lecturer.unit.students');

    Route::get(
        '/lecturer/students',
        [DashboardController::class, 'lecturerStudents']
    )->name('web.lecturer.students');


    /*
    |--------------------------------------------------------------------------
    | Lecturer Pending Verifications
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/lecturer/verifications',
        [DashboardController::class, 'lecturerPendingVerifications']
    )->name('web.lecturer.verifications');

    Route::get(
        '/lecturer/verifications/{verificationId}',
        [DashboardController::class, 'lecturerVerificationReview']
    )->name('web.lecturer.verification.review');

    Route::post(
        '/lecturer/verifications/{verificationId}/review',
        [DashboardController::class, 'lecturerReviewVerification']
    )->name('web.lecturer.verification.submit');


    /*
    |--------------------------------------------------------------------------
    | HOD Web Portal
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/hod/dashboard',
        [DashboardController::class, 'hod']
    )->name('web.hod.dashboard');


    /*
    |--------------------------------------------------------------------------
    | ICT Administrator Web Portal
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/admin/dashboard',
        [DashboardController::class, 'admin']
    )->name('web.admin.dashboard');
});