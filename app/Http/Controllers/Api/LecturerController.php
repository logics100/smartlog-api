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

    // Confirm this lecturer is assigned to the unit.
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

    /*
    |--------------------------------------------------------------------------
    | 1. Make sure this lecturer is assigned to the unit
    |--------------------------------------------------------------------------
    */

    $assigned = DB::table('lecturer_units')
        ->where('lecturer_id', $lecturer->id)
        ->where('unit_id', $unitId)
        ->exists();

    if (!$assigned) {
        return response()->json([
            'message' => 'You are not assigned to this unit.'
        ], 403);
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Validate the student sent by the application
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | 3. Get the unit
    |--------------------------------------------------------------------------
    */

    $unit = DB::table('units')
        ->where('id', $unitId)
        ->where('is_active', true)
        ->first();

    if (!$unit) {
        return response()->json([
            'message' => 'Unit not found or inactive.'
        ], 404);
    }

    /*
    |--------------------------------------------------------------------------
    | 4. Student must belong to the same department as the unit
    |--------------------------------------------------------------------------
    */

    if ($student->department_id != $unit->department_id) {
        return response()->json([
            'message' => 'This student does not belong to the department for this unit.'
        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORTANT:
    | We do NOT compare the student's current year level with the unit's
    | year level.
    |
    | This allows, for example, a Year 4 student to take a Year 3 unit
    | when the lecturer/department permits it.
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | 5. Prevent duplicate enrollment
    |--------------------------------------------------------------------------
    */

    $existingEnrollment = DB::table('enrollments')
        ->where('student_id', $student->id)
        ->where('unit_id', $unitId)
        ->first();

    if ($existingEnrollment) {
        return response()->json([
            'message' => 'This student is already enrolled in this unit.',
            'enrollment_id' => $existingEnrollment->id,
        ], 409);
    }

    /*
    |--------------------------------------------------------------------------
    | 6. Create enrollment and logbook together
    |--------------------------------------------------------------------------
    */

    return DB::transaction(function () use (
        $lecturer,
        $student,
        $unit,
        $unitId
    ) {

        $enrollmentId = DB::table('enrollments')->insertGetId([
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

        /*
        |--------------------------------------------------------------------------
        | 7. Automatically assign logbook if required
        |--------------------------------------------------------------------------
        */

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

                $logbookMessage = 'Logbook assigned automatically.';
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

    // Check that lecturer is assigned to this unit.
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
        ->leftJoin('year_levels', 'users.year_level_id', '=', 'year_levels.id')
        ->where('users.role', 'STUDENT')
        ->where('users.is_active', true)

        // Only show students from the same department as the unit.
        ->where('users.department_id', $unit->department_id)

        ->where(function ($query) use ($search) {
            $query->where('users.dwu_id', 'like', "%{$search}%")
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

    // Add whether each student is already enrolled.
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
}