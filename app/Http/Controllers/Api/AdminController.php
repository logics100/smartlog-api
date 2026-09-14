<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * ICT Admin system-wide dashboard.
     *
     * The ICT Admin can monitor the whole SmartLog system,
     * including departments, users, units, enrollments,
     * logbooks and clinical activity.
     */
    public function dashboard(Request $request)
    {
        $admin = $request->user();

        /*
        |--------------------------------------------------------------------------
        | User Statistics
        |--------------------------------------------------------------------------
        */

        $totalUsers = DB::table('users')->count();

        $activeUsers = DB::table('users')
            ->where('is_active', 1)
            ->count();

        $inactiveUsers = DB::table('users')
            ->where('is_active', 0)
            ->count();

        $students = DB::table('users')
            ->where('role', 'STUDENT')
            ->count();

        $lecturers = DB::table('users')
            ->where('role', 'LECTURER')
            ->count();

        $hods = DB::table('users')
            ->where('role', 'HOD')
            ->count();

        $ictAdmins = DB::table('users')
            ->where('role', 'ICT_ADMIN')
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Department Statistics
        |--------------------------------------------------------------------------
        */

        $totalDepartments = DB::table('departments')
            ->count();

        $activeDepartments = DB::table('departments')
            ->where('is_active', 1)
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Unit Statistics
        |--------------------------------------------------------------------------
        */

        $totalUnits = DB::table('units')
            ->count();

        $activeUnits = DB::table('units')
            ->where('is_active', 1)
            ->count();

        $logbookUnits = DB::table('units')
            ->where('requires_logbook', 1)
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Enrollment Statistics
        |--------------------------------------------------------------------------
        */

        $totalEnrollments = DB::table('enrollments')
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Logbook Statistics
        |--------------------------------------------------------------------------
        */

        $totalLogbooks = DB::table('student_logbooks')
            ->count();

        $activeLogbooks = DB::table('student_logbooks')
            ->where('status', 'ACTIVE')
            ->count();

        $completedLogbooks = DB::table('student_logbooks')
            ->where('status', 'COMPLETED')
            ->count();

        $submittedLogbooks = DB::table('student_logbooks')
            ->where('status', 'SUBMITTED')
            ->count();

        $archivedLogbooks = DB::table('student_logbooks')
            ->where('status', 'ARCHIVED')
            ->count();

        $averageCompletion = round(
            (float) (
                DB::table('student_logbooks')
                    ->avg('completion_percentage') ?? 0
            ),
            2
        );


        /*
        |--------------------------------------------------------------------------
        | Clinical Entry Statistics
        |--------------------------------------------------------------------------
        */

        $totalClinicalEntries = DB::table('clinical_entries')
            ->count();

        $draftClinicalEntries = DB::table('clinical_entries')
            ->where('status', 'DRAFT')
            ->count();

        $pendingClinicalEntries = DB::table('clinical_entries')
            ->where('status', 'PENDING_VERIFICATION')
            ->count();

        $verifiedClinicalEntries = DB::table('clinical_entries')
            ->where('status', 'VERIFIED')
            ->count();

        $rejectedClinicalEntries = DB::table('clinical_entries')
            ->where('status', 'REJECTED')
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Department Overview
        |--------------------------------------------------------------------------
        */

        $departments = DB::table('departments')
            ->where('is_active', 1)
            ->orderBy('department_name')
            ->get()
            ->map(function ($department) {
                $studentCount = DB::table('users')
                    ->where('department_id', $department->id)
                    ->where('role', 'STUDENT')
                    ->where('is_active', 1)
                    ->count();

                $lecturerCount = DB::table('users')
                    ->where('department_id', $department->id)
                    ->where('role', 'LECTURER')
                    ->where('is_active', 1)
                    ->count();

                $hodCount = DB::table('users')
                    ->where('department_id', $department->id)
                    ->where('role', 'HOD')
                    ->where('is_active', 1)
                    ->count();

                $unitCount = DB::table('units')
                    ->where('department_id', $department->id)
                    ->where('is_active', 1)
                    ->count();

                return [
                    'id' => $department->id,
                    'department_code' => $department->department_code,
                    'department_name' => $department->department_name,

                    'statistics' => [
                        'students' => $studentCount,
                        'lecturers' => $lecturerCount,
                        'hods' => $hodCount,
                        'units' => $unitCount,
                    ],
                ];
            })
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' => 'ICT Admin dashboard loaded successfully.',

            'admin' => [
                'id' => $admin->id,
                'dwu_id' => $admin->dwu_id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
            ],

            'summary' => [
                'departments' => $totalDepartments,
                'users' => $totalUsers,
                'students' => $students,
                'lecturers' => $lecturers,
                'hods' => $hods,
                'ict_admins' => $ictAdmins,
                'units' => $totalUnits,
                'enrollments' => $totalEnrollments,
            ],

            'users' => [
                'total' => $totalUsers,
                'active' => $activeUsers,
                'inactive' => $inactiveUsers,

                'roles' => [
                    'students' => $students,
                    'lecturers' => $lecturers,
                    'hods' => $hods,
                    'ict_admins' => $ictAdmins,
                ],
            ],

            'departments' => [
                'total' => $totalDepartments,
                'active' => $activeDepartments,
                'items' => $departments,
            ],

            'units' => [
                'total' => $totalUnits,
                'active' => $activeUnits,
                'requiring_logbook' => $logbookUnits,
            ],

            'enrollments' => [
                'total' => $totalEnrollments,
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
                'draft' => $draftClinicalEntries,
                'pending_verification' => $pendingClinicalEntries,
                'verified' => $verifiedClinicalEntries,
                'rejected' => $rejectedClinicalEntries,
            ],

            'access' => [
                'mode' => 'SYSTEM_ADMIN',

                'can_manage_users' => true,
                'can_manage_departments' => true,
                'can_manage_units' => true,

                'can_review_clinical_verifications' => false,
            ],
        ]);
    }

    /**
     * ICT Admin user management list.
     *
     * Supports optional filters:
     * - search
     * - role
     * - department_id
     * - status
     */
    public function users(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $role = strtoupper(
            trim((string) $request->query('role', ''))
        );

        $departmentId = $request->query('department_id');
        $status = strtolower(
            trim((string) $request->query('status', ''))
        );

        $query = DB::table('users')
            ->leftJoin(
                'departments',
                'users.department_id',
                '=',
                'departments.id'
            )
            ->leftJoin(
                'year_levels',
                'users.year_level_id',
                '=',
                'year_levels.id'
            )
            ->select([
                'users.id',
                'users.dwu_id',
                'users.name',
                'users.email',
                'users.phone',
                'users.role',
                'users.department_id',
                'departments.department_code',
                'departments.department_name',
                'users.year_level_id',
                'year_levels.year_name',
                'year_levels.year_number',
                'users.is_active',
                'users.created_at',
                'users.updated_at',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where(
                        'users.name',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'users.email',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'users.dwu_id',
                        'like',
                        '%' . $search . '%'
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Role Filter
        |--------------------------------------------------------------------------
        */

        $allowedRoles = [
            'STUDENT',
            'LECTURER',
            'HOD',
            'ICT_ADMIN',
        ];

        if (
            $role !== '' &&
            in_array($role, $allowedRoles, true)
        ) {
            $query->where('users.role', $role);
        }

        /*
        |--------------------------------------------------------------------------
        | Department Filter
        |--------------------------------------------------------------------------
        */

        if (
            $departmentId !== null &&
            $departmentId !== ''
        ) {
            $query->where(
                'users.department_id',
                (int) $departmentId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Active / Inactive Filter
        |--------------------------------------------------------------------------
        */

        if ($status === 'active') {
            $query->where('users.is_active', 1);
        }

        if ($status === 'inactive') {
            $query->where('users.is_active', 0);
        }

        /*
        |--------------------------------------------------------------------------
        | Results
        |--------------------------------------------------------------------------
        */

        $users = $query
            ->orderBy('users.role')
            ->orderBy('users.name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'dwu_id' => $user->dwu_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,

                    'role' => $user->role,

                    'department' => $user->department_id
                        ? [
                            'id' => $user->department_id,
                            'code' => $user->department_code,
                            'name' => $user->department_name,
                        ]
                        : null,

                    'year_level' => $user->year_level_id
                        ? [
                            'id' => $user->year_level_id,
                            'name' => $user->year_name,
                            'number' => $user->year_number,
                        ]
                        : null,

                    'is_active' => (bool) $user->is_active,

                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Filter Options
        |--------------------------------------------------------------------------
        */

        $departments = DB::table('departments')
            ->orderBy('department_name')
            ->get([
                'id',
                'department_code',
                'department_name',
                'is_active',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' => 'ICT Admin users loaded successfully.',

            'filters' => [
                'search' => $search !== ''
                    ? $search
                    : null,

                'role' => $role !== ''
                    ? $role
                    : null,

                'department_id' => $departmentId !== null &&
                    $departmentId !== ''
                    ? (int) $departmentId
                    : null,

                'status' => $status !== ''
                    ? $status
                    : null,
            ],

            'summary' => [
                'returned_users' => $users->count(),
            ],

            'users' => $users,

            'options' => [
                'roles' => $allowedRoles,
                'statuses' => [
                    'active',
                    'inactive',
                ],
                'departments' => $departments,
            ],

            'access' => [
                'mode' => 'SYSTEM_ADMIN',
                'can_manage_users' => true,
            ],
        ]);
    }


    /**
     * Create a new SmartLog user account.
     */
    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'dwu_id' => [
                'required',
                'string',
                'max:50',
                'unique:users,dwu_id',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],
            'role' => [
                'required',
                'string',
                'in:STUDENT,LECTURER,HOD,ICT_ADMIN',
            ],
            'department_id' => [
                'nullable',
                'integer',
                'exists:departments,id',
            ],
            'year_level_id' => [
                'nullable',
                'integer',
                'exists:year_levels,id',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $role = $validated['role'];

        /*
        |--------------------------------------------------------------------------
        | Role-specific validation
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $role,
                ['STUDENT', 'LECTURER', 'HOD'],
                true
            ) &&
            empty($validated['department_id'])
        ) {
            return response()->json([
                'message' => 'A department is required for this role.',
                'errors' => [
                    'department_id' => [
                        'Please select a department.',
                    ],
                ],
            ], 422);
        }

        if (
            $role === 'STUDENT' &&
            empty($validated['year_level_id'])
        ) {
            return response()->json([
                'message' => 'A year level is required for students.',
                'errors' => [
                    'year_level_id' => [
                        'Please select a year level.',
                    ],
                ],
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Role cleanup rules
        |--------------------------------------------------------------------------
        */

        if ($role === 'ICT_ADMIN') {
            $validated['department_id'] = null;
            $validated['year_level_id'] = null;
        }

        if (
            in_array(
                $role,
                ['LECTURER', 'HOD'],
                true
            )
        ) {
            $validated['year_level_id'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Create account
        |--------------------------------------------------------------------------
        */

        $userId = DB::table('users')->insertGetId([
            'dwu_id' => strtoupper(
                trim($validated['dwu_id'])
            ),
            'name' => trim(
                $validated['name']
            ),
            'email' => strtolower(
                trim($validated['email'])
            ),
            'phone' => $validated['phone'] ?? null,
            'role' => $role,
            'department_id' => $validated['department_id'] ?? null,
            'year_level_id' => $validated['year_level_id'] ?? null,
            'password' => Hash::make(
                $validated['password']
            ),
            'is_active' => $validated['is_active'] ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Load created account
        |--------------------------------------------------------------------------
        */

        $user = DB::table('users as u')
            ->leftJoin(
                'departments as d',
                'u.department_id',
                '=',
                'd.id'
            )
            ->leftJoin(
                'year_levels as yl',
                'u.year_level_id',
                '=',
                'yl.id'
            )
            ->where('u.id', $userId)
            ->select(
                'u.id',
                'u.dwu_id',
                'u.name',
                'u.email',
                'u.phone',
                'u.role',
                'u.is_active',
                'u.department_id',
                'd.department_code',
                'd.department_name',
                'u.year_level_id',
                'yl.year_name',
                'yl.year_number'
            )
            ->first();

        return response()->json([
            'message' => 'SmartLog user account created successfully.',

            'user' => [
                'id' => $user->id,
                'dwu_id' => $user->dwu_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,

                'department' => $user->department_id
                    ? [
                        'id' => $user->department_id,
                        'code' => $user->department_code,
                        'name' => $user->department_name,
                    ]
                    : null,

                'year_level' => $user->year_level_id
                    ? [
                        'id' => $user->year_level_id,
                        'name' => $user->year_name,
                        'number' => $user->year_number,
                    ]
                    : null,

                'is_active' => (bool) $user->is_active,
            ],

            'access' => [
                'mode' => 'SYSTEM_ADMIN',
                'created_by' => $request->user()->id,
            ],
        ], 201);
    }
        /**
     * Update an existing SmartLog user account.
     */
    public function updateUser(Request $request, int $id)
    {
        /*
        |--------------------------------------------------------------------------
        | Find User
        |--------------------------------------------------------------------------
        */

        $existingUser = DB::table('users')
            ->where('id', $id)
            ->first();

        if (!$existingUser) {
            return response()->json([
                'message' => 'SmartLog user account not found.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'dwu_id' => [
                'required',
                'string',
                'max:50',
                'unique:users,dwu_id,' . $id,
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $id,
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'role' => [
                'required',
                'string',
                'in:STUDENT,LECTURER,HOD,ICT_ADMIN',
            ],

            'department_id' => [
                'nullable',
                'integer',
                'exists:departments,id',
            ],

            'year_level_id' => [
                'nullable',
                'integer',
                'exists:year_levels,id',
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        $role = $validated['role'];

        /*
        |--------------------------------------------------------------------------
        | Role-specific Validation
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $role,
                ['STUDENT', 'LECTURER', 'HOD'],
                true
            ) &&
            empty($validated['department_id'])
        ) {
            return response()->json([
                'message' =>
                    'A department is required for this role.',

                'errors' => [
                    'department_id' => [
                        'Please select a department.',
                    ],
                ],
            ], 422);
        }

        if (
            $role === 'STUDENT' &&
            empty($validated['year_level_id'])
        ) {
            return response()->json([
                'message' =>
                    'A year level is required for students.',

                'errors' => [
                    'year_level_id' => [
                        'Please select a year level.',
                    ],
                ],
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Protect Current ICT Admin
        |--------------------------------------------------------------------------
        |
        | Prevent the logged-in ICT Admin from accidentally disabling
        | their own account while they are currently administering SmartLog.
        |
        */

        $currentAdmin = $request->user();

        if (
            $currentAdmin->id === $id &&
            $validated['is_active'] === false
        ) {
            return response()->json([
                'message' =>
                    'You cannot deactivate your own ICT Admin account.',
            ], 422);
        }

        if (
            $currentAdmin->id === $id &&
            $role !== 'ICT_ADMIN'
        ) {
            return response()->json([
                'message' =>
                    'You cannot change your own ICT Admin role.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Role Cleanup Rules
        |--------------------------------------------------------------------------
        */

        if ($role === 'ICT_ADMIN') {
            $validated['department_id'] = null;
            $validated['year_level_id'] = null;
        }

        if (
            in_array(
                $role,
                ['LECTURER', 'HOD'],
                true
            )
        ) {
            $validated['year_level_id'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Prepare Update
        |--------------------------------------------------------------------------
        */

        $updateData = [
            'dwu_id' => strtoupper(
                trim($validated['dwu_id'])
            ),

            'name' => trim(
                $validated['name']
            ),

            'email' => strtolower(
                trim($validated['email'])
            ),

            'phone' => isset($validated['phone'])
                && trim($validated['phone']) !== ''
                    ? trim($validated['phone'])
                    : null,

            'role' => $role,

            'department_id' =>
                $validated['department_id'] ?? null,

            'year_level_id' =>
                $validated['year_level_id'] ?? null,

            'is_active' =>
                $validated['is_active'],

            'updated_at' => now(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Optional Password Reset
        |--------------------------------------------------------------------------
        |
        | If password is blank, the existing password remains unchanged.
        |
        */

        if (
            isset($validated['password']) &&
            $validated['password'] !== ''
        ) {
            $updateData['password'] =
                Hash::make(
                    $validated['password']
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Update User
        |--------------------------------------------------------------------------
        */

        DB::table('users')
            ->where('id', $id)
            ->update($updateData);

        /*
        |--------------------------------------------------------------------------
        | Load Updated Account
        |--------------------------------------------------------------------------
        */

        $user = DB::table('users as u')
            ->leftJoin(
                'departments as d',
                'u.department_id',
                '=',
                'd.id'
            )
            ->leftJoin(
                'year_levels as yl',
                'u.year_level_id',
                '=',
                'yl.id'
            )
            ->where('u.id', $id)
            ->select(
                'u.id',
                'u.dwu_id',
                'u.name',
                'u.email',
                'u.phone',
                'u.role',
                'u.is_active',
                'u.department_id',
                'd.department_code',
                'd.department_name',
                'u.year_level_id',
                'yl.year_name',
                'yl.year_number',
                'u.created_at',
                'u.updated_at'
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' =>
                'SmartLog user account updated successfully.',

            'user' => [
                'id' => $user->id,

                'dwu_id' =>
                    $user->dwu_id,

                'name' =>
                    $user->name,

                'email' =>
                    $user->email,

                'phone' =>
                    $user->phone,

                'role' =>
                    $user->role,

                'department' =>
                    $user->department_id
                        ? [
                            'id' =>
                                $user->department_id,

                            'code' =>
                                $user->department_code,

                            'name' =>
                                $user->department_name,
                        ]
                        : null,

                'year_level' =>
                    $user->year_level_id
                        ? [
                            'id' =>
                                $user->year_level_id,

                            'name' =>
                                $user->year_name,

                            'number' =>
                                $user->year_number,
                        ]
                        : null,

                'is_active' =>
                    (bool) $user->is_active,

                'created_at' =>
                    $user->created_at,

                'updated_at' =>
                    $user->updated_at,
            ],

            'access' => [
                'mode' =>
                    'SYSTEM_ADMIN',

                'updated_by' =>
                    $currentAdmin->id,
            ],
        ]);
    }
        /**
     * ICT Admin department management list.
     */
    public function departments(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Optional Filters
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) $request->query('search', '')
        );

        $status = strtolower(
            trim(
                (string) $request->query(
                    'status',
                    ''
                )
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Department Query
        |--------------------------------------------------------------------------
        */

        $query = DB::table('departments')
            ->select(
                'id',
                'department_code',
                'department_name',
                'is_active',
                'created_at',
                'updated_at'
            );

        if ($search !== '') {
            $query->where(
                function ($subQuery) use ($search) {
                    $subQuery
                        ->where(
                            'department_code',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'department_name',
                            'like',
                            '%' . $search . '%'
                        );
                }
            );
        }

        if ($status === 'active') {
            $query->where(
                'is_active',
                1
            );
        }

        if ($status === 'inactive') {
            $query->where(
                'is_active',
                0
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Load Departments
        |--------------------------------------------------------------------------
        */

        $departments = $query
            ->orderBy('department_name')
            ->get()
            ->map(
                function ($department) {
                    /*
                    |--------------------------------------------------------------------------
                    | Department Statistics
                    |--------------------------------------------------------------------------
                    */

                    $students = DB::table('users')
                        ->where(
                            'department_id',
                            $department->id
                        )
                        ->where(
                            'role',
                            'STUDENT'
                        )
                        ->count();

                    $lecturers = DB::table('users')
                        ->where(
                            'department_id',
                            $department->id
                        )
                        ->where(
                            'role',
                            'LECTURER'
                        )
                        ->count();

                    $hods = DB::table('users')
                        ->where(
                            'department_id',
                            $department->id
                        )
                        ->where(
                            'role',
                            'HOD'
                        )
                        ->count();

                    $units = DB::table('units')
                        ->where(
                            'department_id',
                            $department->id
                        )
                        ->count();

                    return [
                        'id' =>
                            $department->id,

                        'department_code' =>
                            $department->department_code,

                        'department_name' =>
                            $department->department_name,

                        'is_active' =>
                            (bool) $department->is_active,

                        'statistics' => [
                            'students' =>
                                $students,

                            'lecturers' =>
                                $lecturers,

                            'hods' =>
                                $hods,

                            'units' =>
                                $units,
                        ],

                        'created_at' =>
                            $department->created_at,

                        'updated_at' =>
                            $department->updated_at,
                    ];
                }
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Overall Statistics
        |--------------------------------------------------------------------------
        */

        $totalDepartments =
            DB::table('departments')
                ->count();

        $activeDepartments =
            DB::table('departments')
                ->where(
                    'is_active',
                    1
                )
                ->count();

        $inactiveDepartments =
            DB::table('departments')
                ->where(
                    'is_active',
                    0
                )
                ->count();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' =>
                'ICT Admin departments loaded successfully.',

            'filters' => [
                'search' =>
                    $search !== ''
                        ? $search
                        : null,

                'status' =>
                    $status !== ''
                        ? $status
                        : null,
            ],

            'summary' => [
                'total' =>
                    $totalDepartments,

                'active' =>
                    $activeDepartments,

                'inactive' =>
                    $inactiveDepartments,

                'returned_departments' =>
                    $departments->count(),
            ],

            'departments' =>
                $departments,

            'options' => [
                'statuses' => [
                    'active',
                    'inactive',
                ],
            ],

            'access' => [
                'mode' =>
                    'SYSTEM_ADMIN',

                'can_manage_departments' =>
                    true,
            ],
        ]);
    }


    /**
     * Create a new SmartLog department.
     */
    public function createDepartment(
        Request $request
    ) {
        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'department_code' => [
                    'required',
                    'string',
                    'max:50',
                    'unique:departments,department_code',
                ],

                'department_name' => [
                    'required',
                    'string',
                    'max:255',
                    'unique:departments,department_name',
                ],

                'is_active' => [
                    'nullable',
                    'boolean',
                ],
            ]);

        /*
        |--------------------------------------------------------------------------
        | Create Department
        |--------------------------------------------------------------------------
        */

        $departmentId =
            DB::table('departments')
                ->insertGetId([
                    'department_code' =>
                        strtoupper(
                            trim(
                                $validated[
                                    'department_code'
                                ]
                            )
                        ),

                    'department_name' =>
                        trim(
                            $validated[
                                'department_name'
                            ]
                        ),

                    'is_active' =>
                        $validated[
                            'is_active'
                        ] ?? true,

                    'created_at' =>
                        now(),

                    'updated_at' =>
                        now(),
                ]);

        /*
        |--------------------------------------------------------------------------
        | Load Created Department
        |--------------------------------------------------------------------------
        */

        $department =
            DB::table('departments')
                ->where(
                    'id',
                    $departmentId
                )
                ->first();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' =>
                'SmartLog department created successfully.',

            'department' => [
                'id' =>
                    $department->id,

                'department_code' =>
                    $department->department_code,

                'department_name' =>
                    $department->department_name,

                'is_active' =>
                    (bool) $department->is_active,

                'created_at' =>
                    $department->created_at,

                'updated_at' =>
                    $department->updated_at,
            ],

            'access' => [
                'mode' =>
                    'SYSTEM_ADMIN',

                'created_by' =>
                    $request->user()->id,
            ],
        ], 201);
    }


    /**
     * Update an existing SmartLog department.
     */
    public function updateDepartment(
        Request $request,
        int $id
    ) {
        /*
        |--------------------------------------------------------------------------
        | Find Department
        |--------------------------------------------------------------------------
        */

        $existingDepartment =
            DB::table('departments')
                ->where(
                    'id',
                    $id
                )
                ->first();

        if (!$existingDepartment) {
            return response()->json([
                'message' =>
                    'SmartLog department not found.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'department_code' => [
                    'required',
                    'string',
                    'max:50',
                    'unique:departments,department_code,' .
                        $id,
                ],

                'department_name' => [
                    'required',
                    'string',
                    'max:255',
                    'unique:departments,department_name,' .
                        $id,
                ],

                'is_active' => [
                    'required',
                    'boolean',
                ],
            ]);

        /*
        |--------------------------------------------------------------------------
        | Safety Check Before Deactivation
        |--------------------------------------------------------------------------
        |
        | We allow a department to be deactivated even if historical
        | records exist, but active users should not remain assigned to
        | an inactive department.
        |
        */

        if (
            $validated['is_active'] === false
        ) {
            $activeUsers =
                DB::table('users')
                    ->where(
                        'department_id',
                        $id
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->count();

            if ($activeUsers > 0) {
                return response()->json([
                    'message' =>
                        'This department cannot be deactivated while it has active users.',

                    'errors' => [
                        'is_active' => [
                            'Deactivate or move the department users first.',
                        ],
                    ],
                ], 422);
            }

            $activeUnits =
                DB::table('units')
                    ->where(
                        'department_id',
                        $id
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->count();

            if ($activeUnits > 0) {
                return response()->json([
                    'message' =>
                        'This department cannot be deactivated while it has active units.',

                    'errors' => [
                        'is_active' => [
                            'Deactivate the department units first.',
                        ],
                    ],
                ], 422);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Update Department
        |--------------------------------------------------------------------------
        */

        DB::table('departments')
            ->where(
                'id',
                $id
            )
            ->update([
                'department_code' =>
                    strtoupper(
                        trim(
                            $validated[
                                'department_code'
                            ]
                        )
                    ),

                'department_name' =>
                    trim(
                        $validated[
                            'department_name'
                        ]
                    ),

                'is_active' =>
                    $validated[
                        'is_active'
                    ],

                'updated_at' =>
                    now(),
            ]);

        /*
        |--------------------------------------------------------------------------
        | Load Updated Department
        |--------------------------------------------------------------------------
        */

        $department =
            DB::table('departments')
                ->where(
                    'id',
                    $id
                )
                ->first();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' =>
                'SmartLog department updated successfully.',

            'department' => [
                'id' =>
                    $department->id,

                'department_code' =>
                    $department->department_code,

                'department_name' =>
                    $department->department_name,

                'is_active' =>
                    (bool) $department->is_active,

                'created_at' =>
                    $department->created_at,

                'updated_at' =>
                    $department->updated_at,
            ],

            'access' => [
                'mode' =>
                    'SYSTEM_ADMIN',

                'updated_by' =>
                    $request->user()->id,
            ],
        ]);
    }
        /**
     * ICT Admin unit management list.
     *
     * Optional filters:
     * - search
     * - department_id
     * - year_level_id
     * - semester_id
     * - status
     * - requires_logbook
     */
    public function units(Request $request)
    {
        $search = trim(
            (string) $request->query('search', '')
        );

        $departmentId =
            $request->query('department_id');

        $yearLevelId =
            $request->query('year_level_id');

        $semesterId =
            $request->query('semester_id');

        $status = strtolower(
            trim(
                (string) $request->query(
                    'status',
                    ''
                )
            )
        );

        $requiresLogbook = strtolower(
            trim(
                (string) $request->query(
                    'requires_logbook',
                    ''
                )
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Unit Query
        |--------------------------------------------------------------------------
        */

        $query = DB::table('units as u')
            ->join(
                'departments as d',
                'u.department_id',
                '=',
                'd.id'
            )
            ->join(
                'year_levels as yl',
                'u.year_level_id',
                '=',
                'yl.id'
            )
            ->join(
                'semesters as s',
                'u.semester_id',
                '=',
                's.id'
            )
            ->select(
                'u.id',
                'u.unit_code',
                'u.unit_name',

                'u.department_id',
                'd.department_code',
                'd.department_name',

                'u.year_level_id',
                'yl.year_name',
                'yl.year_number',

                'u.semester_id',
                's.semester_name',
                's.semester_number',

                'u.requires_logbook',
                'u.is_active',
                'u.created_at',
                'u.updated_at'
            );

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($search !== '') {
            $query->where(
                function ($subQuery) use ($search) {
                    $subQuery
                        ->where(
                            'u.unit_code',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'u.unit_name',
                            'like',
                            '%' . $search . '%'
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Department Filter
        |--------------------------------------------------------------------------
        */

        if (
            $departmentId !== null &&
            $departmentId !== ''
        ) {
            $query->where(
                'u.department_id',
                (int) $departmentId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Year Level Filter
        |--------------------------------------------------------------------------
        */

        if (
            $yearLevelId !== null &&
            $yearLevelId !== ''
        ) {
            $query->where(
                'u.year_level_id',
                (int) $yearLevelId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Semester Filter
        |--------------------------------------------------------------------------
        */

        if (
            $semesterId !== null &&
            $semesterId !== ''
        ) {
            $query->where(
                'u.semester_id',
                (int) $semesterId
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if ($status === 'active') {
            $query->where(
                'u.is_active',
                1
            );
        }

        if ($status === 'inactive') {
            $query->where(
                'u.is_active',
                0
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Requires Logbook Filter
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $requiresLogbook,
                ['yes', 'true', '1'],
                true
            )
        ) {
            $query->where(
                'u.requires_logbook',
                1
            );
        }

        if (
            in_array(
                $requiresLogbook,
                ['no', 'false', '0'],
                true
            )
        ) {
            $query->where(
                'u.requires_logbook',
                0
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Load Units
        |--------------------------------------------------------------------------
        */

        $units = $query
            ->orderBy('d.department_name')
            ->orderBy('yl.year_number')
            ->orderBy('s.semester_number')
            ->orderBy('u.unit_code')
            ->get()
            ->map(
                function ($unit) {
                    /*
                    |--------------------------------------------------------------------------
                    | Enrollment Count
                    |--------------------------------------------------------------------------
                    */

                    $enrollmentCount =
                        DB::table('enrollments')
                            ->where(
                                'unit_id',
                                $unit->id
                            )
                            ->count();

                    /*
                    |--------------------------------------------------------------------------
                    | Logbook Count
                    |--------------------------------------------------------------------------
                    |
                    | student_logbooks does NOT have unit_id.
                    | Relationship:
                    |
                    | student_logbooks.enrollment_id
                    |     -> enrollments.id
                    |     -> enrollments.unit_id
                    |
                    */

                    $logbookCount =
                        DB::table(
                            'student_logbooks as sl'
                        )
                            ->join(
                                'enrollments as e',
                                'sl.enrollment_id',
                                '=',
                                'e.id'
                            )
                            ->where(
                                'e.unit_id',
                                $unit->id
                            )
                            ->count();

                    return [
                        'id' =>
                            $unit->id,

                        'unit_code' =>
                            $unit->unit_code,

                        'unit_name' =>
                            $unit->unit_name,

                        'department' => [
                            'id' =>
                                $unit->department_id,

                            'code' =>
                                $unit->department_code,

                            'name' =>
                                $unit->department_name,
                        ],

                        'year_level' => [
                            'id' =>
                                $unit->year_level_id,

                            'name' =>
                                $unit->year_name,

                            'number' =>
                                $unit->year_number,
                        ],

                        'semester' => [
                            'id' =>
                                $unit->semester_id,

                            'name' =>
                                $unit->semester_name,

                            'number' =>
                                $unit->semester_number,
                        ],

                        'requires_logbook' =>
                            (bool) $unit->requires_logbook,

                        'is_active' =>
                            (bool) $unit->is_active,

                        'statistics' => [
                            'enrollments' =>
                                $enrollmentCount,

                            'logbooks' =>
                                $logbookCount,
                        ],

                        'created_at' =>
                            $unit->created_at,

                        'updated_at' =>
                            $unit->updated_at,
                    ];
                }
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Overall Statistics
        |--------------------------------------------------------------------------
        */

        $totalUnits =
            DB::table('units')
                ->count();

        $activeUnits =
            DB::table('units')
                ->where(
                    'is_active',
                    1
                )
                ->count();

        $inactiveUnits =
            DB::table('units')
                ->where(
                    'is_active',
                    0
                )
                ->count();

        $logbookUnits =
            DB::table('units')
                ->where(
                    'requires_logbook',
                    1
                )
                ->count();

        /*
        |--------------------------------------------------------------------------
        | Form Options
        |--------------------------------------------------------------------------
        */

        $departments =
            DB::table('departments')
                ->orderBy(
                    'department_name'
                )
                ->get([
                    'id',
                    'department_code',
                    'department_name',
                    'is_active',
                ]);

        $yearLevels =
            DB::table('year_levels')
                ->orderBy(
                    'year_number'
                )
                ->get([
                    'id',
                    'year_name',
                    'year_number',
                ]);

        $semesters =
            DB::table('semesters')
                ->orderBy(
                    'semester_number'
                )
                ->get([
                    'id',
                    'semester_name',
                    'semester_number',
                ]);

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' =>
                'ICT Admin units loaded successfully.',

            'filters' => [
                'search' =>
                    $search !== ''
                        ? $search
                        : null,

                'department_id' =>
                    $departmentId !== null &&
                    $departmentId !== ''
                        ? (int) $departmentId
                        : null,

                'year_level_id' =>
                    $yearLevelId !== null &&
                    $yearLevelId !== ''
                        ? (int) $yearLevelId
                        : null,

                'semester_id' =>
                    $semesterId !== null &&
                    $semesterId !== ''
                        ? (int) $semesterId
                        : null,

                'status' =>
                    $status !== ''
                        ? $status
                        : null,

                'requires_logbook' =>
                    $requiresLogbook !== ''
                        ? $requiresLogbook
                        : null,
            ],

            'summary' => [
                'total' =>
                    $totalUnits,

                'active' =>
                    $activeUnits,

                'inactive' =>
                    $inactiveUnits,

                'requiring_logbook' =>
                    $logbookUnits,

                'returned_units' =>
                    $units->count(),
            ],

            'units' =>
                $units,

            'options' => [
                'statuses' => [
                    'active',
                    'inactive',
                ],

                'departments' =>
                    $departments,

                'year_levels' =>
                    $yearLevels,

                'semesters' =>
                    $semesters,
            ],

            'access' => [
                'mode' =>
                    'SYSTEM_ADMIN',

                'can_manage_units' =>
                    true,
            ],
        ]);
    }
        /**
     * Create a new SmartLog unit.
     */
    public function createUnit(
        Request $request
    ) {
        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'unit_code' => [
                    'required',
                    'string',
                    'max:50',
                    'unique:units,unit_code',
                ],

                'unit_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'department_id' => [
                    'required',
                    'integer',
                    'exists:departments,id',
                ],

                'year_level_id' => [
                    'required',
                    'integer',
                    'exists:year_levels,id',
                ],

                'semester_id' => [
                    'required',
                    'integer',
                    'exists:semesters,id',
                ],

                'requires_logbook' => [
                    'required',
                    'boolean',
                ],

                'is_active' => [
                    'nullable',
                    'boolean',
                ],
            ]);

        /*
        |--------------------------------------------------------------------------
        | Check Selected Department
        |--------------------------------------------------------------------------
        */

        $department =
            DB::table('departments')
                ->where(
                    'id',
                    $validated[
                        'department_id'
                    ]
                )
                ->first();

        if (!$department) {
            return response()->json([
                'message' =>
                    'Selected department was not found.',
            ], 422);
        }

        if (
            !(bool) $department->is_active
        ) {
            return response()->json([
                'message' =>
                    'The selected department is inactive.',

                'errors' => [
                    'department_id' => [
                        'Select an active department.',
                    ],
                ],
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Create Unit
        |--------------------------------------------------------------------------
        */

        $unitId =
            DB::table('units')
                ->insertGetId([
                    'unit_code' =>
                        strtoupper(
                            trim(
                                $validated[
                                    'unit_code'
                                ]
                            )
                        ),

                    'unit_name' =>
                        trim(
                            $validated[
                                'unit_name'
                            ]
                        ),

                    'department_id' =>
                        $validated[
                            'department_id'
                        ],

                    'year_level_id' =>
                        $validated[
                            'year_level_id'
                        ],

                    'semester_id' =>
                        $validated[
                            'semester_id'
                        ],

                    'requires_logbook' =>
                        $validated[
                            'requires_logbook'
                        ],

                    'is_active' =>
                        $validated[
                            'is_active'
                        ] ?? true,

                    'created_at' =>
                        now(),

                    'updated_at' =>
                        now(),
                ]);

        /*
        |--------------------------------------------------------------------------
        | Load Created Unit
        |--------------------------------------------------------------------------
        */

        $unit =
            DB::table('units as u')
                ->join(
                    'departments as d',
                    'u.department_id',
                    '=',
                    'd.id'
                )
                ->join(
                    'year_levels as yl',
                    'u.year_level_id',
                    '=',
                    'yl.id'
                )
                ->join(
                    'semesters as s',
                    'u.semester_id',
                    '=',
                    's.id'
                )
                ->where(
                    'u.id',
                    $unitId
                )
                ->select(
                    'u.id',
                    'u.unit_code',
                    'u.unit_name',

                    'u.department_id',
                    'd.department_code',
                    'd.department_name',

                    'u.year_level_id',
                    'yl.year_name',
                    'yl.year_number',

                    'u.semester_id',
                    's.semester_name',
                    's.semester_number',

                    'u.requires_logbook',
                    'u.is_active',
                    'u.created_at',
                    'u.updated_at'
                )
                ->first();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' =>
                'SmartLog unit created successfully.',

            'unit' => [
                'id' =>
                    $unit->id,

                'unit_code' =>
                    $unit->unit_code,

                'unit_name' =>
                    $unit->unit_name,

                'department' => [
                    'id' =>
                        $unit->department_id,

                    'code' =>
                        $unit->department_code,

                    'name' =>
                        $unit->department_name,
                ],

                'year_level' => [
                    'id' =>
                        $unit->year_level_id,

                    'name' =>
                        $unit->year_name,

                    'number' =>
                        $unit->year_number,
                ],

                'semester' => [
                    'id' =>
                        $unit->semester_id,

                    'name' =>
                        $unit->semester_name,

                    'number' =>
                        $unit->semester_number,
                ],

                'requires_logbook' =>
                    (bool) $unit->requires_logbook,

                'is_active' =>
                    (bool) $unit->is_active,

                'created_at' =>
                    $unit->created_at,

                'updated_at' =>
                    $unit->updated_at,
            ],

            'access' => [
                'mode' =>
                    'SYSTEM_ADMIN',

                'created_by' =>
                    $request->user()->id,
            ],
        ], 201);
    }
        /**
     * Update an existing SmartLog unit.
     */
    public function updateUnit(
        Request $request,
        int $id
    ) {
        /*
        |--------------------------------------------------------------------------
        | Find Unit
        |--------------------------------------------------------------------------
        */

        $existingUnit =
            DB::table('units')
                ->where(
                    'id',
                    $id
                )
                ->first();

        if (!$existingUnit) {
            return response()->json([
                'message' =>
                    'SmartLog unit not found.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'unit_code' => [
                    'required',
                    'string',
                    'max:50',
                    'unique:units,unit_code,' .
                        $id,
                ],

                'unit_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'department_id' => [
                    'required',
                    'integer',
                    'exists:departments,id',
                ],

                'year_level_id' => [
                    'required',
                    'integer',
                    'exists:year_levels,id',
                ],

                'semester_id' => [
                    'required',
                    'integer',
                    'exists:semesters,id',
                ],

                'requires_logbook' => [
                    'required',
                    'boolean',
                ],

                'is_active' => [
                    'required',
                    'boolean',
                ],
            ]);

        /*
        |--------------------------------------------------------------------------
        | Check Selected Department
        |--------------------------------------------------------------------------
        */

        $department =
            DB::table('departments')
                ->where(
                    'id',
                    $validated[
                        'department_id'
                    ]
                )
                ->first();

        if (!$department) {
            return response()->json([
                'message' =>
                    'Selected department was not found.',
            ], 422);
        }

        if (
            !(bool) $department->is_active
        ) {
            return response()->json([
                'message' =>
                    'The selected department is inactive.',

                'errors' => [
                    'department_id' => [
                        'Select an active department.',
                    ],
                ],
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Safety Check Before Deactivation
        |--------------------------------------------------------------------------
        |
        | Do not deactivate a unit while students are still enrolled.
        |
        */

        if (
            $validated['is_active'] === false
        ) {
            $enrollmentCount =
                DB::table('enrollments')
                    ->where(
                        'unit_id',
                        $id
                    )
                    ->count();

            if ($enrollmentCount > 0) {
                return response()->json([
                    'message' =>
                        'This unit cannot be deactivated while it has enrollments.',

                    'errors' => [
                        'is_active' => [
                            'Remove the students from the unit before deactivating it.',
                        ],
                    ],
                ], 422);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Update Unit
        |--------------------------------------------------------------------------
        */

        DB::table('units')
            ->where(
                'id',
                $id
            )
            ->update([
                'unit_code' =>
                    strtoupper(
                        trim(
                            $validated[
                                'unit_code'
                            ]
                        )
                    ),

                'unit_name' =>
                    trim(
                        $validated[
                            'unit_name'
                        ]
                    ),

                'department_id' =>
                    $validated[
                        'department_id'
                    ],

                'year_level_id' =>
                    $validated[
                        'year_level_id'
                    ],

                'semester_id' =>
                    $validated[
                        'semester_id'
                    ],

                'requires_logbook' =>
                    $validated[
                        'requires_logbook'
                    ],

                'is_active' =>
                    $validated[
                        'is_active'
                    ],

                'updated_at' =>
                    now(),
            ]);

        /*
        |--------------------------------------------------------------------------
        | Load Updated Unit
        |--------------------------------------------------------------------------
        */

        $unit =
            DB::table('units as u')
                ->join(
                    'departments as d',
                    'u.department_id',
                    '=',
                    'd.id'
                )
                ->join(
                    'year_levels as yl',
                    'u.year_level_id',
                    '=',
                    'yl.id'
                )
                ->join(
                    'semesters as s',
                    'u.semester_id',
                    '=',
                    's.id'
                )
                ->where(
                    'u.id',
                    $id
                )
                ->select(
                    'u.id',
                    'u.unit_code',
                    'u.unit_name',

                    'u.department_id',
                    'd.department_code',
                    'd.department_name',

                    'u.year_level_id',
                    'yl.year_name',
                    'yl.year_number',

                    'u.semester_id',
                    's.semester_name',
                    's.semester_number',

                    'u.requires_logbook',
                    'u.is_active',
                    'u.created_at',
                    'u.updated_at'
                )
                ->first();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' =>
                'SmartLog unit updated successfully.',

            'unit' => [
                'id' =>
                    $unit->id,

                'unit_code' =>
                    $unit->unit_code,

                'unit_name' =>
                    $unit->unit_name,

                'department' => [
                    'id' =>
                        $unit->department_id,

                    'code' =>
                        $unit->department_code,

                    'name' =>
                        $unit->department_name,
                ],

                'year_level' => [
                    'id' =>
                        $unit->year_level_id,

                    'name' =>
                        $unit->year_name,

                    'number' =>
                        $unit->year_number,
                ],

                'semester' => [
                    'id' =>
                        $unit->semester_id,

                    'name' =>
                        $unit->semester_name,

                    'number' =>
                        $unit->semester_number,
                ],

                'requires_logbook' =>
                    (bool) $unit->requires_logbook,

                'is_active' =>
                    (bool) $unit->is_active,

                'created_at' =>
                    $unit->created_at,

                'updated_at' =>
                    $unit->updated_at,
            ],

            'access' => [
                'mode' =>
                    'SYSTEM_ADMIN',

                'updated_by' =>
                    $request->user()->id,
            ],
        ]);
    }
}