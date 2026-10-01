<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HodController;
use App\Http\Controllers\Api\LecturerController;
use App\Http\Controllers\Api\LecturerLogbookController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\SupervisorVerificationController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Native ICT Admin appearance management (same files as web portal).
    Route::middleware('role:ICT_ADMIN')->group(function () {
        Route::get('/admin/appearance', [\App\Http\Controllers\Api\AppearanceController::class, 'index']);
        Route::post('/admin/appearance/{slot}', [\App\Http\Controllers\Api\AppearanceController::class, 'upload']);
        Route::delete('/admin/appearance/{slot}', [\App\Http\Controllers\Api\AppearanceController::class, 'reset']);
    });

    // -------------------------------------------------------------------------
    // Authentication
    // -------------------------------------------------------------------------

    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/logout', [AuthController::class, 'logout']);


    // =========================================================================
    // STUDENT
    // =========================================================================

    // -------------------------------------------------------------------------
    // Student Test
    // -------------------------------------------------------------------------

    Route::get('/student/test', function () {
        return response()->json([
            'message' => 'Student access granted.'
        ]);
    })->middleware('role:STUDENT');


    // -------------------------------------------------------------------------
    // Student - Units
    // -------------------------------------------------------------------------

    Route::get(
        '/student/units',
        [StudentController::class, 'myUnits']
    )->middleware('role:STUDENT');


    // -------------------------------------------------------------------------
    // Student - Logbooks
    // -------------------------------------------------------------------------

    Route::get(
        '/student/logbooks',
        [StudentController::class, 'myLogbooks']
    )->middleware('role:STUDENT');

    Route::get(
        '/student/logbooks/{logbookId}',
        [StudentController::class, 'showLogbook']
    )->middleware('role:STUDENT');


    // -------------------------------------------------------------------------
    // Student - Logbook Progress
    // -------------------------------------------------------------------------

    Route::get(
        '/student/logbooks/{logbookId}/progress',
        [StudentController::class, 'logbookProgress']
    )->middleware('role:STUDENT');


    // -------------------------------------------------------------------------
    // Student - Clinical Entries
    // -------------------------------------------------------------------------

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


    // -------------------------------------------------------------------------
    // Student - Attendance
    // -------------------------------------------------------------------------

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


    // -------------------------------------------------------------------------
    // Student - Clinical Supervisors
    // -------------------------------------------------------------------------

    Route::get(
        '/student/clinical-supervisors/search',
        [SupervisorVerificationController::class, 'searchSupervisors']
    )->middleware('role:STUDENT');

    Route::post(
        '/student/clinical-supervisors/{supervisorId}/reference-face',
        [SupervisorVerificationController::class, 'registerReferenceFace']
    )->middleware('role:STUDENT');


    // -------------------------------------------------------------------------
    // Student - Face Verification Testing
    // -------------------------------------------------------------------------

    Route::get(
        '/student/supervisor-verifications/{verificationId}/face-test',
        [SupervisorVerificationController::class, 'testFaceComparison']
    )->middleware('role:STUDENT');

    Route::get(
        '/student/face-service/health',
        [SupervisorVerificationController::class, 'faceServiceHealth']
    )->middleware('role:STUDENT');


    // =========================================================================
    // LECTURER
    // =========================================================================

    // -------------------------------------------------------------------------
    // Lecturer Test
    // -------------------------------------------------------------------------

    Route::get('/lecturer/test', function () {
        return response()->json([
            'message' => 'Lecturer access granted.'
        ]);
    })->middleware('role:LECTURER');
    // -------------------------------------------------------------------------
    // Lecturer - Dashboard
    // -------------------------------------------------------------------------

    Route::get(
        '/lecturer/dashboard',
        [LecturerController::class, 'dashboard']
    )->middleware('role:LECTURER');



    // -------------------------------------------------------------------------
    // Lecturer - Units
    // -------------------------------------------------------------------------

    Route::get(
        '/lecturer/units',
        [LecturerController::class, 'myUnits']
    )->middleware('role:LECTURER');

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


    // -------------------------------------------------------------------------
    // Lecturer - Clinical Verification Review
    // -------------------------------------------------------------------------

    Route::get(
        '/lecturer/verifications/pending',
        [LecturerController::class, 'pendingVerifications']
    )->middleware('role:LECTURER');

    Route::get(
        '/lecturer/verifications/{verificationId}',
        [LecturerController::class, 'showVerification']
    )->middleware('role:LECTURER');

    Route::post(
        '/lecturer/verifications/{verificationId}/review',
        [LecturerController::class, 'reviewVerification']
    )->middleware('role:LECTURER');


    // -------------------------------------------------------------------------
    // Lecturer - Student Clinical Progress
    // -------------------------------------------------------------------------

    Route::get(
        '/lecturer/units/{unitId}/students/{studentId}/progress',
        [LecturerController::class, 'studentProgress']
    )->middleware('role:LECTURER');

    Route::get(
        '/lecturer/units/{unitId}/students/{studentId}/entries/{entryId}',
        [LecturerController::class, 'showStudentClinicalEntry']
    )->middleware('role:LECTURER');



    // -------------------------------------------------------------------------
    // Lecturer - Clinical Logbook Builder
    // -------------------------------------------------------------------------

    Route::get(
        '/lecturer/logbooks',
        [LecturerLogbookController::class, 'index']
    )->middleware('role:LECTURER');

    Route::post(
        '/lecturer/logbooks',
        [LecturerLogbookController::class, 'store']
    )->middleware('role:LECTURER');

    Route::get(
        '/lecturer/logbooks/{templateId}',
        [LecturerLogbookController::class, 'show']
    )->middleware('role:LECTURER');

    Route::put(
        '/lecturer/logbooks/{templateId}',
        [LecturerLogbookController::class, 'update']
    )->middleware('role:LECTURER');

    Route::post(
        '/lecturer/logbooks/{templateId}/assign',
        [LecturerLogbookController::class, 'assign']
    )->middleware('role:LECTURER');

    Route::post(
        '/lecturer/logbooks/{templateId}/sections',
        [LecturerLogbookController::class, 'storeSection']
    )->middleware('role:LECTURER');

    Route::put(
        '/lecturer/logbooks/{templateId}/sections/{sectionId}',
        [LecturerLogbookController::class, 'updateSection']
    )->middleware('role:LECTURER');

    Route::post(
        '/lecturer/logbooks/{templateId}/sections/{sectionId}/items',
        [LecturerLogbookController::class, 'storeItem']
    )->middleware('role:LECTURER');

    Route::put(
        '/lecturer/logbooks/{templateId}/items/{itemId}',
        [LecturerLogbookController::class, 'updateItem']
    )->middleware('role:LECTURER');

    Route::post(
        '/lecturer/logbooks/{templateId}/items/{itemId}/requirements',
        [LecturerLogbookController::class, 'storeRequirement']
    )->middleware('role:LECTURER');

    Route::put(
        '/lecturer/logbooks/{templateId}/requirements/{requirementId}',
        [LecturerLogbookController::class, 'updateRequirement']
    )->middleware('role:LECTURER');

    // =========================================================================
    // HOD
    // =========================================================================

    // -------------------------------------------------------------------------
    // HOD Test
    // -------------------------------------------------------------------------

    Route::get('/hod/test', function () {
        return response()->json([
            'message' => 'HOD access granted.'
        ]);
    })->middleware('role:HOD');


    // -------------------------------------------------------------------------
    // HOD - Department Dashboard
    // -------------------------------------------------------------------------

    Route::get(
        '/hod/dashboard',
        [HodController::class, 'dashboard']
    )->middleware('role:HOD');


    // -------------------------------------------------------------------------
    // HOD - Department Students
    // -------------------------------------------------------------------------

    Route::get(
        '/hod/students',
        [HodController::class, 'students']
    )->middleware('role:HOD');


    // -------------------------------------------------------------------------
    // HOD - Student Progress (Read Only)
    // -------------------------------------------------------------------------

    Route::get(
        '/hod/students/{studentId}/progress',
        [HodController::class, 'studentProgress']
    )->middleware('role:HOD');


    // -------------------------------------------------------------------------
    // HOD - Department Units
    // -------------------------------------------------------------------------

    Route::get(
        '/hod/units',
        [HodController::class, 'units']
    )->middleware('role:HOD');


    // -------------------------------------------------------------------------
    // HOD - Unit Progress (Read Only)
    // -------------------------------------------------------------------------

    Route::get(
        '/hod/units/{unitId}/progress',
        [HodController::class, 'unitProgress']
    )->middleware('role:HOD');


    // -------------------------------------------------------------------------
    // HOD - Department Year Levels
    // -------------------------------------------------------------------------

    Route::get(
        '/hod/year-levels',
        [HodController::class, 'yearLevels']
    )->middleware('role:HOD');


    // -------------------------------------------------------------------------
    // HOD - Department Lecturers (Read Only)
    // -------------------------------------------------------------------------

    Route::get(
        '/hod/lecturers',
        [HodController::class, 'lecturers']
    )->middleware('role:HOD');


    // -------------------------------------------------------------------------
    // HOD - Lecturer Progress (Read Only)
    // -------------------------------------------------------------------------

    Route::get(
        '/hod/lecturers/{lecturerId}/progress',
        [HodController::class, 'lecturerProgress']
    )->middleware('role:HOD');


    // =========================================================================
    // ICT ADMIN
    // =========================================================================

    // -------------------------------------------------------------------------
    // ICT Admin Test
    // -------------------------------------------------------------------------

    Route::get('/admin/test', function () {
        return response()->json([
            'message' => 'ICT Admin access granted.'
        ]);
    })->middleware('role:ICT_ADMIN');


    // -------------------------------------------------------------------------
    // ICT Admin - System Dashboard
    // -------------------------------------------------------------------------

    Route::get(
        '/admin/dashboard',
        [AdminController::class, 'dashboard']
    )->middleware('role:ICT_ADMIN');


    // -------------------------------------------------------------------------
    // ICT Admin - User Management
    // -------------------------------------------------------------------------

    Route::get(
        '/admin/users',
        [AdminController::class, 'users']
    )->middleware('role:ICT_ADMIN');
    Route::post(
    '/admin/users',
    [AdminController::class, 'createUser']
     )->middleware('role:ICT_ADMIN');
     Route::put(
    '/admin/users/{id}',
    [AdminController::class, 'updateUser']
    )->middleware('role:ICT_ADMIN');
    Route::get(
    '/admin/departments',
    [AdminController::class, 'departments']
)->middleware('role:ICT_ADMIN');

