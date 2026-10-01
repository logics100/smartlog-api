<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () { return redirect()->route('web.login'); });
Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('web.login');
    Route::post('/login', [WebAuthController::class, 'login'])->name('web.login.submit');
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('web.logout');
    Route::get('/lecturer/dashboard', [DashboardController::class, 'lecturer'])->name('web.lecturer.dashboard');
    Route::get('/lecturer/units', [DashboardController::class, 'lecturerUnits'])->name('web.lecturer.units');
    Route::get('/lecturer/units/{unitId}/students', [DashboardController::class, 'lecturerUnitStudents'])->name('web.lecturer.unit.students');
    Route::get('/lecturer/students', [DashboardController::class, 'lecturerStudents'])->name('web.lecturer.students');
    Route::get('/lecturer/student-progress', [DashboardController::class, 'lecturerStudentProgress'])->name('web.lecturer.student-progress');
    Route::get('/lecturer/units/{unitId}/students/{studentId}/progress', [DashboardController::class, 'lecturerStudentProgressShow'])->name('web.lecturer.student-progress.show');
    Route::get('/lecturer/verifications', [DashboardController::class, 'lecturerPendingVerifications'])->name('web.lecturer.verifications');
    Route::get('/lecturer/verifications/{verificationId}', [DashboardController::class, 'lecturerVerificationReview'])->name('web.lecturer.verification.review');
    Route::post('/lecturer/verifications/{verificationId}/review', [DashboardController::class, 'lecturerReviewVerification'])->name('web.lecturer.verification.submit');

    Route::get('/lecturer/logbooks', [DashboardController::class, 'lecturerLogbooks'])->name('web.lecturer.logbooks');
    Route::get('/lecturer/logbooks/create', [DashboardController::class, 'lecturerLogbookCreate'])->name('web.lecturer.logbooks.create');
    Route::post('/lecturer/logbooks', [DashboardController::class, 'lecturerLogbookStore'])->name('web.lecturer.logbooks.store');
    Route::get('/lecturer/logbooks/{templateId}', [DashboardController::class, 'lecturerLogbookManage'])->name('web.lecturer.logbooks.manage');
    Route::put('/lecturer/logbooks/{templateId}', [DashboardController::class, 'lecturerLogbookUpdate'])->name('web.lecturer.logbooks.update');
    Route::post('/lecturer/logbooks/{templateId}/assign', [DashboardController::class, 'lecturerLogbookAssign'])->name('web.lecturer.logbooks.assign');
    Route::post('/lecturer/logbooks/{templateId}/sections', [DashboardController::class, 'lecturerLogbookSectionStore'])->name('web.lecturer.logbooks.sections.store');
    Route::put('/lecturer/logbooks/{templateId}/sections/{sectionId}', [DashboardController::class, 'lecturerLogbookSectionUpdate'])->name('web.lecturer.logbooks.sections.update');
    Route::post('/lecturer/logbooks/{templateId}/sections/{sectionId}/items', [DashboardController::class, 'lecturerLogbookItemStore'])->name('web.lecturer.logbooks.items.store');
    Route::put('/lecturer/logbooks/{templateId}/items/{itemId}', [DashboardController::class, 'lecturerLogbookItemUpdate'])->name('web.lecturer.logbooks.items.update');
    Route::post('/lecturer/logbooks/{templateId}/items/{itemId}/requirements', [DashboardController::class, 'lecturerLogbookRequirementStore'])->name('web.lecturer.logbooks.requirements.store');
    Route::put('/lecturer/logbooks/{templateId}/requirements/{requirementId}', [DashboardController::class, 'lecturerLogbookRequirementUpdate'])->name('web.lecturer.logbooks.requirements.update');

    Route::get('/hod/dashboard', [DashboardController::class, 'hod'])->name('web.hod.dashboard');
    Route::get('/hod/students', [DashboardController::class, 'hodStudents'])->name('web.hod.students');
    Route::get('/hod/students/{studentId}/progress', [DashboardController::class, 'hodStudentProgressShow'])->name('web.hod.student-progress.show');
    Route::get('/hod/lecturers', [DashboardController::class, 'hodLecturers'])->name('web.hod.lecturers');
    Route::get('/hod/lecturers/{lecturerId}/progress', [DashboardController::class, 'hodLecturerProgressShow'])->name('web.hod.lecturer-progress.show');
    Route::get('/hod/units', [DashboardController::class, 'hodUnits'])->name('web.hod.units');
    Route::get('/hod/units/{unitId}/progress', [DashboardController::class, 'hodUnitProgressShow'])->name('web.hod.unit-progress.show');
    Route::get('/hod/year-levels', [DashboardController::class, 'hodYearLevels'])->name('web.hod.year-levels');
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('web.admin.dashboard');
    Route::get('/admin/users', [DashboardController::class, 'adminUsers'])->name('web.admin.users');
    Route::get('/admin/users/create', [DashboardController::class, 'adminUserCreate'])->name('web.admin.users.create');
    Route::post('/admin/users', [DashboardController::class, 'adminUserStore'])->name('web.admin.users.store');
    Route::get('/admin/users/{userId}/edit', [DashboardController::class, 'adminUserEdit'])->name('web.admin.users.edit');
    Route::put('/admin/users/{userId}', [DashboardController::class, 'adminUserUpdate'])->name('web.admin.users.update');
    Route::get('/admin/departments', [DashboardController::class, 'adminDepartments'])->name('web.admin.departments');
    Route::post('/admin/departments', [DashboardController::class, 'adminDepartmentStore'])->name('web.admin.departments.store');
    Route::put('/admin/departments/{departmentId}', [DashboardController::class, 'adminDepartmentUpdate'])->name('web.admin.departments.update');
    Route::get('/admin/units', [DashboardController::class, 'adminUnits'])->name('web.admin.units');
    Route::get('/admin/enrollments', [DashboardController::class, 'adminEnrollments'])->name('web.admin.enrollments');

    Route::post(
        '/admin/units/assign-lecturer',
        [DashboardController::class, 'adminUnitAssignLecturer']
    )->name('web.admin.units.assign');
    Route::post('/admin/units/unassign-lecturer',
        [DashboardController::class, 'adminUnitUnassignLecturer']
    )->name('web.admin.units.unassign');
    Route::post('/admin/units', [DashboardController::class, 'adminUnitStore'])->name('web.admin.units.store');
    Route::put('/admin/units/{unitId}', [DashboardController::class, 'adminUnitUpdate'])->name('web.admin.units.update');
    Route::get('/admin/appearance', [DashboardController::class, 'adminAppearance'])->name('web.admin.appearance');
    Route::post('/admin/appearance/{slot}', [DashboardController::class, 'adminAppearanceUpload'])->name('web.admin.appearance.upload');
    Route::delete('/admin/appearance/{slot}', [DashboardController::class, 'adminAppearanceReset'])->name('web.admin.appearance.reset');
    Route::get('/admin/system-overview', [DashboardController::class, 'adminSystemOverview'])->name('web.admin.system-overview');
});
