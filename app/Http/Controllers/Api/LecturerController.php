<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LecturerController extends Controller
{
    public function myUnits(Request $request)
    {
        $lecturer = $request->user();

        $units = DB::table('lecturer_units')
            ->join('units', 'lecturer_units.unit_id', '=', 'units.id')
            ->join('departments', 'units.department_id', '=', 'departments.id')
            ->join('year_levels', 'units.year_level_id', '=', 'year_levels.id')
            ->join('semesters', 'units.semester_id', '=', 'semesters.id')
            ->where('lecturer_units.lecturer_id', $lecturer->id)
            ->select(
                'units.id',
                'units.unit_code',
                'units.unit_name',
                'units.requires_logbook',
                'departments.department_name',
                'year_levels.year_name',
                'semesters.semester_name'
            )
            ->get();

        return response()->json([
            'lecturer' => [
                'id' => $lecturer->id,
                'name' => $lecturer->name,
                'dwu_id' => $lecturer->dwu_id,
            ],
            'units' => $units,
        ]);
    }

    public function unitStudents(Request $request, $unitId)
    {
        $lecturer = $request->user();

        $assigned = DB::table('lecturer_units')
            ->where('lecturer_id', $lecturer->id)
            ->where('unit_id', $unitId)
            ->exists();

        if (!$assigned) {
            return response()->json([
                'message' => 'You are not assigned to this unit.'
            ], 403);
        }

        $unit = DB::table('units')
            ->where('id', $unitId)
            ->first();

        if (!$unit) {
            return response()->json([
                'message' => 'Unit not found.'
            ], 404);
        }

        $students = DB::table('enrollments')
            ->join('users', 'enrollments.student_id', '=', 'users.id')
            ->leftJoin(
                'student_logbooks',
                'enrollments.id',
                '=',
                'student_logbooks.enrollment_id'
            )
            ->where('enrollments.unit_id', $unitId)
            ->select(
                'users.id',
                'users.dwu_id',
                'users.name',
                'users.email',
                'users.year_level_id',
                'enrollments.id as enrollment_id',
                'enrollments.status as enrollment_status',
                'student_logbooks.id as student_logbook_id',
                'student_logbooks.status as logbook_status',
                'student_logbooks.completion_percentage'
            )
            ->get();

        return response()->json([
            'unit' => [
                'id' => $unit->id,
                'unit_code' => $unit->unit_code,
                'unit_name' => $unit->unit_name,
            ],
            'students' => $students,
        ]);
    }

    public function enrollStudent(Request $request, $unitId)
    {
        $lecturer = $request->user();

        $assigned = DB::table('lecturer_units')
            ->where('lecturer_id', $lecturer->id)
            ->where('unit_id', $unitId)
            ->exists();

        if (!$assigned) {
            return response()->json([
                'message' => 'You are not assigned to this unit.'
            ], 403);
        }

        $request->validate([
            'student_id' => 'required|integer|exists:users,id',
        ]);

        $student = DB::table('users')
            ->where('id', $request->student_id)
            ->first();

        if (!$student || $student->role !== 'STUDENT') {
            return response()->json([
                'message' => 'The selected user is not a student.'
            ], 422);
        }

        $unit = DB::table('units')
            ->where('id', $unitId)
            ->where('is_active', true)
            ->first();

        if (!$unit) {
            return response()->json([
                'message' => 'Unit not found or inactive.'
            ], 404);
        }

        if ($student->department_id != $unit->department_id) {
            return response()->json([
                'message' =>
                    'This student does not belong to the department for this unit.'
            ], 422);
        }

        $existingEnrollment = DB::table('enrollments')
            ->where('student_id', $student->id)
            ->where('unit_id', $unitId)
            ->first();

        if ($existingEnrollment) {
            return response()->json([
                'message' =>
                    'This student is already enrolled in this unit.',
                'enrollment_id' => $existingEnrollment->id,
            ], 409);
        }

        return DB::transaction(function () use (
            $lecturer,
            $student,
            $unit,
            $unitId
        ) {
            $enrollmentId = DB::table('enrollments')
                ->insertGetId([
                    'student_id' => $student->id,
                    'unit_id' => $unitId,
                    'enrolled_by' => $lecturer->id,
                    'enrollment_date' => now()->toDateString(),
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            $studentLogbookId = null;
            $logbookMessage = null;

            if ($unit->requires_logbook) {
                $template = DB::table('logbook_templates')
                    ->where('unit_id', $unitId)
                    ->where('is_active', true)
                    ->latest('id')
                    ->first();

                if ($template) {
                    $studentLogbookId = DB::table('student_logbooks')
                        ->insertGetId([
                            'enrollment_id' => $enrollmentId,
                            'logbook_template_id' => $template->id,
                            'assigned_date' => now()->toDateString(),
                            'due_date' => null,
                            'status' => 'ACTIVE',
                            'completion_percentage' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                    $logbookMessage =
                        'Logbook assigned automatically.';
                } else {
                    $logbookMessage =
                        'Enrollment created, but no active logbook template exists for this unit.';
                }
            }

            return response()->json([
                'message' => 'Student enrolled successfully.',
                'enrollment' => [
                    'id' => $enrollmentId,
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'dwu_id' => $student->dwu_id,
                    'unit_id' => $unit->id,
                    'unit_code' => $unit->unit_code,
                    'unit_name' => $unit->unit_name,
                    'status' => 'ACTIVE',
                ],
                'student_logbook_id' => $studentLogbookId,
                'logbook_message' => $logbookMessage,
            ], 201);
        });
    }

    public function searchStudents(Request $request, $unitId)
    {
        $lecturer = $request->user();

        $assigned = DB::table('lecturer_units')
            ->where('lecturer_id', $lecturer->id)
            ->where('unit_id', $unitId)
            ->exists();

        if (!$assigned) {
            return response()->json([
                'message' => 'You are not assigned to this unit.'
            ], 403);
        }

        $unit = DB::table('units')
            ->where('id', $unitId)
            ->where('is_active', true)
            ->first();

        if (!$unit) {
            return response()->json([
                'message' => 'Unit not found or inactive.'
            ], 404);
        }

        $request->validate([
            'search' => 'required|string|min:1',
        ]);

        $search = $request->search;

        $students = DB::table('users')
            ->leftJoin(
                'year_levels',
                'users.year_level_id',
                '=',
                'year_levels.id'
            )
            ->where('users.role', 'STUDENT')
            ->where('users.is_active', true)
            ->where('users.department_id', $unit->department_id)
            ->where(function ($query) use ($search) {
                $query
                    ->where('users.dwu_id', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            })
            ->select(
                'users.id',
                'users.dwu_id',
                'users.name',
                'users.email',
                'users.department_id',
                'users.year_level_id',
                'year_levels.year_name'
            )
            ->limit(20)
            ->get();

        $students = $students->map(function ($student) use ($unitId) {
            $student->already_enrolled = DB::table('enrollments')
                ->where('student_id', $student->id)
                ->where('unit_id', $unitId)
                ->exists();

            return $student;
        });

        return response()->json([
            'unit' => [
                'id' => $unit->id,
                'unit_code' => $unit->unit_code,
                'unit_name' => $unit->unit_name,
            ],
            'students' => $students,
        ]);
    }

    public function pendingVerifications(Request $request)
{
    $lecturer = $request->user();

    // ---------------------------------------------------------
    // Clinical-entry verifications
    // ---------------------------------------------------------
    $latestClinicalVerificationIds =
        DB::table('supervisor_verifications')
            ->whereNotNull('clinical_entry_id')
            ->select(
                'clinical_entry_id',
                DB::raw('MAX(id) as latest_verification_id')
            )
            ->groupBy('clinical_entry_id');

    $clinicalVerifications =
        DB::table('supervisor_verifications')
            ->joinSub(
                $latestClinicalVerificationIds,
                'latest_verifications',
                function ($join) {
                    $join->on(
                        'supervisor_verifications.id',
                        '=',
                        'latest_verifications.latest_verification_id'
                    );
                }
            )
            ->join(
                'clinical_entries',
                'supervisor_verifications.clinical_entry_id',
                '=',
                'clinical_entries.id'
            )
            ->join(
                'student_logbooks',
                'clinical_entries.student_logbook_id',
                '=',
                'student_logbooks.id'
            )
            ->join(
                'enrollments',
                'student_logbooks.enrollment_id',
                '=',
                'enrollments.id'
            )
            ->join(
                'users',
                'enrollments.student_id',
                '=',
                'users.id'
            )
            ->join(
                'units',
                'enrollments.unit_id',
                '=',
                'units.id'
            )
            ->join(
                'lecturer_units',
                'units.id',
                '=',
                'lecturer_units.unit_id'
            )
            ->leftJoin(
                'clinical_supervisors',
                'supervisor_verifications.clinical_supervisor_id',
                '=',
                'clinical_supervisors.id'
            )
            ->where(
                'lecturer_units.lecturer_id',
                $lecturer->id
            )
            ->where(
                'supervisor_verifications.verification_status',
                'MANUAL_REVIEW'
            )
            ->select(
                'supervisor_verifications.id as verification_id',
                'supervisor_verifications.verification_status',
                'supervisor_verifications.verification_timestamp',
                'supervisor_verifications.face_lbph_distance',
                'supervisor_verifications.face_comparison_decision',

                'clinical_entries.id as clinical_entry_id',

                DB::raw('NULL as attendance_record_id'),
                DB::raw("'CLINICAL_ENTRY' as verification_type"),

                'clinical_entries.activity_date',
                'clinical_entries.activity_details as procedure_name',
                'clinical_entries.facility_name',

                DB::raw('NULL as attendance_date'),
                DB::raw('NULL as clinical_unit'),
                DB::raw('NULL as start_time'),
                DB::raw('NULL as finish_time'),
                DB::raw('NULL as total_hours'),

                'users.id as student_id',
                'users.dwu_id as student_dwu_id',
                'users.name as student_name',

                'units.id as unit_id',
                'units.unit_code',
                'units.unit_name',

                'clinical_supervisors.id as clinical_supervisor_id',
                'clinical_supervisors.full_name as supervisor_name',
                'clinical_supervisors.registration_number as supervisor_registration_number',
                'clinical_supervisors.profession as supervisor_profession'
            );

    // ---------------------------------------------------------
    // Attendance verifications
    // ---------------------------------------------------------
    $latestAttendanceVerificationIds =
        DB::table('supervisor_verifications')
            ->whereNotNull('attendance_record_id')
            ->select(
                'attendance_record_id',
                DB::raw('MAX(id) as latest_verification_id')
            )
            ->groupBy('attendance_record_id');

    $attendanceVerifications =
        DB::table('supervisor_verifications')
            ->joinSub(
                $latestAttendanceVerificationIds,
                'latest_verifications',
                function ($join) {
                    $join->on(
                        'supervisor_verifications.id',
                        '=',
                        'latest_verifications.latest_verification_id'
                    );
                }
            )
            ->join(
                'attendance_records',
                'supervisor_verifications.attendance_record_id',
                '=',
                'attendance_records.id'
            )
            ->join(
                'student_logbooks',
                'attendance_records.student_logbook_id',
                '=',
                'student_logbooks.id'
            )
            ->join(
                'enrollments',
                'student_logbooks.enrollment_id',
                '=',
                'enrollments.id'
            )
            ->join(
                'users',
                'enrollments.student_id',
                '=',
                'users.id'
            )
            ->join(
                'units',
                'enrollments.unit_id',
                '=',
                'units.id'
            )
            ->join(
                'lecturer_units',
                'units.id',
                '=',
                'lecturer_units.unit_id'
            )
            ->leftJoin(
                'clinical_supervisors',
                'supervisor_verifications.clinical_supervisor_id',
                '=',
                'clinical_supervisors.id'
            )
            ->where(
                'lecturer_units.lecturer_id',
                $lecturer->id
            )
            ->where(
                'supervisor_verifications.verification_status',
                'MANUAL_REVIEW'
            )
            ->select(
                'supervisor_verifications.id as verification_id',
                'supervisor_verifications.verification_status',
                'supervisor_verifications.verification_timestamp',
                'supervisor_verifications.face_lbph_distance',
                'supervisor_verifications.face_comparison_decision',

                DB::raw('NULL as clinical_entry_id'),
                'attendance_records.id as attendance_record_id',
                DB::raw("'ATTENDANCE' as verification_type"),

                DB::raw('NULL as activity_date'),
                DB::raw("'Attendance' as procedure_name"),
                'attendance_records.facility_name',

                'attendance_records.attendance_date',
                'attendance_records.clinical_unit',
                'attendance_records.start_time',
                'attendance_records.finish_time',
                'attendance_records.total_hours',

                'users.id as student_id',
                'users.dwu_id as student_dwu_id',
                'users.name as student_name',

                'units.id as unit_id',
                'units.unit_code',
                'units.unit_name',

                'clinical_supervisors.id as clinical_supervisor_id',
                'clinical_supervisors.full_name as supervisor_name',
                'clinical_supervisors.registration_number as supervisor_registration_number',
                'clinical_supervisors.profession as supervisor_profession'
            );

    $verifications = $clinicalVerifications
        ->unionAll($attendanceVerifications)
        ->orderByDesc('verification_id')
        ->get();

    return response()->json([
        'count' => $verifications->count(),
        'verifications' => $verifications,
    ]);
}

    public function showVerification(Request $request, $verificationId)
{
    $lecturer = $request->user();

    $baseVerification = DB::table('supervisor_verifications')
        ->where('id', $verificationId)
        ->first();

    if (!$baseVerification) {
        return response()->json([
            'message' => 'Verification not found.'
        ], 404);
    }

    $verification = null;

    // ---------------------------------------------------------
    // Clinical entry verification
    // ---------------------------------------------------------
    if ($baseVerification->clinical_entry_id !== null) {
        $verification = DB::table('supervisor_verifications')
            ->join(
                'clinical_entries',
                'supervisor_verifications.clinical_entry_id',
                '=',
                'clinical_entries.id'
            )
            ->join(
                'student_logbooks',
                'clinical_entries.student_logbook_id',
                '=',
                'student_logbooks.id'
            )
            ->join(
                'enrollments',
                'student_logbooks.enrollment_id',
                '=',
                'enrollments.id'
            )
            ->join(
                'users',
                'enrollments.student_id',
                '=',
                'users.id'
            )
            ->join(
                'units',
                'enrollments.unit_id',
                '=',
                'units.id'
            )
            ->join(
                'lecturer_units',
                'units.id',
                '=',
                'lecturer_units.unit_id'
            )
            ->leftJoin(
                'clinical_supervisors',
                'supervisor_verifications.clinical_supervisor_id',
                '=',
                'clinical_supervisors.id'
            )
            ->leftJoin(
                'users as reviewer',
                'supervisor_verifications.reviewed_by',
                '=',
                'reviewer.id'
            )
            ->where(
                'supervisor_verifications.id',
                $verificationId
            )
            ->where(
                'lecturer_units.lecturer_id',
                $lecturer->id
            )
            ->select(
                'supervisor_verifications.*',

                DB::raw("'CLINICAL_ENTRY' as verification_type"),

                'clinical_entries.id as clinical_entry_id',

                DB::raw('NULL as attendance_record_id'),

                'clinical_entries.activity_date',
                'clinical_entries.activity_details as procedure_name',
                'clinical_entries.facility_name',

                DB::raw('NULL as attendance_date'),
                DB::raw('NULL as clinical_unit'),
                DB::raw('NULL as start_time'),
                DB::raw('NULL as finish_time'),
                DB::raw('NULL as total_hours'),

                'users.id as student_id',
                'users.dwu_id as student_dwu_id',
                'users.name as student_name',
                'users.email as student_email',

                'units.id as unit_id',
                'units.unit_code',
                'units.unit_name',

                'clinical_supervisors.id as clinical_supervisor_id',
                'clinical_supervisors.full_name as registered_supervisor_name',
                'clinical_supervisors.registration_number as supervisor_registration_number',
                'clinical_supervisors.profession as supervisor_profession',
                'clinical_supervisors.facility_name as supervisor_facility',

                'reviewer.name as reviewer_name',
                'reviewer.dwu_id as reviewer_dwu_id'
            )
            ->first();
    }

    // ---------------------------------------------------------
    // Attendance verification
    // ---------------------------------------------------------
    if (
        $baseVerification->attendance_record_id !== null &&
        $verification === null
    ) {
        $verification = DB::table('supervisor_verifications')
            ->join(
                'attendance_records',
                'supervisor_verifications.attendance_record_id',
                '=',
                'attendance_records.id'
            )
            ->join(
                'student_logbooks',
                'attendance_records.student_logbook_id',
                '=',
                'student_logbooks.id'
            )
            ->join(
                'enrollments',
                'student_logbooks.enrollment_id',
                '=',
                'enrollments.id'
            )
            ->join(
                'users',
                'enrollments.student_id',
                '=',
                'users.id'
            )
            ->join(
                'units',
                'enrollments.unit_id',
                '=',
                'units.id'
            )
            ->join(
                'lecturer_units',
                'units.id',
                '=',
                'lecturer_units.unit_id'
            )
            ->leftJoin(
                'clinical_supervisors',
                'supervisor_verifications.clinical_supervisor_id',
                '=',
                'clinical_supervisors.id'
            )
            ->leftJoin(
                'users as reviewer',
                'supervisor_verifications.reviewed_by',
                '=',
                'reviewer.id'
            )
            ->where(
                'supervisor_verifications.id',
                $verificationId
            )
            ->where(
                'lecturer_units.lecturer_id',
                $lecturer->id
            )
            ->select(
                'supervisor_verifications.*',

                DB::raw("'ATTENDANCE' as verification_type"),

                DB::raw('NULL as clinical_entry_id'),

                'attendance_records.id as attendance_record_id',

                DB::raw('NULL as activity_date'),
                DB::raw("'Attendance' as procedure_name"),
                'attendance_records.facility_name',

                'attendance_records.attendance_date',
                'attendance_records.clinical_unit',
                'attendance_records.start_time',
                'attendance_records.finish_time',
                'attendance_records.total_hours',

                'users.id as student_id',
                'users.dwu_id as student_dwu_id',
                'users.name as student_name',
                'users.email as student_email',

                'units.id as unit_id',
                'units.unit_code',
                'units.unit_name',

                'clinical_supervisors.id as clinical_supervisor_id',
                'clinical_supervisors.full_name as registered_supervisor_name',
                'clinical_supervisors.registration_number as supervisor_registration_number',
                'clinical_supervisors.profession as supervisor_profession',
                'clinical_supervisors.facility_name as supervisor_facility',

                'reviewer.name as reviewer_name',
                'reviewer.dwu_id as reviewer_dwu_id'
            )
            ->first();
    }

    if (!$verification) {
        return response()->json([
            'message' =>
                'Verification not found or you do not have permission to review it.'
        ], 404);
    }

    $signatureUrl =
        !empty($verification->signature_path)
            ? asset(
                'storage/' .
                $verification->signature_path
            )
            : null;

    $faceCaptureUrl =
        !empty($verification->face_capture_path)
            ? asset(
                'storage/' .
                $verification->face_capture_path
            )
            : null;

    return response()->json([
        'verification' => $verification,

        'evidence' => [
            'signature_url' => $signatureUrl,
            'face_capture_url' => $faceCaptureUrl,
        ],

        'face_comparison' => [
            'decision' =>
                $verification
                    ->face_comparison_decision,

            'lbph_distance' =>
                $verification
                    ->face_lbph_distance,

            'face_match_score' =>
                $verification
                    ->face_match_score,

            'face_match_passed' =>
                $verification
                    ->face_match_passed,
        ],

        'review' => [
            'reviewed_by' =>
                $verification->reviewed_by,

            'reviewer_name' =>
                $verification->reviewer_name,

            'reviewer_dwu_id' =>
                $verification->reviewer_dwu_id,

            'reviewed_at' =>
                $verification->reviewed_at,

            'comment' =>
                $verification->review_comment,
        ],

        'notice' =>
            'Face comparison is supporting evidence only. '
            . 'The lecturer must make the final review decision.',
    ]);
}

public function reviewVerification(Request $request, $verificationId)
{
    $lecturer = $request->user();

    $validated = $request->validate([
        'decision' =>
            'required|string|in:APPROVED,REJECTED',
        'comment' =>
            'nullable|string|max:1000',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Find Base Verification
    |--------------------------------------------------------------------------
    */

    $baseVerification =
        DB::table('supervisor_verifications')
            ->where('id', $verificationId)
            ->first();

    if (!$baseVerification) {
        return response()->json([
            'message' =>
                'Verification not found.'
        ], 404);
    }

    $verification = null;
    $verificationType = null;

    /*
    |--------------------------------------------------------------------------
    | Clinical Entry Verification
    |--------------------------------------------------------------------------
    */

    if ($baseVerification->clinical_entry_id !== null) {

        $verification =
            DB::table('supervisor_verifications')
                ->join(
                    'clinical_entries',
                    'supervisor_verifications.clinical_entry_id',
                    '=',
                    'clinical_entries.id'
                )
                ->join(
                    'student_logbooks',
                    'clinical_entries.student_logbook_id',
                    '=',
                    'student_logbooks.id'
                )
                ->join(
                    'enrollments',
                    'student_logbooks.enrollment_id',
                    '=',
                    'enrollments.id'
                )
                ->join(
                    'lecturer_units',
                    'enrollments.unit_id',
                    '=',
                    'lecturer_units.unit_id'
                )
                ->where(
                    'supervisor_verifications.id',
                    $verificationId
                )
                ->where(
                    'lecturer_units.lecturer_id',
                    $lecturer->id
                )
                ->select(
                    'supervisor_verifications.id',
                    'supervisor_verifications.clinical_entry_id',
                    'supervisor_verifications.attendance_record_id',
                    'supervisor_verifications.verification_status',
                    'clinical_entries.student_logbook_id'
                )
                ->first();

        $verificationType = 'CLINICAL_ENTRY';
    }

    /*
    |--------------------------------------------------------------------------
    | Attendance Verification
    |--------------------------------------------------------------------------
    */

    if (
        $baseVerification->attendance_record_id !== null &&
        $verification === null
    ) {
        $verification =
            DB::table('supervisor_verifications')
                ->join(
                    'attendance_records',
                    'supervisor_verifications.attendance_record_id',
                    '=',
                    'attendance_records.id'
                )
                ->join(
                    'student_logbooks',
                    'attendance_records.student_logbook_id',
                    '=',
                    'student_logbooks.id'
                )
                ->join(
                    'enrollments',
                    'student_logbooks.enrollment_id',
                    '=',
                    'enrollments.id'
                )
                ->join(
                    'lecturer_units',
                    'enrollments.unit_id',
                    '=',
                    'lecturer_units.unit_id'
                )
                ->where(
                    'supervisor_verifications.id',
                    $verificationId
                )
                ->where(
                    'lecturer_units.lecturer_id',
                    $lecturer->id
                )
                ->select(
                    'supervisor_verifications.id',
                    'supervisor_verifications.clinical_entry_id',
                    'supervisor_verifications.attendance_record_id',
                    'supervisor_verifications.verification_status',
                    'attendance_records.student_logbook_id'
                )
                ->first();

        $verificationType = 'ATTENDANCE';
    }

    /*
    |--------------------------------------------------------------------------
    | Permission / Existence Check
    |--------------------------------------------------------------------------
    */

    if (!$verification) {
        return response()->json([
            'message' =>
                'Verification not found or you do not have permission to review it.'
        ], 404);
    }

    /*
    |--------------------------------------------------------------------------
    | Prevent Multiple Reviews
    |--------------------------------------------------------------------------
    */

    if (
        $verification->verification_status !==
        'MANUAL_REVIEW'
    ) {
        return response()->json([
            'message' =>
                'This verification has already been reviewed.',

            'verification_status' =>
                $verification->verification_status,
        ], 409);
    }

    $decision =
        $validated['decision'];

    $comment =
        $validated['comment'] ?? null;

    $reviewedAt = now();

    $completionPercentage = null;
    $recordStatus = null;

    /*
    |--------------------------------------------------------------------------
    | Review Transaction
    |--------------------------------------------------------------------------
    */

    DB::transaction(function () use (
        $verification,
        $verificationId,
        $verificationType,
        $decision,
        $comment,
        $lecturer,
        $reviewedAt,
        &$completionPercentage,
        &$recordStatus
    ) {

        /*
        |--------------------------------------------------------------------------
        | Update Supervisor Verification
        |--------------------------------------------------------------------------
        */

        DB::table('supervisor_verifications')
            ->where('id', $verificationId)
            ->update([
                'verification_status' =>
                    $decision,

                'reviewed_by' =>
                    $lecturer->id,

                'reviewed_at' =>
                    $reviewedAt,

                'review_comment' =>
                    $comment,

                'updated_at' =>
                    $reviewedAt,
            ]);

        /*
        |--------------------------------------------------------------------------
        | Clinical Entry Review
        |--------------------------------------------------------------------------
        */

        if ($verificationType === 'CLINICAL_ENTRY') {

            $latestVerificationId =
                DB::table('supervisor_verifications')
                    ->where(
                        'clinical_entry_id',
                        $verification->clinical_entry_id
                    )
                    ->max('id');

            if (
                (int) $latestVerificationId ===
                (int) $verification->id
            ) {
                $recordStatus =
                    $decision === 'APPROVED'
                        ? 'VERIFIED'
                        : 'REJECTED';

                DB::table('clinical_entries')
                    ->where(
                        'id',
                        $verification->clinical_entry_id
                    )
                    ->update([
                        'status' =>
                            $recordStatus,

                        'updated_at' =>
                            $reviewedAt,
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Clinical Verification Affects Logbook Completion
            |--------------------------------------------------------------------------
            */

            $completionPercentage =
                $this->recalculateLogbookCompletion(
                    (int) $verification
                        ->student_logbook_id
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Attendance Review
        |--------------------------------------------------------------------------
        */

        if ($verificationType === 'ATTENDANCE') {

            $latestVerificationId =
                DB::table('supervisor_verifications')
                    ->where(
                        'attendance_record_id',
                        $verification
                            ->attendance_record_id
                    )
                    ->max('id');

            if (
                (int) $latestVerificationId ===
                (int) $verification->id
            ) {
                $recordStatus =
                    $decision === 'APPROVED'
                        ? 'VERIFIED'
                        : 'REJECTED';

                DB::table('attendance_records')
                    ->where(
                        'id',
                        $verification
                            ->attendance_record_id
                    )
                    ->update([
                        'status' =>
                            $recordStatus,

                        'updated_at' =>
                            $reviewedAt,
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Attendance Does Not Change Logbook Requirement Completion
            |--------------------------------------------------------------------------
            */

            $completionPercentage = null;
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    return response()->json([
        'message' =>
            $decision === 'APPROVED'
                ? 'Verification approved successfully.'
                : 'Verification rejected successfully.',

        'verification_id' =>
            (int) $verificationId,

        'verification_type' =>
            $verificationType,

        'verification_status' =>
            $decision,

        'record_status' =>
            $recordStatus,

        'reviewed_by' => [
            'id' =>
                $lecturer->id,

            'name' =>
                $lecturer->name,

            'dwu_id' =>
                $lecturer->dwu_id,
        ],

        'reviewed_at' =>
            $reviewedAt->toDateTimeString(),

        'comment' =>
            $comment,

        'completion_percentage' =>
            $completionPercentage,
    ]);
}
    public function studentProgress(Request $request, $unitId, $studentId)
    {
        $lecturer = $request->user();

        $assigned = DB::table('lecturer_units')
            ->where('lecturer_id', $lecturer->id)
            ->where('unit_id', $unitId)
            ->exists();

        if (!$assigned) {
            return response()->json([
                'message' => 'You are not assigned to this unit.'
            ], 403);
        }

        $unit = DB::table('units')
            ->where('id', $unitId)
            ->first();

        if (!$unit) {
            return response()->json([
                'message' => 'Unit not found.'
            ], 404);
        }

        $enrollment = DB::table('enrollments')
            ->join('users', 'enrollments.student_id', '=', 'users.id')
            ->where('enrollments.unit_id', $unitId)
            ->where('enrollments.student_id', $studentId)
            ->select(
                'enrollments.id as enrollment_id',
                'enrollments.status as enrollment_status',
                'enrollments.enrollment_date',
                'users.id as student_id',
                'users.dwu_id',
                'users.name',
                'users.email',
                'users.year_level_id'
            )
            ->first();

        if (!$enrollment) {
            return response()->json([
                'message' => 'Student is not enrolled in this unit.'
            ], 404);
        }

        $logbook = DB::table('student_logbooks')
            ->join(
                'logbook_templates',
                'student_logbooks.logbook_template_id',
                '=',
                'logbook_templates.id'
            )
            ->where(
                'student_logbooks.enrollment_id',
                $enrollment->enrollment_id
            )
            ->select(
                'student_logbooks.*',
                'logbook_templates.template_name',
                'logbook_templates.minimum_completion_percentage'
            )
            ->orderByDesc('student_logbooks.id')
            ->first();

        if (!$logbook) {
            return response()->json([
                'student' => [
                    'id' => $enrollment->student_id,
                    'dwu_id' => $enrollment->dwu_id,
                    'name' => $enrollment->name,
                    'email' => $enrollment->email,
                    'year_level_id' => $enrollment->year_level_id,
                ],
                'unit' => [
                    'id' => $unit->id,
                    'unit_code' => $unit->unit_code,
                    'unit_name' => $unit->unit_name,
                ],
                'enrollment' => [
                    'id' => $enrollment->enrollment_id,
                    'status' => $enrollment->enrollment_status,
                    'enrollment_date' => $enrollment->enrollment_date,
                ],
                'logbook' => null,
                'clinical_entries' => [],
                'summary' => [
                    'total_entries' => 0,
                    'draft_entries' => 0,
                    'pending_entries' => 0,
                    'verified_entries' => 0,
                    'rejected_entries' => 0,
                ],
                'message' =>
                    'No logbook is currently assigned to this student for this unit.',
            ]);
        }

        $completionPercentage =
            $this->recalculateLogbookCompletion(
                (int) $logbook->id
            );

        $logbook = DB::table('student_logbooks')
            ->join(
                'logbook_templates',
                'student_logbooks.logbook_template_id',
                '=',
                'logbook_templates.id'
            )
            ->where('student_logbooks.id', $logbook->id)
            ->select(
                'student_logbooks.*',
                'logbook_templates.template_name',
                'logbook_templates.minimum_completion_percentage'
            )
            ->first();

        $minimumCompletionPercentage =
            (float) (
                $logbook->minimum_completion_percentage ?? 100
            );

        $completionRequirementMet =
            $completionPercentage >= $minimumCompletionPercentage;

        if ($completionRequirementMet) {
            $completionStatus = 'COMPLETION_REQUIREMENT_MET';
        } elseif ($completionPercentage > 0) {
            $completionStatus = 'IN_PROGRESS';
        } else {
            $completionStatus = 'NOT_STARTED';
        }

        $clinicalEntries = DB::table('clinical_entries')
            ->where('student_logbook_id', $logbook->id)
            ->select(
                'id',
                'activity_date',
                'activity_details',
                'facility_name',
                'status',
                'created_at',
                'updated_at'
            )
            ->orderByDesc('activity_date')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'student' => [
                'id' => $enrollment->student_id,
                'dwu_id' => $enrollment->dwu_id,
                'name' => $enrollment->name,
                'email' => $enrollment->email,
                'year_level_id' => $enrollment->year_level_id,
            ],
            'unit' => [
                'id' => $unit->id,
                'unit_code' => $unit->unit_code,
                'unit_name' => $unit->unit_name,
            ],
            'enrollment' => [
                'id' => $enrollment->enrollment_id,
                'status' => $enrollment->enrollment_status,
                'enrollment_date' => $enrollment->enrollment_date,
            ],
            'logbook' => [
                'id' => $logbook->id,
                'template_name' => $logbook->template_name,
                'status' => $logbook->status,
                'completion_percentage' => $completionPercentage,
                'minimum_completion_percentage' =>
                    $minimumCompletionPercentage,
                'completion_requirement_met' =>
                    $completionRequirementMet,
                'completion_status' => $completionStatus,
                'assigned_date' => $logbook->assigned_date,
                'due_date' => $logbook->due_date,
            ],
            'summary' => [
                'total_entries' => $clinicalEntries->count(),
                'draft_entries' =>
                    $clinicalEntries->where('status', 'DRAFT')->count(),
                'pending_entries' =>
                    $clinicalEntries
                        ->where('status', 'PENDING_VERIFICATION')
                        ->count(),
                'verified_entries' =>
                    $clinicalEntries->where('status', 'VERIFIED')->count(),
                'rejected_entries' =>
                    $clinicalEntries->where('status', 'REJECTED')->count(),
            ],
            'clinical_entries' => $clinicalEntries,
        ]);
    }

    public function showStudentClinicalEntry(
        Request $request,
        $unitId,
        $studentId,
        $entryId
    ) {
        $lecturer = $request->user();

        $assigned = DB::table('lecturer_units')
            ->where('lecturer_id', $lecturer->id)
            ->where('unit_id', $unitId)
            ->exists();

        if (!$assigned) {
            return response()->json([
                'message' => 'You are not assigned to this unit.'
            ], 403);
        }

        $entry = DB::table('clinical_entries')
            ->join(
                'student_logbooks',
                'clinical_entries.student_logbook_id',
                '=',
                'student_logbooks.id'
            )
            ->join(
                'enrollments',
                'student_logbooks.enrollment_id',
                '=',
                'enrollments.id'
            )
            ->join(
                'users',
                'enrollments.student_id',
                '=',
                'users.id'
            )
            ->join(
                'units',
                'enrollments.unit_id',
                '=',
                'units.id'
            )
            ->where('clinical_entries.id', $entryId)
            ->where('enrollments.student_id', $studentId)
            ->where('enrollments.unit_id', $unitId)
            ->select(
                'clinical_entries.*',
                'users.id as student_id',
                'users.name as student_name',
                'users.dwu_id as student_dwu_id',
                'users.email as student_email',
                'units.id as unit_id',
                'units.unit_code',
                'units.unit_name',
                'student_logbooks.id as student_logbook_id',
                'student_logbooks.status as logbook_status'
            )
            ->first();

        if (!$entry) {
            return response()->json([
                'message' =>
                    'Clinical entry not found or you do not have permission to view it.'
            ], 404);
        }

        $verifications = DB::table('supervisor_verifications')
            ->leftJoin(
                'clinical_supervisors',
                'supervisor_verifications.clinical_supervisor_id',
                '=',
                'clinical_supervisors.id'
            )
            ->leftJoin(
                'users as reviewer',
                'supervisor_verifications.reviewed_by',
                '=',
                'reviewer.id'
            )
            ->where(
                'supervisor_verifications.clinical_entry_id',
                $entryId
            )
            ->select(
                'supervisor_verifications.id as verification_id',
                'supervisor_verifications.verification_status',
                'supervisor_verifications.verification_timestamp',
                'supervisor_verifications.verification_method',
                'supervisor_verifications.face_lbph_distance',
                'supervisor_verifications.face_comparison_decision',
                'supervisor_verifications.signature_path',
                'supervisor_verifications.face_capture_path',
                'supervisor_verifications.reviewed_by',
                'supervisor_verifications.reviewed_at',
                'supervisor_verifications.review_comment',
                'reviewer.name as reviewer_name',
                'reviewer.dwu_id as reviewer_dwu_id',
                'clinical_supervisors.id as clinical_supervisor_id',
                'clinical_supervisors.full_name as supervisor_name',
                'clinical_supervisors.registration_number',
                'clinical_supervisors.profession',
                'clinical_supervisors.facility_name as supervisor_facility'
            )
            ->orderByDesc('supervisor_verifications.id')
            ->get();

        $verifications = $verifications->map(function ($verification) {
            $verification->signature_url =
                !empty($verification->signature_path)
                    ? asset('storage/' . $verification->signature_path)
                    : null;

            $verification->face_capture_url =
                !empty($verification->face_capture_path)
                    ? asset('storage/' . $verification->face_capture_path)
                    : null;

            return $verification;
        });

        return response()->json([
            'entry' => $entry,
            'verifications' => $verifications,
            'verification_count' => $verifications->count(),
            'notice' =>
                'Supervisor face comparison is supporting evidence only. '
                . 'The lecturer makes the final review decision.',
        ]);
    }

    private function recalculateLogbookCompletion(
        int $studentLogbookId
    ): float {
        $studentLogbook = DB::table('student_logbooks')
            ->where('id', $studentLogbookId)
            ->first();

        if (!$studentLogbook) {
            return 0;
        }

        $template = DB::table('logbook_templates')
            ->where('id', $studentLogbook->logbook_template_id)
            ->first();

        $minimumCompletionPercentage =
            (float) (
                $template->minimum_completion_percentage ?? 100
            );

        $totalRequirements =
            DB::table('logbook_item_requirements')
                ->join(
                    'logbook_items',
                    'logbook_item_requirements.logbook_item_id',
                    '=',
                    'logbook_items.id'
                )
                ->join(
                    'logbook_sections',
                    'logbook_items.logbook_section_id',
                    '=',
                    'logbook_sections.id'
                )
                ->where(
                    'logbook_sections.logbook_template_id',
                    $studentLogbook->logbook_template_id
                )
                ->where('logbook_items.is_active', true)
                ->count('logbook_item_requirements.id');

        if ($totalRequirements <= 0) {
            DB::table('student_logbooks')
                ->where('id', $studentLogbookId)
                ->update([
                    'completion_percentage' => 0,
                    'status' => 'ACTIVE',
                    'updated_at' => now(),
                ]);

            return 0;
        }

        $completedRequirements =
            DB::table('clinical_entries')
                ->join(
                    'logbook_item_requirements',
                    'clinical_entries.logbook_item_requirement_id',
                    '=',
                    'logbook_item_requirements.id'
                )
                ->join(
                    'logbook_items',
                    'logbook_item_requirements.logbook_item_id',
                    '=',
                    'logbook_items.id'
                )
                ->join(
                    'logbook_sections',
                    'logbook_items.logbook_section_id',
                    '=',
                    'logbook_sections.id'
                )
                ->where(
                    'clinical_entries.student_logbook_id',
                    $studentLogbookId
                )
                ->where('clinical_entries.status', 'VERIFIED')
                ->whereNotNull(
                    'clinical_entries.logbook_item_requirement_id'
                )
                ->where(
                    'logbook_sections.logbook_template_id',
                    $studentLogbook->logbook_template_id
                )
                ->where('logbook_items.is_active', true)
                ->distinct()
                ->count(
                    'clinical_entries.logbook_item_requirement_id'
                );

        $completionPercentage = round(
            ($completedRequirements / $totalRequirements) * 100,
            2
        );

        $completionPercentage =
            max(0, min(100, $completionPercentage));

        $newStatus =
            $completionPercentage >= $minimumCompletionPercentage
                ? 'COMPLETED'
                : 'ACTIVE';

        DB::table('student_logbooks')
            ->where('id', $studentLogbookId)
            ->update([
                'completion_percentage' => $completionPercentage,
                'status' => $newStatus,
                'updated_at' => now(),
            ]);

        return (float) $completionPercentage;
    }
}