Route::post(
    '/admin/departments',
    [AdminController::class, 'createDepartment']
)->middleware('role:ICT_ADMIN');

Route::put(
    '/admin/departments/{id}',
    [AdminController::class, 'updateDepartment']
)->middleware('role:ICT_ADMIN');
Route::get(
    '/admin/units',
    [AdminController::class, 'units']
)->middleware([
    'auth:sanctum',
    'role:ICT_ADMIN',
]);

Route::post(
    '/admin/units',
    [AdminController::class, 'createUnit']
)->middleware([
    'auth:sanctum',
    'role:ICT_ADMIN',
]);

Route::put(
    '/admin/units/{id}',
    [AdminController::class, 'updateUnit']
)->middleware([
    'auth:sanctum',
    'role:ICT_ADMIN',
]);
// ICT Admin - Lecturer Unit Assignments

Route::get(
    '/admin/enrollments',
    [\App\Http\Controllers\Api\AdminEnrollmentMonitorController::class, 'index']
)->middleware('role:ICT_ADMIN');

Route::get(
    '/admin/unit-assignments',
    [\App\Http\Controllers\Api\AdminLecturerAssignmentController::class, 'index']
)->middleware('role:ICT_ADMIN');

Route::post(
    '/admin/unit-assignments',
    [\App\Http\Controllers\Api\AdminLecturerAssignmentController::class, 'store']
)->middleware('role:ICT_ADMIN');

Route::delete(
    '/admin/unit-assignments/{id}',
    [\App\Http\Controllers\Api\AdminLecturerAssignmentController::class, 'destroy']
)->middleware('role:ICT_ADMIN');

});
