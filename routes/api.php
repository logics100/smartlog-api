<?php

use App\Http\Controllers\Api\SupervisorVerificationController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\LecturerController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
     Route::get('/student/test', function () {
        return response()->json([
            'message' => 'Student access granted.'
        ]);
    })->middleware('role:STUDENT');

    Route::get('/lecturer/test', function () {
        return response()->json([
            'message' => 'Lecturer access granted.'
        ]);
    })->middleware('role:LECTURER');
    Route::get('/lecturer/units', [LecturerController::class, 'myUnits'])
    ->middleware('role:LECTURER');

    Route::get('/hod/test', function () {
        return response()->json([
            'message' => 'HOD access granted.'
        ]);
    })->middleware('role:HOD');

    Route::get('/admin/test', function () {
        return response()->json([
            'message' => 'ICT Admin access granted.'
        ]);
    })->middleware('role:ICT_ADMIN');
    Route::get(
    '/lecturer/units/{unitId}/students',
    [LecturerController::class, 'unitStudents']
    )->middleware('role:LECTURER');
    Route::post(
    '/lecturer/units/{unitId}/enroll',
    [LecturerController::class, 'enrollStudent']
    )->middleware('role:LECTURER');
    Route::get(
    '/lecturer/units/{unitId}/students/search',
    [LecturerController::class, 'searchStudents']
    )->middleware('role:LECTURER');
    Route::get(
    '/student/units',
    [StudentController::class, 'myUnits']
)->middleware('role:STUDENT');

Route::get(
    '/student/logbooks',
    [StudentController::class, 'myLogbooks']
)->middleware('role:STUDENT');

Route::get(
    '/student/logbooks/{logbookId}',
    [StudentController::class, 'showLogbook']
  )->middleware('role:STUDENT');
  Route::post(
    '/student/logbooks/{logbookId}/clinical-entries',
    [StudentController::class, 'createClinicalEntry']
  )->middleware('role:STUDENT');
  Route::get(
    '/student/logbooks/{logbookId}/clinical-entries',
    [StudentController::class, 'clinicalEntries']
  )->middleware('role:STUDENT');
  Route::post(
    '/student/clinical-entries/{entryId}/verify',
    [SupervisorVerificationController::class, 'verifyClinicalEntry']
  )->middleware('role:STUDENT');
  Route::post(
    '/student/logbooks/{logbookId}/attendance',
    [StudentController::class, 'createAttendance']
)->middleware('role:STUDENT');

Route::get(
    '/student/logbooks/{logbookId}/attendance',
    [StudentController::class, 'attendanceRecords']
)->middleware('role:STUDENT');
Route::post(
    '/student/attendance/{attendanceId}/verify',
    [SupervisorVerificationController::class, 'verifyAttendance']
)->middleware('role:STUDENT');
});