<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HodController extends Controller
{
    /**
     * Get the authenticated HOD and make sure
     * the HOD belongs to a department.
     */
    private function getHod(Request $request)
    {
        $hod = $request->user();

        if (!$hod) {
            abort(401, 'Unauthenticated.');
        }

        if ($hod->role !== 'HOD') {
            abort(403, 'HOD access only.');
        }

        if (!$hod->department_id) {
            abort(422, 'This HOD is not assigned to a department.');
        }

        return $hod;
    }

    /**
     * HOD Department Dashboard
     *
     * Read-only departmental summary.
     */
    public function dashboard(Request $request)
    {
        $hod = $this->getHod($request);
        $departmentId = $hod->department_id;

        $department = DB::table('departments')
            ->where('id', $departmentId)
            ->first();

        if (!$department) {
            return response()->json([
                'message' => 'Department not found.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | User Statistics
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Unit Statistics
        |--------------------------------------------------------------------------
        */

        $totalUnits = DB::table('units')
            ->where('department_id', $departmentId)
            ->where('is_active', 1)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Enrollment Statistics
        |--------------------------------------------------------------------------
        */

        $totalEnrollments = DB::table('enrollments as e')
            ->join('units as u', 'u.id', '=', 'e.unit_id')
            ->where('u.department_id', $departmentId)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Student Logbook Statistics
        |--------------------------------------------------------------------------
        */

        $logbookQuery = DB::table('student_logbooks as sl')
            ->join('enrollments as e', 'e.id', '=', 'sl.enrollment_id')
            ->join('units as u', 'u.id', '=', 'e.unit_id')
            ->where('u.department_id', $departmentId);

        $totalLogbooks = (clone $logbookQuery)->count();

        $activeLogbooks = (clone $logbookQuery)
            ->where('sl.status', 'ACTIVE')
            ->count();

        $completedLogbooks = (clone $logbookQuery)
            ->where('sl.status', 'COMPLETED')
            ->count();

        $submittedLogbooks = (clone $logbookQuery)
            ->where('sl.status', 'SUBMITTED')
            ->count();

        $archivedLogbooks = (clone $logbookQuery)
            ->where('sl.status', 'ARCHIVED')
            ->count();

        $averageCompletion = (clone $logbookQuery)
            ->avg('sl.completion_percentage');

        $averageCompletion = round(
            (float) ($averageCompletion ?? 0),
            2
        );

        /*
        |--------------------------------------------------------------------------
        | Clinical Entry Statistics
        |--------------------------------------------------------------------------
        */

        $clinicalEntryQuery = DB::table('clinical_entries as ce')
            ->join(
                'student_logbooks as sl',
                'sl.id',
                '=',
                'ce.student_logbook_id'
            )
            ->join(
                'enrollments as e',
                'e.id',
                '=',
                'sl.enrollment_id'
            )
            ->join(
                'units as u',
                'u.id',
                '=',
                'e.unit_id'
            )
            ->where('u.department_id', $departmentId);

        $totalClinicalEntries = (clone $clinicalEntryQuery)->count();

        $draftEntries = (clone $clinicalEntryQuery)
            ->where('ce.status', 'DRAFT')
            ->count();

        $pendingEntries = (clone $clinicalEntryQuery)
            ->where('ce.status', 'PENDING_VERIFICATION')
            ->count();

        $verifiedEntries = (clone $clinicalEntryQuery)
            ->where('ce.status', 'VERIFIED')
            ->count();

        $rejectedEntries = (clone $clinicalEntryQuery)
            ->where('ce.status', 'REJECTED')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Year Level Summary
        |--------------------------------------------------------------------------
        */

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
                    ->join(
                        'enrollments as e',
                        'e.id',
                        '=',
                        'sl.enrollment_id'
                    )
                    ->join(
                        'units as u',
                        'u.id',
                        '=',
                        'e.unit_id'
                    )
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

        return response()->json([
            'message' => 'HOD dashboard loaded successfully.',

            'hod' => [
                'id' => $hod->id,
                'dwu_id' => $hod->dwu_id,
                'name' => $hod->name,
                'email' => $hod->email,
                'role' => $hod->role,
            ],

            'department' => [
                'id' => $department->id,
                'department_code' => $department->department_code,
                'department_name' => $department->department_name,
            ],

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

            'clinical_entries' => [
                'total' => $totalClinicalEntries,
                'draft' => $draftEntries,
                'pending_verification' => $pendingEntries,
                'verified' => $verifiedEntries,
                'rejected' => $rejectedEntries,
            ],

            'year_levels' => $yearLevels,

            'access' => [
                'mode' => 'READ_ONLY',
                'can_review_verifications' => false,
                'can_enroll_students' => false,
                'can_modify_clinical_entries' => false,
            ],
        ]);
    }

    /**
     * Get students belonging to the HOD's department.
     */
    public function students(Request $request)
    {
        $hod = $this->getHod($request);
        $departmentId = $hod->department_id;

        $search = trim((string) $request->query('search', ''));
        $yearLevelId = $request->query('year_level_id');

        $query = DB::table('users as students')
            ->leftJoin(
                'year_levels as yl',
                'yl.id',
                '=',
                'students.year_level_id'
            )
            ->where('students.department_id', $departmentId)
            ->where('students.role', 'STUDENT')
            ->where('students.is_active', 1);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('students.name', 'like', "%{$search}%")
                    ->orWhere('students.dwu_id', 'like', "%{$search}%")
                    ->orWhere('students.email', 'like', "%{$search}%");
            });
        }

        if ($yearLevelId) {
            $query->where('students.year_level_id', $yearLevelId);
        }

        $students = $query
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
            ->orderBy('yl.year_number')
            ->orderBy('students.name')
            ->get()
            ->map(function ($student) use ($departmentId) {

                $enrollmentCount = DB::table('enrollments as e')
                    ->join('units as u', 'u.id', '=', 'e.unit_id')
                    ->where('e.student_id', $student->id)
                    ->where('u.department_id', $departmentId)
                    ->count();

                $logbooks = DB::table('student_logbooks as sl')
                    ->join(
                        'enrollments as e',
                        'e.id',
                        '=',
                        'sl.enrollment_id'
                    )
                    ->join(
                        'units as u',
                        'u.id',
                        '=',
                        'e.unit_id'
                    )
                    ->where('e.student_id', $student->id)
                    ->where('u.department_id', $departmentId);

                $logbookCount = (clone $logbooks)->count();

                $averageCompletion = (clone $logbooks)
                    ->avg('sl.completion_percentage');

                $completedLogbooks = (clone $logbooks)
                    ->where('sl.status', 'COMPLETED')
                    ->count();

                return [
                    'id' => $student->id,
                    'dwu_id' => $student->dwu_id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'phone' => $student->phone,

                    'year_level' => [
                        'id' => $student->year_level_id,
                        'year_name' => $student->year_name,
                        'year_number' => $student->year_number,
                    ],

                    'progress' => [
                        'enrollment_count' => $enrollmentCount,
                        'logbook_count' => $logbookCount,
                        'completed_logbooks' => $completedLogbooks,

                        'average_completion_percentage' => round(
                            (float) ($averageCompletion ?? 0),
                            2
                        ),
                    ],
                ];
            });

        return response()->json([
            'message' => 'Department students loaded successfully.',
            'total' => $students->count(),
            'students' => $students,
        ]);
    }

    /**
     * Get detailed progress for one student.
     *
     * Read-only HOD access.
     * The student must belong to the HOD's department.
     */
    public function studentProgress(Request $request, int $studentId)
    {
        $hod = $this->getHod($request);
        $departmentId = $hod->department_id;

        $student = DB::table('users as students')
            ->leftJoin(
                'year_levels as yl',
                'yl.id',
                '=',
                'students.year_level_id'
            )
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
            return response()->json([
                'message' => 'Student not found in your department.',
            ], 404);
        }

        $enrollments = DB::table('enrollments as e')
            ->join(
                'units as u',
                'u.id',
                '=',
                'e.unit_id'
            )
            ->leftJoin(
                'year_levels as yl',
                'yl.id',
                '=',
                'u.year_level_id'
            )
            ->leftJoin(
                'student_logbooks as sl',
                'sl.enrollment_id',
                '=',
                'e.id'
            )
            ->leftJoin(
                'logbook_templates as lt',
                'lt.id',
                '=',
                'sl.logbook_template_id'
            )
            ->where('e.student_id', $studentId)
            ->where('u.department_id', $departmentId)
            ->select(
                'e.id as enrollment_id',
                'e.enrollment_date',
                'e.status as enrollment_status',

                'u.id as unit_id',
                'u.unit_code',
                'u.unit_name',
                'u.requires_logbook',

                'yl.id as unit_year_level_id',
                'yl.year_name as unit_year_name',
                'yl.year_number as unit_year_number',

                'sl.id as student_logbook_id',
                'sl.status as logbook_status',
                'sl.completion_percentage',
                'sl.assigned_date',
                'sl.due_date',

                'lt.id as template_id',
                'lt.template_name',
                'lt.minimum_completion_percentage'
            )
            ->orderBy('yl.year_number')
            ->orderBy('u.unit_code')
            ->get();

        $units = $enrollments->map(function ($row) {

            $clinicalEntries = [
                'total' => 0,
                'draft' => 0,
                'pending_verification' => 0,
                'verified' => 0,
                'rejected' => 0,
            ];

            if ($row->student_logbook_id) {
                $entryQuery = DB::table('clinical_entries')
                    ->where(
                        'student_logbook_id',
                        $row->student_logbook_id
                    );

                $clinicalEntries['total'] =
                    (clone $entryQuery)->count();

                $clinicalEntries['draft'] =
                    (clone $entryQuery)
                        ->where('status', 'DRAFT')
                        ->count();

                $clinicalEntries['pending_verification'] =
                    (clone $entryQuery)
                        ->where(
                            'status',
                            'PENDING_VERIFICATION'
                        )
                        ->count();

                $clinicalEntries['verified'] =
                    (clone $entryQuery)
                        ->where('status', 'VERIFIED')
                        ->count();

                $clinicalEntries['rejected'] =
                    (clone $entryQuery)
                        ->where('status', 'REJECTED')
                        ->count();
            }

            $completionPercentage = round(
                (float) ($row->completion_percentage ?? 0),
                2
            );

            $minimumCompletion = round(
                (float) ($row->minimum_completion_percentage ?? 0),
                2
            );

            return [
                'enrollment' => [
                    'id' => $row->enrollment_id,
                    'status' => $row->enrollment_status,
                    'enrollment_date' => $row->enrollment_date,
                ],

                'unit' => [
                    'id' => $row->unit_id,
                    'unit_code' => $row->unit_code,
                    'unit_name' => $row->unit_name,
                    'requires_logbook' => (bool) $row->requires_logbook,

                    'year_level' => [
                        'id' => $row->unit_year_level_id,
                        'year_name' => $row->unit_year_name,
                        'year_number' => $row->unit_year_number,
                    ],
                ],

                'logbook' => $row->student_logbook_id
                    ? [
                        'id' => $row->student_logbook_id,
                        'template_id' => $row->template_id,
                        'template_name' => $row->template_name,
                        'status' => $row->logbook_status,
                        'completion_percentage' => $completionPercentage,
                        'minimum_completion_percentage' => $minimumCompletion,
                        'completion_requirement_met' =>
                            $minimumCompletion > 0
                                ? $completionPercentage >= $minimumCompletion
                                : false,
                        'assigned_date' => $row->assigned_date,
                        'due_date' => $row->due_date,
                    ]
                    : null,

                'clinical_entries' => $clinicalEntries,
            ];
        })->values();

        $studentLogbooks = DB::table('student_logbooks as sl')
            ->join(
                'enrollments as e',
                'e.id',
                '=',
                'sl.enrollment_id'
            )
            ->join(
                'units as u',
                'u.id',
                '=',
                'e.unit_id'
            )
            ->where('e.student_id', $studentId)
            ->where('u.department_id', $departmentId);

        $totalLogbooks = (clone $studentLogbooks)->count();

        $activeLogbooks = (clone $studentLogbooks)
            ->where('sl.status', 'ACTIVE')
            ->count();

        $completedLogbooks = (clone $studentLogbooks)
            ->where('sl.status', 'COMPLETED')
            ->count();

        $submittedLogbooks = (clone $studentLogbooks)
            ->where('sl.status', 'SUBMITTED')
            ->count();

        $archivedLogbooks = (clone $studentLogbooks)
            ->where('sl.status', 'ARCHIVED')
            ->count();

        $averageCompletion = (clone $studentLogbooks)
            ->avg('sl.completion_percentage');

        $studentEntries = DB::table('clinical_entries as ce')
            ->join(
                'student_logbooks as sl',
                'sl.id',
                '=',
                'ce.student_logbook_id'
            )
            ->join(
                'enrollments as e',
                'e.id',
                '=',
                'sl.enrollment_id'
            )
            ->join(
                'units as u',
                'u.id',
                '=',
                'e.unit_id'
            )
            ->where('e.student_id', $studentId)
            ->where('u.department_id', $departmentId);

        $totalEntries = (clone $studentEntries)->count();

        $draftEntries = (clone $studentEntries)
            ->where('ce.status', 'DRAFT')
            ->count();

        $pendingEntries = (clone $studentEntries)
            ->where('ce.status', 'PENDING_VERIFICATION')
            ->count();

        $verifiedEntries = (clone $studentEntries)
            ->where('ce.status', 'VERIFIED')
            ->count();

        $rejectedEntries = (clone $studentEntries)
            ->where('ce.status', 'REJECTED')
            ->count();

        return response()->json([
            'message' => 'Student progress loaded successfully.',

            'student' => [
                'id' => $student->id,
                'dwu_id' => $student->dwu_id,
                'name' => $student->name,
                'email' => $student->email,
                'phone' => $student->phone,

                'year_level' => [
                    'id' => $student->year_level_id,
                    'year_name' => $student->year_name,
                    'year_number' => $student->year_number,
                ],
            ],

            'summary' => [
                'enrollment_count' => $enrollments->count(),

                'logbooks' => [
                    'total' => $totalLogbooks,
                    'active' => $activeLogbooks,
                    'completed' => $completedLogbooks,
                    'submitted' => $submittedLogbooks,
                    'archived' => $archivedLogbooks,

                    'average_completion_percentage' => round(
                        (float) ($averageCompletion ?? 0),
                        2
                    ),
                ],

                'clinical_entries' => [
                    'total' => $totalEntries,
                    'draft' => $draftEntries,
                    'pending_verification' => $pendingEntries,
                    'verified' => $verifiedEntries,
                    'rejected' => $rejectedEntries,
                ],
            ],

            'units' => $units,

            'access' => [
                'mode' => 'READ_ONLY',
                'can_review_verifications' => false,
                'can_enroll_students' => false,
                'can_modify_clinical_entries' => false,
            ],
        ]);
    }

    /**
     * Get units belonging to the HOD's department.
     */
    public function units(Request $request)
    {
        $hod = $this->getHod($request);
        $departmentId = $hod->department_id;

        $yearLevelId = $request->query('year_level_id');

        $query = DB::table('units as u')
            ->leftJoin(
                'year_levels as yl',
                'yl.id',
                '=',
                'u.year_level_id'
            )
            ->where('u.department_id', $departmentId)
            ->where('u.is_active', 1);

        if ($yearLevelId) {
            $query->where('u.year_level_id', $yearLevelId);
        }

        $units = $query
            ->select(
                'u.id',
                'u.unit_code',
                'u.unit_name',
                'u.year_level_id',
                'u.semester_id',
                'u.requires_logbook',
                'yl.year_name',
                'yl.year_number'
            )
            ->orderBy('yl.year_number')
            ->orderBy('u.unit_code')
            ->get()
            ->map(function ($unit) {

                $enrolledStudents = DB::table('enrollments')
                    ->where('unit_id', $unit->id)
                    ->distinct('student_id')
                    ->count('student_id');

                $averageCompletion = DB::table('student_logbooks as sl')
                    ->join(
                        'enrollments as e',
                        'e.id',
                        '=',
                        'sl.enrollment_id'
                    )
                    ->where('e.unit_id', $unit->id)
                    ->avg('sl.completion_percentage');

                $logbookCount = DB::table('student_logbooks as sl')
                    ->join(
                        'enrollments as e',
                        'e.id',
                        '=',
                        'sl.enrollment_id'
                    )
                    ->where('e.unit_id', $unit->id)
                    ->count();

                return [
                    'id' => $unit->id,
                    'unit_code' => $unit->unit_code,
                    'unit_name' => $unit->unit_name,
                    'requires_logbook' => (bool) $unit->requires_logbook,

                    'year_level' => [
                        'id' => $unit->year_level_id,
                        'year_name' => $unit->year_name,
                        'year_number' => $unit->year_number,
                    ],

                    'statistics' => [
                        'enrolled_students' => $enrolledStudents,
                        'logbook_count' => $logbookCount,

                        'average_completion_percentage' => round(
                            (float) ($averageCompletion ?? 0),
                            2
                        ),
                    ],
                ];
            });

        return response()->json([
            'message' => 'Department units loaded successfully.',
            'total' => $units->count(),
            'units' => $units,
        ]);
    }

    /**
     * Get detailed progress for one department unit.
     *
     * Read-only HOD access.
     * The unit must belong to the HOD's department.
     */
    public function unitProgress(Request $request, int $unitId)
    {
        $hod = $this->getHod($request);
        $departmentId = $hod->department_id;

        /*
        |--------------------------------------------------------------------------
        | Unit
        |--------------------------------------------------------------------------
        */

        $unit = DB::table('units as u')
            ->leftJoin(
                'year_levels as yl',
                'yl.id',
                '=',
                'u.year_level_id'
            )
            ->where('u.id', $unitId)
            ->where('u.department_id', $departmentId)
            ->where('u.is_active', 1)
            ->select(
                'u.id',
                'u.unit_code',
                'u.unit_name',
                'u.year_level_id',
                'u.semester_id',
                'u.requires_logbook',
                'yl.year_name',
                'yl.year_number'
            )
            ->first();

        if (!$unit) {
            return response()->json([
                'message' => 'Unit not found in your department.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Enrolled Students
        |--------------------------------------------------------------------------
        */

        $enrollments = DB::table('enrollments as e')
            ->join(
                'users as students',
                'students.id',
                '=',
                'e.student_id'
            )
            ->leftJoin(
                'year_levels as syl',
                'syl.id',
                '=',
                'students.year_level_id'
            )
            ->where('e.unit_id', $unitId)
            ->where('students.department_id', $departmentId)
            ->where('students.role', 'STUDENT')
            ->where('students.is_active', 1)
            ->select(
                'e.id as enrollment_id',
                'e.enrollment_date',
                'e.status as enrollment_status',

                'students.id as student_id',
                'students.dwu_id',
                'students.name',
                'students.email',
                'students.phone',
                'students.year_level_id as student_year_level_id',

                'syl.year_name as student_year_name',
                'syl.year_number as student_year_number'
            )
            ->orderBy('students.name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Build Student Progress
        |--------------------------------------------------------------------------
        */

        $students = $enrollments->map(function ($row) {

            $logbooks = DB::table('student_logbooks as sl')
                ->leftJoin(
                    'logbook_templates as lt',
                    'lt.id',
                    '=',
                    'sl.logbook_template_id'
                )
                ->where(
                    'sl.enrollment_id',
                    $row->enrollment_id
                )
                ->select(
                    'sl.id',
                    'sl.status',
                    'sl.completion_percentage',
                    'sl.assigned_date',
                    'sl.due_date',

                    'lt.id as template_id',
                    'lt.template_name',
                    'lt.minimum_completion_percentage'
                )
                ->orderBy('sl.id')
                ->get();

            $logbookItems = $logbooks->map(function ($logbook) {

                $entryQuery = DB::table('clinical_entries')
                    ->where(
                        'student_logbook_id',
                        $logbook->id
                    );

                $totalEntries =
                    (clone $entryQuery)->count();

                $draftEntries =
                    (clone $entryQuery)
                        ->where('status', 'DRAFT')
                        ->count();

                $pendingEntries =
                    (clone $entryQuery)
                        ->where(
                            'status',
                            'PENDING_VERIFICATION'
                        )
                        ->count();

                $verifiedEntries =
                    (clone $entryQuery)
                        ->where('status', 'VERIFIED')
                        ->count();

                $rejectedEntries =
                    (clone $entryQuery)
                        ->where('status', 'REJECTED')
                        ->count();

                $completionPercentage = round(
                    (float) ($logbook->completion_percentage ?? 0),
                    2
                );

                $minimumCompletion = round(
                    (float) ($logbook->minimum_completion_percentage ?? 0),
                    2
                );

                return [
                    'id' => $logbook->id,
                    'template_id' => $logbook->template_id,
                    'template_name' => $logbook->template_name,
                    'status' => $logbook->status,

                    'completion_percentage' =>
                        $completionPercentage,

                    'minimum_completion_percentage' =>
                        $minimumCompletion,

                    'completion_requirement_met' =>
                        $minimumCompletion > 0
                            ? $completionPercentage >= $minimumCompletion
                            : false,

                    'assigned_date' => $logbook->assigned_date,
                    'due_date' => $logbook->due_date,

                    'clinical_entries' => [
                        'total' => $totalEntries,
                        'draft' => $draftEntries,
                        'pending_verification' => $pendingEntries,
                        'verified' => $verifiedEntries,
                        'rejected' => $rejectedEntries,
                    ],
                ];
            })->values();

            $averageCompletion = $logbooks->avg(
                'completion_percentage'
            );

            $activeLogbooks = $logbooks
                ->where('status', 'ACTIVE')
                ->count();

            $completedLogbooks = $logbooks
                ->where('status', 'COMPLETED')
                ->count();

            $submittedLogbooks = $logbooks
                ->where('status', 'SUBMITTED')
                ->count();

            $archivedLogbooks = $logbooks
                ->where('status', 'ARCHIVED')
                ->count();

            $studentEntryQuery = DB::table('clinical_entries as ce')
                ->join(
                    'student_logbooks as sl',
                    'sl.id',
                    '=',
                    'ce.student_logbook_id'
                )
                ->where(
                    'sl.enrollment_id',
                    $row->enrollment_id
                );

            $totalEntries =
                (clone $studentEntryQuery)->count();

            $draftEntries =
                (clone $studentEntryQuery)
                    ->where('ce.status', 'DRAFT')
                    ->count();

            $pendingEntries =
                (clone $studentEntryQuery)
                    ->where(
                        'ce.status',
                        'PENDING_VERIFICATION'
                    )
                    ->count();

            $verifiedEntries =
                (clone $studentEntryQuery)
                    ->where('ce.status', 'VERIFIED')
                    ->count();

            $rejectedEntries =
                (clone $studentEntryQuery)
                    ->where('ce.status', 'REJECTED')
                    ->count();

            return [
                'student' => [
                    'id' => $row->student_id,
                    'dwu_id' => $row->dwu_id,
                    'name' => $row->name,
                    'email' => $row->email,
                    'phone' => $row->phone,

                    'year_level' => [
                        'id' => $row->student_year_level_id,
                        'year_name' => $row->student_year_name,
                        'year_number' => $row->student_year_number,
                    ],
                ],

                'enrollment' => [
                    'id' => $row->enrollment_id,
                    'status' => $row->enrollment_status,
                    'enrollment_date' => $row->enrollment_date,
                ],

                'progress' => [
                    'logbook_count' => $logbooks->count(),

                    'active_logbooks' => $activeLogbooks,
                    'completed_logbooks' => $completedLogbooks,
                    'submitted_logbooks' => $submittedLogbooks,
                    'archived_logbooks' => $archivedLogbooks,

                    'average_completion_percentage' => round(
                        (float) ($averageCompletion ?? 0),
                        2
                    ),

                    'clinical_entries' => [
                        'total' => $totalEntries,
                        'draft' => $draftEntries,
                        'pending_verification' => $pendingEntries,
                        'verified' => $verifiedEntries,
                        'rejected' => $rejectedEntries,
                    ],
                ],

                'logbooks' => $logbookItems,
            ];
        })->values();

        /*
        |--------------------------------------------------------------------------
        | Overall Unit Logbook Statistics
        |--------------------------------------------------------------------------
        */

        $unitLogbooks = DB::table('student_logbooks as sl')
            ->join(
                'enrollments as e',
                'e.id',
                '=',
                'sl.enrollment_id'
            )
            ->where('e.unit_id', $unitId);

        $totalLogbooks = (clone $unitLogbooks)->count();

        $activeLogbooks = (clone $unitLogbooks)
            ->where('sl.status', 'ACTIVE')
            ->count();

        $completedLogbooks = (clone $unitLogbooks)
            ->where('sl.status', 'COMPLETED')
            ->count();

        $submittedLogbooks = (clone $unitLogbooks)
            ->where('sl.status', 'SUBMITTED')
            ->count();

        $archivedLogbooks = (clone $unitLogbooks)
            ->where('sl.status', 'ARCHIVED')
            ->count();

        $averageCompletion = (clone $unitLogbooks)
            ->avg('sl.completion_percentage');

        /*
        |--------------------------------------------------------------------------
        | Overall Unit Clinical Entry Statistics
        |--------------------------------------------------------------------------
        */

        $unitEntries = DB::table('clinical_entries as ce')
            ->join(
                'student_logbooks as sl',
                'sl.id',
                '=',
                'ce.student_logbook_id'
            )
            ->join(
                'enrollments as e',
                'e.id',
                '=',
                'sl.enrollment_id'
            )
            ->where('e.unit_id', $unitId);

        $totalEntries =
            (clone $unitEntries)->count();

        $draftEntries =
            (clone $unitEntries)
                ->where('ce.status', 'DRAFT')
                ->count();

        $pendingEntries =
            (clone $unitEntries)
                ->where(
                    'ce.status',
                    'PENDING_VERIFICATION'
                )
                ->count();

        $verifiedEntries =
            (clone $unitEntries)
                ->where('ce.status', 'VERIFIED')
                ->count();

        $rejectedEntries =
            (clone $unitEntries)
                ->where('ce.status', 'REJECTED')
                ->count();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' => 'Unit progress loaded successfully.',

            'unit' => [
                'id' => $unit->id,
                'unit_code' => $unit->unit_code,
                'unit_name' => $unit->unit_name,
                'requires_logbook' => (bool) $unit->requires_logbook,
                'semester_id' => $unit->semester_id,

                'year_level' => [
                    'id' => $unit->year_level_id,
                    'year_name' => $unit->year_name,
                    'year_number' => $unit->year_number,
                ],
            ],

            'summary' => [
                'enrollment_count' => $enrollments->count(),

                'enrolled_students' => $enrollments
                    ->pluck('student_id')
                    ->unique()
                    ->count(),

                'logbooks' => [
                    'total' => $totalLogbooks,
                    'active' => $activeLogbooks,
                    'completed' => $completedLogbooks,
                    'submitted' => $submittedLogbooks,
                    'archived' => $archivedLogbooks,

                    'average_completion_percentage' => round(
                        (float) ($averageCompletion ?? 0),
                        2
                    ),
                ],

                'clinical_entries' => [
                    'total' => $totalEntries,
                    'draft' => $draftEntries,
                    'pending_verification' => $pendingEntries,
                    'verified' => $verifiedEntries,
                    'rejected' => $rejectedEntries,
                ],
            ],

            'students' => $students,

            'access' => [
                'mode' => 'READ_ONLY',
                'can_review_verifications' => false,
                'can_enroll_students' => false,
                'can_modify_clinical_entries' => false,
            ],
        ]);
    }

    /**
     * Get active lecturers belonging to the HOD's department.
     *
     * Read-only HOD access.
     * Optional search by lecturer name, DWU ID, or email.
     */
    public function lecturers(Request $request)
    {
        $hod = $this->getHod($request);
        $departmentId = $hod->department_id;

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

                return [
                    'id' => $lecturer->id,
                    'dwu_id' => $lecturer->dwu_id,
                    'name' => $lecturer->name,
                    'email' => $lecturer->email,
                    'phone' => $lecturer->phone,

                    'statistics' => [
                        'assigned_unit_count' => $assignedUnits->count(),
                        'enrolled_student_count' => $enrolledStudents,
                        'logbook_count' => $logbookCount,
                        'average_completion_percentage' => round(
                            (float) ($averageCompletion ?? 0),
                            2
                        ),
                    ],

                    'units' => $assignedUnits
                        ->map(function ($unit) {
                            return [
                                'id' => $unit->id,
                                'unit_code' => $unit->unit_code,
                                'unit_name' => $unit->unit_name,
                                'requires_logbook' =>
                                    (bool) $unit->requires_logbook,

                                'year_level' => [
                                    'id' => $unit->year_level_id,
                                    'year_name' => $unit->year_name,
                                    'year_number' => $unit->year_number,
                                ],
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();

        return response()->json([
            'message' => 'Department lecturers loaded successfully.',
            'total' => $lecturers->count(),
            'lecturers' => $lecturers,

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
     * Get detailed read-only information for one lecturer.
     *
     * The lecturer must belong to the HOD's department.
     */
    public function lecturerProgress(Request $request, int $lecturerId)
    {
        $hod = $this->getHod($request);
        $departmentId = $hod->department_id;

        $lecturer = DB::table('users')
            ->where('id', $lecturerId)
            ->where('department_id', $departmentId)
            ->where('role', 'LECTURER')
            ->where('is_active', 1)
            ->select(
                'id',
                'dwu_id',
                'name',
                'email',
                'phone'
            )
            ->first();

        if (!$lecturer) {
            return response()->json([
                'message' => 'Lecturer not found in your department.',
            ], 404);
        }

        $assignedUnits = DB::table('lecturer_units as lu')
            ->join('units as u', 'u.id', '=', 'lu.unit_id')
            ->leftJoin(
                'year_levels as yl',
                'yl.id',
                '=',
                'u.year_level_id'
            )
            ->where('lu.lecturer_id', $lecturerId)
            ->where('u.department_id', $departmentId)
            ->where('u.is_active', 1)
            ->select(
                'u.id',
                'u.unit_code',
                'u.unit_name',
                'u.requires_logbook',
                'u.semester_id',
                'u.year_level_id',
                'yl.year_name',
                'yl.year_number'
            )
            ->orderBy('yl.year_number')
            ->orderBy('u.unit_code')
            ->get();

        $units = $assignedUnits
            ->map(function ($unit) {

                $enrollmentQuery = DB::table('enrollments')
                    ->where('unit_id', $unit->id);

                $enrollmentCount =
                    (clone $enrollmentQuery)->count();

                $enrolledStudents =
                    (clone $enrollmentQuery)
                        ->distinct('student_id')
                        ->count('student_id');

                $logbookQuery = DB::table('student_logbooks as sl')
                    ->join(
                        'enrollments as e',
                        'e.id',
                        '=',
                        'sl.enrollment_id'
                    )
                    ->where('e.unit_id', $unit->id);

                $totalLogbooks =
                    (clone $logbookQuery)->count();

                $activeLogbooks =
                    (clone $logbookQuery)
                        ->where('sl.status', 'ACTIVE')
                        ->count();

                $completedLogbooks =
                    (clone $logbookQuery)
                        ->where('sl.status', 'COMPLETED')
                        ->count();

                $submittedLogbooks =
                    (clone $logbookQuery)
                        ->where('sl.status', 'SUBMITTED')
                        ->count();

                $archivedLogbooks =
                    (clone $logbookQuery)
                        ->where('sl.status', 'ARCHIVED')
                        ->count();

                $averageCompletion =
                    (clone $logbookQuery)
                        ->avg('sl.completion_percentage');

                $entryQuery = DB::table('clinical_entries as ce')
                    ->join(
                        'student_logbooks as sl',
                        'sl.id',
                        '=',
                        'ce.student_logbook_id'
                    )
                    ->join(
                        'enrollments as e',
                        'e.id',
                        '=',
                        'sl.enrollment_id'
                    )
                    ->where('e.unit_id', $unit->id);

                $totalEntries =
                    (clone $entryQuery)->count();

                $draftEntries =
                    (clone $entryQuery)
                        ->where('ce.status', 'DRAFT')
                        ->count();

                $pendingEntries =
                    (clone $entryQuery)
                        ->where(
                            'ce.status',
                            'PENDING_VERIFICATION'
                        )
                        ->count();

                $verifiedEntries =
                    (clone $entryQuery)
                        ->where('ce.status', 'VERIFIED')
                        ->count();

                $rejectedEntries =
                    (clone $entryQuery)
                        ->where('ce.status', 'REJECTED')
                        ->count();

                return [
                    'id' => $unit->id,
                    'unit_code' => $unit->unit_code,
                    'unit_name' => $unit->unit_name,
                    'requires_logbook' =>
                        (bool) $unit->requires_logbook,
                    'semester_id' => $unit->semester_id,

                    'year_level' => [
                        'id' => $unit->year_level_id,
                        'year_name' => $unit->year_name,
                        'year_number' => $unit->year_number,
                    ],

                    'statistics' => [
                        'enrollment_count' => $enrollmentCount,
                        'enrolled_students' => $enrolledStudents,

                        'logbooks' => [
                            'total' => $totalLogbooks,
                            'active' => $activeLogbooks,
                            'completed' => $completedLogbooks,
                            'submitted' => $submittedLogbooks,
                            'archived' => $archivedLogbooks,

                            'average_completion_percentage' => round(
                                (float) ($averageCompletion ?? 0),
                                2
                            ),
                        ],

                        'clinical_entries' => [
                            'total' => $totalEntries,
                            'draft' => $draftEntries,
                            'pending_verification' => $pendingEntries,
                            'verified' => $verifiedEntries,
                            'rejected' => $rejectedEntries,
                        ],
                    ],
                ];
            })
            ->values();

        $unitIds = $assignedUnits
            ->pluck('id')
            ->filter()
            ->values();

        $totalEnrolledStudents = $unitIds->isEmpty()
            ? 0
            : DB::table('enrollments')
                ->whereIn('unit_id', $unitIds)
                ->distinct('student_id')
                ->count('student_id');

        $overallLogbookQuery = DB::table('student_logbooks as sl')
            ->join(
                'enrollments as e',
                'e.id',
                '=',
                'sl.enrollment_id'
            );

        if ($unitIds->isEmpty()) {
            $overallLogbookQuery->whereRaw('1 = 0');
        } else {
            $overallLogbookQuery->whereIn('e.unit_id', $unitIds);
        }

        $totalLogbooks = (clone $overallLogbookQuery)->count();

        $activeLogbooks = (clone $overallLogbookQuery)
            ->where('sl.status', 'ACTIVE')
            ->count();

        $completedLogbooks = (clone $overallLogbookQuery)
            ->where('sl.status', 'COMPLETED')
            ->count();

        $averageCompletion = (clone $overallLogbookQuery)
            ->avg('sl.completion_percentage');

        return response()->json([
            'message' => 'Lecturer progress loaded successfully.',

            'lecturer' => [
                'id' => $lecturer->id,
                'dwu_id' => $lecturer->dwu_id,
                'name' => $lecturer->name,
                'email' => $lecturer->email,
                'phone' => $lecturer->phone,
            ],

            'summary' => [
                'assigned_unit_count' => $assignedUnits->count(),
                'enrolled_student_count' => $totalEnrolledStudents,

                'logbooks' => [
                    'total' => $totalLogbooks,
                    'active' => $activeLogbooks,
                    'completed' => $completedLogbooks,
                    'average_completion_percentage' => round(
                        (float) ($averageCompletion ?? 0),
                        2
                    ),
                ],
            ],

            'units' => $units,

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
     * Get year-level statistics for the HOD's department.
     */
    public function yearLevels(Request $request)
    {
        $hod = $this->getHod($request);
        $departmentId = $hod->department_id;

        $yearLevels = DB::table('year_levels')
            ->orderBy('year_number')
            ->get()
            ->map(function ($yearLevel) use ($departmentId) {

                $students = DB::table('users')
                    ->where('department_id', $departmentId)
                    ->where('year_level_id', $yearLevel->id)
                    ->where('role', 'STUDENT')
                    ->where('is_active', 1)
                    ->count();

                $units = DB::table('units')
                    ->where('department_id', $departmentId)
                    ->where('year_level_id', $yearLevel->id)
                    ->where('is_active', 1)
                    ->count();

                $logbookQuery = DB::table('student_logbooks as sl')
                    ->join(
                        'enrollments as e',
                        'e.id',
                        '=',
                        'sl.enrollment_id'
                    )
                    ->join(
                        'units as u',
                        'u.id',
                        '=',
                        'e.unit_id'
                    )
                    ->where('u.department_id', $departmentId)
                    ->where('u.year_level_id', $yearLevel->id);

                $totalLogbooks = (clone $logbookQuery)->count();

                $completedLogbooks = (clone $logbookQuery)
                    ->where('sl.status', 'COMPLETED')
                    ->count();

                $averageCompletion = (clone $logbookQuery)
                    ->avg('sl.completion_percentage');

                return [
                    'id' => $yearLevel->id,
                    'year_name' => $yearLevel->year_name,
                    'year_number' => $yearLevel->year_number,

                    'statistics' => [
                        'students' => $students,
                        'units' => $units,
                        'total_logbooks' => $totalLogbooks,
                        'completed_logbooks' => $completedLogbooks,

                        'average_completion_percentage' => round(
                            (float) ($averageCompletion ?? 0),
                            2
                        ),
                    ],
                ];
            })
            ->filter(function ($yearLevel) {
                return $yearLevel['statistics']['students'] > 0
                    || $yearLevel['statistics']['units'] > 0;
            })
            ->values();

        return response()->json([
            'message' => 'Department year levels loaded successfully.',
            'year_levels' => $yearLevels,
        ]);
    }
}