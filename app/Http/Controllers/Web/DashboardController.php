<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /**
     * Lecturer dashboard.
     */
    public function lecturer()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'LECTURER') {
            abort(403, 'You are not authorized to access the Lecturer dashboard.');
        }

        $unitIds = DB::table('lecturer_units')
            ->where('lecturer_id', $user->id)
            ->pluck('unit_id');

        $myUnitsCount = $unitIds->count();

        $enrolledStudentsCount = 0;

        if ($unitIds->isNotEmpty()) {
            $enrolledStudentsCount = DB::table('enrollments')
                ->whereIn('unit_id', $unitIds)
                ->where('status', 'ACTIVE')
                ->distinct()
                ->count('student_id');
        }

        $pendingClinicalVerifications = 0;
        $pendingAttendanceVerifications = 0;

        if ($unitIds->isNotEmpty()) {
            /*
            |--------------------------------------------------------------------------
            | Latest Clinical Verification IDs
            |--------------------------------------------------------------------------
            */

            $latestClinicalVerificationIds =
                DB::table('supervisor_verifications')
                    ->whereNotNull('clinical_entry_id')
                    ->select(
                        'clinical_entry_id',
                        DB::raw('MAX(id) as latest_verification_id')
                    )
                    ->groupBy('clinical_entry_id');

            $pendingClinicalVerifications =
                DB::table('supervisor_verifications')
                    ->joinSub(
                        $latestClinicalVerificationIds,
                        'latest_clinical_verifications',
                        function ($join) {
                            $join->on(
                                'supervisor_verifications.id',
                                '=',
                                'latest_clinical_verifications.latest_verification_id'
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
                    ->whereIn('enrollments.unit_id', $unitIds)
                    ->where(
                        'supervisor_verifications.verification_status',
                        'MANUAL_REVIEW'
                    )
                    ->count();

            /*
            |--------------------------------------------------------------------------
            | Latest Attendance Verification IDs
            |--------------------------------------------------------------------------
            */

            $latestAttendanceVerificationIds =
                DB::table('supervisor_verifications')
                    ->whereNotNull('attendance_record_id')
                    ->select(
                        'attendance_record_id',
                        DB::raw('MAX(id) as latest_verification_id')
                    )
                    ->groupBy('attendance_record_id');

            $pendingAttendanceVerifications =
                DB::table('supervisor_verifications')
                    ->joinSub(
                        $latestAttendanceVerificationIds,
                        'latest_attendance_verifications',
                        function ($join) {
                            $join->on(
                                'supervisor_verifications.id',
                                '=',
                                'latest_attendance_verifications.latest_verification_id'
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
                    ->whereIn('enrollments.unit_id', $unitIds)
                    ->where(
                        'supervisor_verifications.verification_status',
                        'MANUAL_REVIEW'
                    )
                    ->count();
        }

        $pendingVerificationsCount =
            $pendingClinicalVerifications +
            $pendingAttendanceVerifications;

        return view('web.lecturer.dashboard', [
            'user' => $user,
            'myUnitsCount' => $myUnitsCount,
            'enrolledStudentsCount' => $enrolledStudentsCount,
            'pendingVerificationsCount' => $pendingVerificationsCount,
        ]);
    }

    /**
     * Lecturer assigned units.
     */
    public function lecturerUnits()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'LECTURER') {
            abort(403, 'You are not authorized to access Lecturer units.');
        }

        $units = DB::table('lecturer_units')
            ->join(
                'units',
                'lecturer_units.unit_id',
                '=',
                'units.id'
            )
            ->join(
                'departments',
                'units.department_id',
                '=',
                'departments.id'
            )
            ->join(
                'year_levels',
                'units.year_level_id',
                '=',
                'year_levels.id'
            )
            ->join(
                'semesters',
                'units.semester_id',
                '=',
                'semesters.id'
            )
            ->where(
                'lecturer_units.lecturer_id',
                $user->id
            )
            ->select(
                'units.id',
                'units.unit_code',
                'units.unit_name',
                'units.requires_logbook',
                'departments.department_name',
                'year_levels.year_name',
                'semesters.semester_name'
            )
            ->orderBy('units.unit_code')
            ->get();

        return view('web.lecturer.units', [
            'user' => $user,
            'units' => $units,
        ]);
    }

    /**
     * Students enrolled in one lecturer-assigned unit.
     */
    public function lecturerUnitStudents($unitId)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'LECTURER') {
            abort(403, 'You are not authorized to access Lecturer students.');
        }

        $assigned = DB::table('lecturer_units')
            ->where('lecturer_id', $user->id)
            ->where('unit_id', $unitId)
            ->exists();

        if (!$assigned) {
            abort(403, 'You are not assigned to this unit.');
        }

        $unit = DB::table('units')
            ->leftJoin(
                'departments',
                'units.department_id',
                '=',
                'departments.id'
            )
            ->leftJoin(
                'year_levels',
                'units.year_level_id',
                '=',
                'year_levels.id'
            )
            ->leftJoin(
                'semesters',
                'units.semester_id',
                '=',
                'semesters.id'
            )
            ->where('units.id', $unitId)
            ->select(
                'units.id',
                'units.unit_code',
                'units.unit_name',
                'units.requires_logbook',
                'departments.department_name',
                'year_levels.year_name',
                'semesters.semester_name'
            )
            ->first();

        if (!$unit) {
            abort(404, 'Unit not found.');
        }

        $students = DB::table('enrollments')
            ->join(
                'users',
                'enrollments.student_id',
                '=',
                'users.id'
            )
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
            ->orderBy('users.name')
            ->get();

        return view('web.lecturer.unit-students', [
            'user' => $user,
            'unit' => $unit,
            'students' => $students,
        ]);
    }

    /**
     * All students enrolled in units assigned to the lecturer.
     */
    public function lecturerStudents()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'LECTURER') {
            abort(403, 'You are not authorized to access Lecturer students.');
        }

        $unitIds = DB::table('lecturer_units')
            ->where('lecturer_id', $user->id)
            ->pluck('unit_id');

        $students = collect();

        if ($unitIds->isNotEmpty()) {
            $students = DB::table('enrollments')
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
                ->leftJoin(
                    'student_logbooks',
                    'enrollments.id',
                    '=',
                    'student_logbooks.enrollment_id'
                )
                ->whereIn(
                    'enrollments.unit_id',
                    $unitIds
                )
                ->select(
                    'users.id as student_id',
                    'users.dwu_id',
                    'users.name',
                    'users.email',
                    'units.id as unit_id',
                    'units.unit_code',
                    'units.unit_name',
                    'enrollments.id as enrollment_id',
                    'enrollments.status as enrollment_status',
                    'student_logbooks.id as student_logbook_id',
                    'student_logbooks.status as logbook_status',
                    'student_logbooks.completion_percentage'
                )
                ->orderBy('users.name')
                ->orderBy('units.unit_code')
                ->get();
        }

        $uniqueStudentsCount =
            $students->pluck('student_id')->unique()->count();

        $activeEnrollmentsCount =
            $students->filter(function ($student) {
                return strtoupper(
                    (string) $student->enrollment_status
                ) === 'ACTIVE';
            })->count();

        $assignedUnitsCount = $unitIds->count();

        return view('web.lecturer.students', [
            'user' => $user,
            'students' => $students,
            'uniqueStudentsCount' => $uniqueStudentsCount,
            'activeEnrollmentsCount' => $activeEnrollmentsCount,
            'assignedUnitsCount' => $assignedUnitsCount,
        ]);
    }
        /**
     * Student progress for students enrolled in units assigned
     * to the logged-in lecturer.
     */
    public function lecturerStudentProgress()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'LECTURER') {
            abort(
                403,
                'You are not authorized to access Lecturer student progress.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Units Assigned to Lecturer
        |--------------------------------------------------------------------------
        */

        $unitIds = DB::table('lecturer_units')
            ->where('lecturer_id', $user->id)
            ->pluck('unit_id');

        $students = collect();

        if ($unitIds->isNotEmpty()) {

            /*
            |--------------------------------------------------------------------------
            | Verified Clinical Entries
            |--------------------------------------------------------------------------
            */

            $verifiedClinicalEntries =
                DB::table('clinical_entries')
                    ->select(
                        'student_logbook_id',
                        DB::raw('COUNT(*) as verified_entries')
                    )
                    ->where('status', 'VERIFIED')
                    ->groupBy('student_logbook_id');

            /*
            |--------------------------------------------------------------------------
            | Attendance Summary
            |--------------------------------------------------------------------------
            */

            $attendanceSummary =
                DB::table('attendance_records')
                    ->select(
                        'student_logbook_id',
                        DB::raw(
                            'COUNT(*) as total_attendance_records'
                        ),
                        DB::raw(
                            "SUM(CASE WHEN status = 'VERIFIED' " .
                            "THEN 1 ELSE 0 END) " .
                            'as verified_attendance_records'
                        ),
                        DB::raw(
                            "SUM(CASE WHEN status = 'VERIFIED' " .
                            "THEN COALESCE(total_hours, 0) " .
                            "ELSE 0 END) " .
                            'as verified_attendance_hours'
                        )
                    )
                    ->groupBy('student_logbook_id');

            /*
            |--------------------------------------------------------------------------
            | Latest Clinical Verification IDs
            |--------------------------------------------------------------------------
            */

            $latestClinicalVerificationIds =
                DB::table('supervisor_verifications')
                    ->whereNotNull('clinical_entry_id')
                    ->select(
                        'clinical_entry_id',
                        DB::raw(
                            'MAX(id) as latest_verification_id'
                        )
                    )
                    ->groupBy('clinical_entry_id');

            /*
            |--------------------------------------------------------------------------
            | Pending Clinical Verifications
            |--------------------------------------------------------------------------
            */

            $pendingClinicalVerifications =
                DB::table('supervisor_verifications')
                    ->joinSub(
                        $latestClinicalVerificationIds,
                        'latest_clinical_verifications',
                        function ($join) {
                            $join->on(
                                'supervisor_verifications.id',
                                '=',
                                'latest_clinical_verifications.latest_verification_id'
                            );
                        }
                    )
                    ->join(
                        'clinical_entries',
                        'supervisor_verifications.clinical_entry_id',
                        '=',
                        'clinical_entries.id'
                    )
                    ->where(
                        'supervisor_verifications.verification_status',
                        'MANUAL_REVIEW'
                    )
                    ->select(
                        'clinical_entries.student_logbook_id',
                        DB::raw(
                            'COUNT(*) as pending_clinical_verifications'
                        )
                    )
                    ->groupBy(
                        'clinical_entries.student_logbook_id'
                    );

            /*
            |--------------------------------------------------------------------------
            | Latest Attendance Verification IDs
            |--------------------------------------------------------------------------
            */

            $latestAttendanceVerificationIds =
                DB::table('supervisor_verifications')
                    ->whereNotNull('attendance_record_id')
                    ->select(
                        'attendance_record_id',
                        DB::raw(
                            'MAX(id) as latest_verification_id'
                        )
                    )
                    ->groupBy('attendance_record_id');

            /*
            |--------------------------------------------------------------------------
            | Pending Attendance Verifications
            |--------------------------------------------------------------------------
            */

            $pendingAttendanceVerifications =
                DB::table('supervisor_verifications')
                    ->joinSub(
                        $latestAttendanceVerificationIds,
                        'latest_attendance_verifications',
                        function ($join) {
                            $join->on(
                                'supervisor_verifications.id',
                                '=',
                                'latest_attendance_verifications.latest_verification_id'
                            );
                        }
                    )
                    ->join(
                        'attendance_records',
                        'supervisor_verifications.attendance_record_id',
                        '=',
                        'attendance_records.id'
                    )
                    ->where(
                        'supervisor_verifications.verification_status',
                        'MANUAL_REVIEW'
                    )
                    ->select(
                        'attendance_records.student_logbook_id',
                        DB::raw(
                            'COUNT(*) as pending_attendance_verifications'
                        )
                    )
                    ->groupBy(
                        'attendance_records.student_logbook_id'
                    );

            /*
            |--------------------------------------------------------------------------
            | Student Progress Records
            |--------------------------------------------------------------------------
            */

            $students =
                DB::table('enrollments')
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
                    ->leftJoin(
                        'student_logbooks',
                        'enrollments.id',
                        '=',
                        'student_logbooks.enrollment_id'
                    )
                    ->leftJoinSub(
                        $verifiedClinicalEntries,
                        'verified_clinical_entries',
                        function ($join) {
                            $join->on(
                                'student_logbooks.id',
                                '=',
                                'verified_clinical_entries.student_logbook_id'
                            );
                        }
                    )
                    ->leftJoinSub(
                        $attendanceSummary,
                        'attendance_summary',
                        function ($join) {
                            $join->on(
                                'student_logbooks.id',
                                '=',
                                'attendance_summary.student_logbook_id'
                            );
                        }
                    )
                    ->leftJoinSub(
                        $pendingClinicalVerifications,
                        'pending_clinical',
                        function ($join) {
                            $join->on(
                                'student_logbooks.id',
                                '=',
                                'pending_clinical.student_logbook_id'
                            );
                        }
                    )
                    ->leftJoinSub(
                        $pendingAttendanceVerifications,
                        'pending_attendance',
                        function ($join) {
                            $join->on(
                                'student_logbooks.id',
                                '=',
                                'pending_attendance.student_logbook_id'
                            );
                        }
                    )
                    ->whereIn(
                        'enrollments.unit_id',
                        $unitIds
                    )
                    ->select(
                        'users.id as student_id',
                        'users.dwu_id',
                        'users.name',
                        'users.email',

                        'units.id as unit_id',
                        'units.unit_code',
                        'units.unit_name',

                        'enrollments.id as enrollment_id',
                        'enrollments.status as enrollment_status',

                        'student_logbooks.id as student_logbook_id',
                        'student_logbooks.status as logbook_status',
                        'student_logbooks.completion_percentage',

                        DB::raw(
                            'COALESCE(' .
                            'verified_clinical_entries.verified_entries, 0' .
                            ') as verified_entries'
                        ),

                        DB::raw(
                            'COALESCE(' .
                            'attendance_summary.total_attendance_records, 0' .
                            ') as total_attendance_records'
                        ),

                        DB::raw(
                            'COALESCE(' .
                            'attendance_summary.verified_attendance_records, 0' .
                            ') as verified_attendance_records'
                        ),

                        DB::raw(
                            'COALESCE(' .
                            'attendance_summary.verified_attendance_hours, 0' .
                            ') as verified_attendance_hours'
                        ),

                        DB::raw(
                            'COALESCE(' .
                            'pending_clinical.pending_clinical_verifications, 0' .
                            ') as pending_clinical_verifications'
                        ),

                        DB::raw(
                            'COALESCE(' .
                            'pending_attendance.pending_attendance_verifications, 0' .
                            ') as pending_attendance_verifications'
                        )
                    )
                    ->orderBy('users.name')
                    ->orderBy('units.unit_code')
                    ->get();

            /*
            |--------------------------------------------------------------------------
            | Normalize Values
            |--------------------------------------------------------------------------
            */

            $students =
                $students->map(function ($student) {
                    $student->completion_percentage =
                        max(
                            0,
                            min(
                                100,
                                (float) (
                                    $student->completion_percentage
                                    ?? 0
                                )
                            )
                        );

                    $student->verified_entries =
                        (int) $student->verified_entries;

                    $student->total_attendance_records =
                        (int) $student->total_attendance_records;

                    $student->verified_attendance_records =
                        (int) $student->verified_attendance_records;

                    $student->verified_attendance_hours =
                        (float) $student->verified_attendance_hours;

                    $student->pending_clinical_verifications =
                        (int) $student
                            ->pending_clinical_verifications;

                    $student->pending_attendance_verifications =
                        (int) $student
                            ->pending_attendance_verifications;

                    $student->pending_verifications =
                        $student->pending_clinical_verifications +
                        $student->pending_attendance_verifications;

                    return $student;
                });
        }

        /*
        |--------------------------------------------------------------------------
        | Page Summary
        |--------------------------------------------------------------------------
        */

        $uniqueStudentsCount =
            $students
                ->pluck('student_id')
                ->unique()
                ->count();

        $logbooksCount =
            $students
                ->whereNotNull('student_logbook_id')
                ->count();

        $completedLogbooksCount =
            $students
                ->filter(function ($student) {
                    return strtoupper(
                        (string) (
                            $student->logbook_status ?? ''
                        )
                    ) === 'COMPLETED';
                })
                ->count();

        $pendingVerificationsCount =
            $students->sum('pending_verifications');

        return view(
            'web.lecturer.student-progress',
            [
                'user' => $user,
                'students' => $students,
                'uniqueStudentsCount' =>
                    $uniqueStudentsCount,
                'logbooksCount' =>
                    $logbooksCount,
                'completedLogbooksCount' =>
                    $completedLogbooksCount,
                'pendingVerificationsCount' =>
                    $pendingVerificationsCount,
            ]
        );
    }
        /**
     * Show detailed progress for one student in one unit
     * assigned to the logged-in lecturer.
     */
    public function lecturerStudentProgressShow($unitId, $studentId)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'LECTURER') {
            abort(
                403,
                'You are not authorized to access Lecturer student progress.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Confirm Lecturer Is Assigned to the Unit
        |--------------------------------------------------------------------------
        */

        $assigned = DB::table('lecturer_units')
            ->where('lecturer_id', $user->id)
            ->where('unit_id', $unitId)
            ->exists();

        if (!$assigned) {
            abort(403, 'You are not assigned to this unit.');
        }

        /*
        |--------------------------------------------------------------------------
        | Unit
        |--------------------------------------------------------------------------
        */

        $unit = DB::table('units')
            ->where('id', $unitId)
            ->first();

        if (!$unit) {
            abort(404, 'Unit not found.');
        }

        /*
        |--------------------------------------------------------------------------
        | Student Enrollment
        |--------------------------------------------------------------------------
        */

        $enrollment = DB::table('enrollments')
            ->join(
                'users',
                'enrollments.student_id',
                '=',
                'users.id'
            )
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
            abort(404, 'Student is not enrolled in this unit.');
        }

        /*
        |--------------------------------------------------------------------------
        | Student Logbook
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Defaults When No Logbook Is Assigned
        |--------------------------------------------------------------------------
        */

        $clinicalEntries = collect();
        $attendanceRecords = collect();

        $completionPercentage = 0;
        $minimumCompletionPercentage = 100;
        $completionRequirementMet = false;
        $completionStatus = 'NOT_STARTED';

        $summary = [
            'total_entries' => 0,
            'draft_entries' => 0,
            'pending_entries' => 0,
            'verified_entries' => 0,
            'rejected_entries' => 0,
            'total_attendance_records' => 0,
            'verified_attendance_records' => 0,
            'verified_attendance_hours' => 0,
        ];

        if ($logbook) {

            /*
            |--------------------------------------------------------------------------
            | Recalculate Existing SmartLog Completion
            |--------------------------------------------------------------------------
            */

            $completionPercentage =
                $this->recalculateLogbookCompletion(
                    (int) $logbook->id
                );

            /*
            |--------------------------------------------------------------------------
            | Reload Logbook After Recalculation
            |--------------------------------------------------------------------------
            */

            $logbook = DB::table('student_logbooks')
                ->join(
                    'logbook_templates',
                    'student_logbooks.logbook_template_id',
                    '=',
                    'logbook_templates.id'
                )
                ->where(
                    'student_logbooks.id',
                    $logbook->id
                )
                ->select(
                    'student_logbooks.*',
                    'logbook_templates.template_name',
                    'logbook_templates.minimum_completion_percentage'
                )
                ->first();

            $minimumCompletionPercentage =
                (float) (
                    $logbook->minimum_completion_percentage
                    ?? 100
                );

            $completionPercentage =
                max(
                    0,
                    min(
                        100,
                        (float) $completionPercentage
                    )
                );

            $completionRequirementMet =
                $completionPercentage >=
                $minimumCompletionPercentage;

            if ($completionRequirementMet) {
                $completionStatus =
                    'COMPLETION_REQUIREMENT_MET';
            } elseif ($completionPercentage > 0) {
                $completionStatus = 'IN_PROGRESS';
            } else {
                $completionStatus = 'NOT_STARTED';
            }

            /*
            |--------------------------------------------------------------------------
            | Clinical Entries
            |--------------------------------------------------------------------------
            */

            $clinicalEntries =
                DB::table('clinical_entries')
                    ->where(
                        'student_logbook_id',
                        $logbook->id
                    )
                    ->select(
                        'id',
                        'activity_date',
                        'activity_time',
                        'facility_name',
                        'clinical_area',
                        'activity_details',
                        'competency_level',
                        'status',
                        'created_at',
                        'updated_at'
                    )
                    ->orderByDesc('activity_date')
                    ->orderByDesc('id')
                    ->get();

            /*
            |--------------------------------------------------------------------------
            | Attendance Records
            |--------------------------------------------------------------------------
            */

            $attendanceRecords =
                DB::table('attendance_records')
                    ->where(
                        'student_logbook_id',
                        $logbook->id
                    )
                    ->select(
                        'id',
                        'attendance_date',
                        'facility_name',
                        'clinical_unit',
                        'start_time',
                        'finish_time',
                        'total_hours',
                        'status',
                        'created_at',
                        'updated_at'
                    )
                    ->orderByDesc('attendance_date')
                    ->orderByDesc('id')
                    ->get();

            /*
            |--------------------------------------------------------------------------
            | Progress Summary
            |--------------------------------------------------------------------------
            */

            $summary = [
                'total_entries' =>
                    $clinicalEntries->count(),

                'draft_entries' =>
                    $clinicalEntries
                        ->where('status', 'DRAFT')
                        ->count(),

                'pending_entries' =>
                    $clinicalEntries
                        ->where(
                            'status',
                            'PENDING_VERIFICATION'
                        )
                        ->count(),

                'verified_entries' =>
                    $clinicalEntries
                        ->where('status', 'VERIFIED')
                        ->count(),

                'rejected_entries' =>
                    $clinicalEntries
                        ->where('status', 'REJECTED')
                        ->count(),

                'total_attendance_records' =>
                    $attendanceRecords->count(),

                'verified_attendance_records' =>
                    $attendanceRecords
                        ->where('status', 'VERIFIED')
                        ->count(),

                'verified_attendance_hours' =>
                    (float) $attendanceRecords
                        ->where('status', 'VERIFIED')
                        ->sum('total_hours'),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Render Detailed Student Progress Page
        |--------------------------------------------------------------------------
        */

        return view(
            'web.lecturer.student-progress-show',
            [
                'user' => $user,
                'student' => $enrollment,
                'unit' => $unit,
                'logbook' => $logbook,
                'clinicalEntries' => $clinicalEntries,
                'attendanceRecords' => $attendanceRecords,
                'completionPercentage' =>
                    $completionPercentage,
                'minimumCompletionPercentage' =>
                    $minimumCompletionPercentage,
                'completionRequirementMet' =>
                    $completionRequirementMet,
                'completionStatus' =>
                    $completionStatus,
                'summary' => $summary,
            ]
        );
    }
        /**
     * Pending supervisor verifications for the logged-in lecturer.
     *
     * Only the latest verification request for each clinical entry or
     * attendance record is shown. This matches the existing mobile/API
     * workflow.
     */
    public function lecturerPendingVerifications()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'LECTURER') {
            abort(403, 'You are not authorized to access Lecturer verifications.');
        }

        /*
        |--------------------------------------------------------------------------
        | Latest Clinical Verification IDs
        |--------------------------------------------------------------------------
        */

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
                    $user->id
                )
                ->where(
                    'supervisor_verifications.verification_status',
                    'MANUAL_REVIEW'
                )
                ->select(
                    'supervisor_verifications.id as verification_id',
                    'supervisor_verifications.verification_status',
                    'supervisor_verifications.verification_timestamp',
                    'supervisor_verifications.face_match_score',
                    'supervisor_verifications.face_match_passed',
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
                    'clinical_supervisors.full_name as registered_supervisor_name',
                    'clinical_supervisors.registration_number as supervisor_registration_number',
                    'clinical_supervisors.profession as supervisor_profession',

                    'supervisor_verifications.supervisor_name as captured_supervisor_name'
                )
                ->get();

        /*
        |--------------------------------------------------------------------------
        | Latest Attendance Verification IDs
        |--------------------------------------------------------------------------
        */

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
                    $user->id
                )
                ->where(
                    'supervisor_verifications.verification_status',
                    'MANUAL_REVIEW'
                )
                ->select(
                    'supervisor_verifications.id as verification_id',
                    'supervisor_verifications.verification_status',
                    'supervisor_verifications.verification_timestamp',
                    'supervisor_verifications.face_match_score',
                    'supervisor_verifications.face_match_passed',
                    'supervisor_verifications.face_lbph_distance',
                    'supervisor_verifications.face_comparison_decision',

                    DB::raw('NULL as clinical_entry_id'),
                    'attendance_records.id as attendance_record_id',
                    DB::raw("'ATTENDANCE' as verification_type"),

                    DB::raw('NULL as activity_date'),
                    DB::raw('NULL as procedure_name'),
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
                    'clinical_supervisors.full_name as registered_supervisor_name',
                    'clinical_supervisors.registration_number as supervisor_registration_number',
                    'clinical_supervisors.profession as supervisor_profession',

                    'supervisor_verifications.supervisor_name as captured_supervisor_name'
                )
                ->get();

        $verifications =
            $clinicalVerifications
                ->concat($attendanceVerifications)
                ->sortByDesc(function ($verification) {
                    return $verification->verification_timestamp
                        ?? $verification->verification_id;
                })
                ->values();

        $clinicalCount =
            $verifications
                ->where('verification_type', 'CLINICAL_ENTRY')
                ->count();

        $attendanceCount =
            $verifications
                ->where('verification_type', 'ATTENDANCE')
                ->count();

        $pendingCount = $verifications->count();

        return view('web.lecturer.pending-verifications', [
            'user' => $user,
            'verifications' => $verifications,
            'pendingCount' => $pendingCount,
            'clinicalCount' => $clinicalCount,
            'attendanceCount' => $attendanceCount,
        ]);
    }
        /**
     * Show one verification and its evidence.
     */
    public function lecturerVerificationReview($verificationId)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'LECTURER') {
            abort(403, 'You are not authorized to review Lecturer verifications.');
        }

        $baseVerification =
            DB::table('supervisor_verifications')
                ->where('id', $verificationId)
                ->first();

        if (!$baseVerification) {
            abort(404, 'Verification not found.');
        }

        $verification = null;

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
                        $user->id
                    )
                    ->select(
                        'supervisor_verifications.*',

                        DB::raw("'CLINICAL_ENTRY' as verification_type"),

                        'clinical_entries.student_logbook_id',
                        'clinical_entries.activity_date',
                        'clinical_entries.activity_time',
                        'clinical_entries.facility_name as entry_facility_name',
                        'clinical_entries.clinical_area',
                        'clinical_entries.activity_details',
                        'clinical_entries.competency_level',
                        'clinical_entries.status as record_status',

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

                        'clinical_supervisors.full_name as registered_supervisor_name',
                        'clinical_supervisors.facility_name as registered_supervisor_facility',
                        'clinical_supervisors.profession as supervisor_profession',
                        'clinical_supervisors.position_title as supervisor_position_title',
                        'clinical_supervisors.registration_number as supervisor_registration_number',
                        'clinical_supervisors.reference_face_path',
                        'clinical_supervisors.is_verified_supervisor',
                        'clinical_supervisors.is_active as supervisor_is_active',

                        'reviewer.name as reviewer_name',
                        'reviewer.dwu_id as reviewer_dwu_id'
                    )
                    ->first();
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
                        $user->id
                    )
                    ->select(
                        'supervisor_verifications.*',

                        DB::raw("'ATTENDANCE' as verification_type"),

                        'attendance_records.student_logbook_id',

                        DB::raw('NULL as activity_date'),
                        DB::raw('NULL as activity_time'),
                        DB::raw('NULL as entry_facility_name'),
                        DB::raw('NULL as clinical_area'),
                        DB::raw('NULL as activity_details'),
                        DB::raw('NULL as competency_level'),

                        'attendance_records.status as record_status',
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

                        'clinical_supervisors.full_name as registered_supervisor_name',
                        'clinical_supervisors.facility_name as registered_supervisor_facility',
                        'clinical_supervisors.profession as supervisor_profession',
                        'clinical_supervisors.position_title as supervisor_position_title',
                        'clinical_supervisors.registration_number as supervisor_registration_number',
                        'clinical_supervisors.reference_face_path',
                        'clinical_supervisors.is_verified_supervisor',
                        'clinical_supervisors.is_active as supervisor_is_active',

                        'reviewer.name as reviewer_name',
                        'reviewer.dwu_id as reviewer_dwu_id'
                    )
                    ->first();
        }

        if (!$verification) {
            abort(
                404,
                'Verification not found or you do not have permission to view it.'
            );
        }

        $signatureUrl =
            $this->verificationEvidenceUrl(
                $verification->signature_path ?? null
            );

        $faceCaptureUrl =
            $this->verificationEvidenceUrl(
                $verification->face_capture_path ?? null
            );

        $referenceFaceUrl =
            $this->verificationEvidenceUrl(
                $verification->reference_face_path ?? null
            );

        return view('web.lecturer.verification-review', [
            'user' => $user,
            'verification' => $verification,
            'signatureUrl' => $signatureUrl,
            'faceCaptureUrl' => $faceCaptureUrl,
            'referenceFaceUrl' => $referenceFaceUrl,
        ]);
    }
        /**
     * Lecturer approves or rejects one supervisor verification.
     */
    public function lecturerReviewVerification(
        Request $request,
        $verificationId
    ) {
        $user = Auth::user();

        if (!$user || $user->role !== 'LECTURER') {
            abort(403, 'You are not authorized to review Lecturer verifications.');
        }

        $validated = $request->validate([
            'decision' =>
                'required|string|in:APPROVED,REJECTED',

            'comment' =>
                'nullable|string|max:1000',
        ]);

        $baseVerification =
            DB::table('supervisor_verifications')
                ->where('id', $verificationId)
                ->first();

        if (!$baseVerification) {
            return redirect()
                ->route('web.lecturer.verifications')
                ->with(
                    'error',
                    'Verification not found.'
                );
        }

        $verification = null;
        $verificationType = null;

        /*
        |--------------------------------------------------------------------------
        | Clinical Entry Permission Check
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
                        $user->id
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
        | Attendance Permission Check
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
                        $user->id
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

        if (!$verification) {
            abort(
                404,
                'Verification not found or you do not have permission to review it.'
            );
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
            return redirect()
                ->route(
                    'web.lecturer.verification.review',
                    ['verificationId' => $verificationId]
                )
                ->with(
                    'error',
                    'This verification has already been reviewed.'
                );
        }

        $decision = $validated['decision'];
        $comment = $validated['comment'] ?? null;
        $reviewedAt = now();

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
            $user,
            $reviewedAt
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
                        $user->id,

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

                $this->recalculateLogbookCompletion(
                    (int) $verification->student_logbook_id
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
                            $verification->attendance_record_id
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
                            $verification->attendance_record_id
                        )
                        ->update([
                            'status' =>
                                $recordStatus,

                            'updated_at' =>
                                $reviewedAt,
                        ]);
                }
            }
        });

        $message =
            $decision === 'APPROVED'
                ? 'Verification approved successfully.'
                : 'Verification rejected successfully.';

        return redirect()
            ->route('web.lecturer.verifications')
            ->with('success', $message);
    }
        /** Lecturer clinical logbook templates for assigned units. */
    public function lecturerLogbooks()
    {
        $user = $this->requireLecturer();
        $unitIds = DB::table('lecturer_units')->where('lecturer_id', $user->id)->pluck('unit_id');

        $templates = DB::table('logbook_templates as lt')
            ->join('units as u', 'lt.unit_id', '=', 'u.id')
            ->leftJoin('year_levels as yl', 'u.year_level_id', '=', 'yl.id')
            ->leftJoin('semesters as s', 'u.semester_id', '=', 's.id')
            ->whereIn('lt.unit_id', $unitIds)
            ->select('lt.*', 'u.unit_code', 'u.unit_name', 'yl.year_name', 's.semester_name')
            ->orderBy('u.unit_code')->orderByDesc('lt.id')->get()
            ->map(function ($template) {
                $template->sections_count = DB::table('logbook_sections')->where('logbook_template_id', $template->id)->count();
                $template->items_count = DB::table('logbook_items as li')->join('logbook_sections as ls','li.logbook_section_id','=','ls.id')->where('ls.logbook_template_id',$template->id)->count();
                $template->requirements_count = DB::table('logbook_item_requirements as lir')->join('logbook_items as li','lir.logbook_item_id','=','li.id')->join('logbook_sections as ls','li.logbook_section_id','=','ls.id')->where('ls.logbook_template_id',$template->id)->count();
                $template->student_logbooks_count = DB::table('student_logbooks')->where('logbook_template_id',$template->id)->count();
                return $template;
            });

        return view('web.lecturer.logbooks', compact('user','templates'));
    }

    public function lecturerLogbookCreate()
    {
        $user = $this->requireLecturer();
        $units = DB::table('lecturer_units as lu')->join('units as u','lu.unit_id','=','u.id')
            ->where('lu.lecturer_id',$user->id)->where('u.requires_logbook',1)->where('u.is_active',1)
            ->select('u.id','u.unit_code','u.unit_name')->orderBy('u.unit_code')->get();
        return view('web.lecturer.logbook-create', compact('user','units'));
    }

    public function lecturerLogbookStore(Request $request)
    {
        $user = $this->requireLecturer();
        $v = $request->validate([
            'unit_id'=>'required|integer|exists:units,id', 'template_name'=>'required|string|max:255',
            'description'=>'nullable|string|max:2000', 'minimum_completion_percentage'=>'required|numeric|min:0|max:100',
            'is_active'=>'required|boolean',
        ]);
        $this->requireLecturerUnit($user->id, (int)$v['unit_id']);
        $unit = DB::table('units')->where('id',$v['unit_id'])->first();
        if (!$unit || !(bool)$unit->requires_logbook) return back()->withInput()->withErrors(['unit_id'=>'This unit is not configured to use a clinical logbook.']);

        $id = DB::table('logbook_templates')->insertGetId([
            'unit_id'=>(int)$v['unit_id'], 'template_name'=>trim($v['template_name']),
            'description'=>isset($v['description']) && trim($v['description'])!=='' ? trim($v['description']) : null,
            'minimum_completion_percentage'=>(float)$v['minimum_completion_percentage'], 'is_active'=>(bool)$v['is_active'],
            'created_at'=>now(), 'updated_at'=>now(),
        ]);
        return redirect()->route('web.lecturer.logbooks.manage',['templateId'=>$id])->with('success','Clinical logbook template created. Now add its sections, items and requirements.');
    }

    public function lecturerLogbookManage($templateId)
    {
        $user = $this->requireLecturer();
        $template = $this->lecturerTemplateForUser($user->id, (int)$templateId);
        $sections = DB::table('logbook_sections')->where('logbook_template_id',$template->id)->orderBy('display_order')->orderBy('id')->get();
        foreach ($sections as $section) {
            $section->items = DB::table('logbook_items')->where('logbook_section_id',$section->id)->orderBy('display_order')->orderBy('id')->get();
            foreach ($section->items as $item) {
                $item->requirements = DB::table('logbook_item_requirements')->where('logbook_item_id',$item->id)->orderBy('sequence_number')->orderBy('id')->get();
            }
        }
        $assignedCount = DB::table('student_logbooks')->where('logbook_template_id',$template->id)->count();
        $eligibleEnrollments = DB::table('enrollments')->where('unit_id',$template->unit_id)->where('status','ACTIVE')->count();
        return view('web.lecturer.logbook-manage', compact('user','template','sections','assignedCount','eligibleEnrollments'));
    }

    public function lecturerLogbookUpdate(Request $request, $templateId)
    {
        $user=$this->requireLecturer(); $template=$this->lecturerTemplateForUser($user->id,(int)$templateId);
        $v=$request->validate(['template_name'=>'required|string|max:255','description'=>'nullable|string|max:2000','minimum_completion_percentage'=>'required|numeric|min:0|max:100','is_active'=>'required|boolean']);
        DB::table('logbook_templates')->where('id',$template->id)->update([
            'template_name'=>trim($v['template_name']), 'description'=>isset($v['description'])&&trim($v['description'])!==''?trim($v['description']):null,
            'minimum_completion_percentage'=>(float)$v['minimum_completion_percentage'], 'is_active'=>(bool)$v['is_active'], 'updated_at'=>now(),
        ]);
        return back()->with('success','Clinical logbook template updated successfully.');
    }

    public function lecturerLogbookAssign($templateId)
    {
        $user=$this->requireLecturer(); $template=$this->lecturerTemplateForUser($user->id,(int)$templateId);
        if (!(bool)$template->is_active) return back()->withErrors(['template'=>'Activate the template before assigning it to students.']);
        $requirements=DB::table('logbook_item_requirements as r')->join('logbook_items as i','r.logbook_item_id','=','i.id')->join('logbook_sections as s','i.logbook_section_id','=','s.id')->where('s.logbook_template_id',$template->id)->where('i.is_active',1)->count();
        if ($requirements < 1) return back()->withErrors(['template'=>'Add at least one active item requirement before assigning this logbook.']);
        $enrollments=DB::table('enrollments')->where('unit_id',$template->unit_id)->where('status','ACTIVE')->pluck('id');
        $created=0;
        DB::transaction(function() use($enrollments,$template,&$created){
            foreach($enrollments as $enrollmentId){
                $exists=DB::table('student_logbooks')->where('enrollment_id',$enrollmentId)->where('logbook_template_id',$template->id)->exists();
                if(!$exists){ DB::table('student_logbooks')->insert(['enrollment_id'=>$enrollmentId,'logbook_template_id'=>$template->id,'assigned_date'=>now()->toDateString(),'due_date'=>null,'status'=>'ACTIVE','completion_percentage'=>0,'created_at'=>now(),'updated_at'=>now()]); $created++; }
            }
        });
        return back()->with('success',$created.' student logbook(s) assigned. Existing student logbooks were left unchanged.');
    }

    public function lecturerLogbookSectionStore(Request $request, $templateId)
    {
        $user=$this->requireLecturer(); $template=$this->lecturerTemplateForUser($user->id,(int)$templateId);
        $v=$request->validate(['section_title'=>'required|string|max:255','section_type'=>'required|string|in:ATTENDANCE,PROCEDURE,CHECKLIST,PATIENT_LOG,CASELOAD,SKILLS,REFLECTION,ASSESSMENT,GENERAL','instructions'=>'nullable|string|max:2000','display_order'=>'required|integer|min:0','requires_supervisor_verification'=>'required|boolean']);
        DB::table('logbook_sections')->insert(['logbook_template_id'=>$template->id,'section_title'=>trim($v['section_title']),'section_type'=>trim($v['section_type']),'instructions'=>isset($v['instructions'])&&trim($v['instructions'])!==''?trim($v['instructions']):null,'display_order'=>(int)$v['display_order'],'requires_supervisor_verification'=>(bool)$v['requires_supervisor_verification'],'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success','Section added successfully.');
    }

    public function lecturerLogbookSectionUpdate(Request $request, $templateId, $sectionId)
    {
        $user=$this->requireLecturer(); $template=$this->lecturerTemplateForUser($user->id,(int)$templateId); $this->requireTemplateSection($template->id,(int)$sectionId);
        $v=$request->validate(['section_title'=>'required|string|max:255','section_type'=>'required|string|in:ATTENDANCE,PROCEDURE,CHECKLIST,PATIENT_LOG,CASELOAD,SKILLS,REFLECTION,ASSESSMENT,GENERAL','instructions'=>'nullable|string|max:2000','display_order'=>'required|integer|min:0','requires_supervisor_verification'=>'required|boolean']);
        DB::table('logbook_sections')->where('id',$sectionId)->update(['section_title'=>trim($v['section_title']),'section_type'=>trim($v['section_type']),'instructions'=>isset($v['instructions'])&&trim($v['instructions'])!==''?trim($v['instructions']):null,'display_order'=>(int)$v['display_order'],'requires_supervisor_verification'=>(bool)$v['requires_supervisor_verification'],'updated_at'=>now()]);
        return back()->with('success','Section updated successfully.');
    }

    public function lecturerLogbookItemStore(Request $request, $templateId, $sectionId)
    {
        $user=$this->requireLecturer(); $template=$this->lecturerTemplateForUser($user->id,(int)$templateId); $this->requireTemplateSection($template->id,(int)$sectionId);
        $v=$request->validate(['item_name'=>'required|string|max:255','item_description'=>'nullable|string|max:2000','required_level'=>'nullable|string|max:100','required_count'=>'required|integer|min:1|max:999','requires_supervisor_verification'=>'required|boolean','display_order'=>'required|integer|min:0','is_active'=>'required|boolean']);
        $itemId=DB::table('logbook_items')->insertGetId(['logbook_section_id'=>(int)$sectionId,'item_name'=>trim($v['item_name']),'item_description'=>isset($v['item_description'])&&trim($v['item_description'])!==''?trim($v['item_description']):null,'required_level'=>isset($v['required_level'])&&trim($v['required_level'])!==''?trim($v['required_level']):null,'required_count'=>(int)$v['required_count'],'requires_supervisor_verification'=>(bool)$v['requires_supervisor_verification'],'display_order'=>(int)$v['display_order'],'is_active'=>(bool)$v['is_active'],'created_at'=>now(),'updated_at'=>now()]);
        for($i=1;$i<=(int)$v['required_count'];$i++) DB::table('logbook_item_requirements')->insert(['logbook_item_id'=>$itemId,'sequence_number'=>$i,'requirement_code'=>'R'.$i,'requirement_label'=>'Attempt '.$i,'is_simulation'=>false,'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success','Clinical item added with '.$v['required_count'].' requirement slot(s).');
    }

    public function lecturerLogbookItemUpdate(Request $request, $templateId, $itemId)
    {
        $user=$this->requireLecturer(); $template=$this->lecturerTemplateForUser($user->id,(int)$templateId); $item=$this->requireTemplateItem($template->id,(int)$itemId);
        $v=$request->validate(['item_name'=>'required|string|max:255','item_description'=>'nullable|string|max:2000','required_level'=>'nullable|string|max:100','requires_supervisor_verification'=>'required|boolean','display_order'=>'required|integer|min:0','is_active'=>'required|boolean']);
        DB::table('logbook_items')->where('id',$item->id)->update(['item_name'=>trim($v['item_name']),'item_description'=>isset($v['item_description'])&&trim($v['item_description'])!==''?trim($v['item_description']):null,'required_level'=>isset($v['required_level'])&&trim($v['required_level'])!==''?trim($v['required_level']):null,'requires_supervisor_verification'=>(bool)$v['requires_supervisor_verification'],'display_order'=>(int)$v['display_order'],'is_active'=>(bool)$v['is_active'],'updated_at'=>now()]);
        return back()->with('success','Clinical item updated successfully.');
    }

    public function lecturerLogbookRequirementStore(Request $request, $templateId, $itemId)
    {
        $user=$this->requireLecturer(); $template=$this->lecturerTemplateForUser($user->id,(int)$templateId); $item=$this->requireTemplateItem($template->id,(int)$itemId);
        $v=$request->validate(['requirement_code'=>'required|string|max:100','requirement_label'=>'required|string|max:255','is_simulation'=>'required|boolean']);
        $next=(int)(DB::table('logbook_item_requirements')->where('logbook_item_id',$item->id)->max('sequence_number')??0)+1;
        DB::table('logbook_item_requirements')->insert(['logbook_item_id'=>$item->id,'sequence_number'=>$next,'requirement_code'=>trim($v['requirement_code']),'requirement_label'=>trim($v['requirement_label']),'is_simulation'=>(bool)$v['is_simulation'],'created_at'=>now(),'updated_at'=>now()]);
        DB::table('logbook_items')->where('id',$item->id)->update(['required_count'=>DB::table('logbook_item_requirements')->where('logbook_item_id',$item->id)->count(),'updated_at'=>now()]);
        return back()->with('success','Requirement added successfully.');
    }

    public function lecturerLogbookRequirementUpdate(Request $request, $templateId, $requirementId)
    {
        $user=$this->requireLecturer(); $template=$this->lecturerTemplateForUser($user->id,(int)$templateId);
        $req=DB::table('logbook_item_requirements as r')->join('logbook_items as i','r.logbook_item_id','=','i.id')->join('logbook_sections as s','i.logbook_section_id','=','s.id')->where('r.id',$requirementId)->where('s.logbook_template_id',$template->id)->select('r.*')->first();
        if(!$req) abort(404,'Requirement not found in this logbook.');
        $v=$request->validate(['requirement_code'=>'required|string|max:100','requirement_label'=>'required|string|max:255','is_simulation'=>'required|boolean']);
        DB::table('logbook_item_requirements')->where('id',$requirementId)->update(['requirement_code'=>trim($v['requirement_code']),'requirement_label'=>trim($v['requirement_label']),'is_simulation'=>(bool)$v['is_simulation'],'updated_at'=>now()]);
        return back()->with('success','Requirement updated successfully.');
    }

    private function requireLecturer()
    {
        $user=Auth::user(); if(!$user || $user->role!=='LECTURER') abort(403,'You are not authorized to access Lecturer clinical logbooks.'); return $user;
    }
    private function requireLecturerUnit(int $lecturerId,int $unitId): void
    {
        if(!DB::table('lecturer_units')->where('lecturer_id',$lecturerId)->where('unit_id',$unitId)->exists()) abort(403,'You are not assigned to this unit.');
    }
    private function lecturerTemplateForUser(int $lecturerId,int $templateId)
    {
        $template=DB::table('logbook_templates as lt')->join('units as u','lt.unit_id','=','u.id')->join('lecturer_units as lu','u.id','=','lu.unit_id')->where('lt.id',$templateId)->where('lu.lecturer_id',$lecturerId)->select('lt.*','u.unit_code','u.unit_name')->first();
        if(!$template) abort(404,'Clinical logbook not found or you are not assigned to its unit.'); return $template;
    }
    private function requireTemplateSection(int $templateId,int $sectionId)
    {
        $section=DB::table('logbook_sections')->where('id',$sectionId)->where('logbook_template_id',$templateId)->first(); if(!$section) abort(404,'Section not found in this logbook.'); return $section;
    }
    private function requireTemplateItem(int $templateId,int $itemId)
    {
        $item=DB::table('logbook_items as i')->join('logbook_sections as s','i.logbook_section_id','=','s.id')->where('i.id',$itemId)->where('s.logbook_template_id',$templateId)->select('i.*')->first(); if(!$item) abort(404,'Clinical item not found in this logbook.'); return $item;
    }

    /**
     * HOD dashboard.
     */
    public function hod()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'HOD') {
            abort(403, 'You are not authorized to access the HOD dashboard.');
        }

        $departmentId = $user->department_id;

        $department = DB::table('departments')
            ->where('id', $departmentId)
            ->first();

        if (!$department) {
            abort(404, 'Department not found.');
        }

        $totalStudents = DB::table('users')
            ->where('department_id', $departmentId)
            ->where('role', 'STUDENT')
            ->where('is_active', 1)
            ->count();

        $totalLecturers = DB::table('users')
            ->where('department_id', $departmentId)
            ->where('role', 'LECTURER')
            ->where('is_active', 1)
            ->count();

        $totalUnits = DB::table('units')
            ->where('department_id', $departmentId)
            ->where('is_active', 1)
            ->count();

        $totalEnrollments = DB::table('enrollments as e')
            ->join('units as u', 'u.id', '=', 'e.unit_id')
            ->where('u.department_id', $departmentId)
            ->count();

        $logbookQuery = DB::table('student_logbooks as sl')
            ->join('enrollments as e', 'e.id', '=', 'sl.enrollment_id')
            ->join('units as u', 'u.id', '=', 'e.unit_id')
            ->where('u.department_id', $departmentId);

        $totalLogbooks = (clone $logbookQuery)->count();
        $activeLogbooks = (clone $logbookQuery)->where('sl.status', 'ACTIVE')->count();
        $completedLogbooks = (clone $logbookQuery)->where('sl.status', 'COMPLETED')->count();
        $submittedLogbooks = (clone $logbookQuery)->where('sl.status', 'SUBMITTED')->count();
        $archivedLogbooks = (clone $logbookQuery)->where('sl.status', 'ARCHIVED')->count();

        $averageCompletion = round(
            (float) ((clone $logbookQuery)->avg('sl.completion_percentage') ?? 0),
            2
        );

        $clinicalEntryQuery = DB::table('clinical_entries as ce')
            ->join('student_logbooks as sl', 'sl.id', '=', 'ce.student_logbook_id')
            ->join('enrollments as e', 'e.id', '=', 'sl.enrollment_id')
            ->join('units as u', 'u.id', '=', 'e.unit_id')
            ->where('u.department_id', $departmentId);

        $totalClinicalEntries = (clone $clinicalEntryQuery)->count();
        $draftEntries = (clone $clinicalEntryQuery)->where('ce.status', 'DRAFT')->count();
        $pendingEntries = (clone $clinicalEntryQuery)
            ->where('ce.status', 'PENDING_VERIFICATION')
            ->count();
        $verifiedEntries = (clone $clinicalEntryQuery)->where('ce.status', 'VERIFIED')->count();
        $rejectedEntries = (clone $clinicalEntryQuery)->where('ce.status', 'REJECTED')->count();

        $yearLevels = DB::table('year_levels')
            ->orderBy('year_number')
            ->get()
            ->map(function ($yearLevel) use ($departmentId) {
                $studentCount = DB::table('users')
                    ->where('department_id', $departmentId)
                    ->where('year_level_id', $yearLevel->id)
                    ->where('role', 'STUDENT')
                    ->where('is_active', 1)
                    ->count();

                $unitCount = DB::table('units')
                    ->where('department_id', $departmentId)
                    ->where('year_level_id', $yearLevel->id)
                    ->where('is_active', 1)
                    ->count();

                $averageProgress = DB::table('student_logbooks as sl')
                    ->join('enrollments as e', 'e.id', '=', 'sl.enrollment_id')
                    ->join('units as u', 'u.id', '=', 'e.unit_id')
                    ->where('u.department_id', $departmentId)
                    ->where('u.year_level_id', $yearLevel->id)
                    ->avg('sl.completion_percentage');

                return [
                    'year_level_id' => $yearLevel->id,
                    'year_name' => $yearLevel->year_name,
                    'year_number' => $yearLevel->year_number,
                    'student_count' => $studentCount,
                    'unit_count' => $unitCount,
                    'average_completion_percentage' => round(
                        (float) ($averageProgress ?? 0),
                        2
                    ),
                ];
            })
            ->filter(function ($yearLevel) {
                return $yearLevel['student_count'] > 0
                    || $yearLevel['unit_count'] > 0;
            })
            ->values();

        return view('web.hod.dashboard', [
            'user' => $user,
            'department' => $department,
            'summary' => [
                'total_students' => $totalStudents,
                'total_lecturers' => $totalLecturers,
                'total_units' => $totalUnits,
                'total_enrollments' => $totalEnrollments,
            ],
            'logbooks' => [
                'total' => $totalLogbooks,
                'active' => $activeLogbooks,
                'completed' => $completedLogbooks,
                'submitted' => $submittedLogbooks,
                'archived' => $archivedLogbooks,
                'average_completion_percentage' => $averageCompletion,
            ],
            'clinicalEntries' => [
                'total' => $totalClinicalEntries,
                'draft' => $draftEntries,
                'pending_verification' => $pendingEntries,
                'verified' => $verifiedEntries,
                'rejected' => $rejectedEntries,
            ],
            'yearLevels' => $yearLevels,
            'access' => [
                'mode' => 'READ_ONLY',
                'can_review_verifications' => false,
                'can_enroll_students' => false,
                'can_modify_clinical_entries' => false,
            ],
        ]);
    }

    /**
     * HOD students.
     *
     * Read-only department-scoped student monitoring.
     */
    public function hodStudents(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'HOD') {
            abort(403, 'You are not authorized to access HOD students.');
        }

        $departmentId = $user->department_id;

        $department = DB::table('departments')
            ->where('id', $departmentId)
            ->first();

        if (!$department) {
            abort(404, 'Department not found.');
        }

        $search = trim((string) $request->query('search', ''));
        $yearLevelId = $request->query('year_level_id');

        $studentsQuery = DB::table('users as students')
            ->leftJoin('year_levels as yl', 'students.year_level_id', '=', 'yl.id')
            ->where('students.department_id', $departmentId)
            ->where('students.role', 'STUDENT')
            ->where('students.is_active', 1);

        if ($search !== '') {
            $studentsQuery->where(function ($query) use ($search) {
                $query->where('students.name', 'like', '%' . $search . '%')
                    ->orWhere('students.dwu_id', 'like', '%' . $search . '%')
                    ->orWhere('students.email', 'like', '%' . $search . '%');
            });
        }

        if ($yearLevelId !== null && $yearLevelId !== '' && is_numeric($yearLevelId)) {
            $studentsQuery->where('students.year_level_id', (int) $yearLevelId);
        }

        $students = $studentsQuery
            ->select(
                'students.id',
                'students.dwu_id',
                'students.name',
                'students.email',
                'students.phone',
                'students.year_level_id',
                'yl.year_name'
            )
            ->orderBy('students.name')
            ->get();

        $students = $students->map(function ($student) use ($departmentId) {
            $enrollmentIds = DB::table('enrollments as e')
                ->join('units as u', 'u.id', '=', 'e.unit_id')
                ->where('e.student_id', $student->id)
                ->where('u.department_id', $departmentId)
                ->pluck('e.id');

            $student->enrollment_count = $enrollmentIds->count();

            if ($enrollmentIds->isEmpty()) {
                $student->logbook_count = 0;
                $student->completed_logbook_count = 0;
                $student->average_completion_percentage = 0.0;
                return $student;
            }

            $logbookQuery = DB::table('student_logbooks')
                ->whereIn('enrollment_id', $enrollmentIds);

            $student->logbook_count = (clone $logbookQuery)->count();

            $student->completed_logbook_count = (clone $logbookQuery)
                ->where('status', 'COMPLETED')
                ->count();

            $student->average_completion_percentage = max(
                0,
                min(
                    100,
                    round(
                        (float) ((clone $logbookQuery)->avg('completion_percentage') ?? 0),
                        2
                    )
                )
            );

            return $student;
        });

        $yearLevels = DB::table('year_levels')
            ->whereIn(
                'id',
                DB::table('users')
                    ->where('department_id', $departmentId)
                    ->where('role', 'STUDENT')
                    ->where('is_active', 1)
                    ->whereNotNull('year_level_id')
                    ->select('year_level_id')
            )
            ->orderBy('year_number')
            ->get();

        return view('web.hod.students', [
            'user' => $user,
            'department' => $department,
            'students' => $students,
            'yearLevels' => $yearLevels,
            'search' => $search,
            'selectedYearLevelId' =>
                ($yearLevelId !== null && $yearLevelId !== '')
                    ? (int) $yearLevelId
                    : null,
            'access' => [
                'mode' => 'READ_ONLY',
                'can_review_verifications' => false,
                'can_enroll_students' => false,
                'can_modify_clinical_entries' => false,
            ],
        ]);
    }

    /**
     * HOD teaching staff.
     *
     * Read-only department-scoped lecturer monitoring.
     */
    public function hodLecturers(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'HOD') {
            abort(403, 'You are not authorized to access HOD teaching staff.');
        }

        $departmentId = $user->department_id;

        $department = DB::table('departments')
            ->where('id', $departmentId)
            ->first();

        if (!$department) {
            abort(404, 'Department not found.');
        }

        $search = trim((string) $request->query('search', ''));

        $query = DB::table('users as lecturers')
            ->where('lecturers.department_id', $departmentId)
            ->where('lecturers.role', 'LECTURER')
            ->where('lecturers.is_active', 1);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('lecturers.name', 'like', "%{$search}%")
                    ->orWhere('lecturers.dwu_id', 'like', "%{$search}%")
                    ->orWhere('lecturers.email', 'like', "%{$search}%");
            });
        }

        $lecturers = $query
            ->select(
                'lecturers.id',
                'lecturers.dwu_id',
                'lecturers.name',
                'lecturers.email',
                'lecturers.phone'
            )
            ->orderBy('lecturers.name')
            ->get()
            ->map(function ($lecturer) use ($departmentId) {
                $assignedUnits = DB::table('lecturer_units as lu')
                    ->join('units as u', 'u.id', '=', 'lu.unit_id')
                    ->leftJoin(
                        'year_levels as yl',
                        'yl.id',
                        '=',
                        'u.year_level_id'
                    )
                    ->where('lu.lecturer_id', $lecturer->id)
                    ->where('u.department_id', $departmentId)
                    ->where('u.is_active', 1)
                    ->select(
                        'u.id',
                        'u.unit_code',
                        'u.unit_name',
                        'u.requires_logbook',
                        'u.year_level_id',
                        'yl.year_name',
                        'yl.year_number'
                    )
                    ->orderBy('yl.year_number')
                    ->orderBy('u.unit_code')
                    ->get();

                $unitIds = $assignedUnits
                    ->pluck('id')
                    ->filter()
                    ->values();

                $enrolledStudents = $unitIds->isEmpty()
                    ? 0
                    : DB::table('enrollments')
                        ->whereIn('unit_id', $unitIds)
                        ->distinct('student_id')
                        ->count('student_id');

                $logbookCount = $unitIds->isEmpty()
                    ? 0
                    : DB::table('student_logbooks as sl')
                        ->join(
                            'enrollments as e',
                            'e.id',
                            '=',
                            'sl.enrollment_id'
                        )
                        ->whereIn('e.unit_id', $unitIds)
                        ->count();

                $averageCompletion = $unitIds->isEmpty()
                    ? 0
                    : DB::table('student_logbooks as sl')
                        ->join(
                            'enrollments as e',
                            'e.id',
                            '=',
                            'sl.enrollment_id'
                        )
                        ->whereIn('e.unit_id', $unitIds)
                        ->avg('sl.completion_percentage');

                $lecturer->assigned_units = $assignedUnits;
                $lecturer->assigned_unit_count = $assignedUnits->count();
                $lecturer->enrolled_student_count = $enrolledStudents;
                $lecturer->logbook_count = $logbookCount;
                $lecturer->average_completion_percentage = max(
                    0,
                    min(
                        100,
                        round((float) ($averageCompletion ?? 0), 2)
                    )
                );

                return $lecturer;
            })
            ->values();

        return view('web.hod.lecturers', [
            'user' => $user,
            'department' => $department,
            'lecturers' => $lecturers,
            'search' => $search,
            'access' => [
                'mode' => 'READ_ONLY',
                'can_assign_units' => false,
                'can_enroll_students' => false,
                'can_review_verifications' => false,
                'can_modify_lecturers' => false,
            ],
        ]);
    }

    /**
     * HOD student progress detail.
     *
     * Read-only and restricted to the HOD's department.
     */
    public function hodStudentProgressShow($studentId)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'HOD') {
            abort(403, 'You are not authorized to access HOD student progress.');
        }

        $departmentId = $user->department_id;

        $department = DB::table('departments')
            ->where('id', $departmentId)
            ->first();

        if (!$department) {
            abort(404, 'Department not found.');
        }

        $student = DB::table('users as students')
            ->leftJoin('year_levels as yl', 'students.year_level_id', '=', 'yl.id')
            ->where('students.id', $studentId)
            ->where('students.department_id', $departmentId)
            ->where('students.role', 'STUDENT')
            ->where('students.is_active', 1)
            ->select(
                'students.id',
                'students.dwu_id',
                'students.name',
                'students.email',
                'students.phone',
                'students.year_level_id',
                'yl.year_name',
                'yl.year_number'
            )
            ->first();

        if (!$student) {
            abort(404, 'Student not found in your department.');
        }

        $enrollments = DB::table('enrollments as e')
            ->join('units as u', 'u.id', '=', 'e.unit_id')
            ->leftJoin('student_logbooks as sl', 'sl.enrollment_id', '=', 'e.id')
            ->leftJoin('logbook_templates as lt', 'lt.id', '=', 'sl.logbook_template_id')
            ->where('e.student_id', $student->id)
            ->where('u.department_id', $departmentId)
            ->select(
                'e.id as enrollment_id',
                'e.status as enrollment_status',
                'e.enrollment_date',
                'u.id as unit_id',
                'u.unit_code',
                'u.unit_name',
                'u.requires_logbook',
                'sl.id as student_logbook_id',
                'sl.status as logbook_status',
                'sl.completion_percentage',
                'lt.template_name',
                'lt.minimum_completion_percentage'
            )
            ->orderBy('u.unit_code')
            ->get()
            ->map(function ($record) {
                $record->completion_percentage = max(
                    0,
                    min(100, (float) ($record->completion_percentage ?? 0))
                );

                $record->minimum_completion_percentage = (float) (
                    $record->minimum_completion_percentage ?? 100
                );

                if ($record->student_logbook_id) {
                    $record->clinical_entries = DB::table('clinical_entries')
                        ->where('student_logbook_id', $record->student_logbook_id)
                        ->count();

                    $record->verified_entries = DB::table('clinical_entries')
                        ->where('student_logbook_id', $record->student_logbook_id)
                        ->where('status', 'VERIFIED')
                        ->count();

                    $record->pending_entries = DB::table('clinical_entries')
                        ->where('student_logbook_id', $record->student_logbook_id)
                        ->where('status', 'PENDING_VERIFICATION')
                        ->count();

                    $record->attendance_records = DB::table('attendance_records')
                        ->where('student_logbook_id', $record->student_logbook_id)
                        ->count();

                    $record->verified_attendance_hours = (float) DB::table('attendance_records')
                        ->where('student_logbook_id', $record->student_logbook_id)
                        ->where('status', 'VERIFIED')
                        ->sum('total_hours');
                } else {
                    $record->clinical_entries = 0;
                    $record->verified_entries = 0;
                    $record->pending_entries = 0;
                    $record->attendance_records = 0;
                    $record->verified_attendance_hours = 0.0;
                }

                return $record;
            });

        $summary = [
            'enrollments' => $enrollments->count(),
            'logbooks' => $enrollments->whereNotNull('student_logbook_id')->count(),
            'completed_logbooks' => $enrollments->filter(function ($record) {
                return strtoupper((string) ($record->logbook_status ?? '')) === 'COMPLETED';
            })->count(),
            'average_completion_percentage' => round(
                (float) ($enrollments->whereNotNull('student_logbook_id')->avg('completion_percentage') ?? 0),
                2
            ),
            'clinical_entries' => (int) $enrollments->sum('clinical_entries'),
            'verified_entries' => (int) $enrollments->sum('verified_entries'),
            'verified_attendance_hours' => round(
                (float) $enrollments->sum('verified_attendance_hours'),
                2
            ),
        ];

        return view('web.hod.student-progress-show', [
            'user' => $user,
            'department' => $department,
            'student' => $student,
            'enrollments' => $enrollments,
            'summary' => $summary,
            'access' => [
                'mode' => 'READ_ONLY',
                'can_review_verifications' => false,
                'can_enroll_students' => false,
                'can_modify_clinical_entries' => false,
            ],
        ]);
    }

    /** Compatibility name for HOD student progress routes. */
    public function hodStudentProgress($studentId)
    {
        return $this->hodStudentProgressShow($studentId);
    }

    /**
     * HOD lecturer progress detail.
     *
     * Read-only and restricted to lecturers in the HOD's department.
     */
    public function hodLecturerProgressShow($lecturerId)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'HOD') {
            abort(403, 'You are not authorized to access HOD lecturer progress.');
        }

        $departmentId = $user->department_id;

        $department = DB::table('departments')
            ->where('id', $departmentId)
            ->first();

        if (!$department) {
            abort(404, 'Department not found.');
        }

        $lecturer = DB::table('users')
            ->where('id', $lecturerId)
            ->where('department_id', $departmentId)
            ->where('role', 'LECTURER')
            ->where('is_active', 1)
            ->select('id', 'dwu_id', 'name', 'email', 'phone')
            ->first();

        if (!$lecturer) {
            abort(404, 'Lecturer not found in your department.');
        }

        $units = DB::table('lecturer_units as lu')
            ->join('units as u', 'u.id', '=', 'lu.unit_id')
            ->leftJoin('year_levels as yl', 'yl.id', '=', 'u.year_level_id')
            ->where('lu.lecturer_id', $lecturer->id)
            ->where('u.department_id', $departmentId)
            ->where('u.is_active', 1)
            ->select(
                'u.id',
                'u.unit_code',
                'u.unit_name',
                'u.requires_logbook',
                'u.year_level_id',
                'yl.year_name',
                'yl.year_number'
            )
            ->orderBy('yl.year_number')
            ->orderBy('u.unit_code')
            ->get()
            ->map(function ($unit) {
                $unit->enrolled_student_count = DB::table('enrollments')
                    ->where('unit_id', $unit->id)
                    ->distinct()
                    ->count('student_id');

                $logbookQuery = DB::table('student_logbooks as sl')
                    ->join('enrollments as e', 'e.id', '=', 'sl.enrollment_id')
                    ->where('e.unit_id', $unit->id);

                $unit->logbook_count = (clone $logbookQuery)->count();
                $unit->completed_logbook_count = (clone $logbookQuery)
                    ->where('sl.status', 'COMPLETED')
                    ->count();
                $unit->average_completion_percentage = max(
                    0,
                    min(
                        100,
                        round((float) ((clone $logbookQuery)->avg('sl.completion_percentage') ?? 0), 2)
                    )
                );

                return $unit;
            });

        $summary = [
            'assigned_units' => $units->count(),
            'enrolled_students' => DB::table('enrollments')
                ->whereIn('unit_id', $units->pluck('id'))
                ->distinct()
                ->count('student_id'),
            'logbooks' => (int) $units->sum('logbook_count'),
            'completed_logbooks' => (int) $units->sum('completed_logbook_count'),
            'average_completion_percentage' => round(
                (float) ($units->avg('average_completion_percentage') ?? 0),
                2
            ),
        ];

        return view('web.hod.lecturer-progress-show', [
            'user' => $user,
            'department' => $department,
            'lecturer' => $lecturer,
            'units' => $units,
            'summary' => $summary,
            'access' => [
                'mode' => 'READ_ONLY',
                'can_assign_units' => false,
                'can_enroll_students' => false,
                'can_review_verifications' => false,
                'can_modify_lecturers' => false,
            ],
        ]);
    }

    /** Compatibility name for HOD lecturer progress routes. */
    public function hodLecturerProgress($lecturerId)
    {
        return $this->hodLecturerProgressShow($lecturerId);
    }

    /**
     * HOD units.
     *
     * Read-only department-scoped unit monitoring.
     */
    public function hodUnits(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'HOD') {
            abort(403, 'You are not authorized to access HOD units.');
        }

        $departmentId = $user->department_id;
        $department = DB::table('departments')->where('id', $departmentId)->first();

        if (!$department) {
            abort(404, 'Department not found.');
        }

        $search = trim((string) $request->query('search', ''));
        $yearLevelId = $request->query('year_level_id');

        $query = DB::table('units as u')
            ->leftJoin('year_levels as yl', 'yl.id', '=', 'u.year_level_id')
            ->where('u.department_id', $departmentId)
            ->where('u.is_active', 1);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('u.unit_code', 'like', '%' . $search . '%')
                    ->orWhere('u.unit_name', 'like', '%' . $search . '%');
            });
        }

        if ($yearLevelId !== null && $yearLevelId !== '' && is_numeric($yearLevelId)) {
            $query->where('u.year_level_id', (int) $yearLevelId);
        }

        $units = $query
            ->select(
                'u.id',
                'u.unit_code',
                'u.unit_name',
                'u.requires_logbook',
                'u.year_level_id',
                'yl.year_name',
                'yl.year_number'
            )
            ->orderBy('yl.year_number')
            ->orderBy('u.unit_code')
            ->get()
            ->map(function ($unit) {
                $unit->lecturer_count = DB::table('lecturer_units')
                    ->where('unit_id', $unit->id)
                    ->distinct()
                    ->count('lecturer_id');

                $unit->enrolled_student_count = DB::table('enrollments')
                    ->where('unit_id', $unit->id)
                    ->distinct()
                    ->count('student_id');

                $logbookQuery = DB::table('student_logbooks as sl')
                    ->join('enrollments as e', 'e.id', '=', 'sl.enrollment_id')
                    ->where('e.unit_id', $unit->id);

                $unit->logbook_count = (clone $logbookQuery)->count();
                $unit->completed_logbook_count = (clone $logbookQuery)
                    ->where('sl.status', 'COMPLETED')
                    ->count();
                $unit->average_completion_percentage = max(
                    0,
                    min(
                        100,
                        round((float) ((clone $logbookQuery)->avg('sl.completion_percentage') ?? 0), 2)
                    )
                );

                return $unit;
            });

        $yearLevels = DB::table('year_levels as yl')
            ->whereIn(
                'yl.id',
                DB::table('units')
                    ->where('department_id', $departmentId)
                    ->where('is_active', 1)
                    ->whereNotNull('year_level_id')
                    ->select('year_level_id')
            )
            ->orderBy('yl.year_number')
            ->get();

        return view('web.hod.units', [
            'user' => $user,
            'department' => $department,
            'units' => $units,
            'yearLevels' => $yearLevels,
            'search' => $search,
            'selectedYearLevelId' =>
                ($yearLevelId !== null && $yearLevelId !== '') ? (int) $yearLevelId : null,
            'access' => [
                'mode' => 'READ_ONLY',
                'can_modify_units' => false,
                'can_assign_units' => false,
                'can_enroll_students' => false,
            ],
        ]);
    }

    /**
     * HOD unit progress detail.
     */
    public function hodUnitProgressShow($unitId)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'HOD') {
            abort(403, 'You are not authorized to access HOD unit progress.');
        }

        $departmentId = $user->department_id;
        $department = DB::table('departments')->where('id', $departmentId)->first();

        if (!$department) {
            abort(404, 'Department not found.');
        }

        $unit = DB::table('units as u')
            ->leftJoin('year_levels as yl', 'yl.id', '=', 'u.year_level_id')
            ->where('u.id', $unitId)
            ->where('u.department_id', $departmentId)
            ->where('u.is_active', 1)
            ->select(
                'u.id',
                'u.unit_code',
                'u.unit_name',
                'u.requires_logbook',
                'u.year_level_id',
                'yl.year_name',
                'yl.year_number'
            )
            ->first();

        if (!$unit) {
            abort(404, 'Unit not found in your department.');
        }

        $lecturers = DB::table('lecturer_units as lu')
            ->join('users as lecturers', 'lecturers.id', '=', 'lu.lecturer_id')
            ->where('lu.unit_id', $unit->id)
            ->where('lecturers.department_id', $departmentId)
            ->where('lecturers.role', 'LECTURER')
            ->where('lecturers.is_active', 1)
            ->select('lecturers.id', 'lecturers.dwu_id', 'lecturers.name', 'lecturers.email')
            ->orderBy('lecturers.name')
            ->get();

        $students = DB::table('enrollments as e')
            ->join('users as students', 'students.id', '=', 'e.student_id')
            ->leftJoin('student_logbooks as sl', 'sl.enrollment_id', '=', 'e.id')
            ->where('e.unit_id', $unit->id)
            ->where('students.department_id', $departmentId)
            ->where('students.role', 'STUDENT')
            ->select(
                'students.id as student_id',
                'students.dwu_id',
                'students.name',
                'students.email',
                'e.id as enrollment_id',
                'e.status as enrollment_status',
                'sl.id as student_logbook_id',
                'sl.status as logbook_status',
                'sl.completion_percentage'
            )
            ->orderBy('students.name')
            ->get()
            ->map(function ($student) {
                $student->completion_percentage = max(
                    0,
                    min(100, (float) ($student->completion_percentage ?? 0))
                );
                return $student;
            });

        $summary = [
            'lecturers' => $lecturers->count(),
            'students' => $students->pluck('student_id')->unique()->count(),
            'logbooks' => $students->whereNotNull('student_logbook_id')->count(),
            'completed_logbooks' => $students->filter(function ($student) {
                return strtoupper((string) ($student->logbook_status ?? '')) === 'COMPLETED';
            })->count(),
            'average_completion_percentage' => round(
                (float) ($students->whereNotNull('student_logbook_id')->avg('completion_percentage') ?? 0),
                2
            ),
        ];

        return view('web.hod.unit-progress-show', [
            'user' => $user,
            'department' => $department,
            'unit' => $unit,
            'lecturers' => $lecturers,
            'students' => $students,
            'summary' => $summary,
            'access' => [
                'mode' => 'READ_ONLY',
                'can_modify_units' => false,
                'can_assign_units' => false,
                'can_enroll_students' => false,
            ],
        ]);
    }

    /** Compatibility name for HOD unit progress routes. */
    public function hodUnitProgress($unitId)
    {
        return $this->hodUnitProgressShow($unitId);
    }

    /**
     * HOD year-level monitoring.
     */
    public function hodYearLevels()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'HOD') {
            abort(403, 'You are not authorized to access HOD year levels.');
        }

        $departmentId = $user->department_id;
        $department = DB::table('departments')->where('id', $departmentId)->first();

        if (!$department) {
            abort(404, 'Department not found.');
        }

        $yearLevels = DB::table('year_levels')
            ->orderBy('year_number')
            ->get()
            ->map(function ($yearLevel) use ($departmentId) {
                $studentIds = DB::table('users')
                    ->where('department_id', $departmentId)
                    ->where('year_level_id', $yearLevel->id)
                    ->where('role', 'STUDENT')
                    ->where('is_active', 1)
                    ->pluck('id');

                $unitIds = DB::table('units')
                    ->where('department_id', $departmentId)
                    ->where('year_level_id', $yearLevel->id)
                    ->where('is_active', 1)
                    ->pluck('id');

                $enrollmentQuery = DB::table('enrollments')
                    ->whereIn('unit_id', $unitIds);

                $logbookQuery = DB::table('student_logbooks as sl')
                    ->join('enrollments as e', 'e.id', '=', 'sl.enrollment_id')
                    ->whereIn('e.unit_id', $unitIds);

                return (object) [
                    'id' => $yearLevel->id,
                    'year_name' => $yearLevel->year_name,
                    'year_number' => $yearLevel->year_number,
                    'student_count' => $studentIds->count(),
                    'unit_count' => $unitIds->count(),
                    'enrollment_count' => (clone $enrollmentQuery)->count(),
                    'logbook_count' => (clone $logbookQuery)->count(),
                    'completed_logbook_count' => (clone $logbookQuery)
                        ->where('sl.status', 'COMPLETED')
                        ->count(),
                    'average_completion_percentage' => max(
                        0,
                        min(
                            100,
                            round(
                                (float) ((clone $logbookQuery)->avg('sl.completion_percentage') ?? 0),
                                2
                            )
                        )
                    ),
                ];
            })
            ->filter(function ($yearLevel) {
                return $yearLevel->student_count > 0 || $yearLevel->unit_count > 0;
            })
            ->values();

        return view('web.hod.year-levels', [
            'user' => $user,
            'department' => $department,
            'yearLevels' => $yearLevels,
            'access' => [
                'mode' => 'READ_ONLY',
                'can_modify_year_levels' => false,
                'can_modify_students' => false,
                'can_modify_units' => false,
            ],
        ]);
    }

    /**
     * ICT Administrator dashboard.
     */
    public function admin()
    {
        $user = $this->requireAdmin();

        $summary = [
            'users' => DB::table('users')->count(),
            'active_users' => DB::table('users')->where('is_active', 1)->count(),
            'inactive_users' => DB::table('users')->where('is_active', 0)->count(),
            'students' => DB::table('users')->where('role', 'STUDENT')->count(),
            'lecturers' => DB::table('users')->where('role', 'LECTURER')->count(),
            'hods' => DB::table('users')->where('role', 'HOD')->count(),
            'ict_admins' => DB::table('users')->where('role', 'ICT_ADMIN')->count(),
            'departments' => DB::table('departments')->count(),
            'active_departments' => DB::table('departments')->where('is_active', 1)->count(),
            'units' => DB::table('units')->count(),
            'active_units' => DB::table('units')->where('is_active', 1)->count(),
            'logbook_units' => DB::table('units')->where('requires_logbook', 1)->count(),
            'enrollments' => DB::table('enrollments')->count(),
            'logbooks' => DB::table('student_logbooks')->count(),
            'clinical_entries' => DB::table('clinical_entries')->count(),
        ];

        return view('web.admin.dashboard', compact('user', 'summary'));
    }

    /** Read-only enrollment monitoring for ICT Admin. */
    public function adminEnrollments(\Illuminate\Http\Request $request)
    {
        $user = $this->requireAdmin();
        $search = trim((string) $request->query('search', ''));
        $unitId = $request->query('unit_id');
        $query = \Illuminate\Support\Facades\DB::table('enrollments as e')
            ->join('users as student', 'student.id', '=', 'e.student_id')
            ->join('units as unit', 'unit.id', '=', 'e.unit_id')
            ->leftJoin('users as lecturer', 'lecturer.id', '=', 'e.enrolled_by')
            ->select(
                'e.id', 'e.student_id', 'e.unit_id', 'e.status',
                'e.enrollment_date', 'student.name as student_name',
                'student.dwu_id', 'student.department_id as student_department_id',
                'unit.unit_code', 'unit.unit_name', 'unit.department_id as unit_department_id',
                'lecturer.name as enrolled_by_name'
            );
        if ($unitId !== null && $unitId !== '') {
            $query->where('e.unit_id', (int) $unitId);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('student.name', 'like', '%' . $search . '%')
                  ->orWhere('student.dwu_id', 'like', '%' . $search . '%')
                  ->orWhere('unit.unit_code', 'like', '%' . $search . '%');
            });
        }
        $enrollments = $query->orderByDesc('e.id')->paginate(30)->withQueryString();
        $units = \Illuminate\Support\Facades\DB::table('units')
            ->orderBy('unit_code')->get(['id','unit_code','unit_name']);
        $duplicatePairs = \Illuminate\Support\Facades\DB::table('enrollments')
            ->select('student_id', 'unit_id')
            ->groupBy('student_id','unit_id')
            ->havingRaw('COUNT(*) > 1')->get()
            ->mapWithKeys(fn ($e) => [$e->student_id . ':' . $e->unit_id => true]);
        return view('web.admin.enrollments', compact(
            'user', 'enrollments', 'units', 'search', 'unitId', 'duplicatePairs'
        ));
    }

    /** ICT Admin user management. */
    public function adminUsers(Request $request)
    {
        $user = $this->requireAdmin();
        $search = trim((string) $request->query('search', ''));
        $role = strtoupper(trim((string) $request->query('role', '')));
        $departmentId = $request->query('department_id');
        $status = strtolower(trim((string) $request->query('status', '')));
        $allowedRoles = ['STUDENT', 'LECTURER', 'HOD', 'ICT_ADMIN'];

        $query = DB::table('users as u')
            ->leftJoin('departments as d', 'u.department_id', '=', 'd.id')
            ->leftJoin('year_levels as yl', 'u.year_level_id', '=', 'yl.id')
            ->select('u.*', 'd.department_code', 'd.department_name', 'yl.year_name', 'yl.year_number');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('u.name', 'like', "%{$search}%")
                    ->orWhere('u.email', 'like', "%{$search}%")
                    ->orWhere('u.dwu_id', 'like', "%{$search}%");
            });
        }
        if (in_array($role, $allowedRoles, true)) $query->where('u.role', $role);
        if ($departmentId !== null && $departmentId !== '') $query->where('u.department_id', (int) $departmentId);
        if ($status === 'active') $query->where('u.is_active', 1);
        if ($status === 'inactive') $query->where('u.is_active', 0);

        $users = $query->orderBy('u.role')->orderBy('u.name')->get();
        $departments = DB::table('departments')->orderBy('department_name')->get();

        return view('web.admin.users', compact(
            'user', 'users', 'departments', 'allowedRoles', 'search', 'role', 'departmentId', 'status'
        ));
    }

    public function adminUserCreate()
    {
        $user = $this->requireAdmin();
        $departments = DB::table('departments')->where('is_active', 1)->orderBy('department_name')->get();
        $yearLevels = DB::table('year_levels')->orderBy('year_number')->get();
        return view('web.admin.user-create', compact('user', 'departments', 'yearLevels'));
    }

    public function adminUserStore(Request $request)
    {
        $this->requireAdmin();
        $validated = $request->validate([
            'dwu_id' => 'required|string|max:50|unique:users,dwu_id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'role' => 'required|string|in:STUDENT,LECTURER,HOD,ICT_ADMIN',
            'department_id' => 'nullable|integer|exists:departments,id',
            'year_level_id' => 'nullable|integer|exists:year_levels,id',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'required|boolean',
        ]);
        $this->validateAdminUserRoleFields($validated);
        $this->cleanAdminUserRoleFields($validated);

        DB::table('users')->insert([
            'dwu_id' => strtoupper(trim($validated['dwu_id'])),
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'phone' => isset($validated['phone']) && trim($validated['phone']) !== '' ? trim($validated['phone']) : null,
            'role' => $validated['role'],
            'department_id' => $validated['department_id'] ?? null,
            'year_level_id' => $validated['year_level_id'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => (bool) $validated['is_active'],
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return redirect()->route('web.admin.users')->with('success', 'SmartLog user account created successfully.');
    }

    public function adminUserEdit($userId)
    {
        $user = $this->requireAdmin();
        $managedUser = DB::table('users')->where('id', $userId)->first();
        if (!$managedUser) abort(404, 'SmartLog user account not found.');
        $departments = DB::table('departments')->orderBy('department_name')->get();
        $yearLevels = DB::table('year_levels')->orderBy('year_number')->get();
        return view('web.admin.user-edit', compact('user', 'managedUser', 'departments', 'yearLevels'));
    }

    public function adminUserUpdate(Request $request, $userId)
    {
        $admin = $this->requireAdmin();
        $existing = DB::table('users')->where('id', $userId)->first();
        if (!$existing) abort(404, 'SmartLog user account not found.');

        $validated = $request->validate([
            'dwu_id' => 'required|string|max:50|unique:users,dwu_id,' . $userId,
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $userId,
            'phone' => 'nullable|string|max:50',
            'role' => 'required|string|in:STUDENT,LECTURER,HOD,ICT_ADMIN',
            'department_id' => 'nullable|integer|exists:departments,id',
            'year_level_id' => 'nullable|integer|exists:year_levels,id',
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'required|boolean',
        ]);
        $this->validateAdminUserRoleFields($validated);

        if ((int) $admin->id === (int) $userId && !(bool) $validated['is_active']) {
            return back()->withInput()->withErrors(['is_active' => 'You cannot deactivate your own ICT Admin account.']);
        }
        if ((int) $admin->id === (int) $userId && $validated['role'] !== 'ICT_ADMIN') {
            return back()->withInput()->withErrors(['role' => 'You cannot change your own ICT Admin role.']);
        }
        $this->cleanAdminUserRoleFields($validated);

        $data = [
            'dwu_id' => strtoupper(trim($validated['dwu_id'])),
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'phone' => isset($validated['phone']) && trim($validated['phone']) !== '' ? trim($validated['phone']) : null,
            'role' => $validated['role'],
            'department_id' => $validated['department_id'] ?? null,
            'year_level_id' => $validated['year_level_id'] ?? null,
            'is_active' => (bool) $validated['is_active'],
            'updated_at' => now(),
        ];
        if (!empty($validated['password'])) $data['password'] = Hash::make($validated['password']);
        DB::table('users')->where('id', $userId)->update($data);

        return redirect()->route('web.admin.users')->with('success', 'SmartLog user account updated successfully.');
    }

    public function adminDepartments(Request $request)
    {
        $user = $this->requireAdmin();
        $search = trim((string) $request->query('search', ''));
        $status = strtolower(trim((string) $request->query('status', '')));
        $query = DB::table('departments');
        if ($search !== '') $query->where(fn($q) => $q->where('department_code','like',"%{$search}%")->orWhere('department_name','like',"%{$search}%"));
        if ($status === 'active') $query->where('is_active',1);
        if ($status === 'inactive') $query->where('is_active',0);
        $departments = $query->orderBy('department_name')->get()->map(function($d){
            $d->students = DB::table('users')->where('department_id',$d->id)->where('role','STUDENT')->count();
            $d->lecturers = DB::table('users')->where('department_id',$d->id)->where('role','LECTURER')->count();
            $d->hods = DB::table('users')->where('department_id',$d->id)->where('role','HOD')->count();
            $d->units = DB::table('units')->where('department_id',$d->id)->count();
            return $d;
        });
        $summary = ['total'=>DB::table('departments')->count(),'active'=>DB::table('departments')->where('is_active',1)->count(),'inactive'=>DB::table('departments')->where('is_active',0)->count()];
        return view('web.admin.departments', compact('user','departments','summary','search','status'));
    }

    public function adminDepartmentStore(Request $request)
    {
        $this->requireAdmin();
        $v=$request->validate(['department_code'=>'required|string|max:50|unique:departments,department_code','department_name'=>'required|string|max:255|unique:departments,department_name','is_active'=>'required|boolean']);
        DB::table('departments')->insert(['department_code'=>strtoupper(trim($v['department_code'])),'department_name'=>trim($v['department_name']),'is_active'=>(bool)$v['is_active'],'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success','SmartLog department created successfully.');
    }

    public function adminDepartmentUpdate(Request $request, $departmentId)
    {
        $this->requireAdmin();
        $department=DB::table('departments')->where('id',$departmentId)->first();
        if(!$department) abort(404,'SmartLog department not found.');
        $v=$request->validate(['department_code'=>'required|string|max:50|unique:departments,department_code,'.$departmentId,'department_name'=>'required|string|max:255|unique:departments,department_name,'.$departmentId,'is_active'=>'required|boolean']);
        if(!(bool)$v['is_active']){
            if(DB::table('users')->where('department_id',$departmentId)->where('is_active',1)->exists()) return back()->withInput()->withErrors(['is_active'=>'Deactivate or move the department users first.']);
            if(DB::table('units')->where('department_id',$departmentId)->where('is_active',1)->exists()) return back()->withInput()->withErrors(['is_active'=>'Deactivate the department units first.']);
        }
        DB::table('departments')->where('id',$departmentId)->update(['department_code'=>strtoupper(trim($v['department_code'])),'department_name'=>trim($v['department_name']),'is_active'=>(bool)$v['is_active'],'updated_at'=>now()]);
        return back()->with('success','SmartLog department updated successfully.');
    }

    public function adminUnits(Request $request)
    {
        $user=$this->requireAdmin();
        $search=trim((string)$request->query('search','')); $departmentId=$request->query('department_id'); $yearLevelId=$request->query('year_level_id'); $semesterId=$request->query('semester_id'); $status=strtolower(trim((string)$request->query('status',''))); $requiresLogbook=strtolower(trim((string)$request->query('requires_logbook','')));
        $q=DB::table('units as u')->join('departments as d','u.department_id','=','d.id')->join('year_levels as yl','u.year_level_id','=','yl.id')->join('semesters as s','u.semester_id','=','s.id')->select('u.*','d.department_code','d.department_name','yl.year_name','yl.year_number','s.semester_name','s.semester_number');
        if($search!=='') $q->where(fn($x)=>$x->where('u.unit_code','like',"%{$search}%")->orWhere('u.unit_name','like',"%{$search}%"));
        if($departmentId!==' ' && $departmentId!==null && $departmentId!=='') $q->where('u.department_id',(int)$departmentId);
        if($yearLevelId!==null && $yearLevelId!=='') $q->where('u.year_level_id',(int)$yearLevelId);
        if($semesterId!==null && $semesterId!=='') $q->where('u.semester_id',(int)$semesterId);
        if($status==='active') $q->where('u.is_active',1); if($status==='inactive') $q->where('u.is_active',0);
        if(in_array($requiresLogbook,['yes','true','1'],true)) $q->where('u.requires_logbook',1); if(in_array($requiresLogbook,['no','false','0'],true)) $q->where('u.requires_logbook',0);
        $units=$q->orderBy('d.department_name')->orderBy('yl.year_number')->orderBy('s.semester_number')->orderBy('u.unit_code')->get()->map(function($u){$u->enrollments=DB::table('enrollments')->where('unit_id',$u->id)->count();$u->logbooks=DB::table('student_logbooks as sl')->join('enrollments as e','sl.enrollment_id','=','e.id')->where('e.unit_id',$u->id)->count();return $u;});
        $departments=DB::table('departments')->orderBy('department_name')->get(); $yearLevels=DB::table('year_levels')->orderBy('year_number')->get(); $semesters=DB::table('semesters')->orderBy('semester_number')->get();
        $summary=['total'=>DB::table('units')->count(),'active'=>DB::table('units')->where('is_active',1)->count(),'inactive'=>DB::table('units')->where('is_active',0)->count(),'logbook'=>DB::table('units')->where('requires_logbook',1)->count()];
        $lecturers = DB::table('users')
    ->where('role', 'LECTURER')
    ->where('is_active', 1)
    ->orderBy('name')
    ->get(['id', 'name', 'department_id']);

$unitAssignments = DB::table('lecturer_units as lu')
    ->join('users as u', 'u.id', '=', 'lu.lecturer_id')
    ->get(['lu.unit_id', 'u.id as lecturer_id', 'u.name'])
    ->groupBy('unit_id');
return view('web.admin.units', compact('user','units','departments','yearLevels','semesters','summary','search','departmentId','yearLevelId','semesterId','status','requiresLogbook', 'lecturers', 'unitAssignments'));
    }

    
public function adminUnitAssignLecturer(Request $request)
{
    $this->requireAdmin();

    $data = $request->validate([
        'lecturer_id' => 'required|integer|exists:users,id',
        'unit_id' => 'required|integer|exists:units,id',
    ]);

    $lecturer = DB::table('users')
        ->where('id', $data['lecturer_id'])
        ->where('role', 'LECTURER')
        ->where('is_active', 1)
        ->first();

    $unit = DB::table('units')
        ->where('id', $data['unit_id'])
        ->where('is_active', 1)
        ->first();

    if (!$lecturer || !$unit ||
        $lecturer->department_id != $unit->department_id) {
        return back()->withErrors([
            'assignment' => 'Select an active lecturer from the same department as the active unit.',
        ]);
    }

    $exists = DB::table('lecturer_units')
        ->where('lecturer_id', $lecturer->id)
        ->where('unit_id', $unit->id)
        ->exists();

    if ($exists) {
        return back()->withErrors([
            'assignment' => 'This lecturer is already assigned to this unit.',
        ]);
    }

    DB::table('lecturer_units')->insert([
        'lecturer_id' => $lecturer->id,
        'unit_id' => $unit->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return back()->with('success', 'Lecturer assigned successfully.');
}
public function adminUnitUnassignLecturer(Request $request)
{
    $this->requireAdmin();
    $data = $request->validate([
        'lecturer_id' => 'required|integer|exists:users,id',
        'unit_id' => 'required|integer|exists:units,id',
    ]);
    $deleted = DB::table('lecturer_units')
        ->where('lecturer_id', $data['lecturer_id'])
        ->where('unit_id', $data['unit_id'])
        ->delete();
    if (!$deleted) {
        return back()->withErrors(['assignment' => 'Assignment not found.']);
    }
    return back()->with('success', 'Lecturer unassigned. Existing student records were not deleted.');
}

public function adminUnitStore(Request $request)
    {
        $this->requireAdmin();
        $v=$request->validate(['unit_code'=>'required|string|max:50|unique:units,unit_code','unit_name'=>'required|string|max:255','department_id'=>'required|integer|exists:departments,id','year_level_id'=>'required|integer|exists:year_levels,id','semester_id'=>'required|integer|exists:semesters,id','requires_logbook'=>'required|boolean','is_active'=>'required|boolean']);
        $department=DB::table('departments')->where('id',$v['department_id'])->first();
        if(!$department || !(bool)$department->is_active) return back()->withInput()->withErrors(['department_id'=>'Select an active department.']);
        DB::table('units')->insert(['unit_code'=>strtoupper(trim($v['unit_code'])),'unit_name'=>trim($v['unit_name']),'department_id'=>$v['department_id'],'year_level_id'=>$v['year_level_id'],'semester_id'=>$v['semester_id'],'requires_logbook'=>(bool)$v['requires_logbook'],'is_active'=>(bool)$v['is_active'],'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success','SmartLog unit created successfully.');
    }

    public function adminUnitUpdate(Request $request, $unitId)
    {
        $this->requireAdmin();
        if(!DB::table('units')->where('id',$unitId)->exists()) abort(404,'SmartLog unit not found.');
        $v=$request->validate(['unit_code'=>'required|string|max:50|unique:units,unit_code,'.$unitId,'unit_name'=>'required|string|max:255','department_id'=>'required|integer|exists:departments,id','year_level_id'=>'required|integer|exists:year_levels,id','semester_id'=>'required|integer|exists:semesters,id','requires_logbook'=>'required|boolean','is_active'=>'required|boolean']);
        $department=DB::table('departments')->where('id',$v['department_id'])->first();
        if(!$department || !(bool)$department->is_active) return back()->withInput()->withErrors(['department_id'=>'Select an active department.']);
        if(!(bool)$v['is_active'] && DB::table('enrollments')->where('unit_id',$unitId)->exists()) return back()->withInput()->withErrors(['is_active'=>'Remove the students from the unit before deactivating it.']);
        DB::table('units')->where('id',$unitId)->update(['unit_code'=>strtoupper(trim($v['unit_code'])),'unit_name'=>trim($v['unit_name']),'department_id'=>$v['department_id'],'year_level_id'=>$v['year_level_id'],'semester_id'=>$v['semester_id'],'requires_logbook'=>(bool)$v['requires_logbook'],'is_active'=>(bool)$v['is_active'],'updated_at'=>now()]);
        return back()->with('success','SmartLog unit updated successfully.');
    }

    public function adminSystemOverview()
    {
        $user=$this->requireAdmin();
        $overview=[
            'users'=>DB::table('users')->count(),'active_users'=>DB::table('users')->where('is_active',1)->count(),'departments'=>DB::table('departments')->count(),'active_departments'=>DB::table('departments')->where('is_active',1)->count(),'units'=>DB::table('units')->count(),'active_units'=>DB::table('units')->where('is_active',1)->count(),'enrollments'=>DB::table('enrollments')->count(),'logbooks'=>DB::table('student_logbooks')->count(),'active_logbooks'=>DB::table('student_logbooks')->where('status','ACTIVE')->count(),'completed_logbooks'=>DB::table('student_logbooks')->where('status','COMPLETED')->count(),'submitted_logbooks'=>DB::table('student_logbooks')->where('status','SUBMITTED')->count(),'archived_logbooks'=>DB::table('student_logbooks')->where('status','ARCHIVED')->count(),'average_completion'=>round((float)(DB::table('student_logbooks')->avg('completion_percentage')??0),2),'clinical_entries'=>DB::table('clinical_entries')->count(),'draft_entries'=>DB::table('clinical_entries')->where('status','DRAFT')->count(),'pending_entries'=>DB::table('clinical_entries')->where('status','PENDING_VERIFICATION')->count(),'verified_entries'=>DB::table('clinical_entries')->where('status','VERIFIED')->count(),'rejected_entries'=>DB::table('clinical_entries')->where('status','REJECTED')->count(),
        ];
        $departments=DB::table('departments')->orderBy('department_name')->get()->map(function($d){$d->students=DB::table('users')->where('department_id',$d->id)->where('role','STUDENT')->where('is_active',1)->count();$d->lecturers=DB::table('users')->where('department_id',$d->id)->where('role','LECTURER')->where('is_active',1)->count();$d->hods=DB::table('users')->where('department_id',$d->id)->where('role','HOD')->where('is_active',1)->count();$d->units=DB::table('units')->where('department_id',$d->id)->where('is_active',1)->count();return $d;});
        return view('web.admin.system-overview',compact('user','overview','departments'));
    }

    private function requireAdmin()
    {
        $user=Auth::user();
        if(!$user || $user->role!=='ICT_ADMIN') abort(403,'You are not authorized to access the ICT Admin portal.');
        return $user;
    }

    private function validateAdminUserRoleFields(array $validated): void
    {
        $role=$validated['role'];
        if(in_array($role,['STUDENT','LECTURER','HOD'],true) && empty($validated['department_id'])) throw \Illuminate\Validation\ValidationException::withMessages(['department_id' => 'Please select a department.']);
        if($role==='STUDENT' && empty($validated['year_level_id'])) throw \Illuminate\Validation\ValidationException::withMessages(['year_level_id' => 'Please select a year level.']);
    }

    private function cleanAdminUserRoleFields(array &$validated): void
    {
        if($validated['role']==='ICT_ADMIN'){ $validated['department_id']=null; $validated['year_level_id']=null; }
        elseif(in_array($validated['role'],['LECTURER','HOD'],true)){ $validated['year_level_id']=null; }
    }

    /**
     * Convert a stored verification evidence path into a browser URL.
     */
    private function verificationEvidenceUrl(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $path = trim($path);

        if (
            str_starts_with($path, 'http://') ||
            str_starts_with($path, 'https://')
        ) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return asset('storage/' . $path);
    }

    /**
     * Recalculate clinical logbook requirement completion.
     *
     * This follows the same calculation currently used by the
     * working Lecturer mobile/API workflow.
     */
    private function recalculateLogbookCompletion(
        int $studentLogbookId
    ): float {
        $studentLogbook =
            DB::table('student_logbooks')
                ->where('id', $studentLogbookId)
                ->first();

        if (!$studentLogbook) {
            return 0;
        }

        $template =
            DB::table('logbook_templates')
                ->where(
                    'id',
                    $studentLogbook->logbook_template_id
                )
                ->first();

        $minimumCompletionPercentage =
            (float) (
                $template->minimum_completion_percentage
                ?? 100
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
                ->where(
                    'logbook_items.is_active',
                    true
                )
                ->count(
                    'logbook_item_requirements.id'
                );

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
                ->where(
                    'clinical_entries.status',
                    'VERIFIED'
                )
                ->whereNotNull(
                    'clinical_entries.logbook_item_requirement_id'
                )
                ->where(
                    'logbook_sections.logbook_template_id',
                    $studentLogbook->logbook_template_id
                )
                ->where(
                    'logbook_items.is_active',
                    true
                )
                ->distinct()
                ->count(
                    'clinical_entries.logbook_item_requirement_id'
                );

        $completionPercentage =
            round(
                (
                    $completedRequirements /
                    $totalRequirements
                ) * 100,
                2
            );

        $completionPercentage =
            max(
                0,
                min(100, $completionPercentage)
            );

        $newStatus =
            $completionPercentage >=
            $minimumCompletionPercentage
                ? 'COMPLETED'
                : 'ACTIVE';

        DB::table('student_logbooks')
            ->where('id', $studentLogbookId)
            ->update([
                'completion_percentage' =>
                    $completionPercentage,

                'status' =>
                    $newStatus,

                'updated_at' =>
                    now(),
            ]);

        return (float) $completionPercentage;
    }

    /**
     * ICT Admin - SmartLog Appearance Manager.
     *
     * Hero/banner photographs are editable.
     * Logos, navigation, icons and structural UI remain fixed.
     */
    public function adminAppearance()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'ICT_ADMIN') {
            abort(403, 'You are not authorized to access SmartLog appearance settings.');
        }

        $slots = [
            'SMARTLOG_LOGO' => [
                'title' => 'SmartLog Official Logo',
                'description' => 'Official SmartLog logo used across the web portal and mobile application.',
                'filename' => 'smartlog-logo.png',
                'default' => '/images/smartlog/smartlog-logo.png',
                'display_mode' => 'contain',
            ],
            'PUBLIC_HERO' => [
                'title' => 'Public Landing Page',
                'description' => 'Main clinical photograph shown on the public SmartLog landing page.',
                'filename' => 'public-hero.jpg',
                'default' => '/images/smartlog/clinical-team.jpg',
            ],

            'LECTURER_HERO' => [
                'title' => 'Lecturer Dashboard',
                'description' => 'Hero/banner photograph shown on the Lecturer portal.',
                'filename' => 'lecturer-hero.jpg',
                'default' => '/images/smartlog/clinical-team.jpg',
            ],

            'HOD_HERO' => [
                'title' => 'HOD Dashboard',
                'description' => 'Hero/banner photograph shown on the HOD portal.',
                'filename' => 'hod-hero.jpg',
                'default' => '/images/smartlog/campus.jpg',
            ],

            'ADMIN_HERO' => [
                'title' => 'ICT Admin Dashboard',
                'description' => 'Hero/banner photograph shown on the ICT Administration portal.',
                'filename' => 'admin-hero.jpg',
                'default' => '/images/smartlog/campus.jpg',
            ],
        ];

        foreach ($slots as $key => &$slot) {

            $customPath =
                public_path(
                    'images/smartlog/custom/' .
                    $slot['filename']
                );

            $slot['custom_exists'] =
                file_exists($customPath);

            $slot['url'] =
                $slot['custom_exists']
                    ? asset(
                        'images/smartlog/custom/' .
                        $slot['filename']
                    ) . '?v=' . filemtime($customPath)
                    : asset(
                        ltrim($slot['default'], '/')
                    );
        }

        unset($slot);

        return view(
            'web.admin.appearance',
            compact('slots', 'user')
        );
    }


    /**
     * ICT Admin - replace one SmartLog hero photograph.
     */
    public function adminAppearanceUpload(
        Request $request,
        string $slot
    ) {
        $user = Auth::user();

        if (!$user || $user->role !== 'ICT_ADMIN') {
            abort(403, 'You are not authorized to change SmartLog appearance settings.');
        }

        $allowedSlots = [
            'SMARTLOG_LOGO' => 'smartlog-logo.png',
            'PUBLIC_HERO'   => 'public-hero.jpg',
            'LECTURER_HERO' => 'lecturer-hero.jpg',
            'HOD_HERO'      => 'hod-hero.jpg',
            'ADMIN_HERO'    => 'admin-hero.jpg',
        ];

        if (!array_key_exists($slot, $allowedSlots)) {
            abort(404);
        }

        $request->validate([
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:8192',
            ],
        ]);

        $directory =
            public_path('images/smartlog/custom');

        if (!is_dir($directory)) {
            mkdir(
                $directory,
                0755,
                true
            );
        }

        $uploadedFile =
            $request->file('image');

        /*
         * Keep a predictable JPG slot filename.
         *
         * For JPG/JPEG uploads we can move directly.
         * PNG/WebP are stored with their original extension
         * and a small slot pointer file is not needed for this
         * deployment version, so we normalise the destination
         * extension according to the uploaded file.
         */

        $extension =
            strtolower(
                $uploadedFile->getClientOriginalExtension()
            );

        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        $baseNames = [
            'SMARTLOG_LOGO' => 'smartlog-logo',
            'PUBLIC_HERO'   => 'public-hero',
            'LECTURER_HERO' => 'lecturer-hero',
            'HOD_HERO'      => 'hod-hero',
            'ADMIN_HERO'    => 'admin-hero',
        ];

        $baseName =
            $baseNames[$slot];

        /*
         * Remove the previous version of this slot,
         * regardless of its image extension.
         */
        foreach (
            ['jpg', 'jpeg', 'png', 'webp']
            as $oldExtension
        ) {
            $oldFile =
                $directory .
                DIRECTORY_SEPARATOR .
                $baseName .
                '.' .
                $oldExtension;

            if (file_exists($oldFile)) {
                @unlink($oldFile);
            }
        }

        $filename =
            $baseName .
            '.' .
            $extension;

        $uploadedFile->move(
            $directory,
            $filename
        );

        /*
         * Save the actual extension for this slot.
         */
        file_put_contents(
            $directory .
            DIRECTORY_SEPARATOR .
            $baseName .
            '.txt',
            $filename
        );

        return redirect()
            ->route('web.admin.appearance')
            ->with(
                'success',
                'SmartLog image updated successfully.'
            );
    }


    /**
     * ICT Admin - reset a hero image to the built-in default.
     */
    public function adminAppearanceReset(
        string $slot
    ) {
        $user = Auth::user();

        if (!$user || $user->role !== 'ICT_ADMIN') {
            abort(403, 'You are not authorized to change SmartLog appearance settings.');
        }

        $baseNames = [
            'SMARTLOG_LOGO' => 'smartlog-logo',
            'PUBLIC_HERO'   => 'public-hero',
            'LECTURER_HERO' => 'lecturer-hero',
            'HOD_HERO'      => 'hod-hero',
            'ADMIN_HERO'    => 'admin-hero',
        ];

        if (!array_key_exists($slot, $baseNames)) {
            abort(404);
        }

        $directory =
            public_path('images/smartlog/custom');

        $baseName =
            $baseNames[$slot];

        foreach (
            ['jpg', 'jpeg', 'png', 'webp', 'txt']
            as $extension
        ) {
            $file =
                $directory .
                DIRECTORY_SEPARATOR .
                $baseName .
                '.' .
                $extension;

            if (file_exists($file)) {
                @unlink($file);
            }
        }

        return redirect()
            ->route('web.admin.appearance')
            ->with(
                'success',
                'Image reset to the SmartLog default.'
            );
    }


    /**
     * Resolve the current image URL for a SmartLog UI slot.
     */
    private function smartLogImage(
        string $slot
    ): string {
        $settings = [
            'SMARTLOG_LOGO' => [
                'base' => 'smartlog-logo',
                'default' => 'images/smartlog/smartlog-logo.png',
            ],

            'PUBLIC_HERO' => [
                'base' => 'public-hero',
                'default' => 'images/smartlog/clinical-team.jpg',
            ],

            'LECTURER_HERO' => [
                'base' => 'lecturer-hero',
                'default' => 'images/smartlog/clinical-team.jpg',
            ],

            'HOD_HERO' => [
                'base' => 'hod-hero',
                'default' => 'images/smartlog/campus.jpg',
            ],

            'ADMIN_HERO' => [
                'base' => 'admin-hero',
                'default' => 'images/smartlog/campus.jpg',
            ],
        ];

        if (!isset($settings[$slot])) {
            return asset(
                'images/smartlog/campus.jpg'
            );
        }

        $directory =
            public_path('images/smartlog/custom');

        $pointer =
            $directory .
            DIRECTORY_SEPARATOR .
            $settings[$slot]['base'] .
            '.txt';

        if (file_exists($pointer)) {

            $filename =
                trim(
                    file_get_contents($pointer)
                );

            $fullPath =
                $directory .
                DIRECTORY_SEPARATOR .
                $filename;

            if (
                $filename !== '' &&
                file_exists($fullPath)
            ) {
                return asset(
                    'images/smartlog/custom/' .
                    $filename
                ) . '?v=' . filemtime($fullPath);
            }
        }

        /*
         * Compatibility with an earlier JPG-only slot.
         */
        $legacy =
            $directory .
            DIRECTORY_SEPARATOR .
            $settings[$slot]['base'] .
            '.jpg';

        if (file_exists($legacy)) {
            return asset(
                'images/smartlog/custom/' .
                basename($legacy)
            ) . '?v=' . filemtime($legacy);
        }

        return asset(
            $settings[$slot]['default']
        );
    }
}

