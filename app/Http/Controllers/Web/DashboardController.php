<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        /**
     * HOD dashboard.
     */
    public function hod()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'HOD') {
            abort(403, 'You are not authorized to access the HOD dashboard.');
        }

        return view('web.hod.dashboard', [
            'user' => $user,
        ]);
    }

    /**
     * ICT Administrator dashboard.
     */
    public function admin()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'ICT_ADMIN') {
            abort(403, 'You are not authorized to access the ICT Admin dashboard.');
        }

        return view('web.admin.dashboard', [
            'user' => $user,
        ]);
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
}