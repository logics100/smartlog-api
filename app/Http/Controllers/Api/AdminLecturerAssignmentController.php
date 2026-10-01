<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminLecturerAssignmentController extends Controller
{
    // Display available lecturers and existing assignments.
    public function index()
    {
        return response()->json([
            'lecturers' => DB::table('users')
                ->where('role', 'LECTURER')
                ->where('is_active', 1)
                ->select('id', 'name', 'department_id')
                ->orderBy('name')
                ->get(),

            'assignments' => DB::table('lecturer_units as lu')
                ->join('users as u', 'u.id', '=', 'lu.lecturer_id')
                ->join('units as un', 'un.id', '=', 'lu.unit_id')
                ->select(
                    'lu.id',
                    'lu.lecturer_id',
                    'lu.unit_id',
                    'u.name as lecturer_name',
                    'un.unit_code',
                    'un.unit_name'
                )
                ->orderBy('un.unit_code')
                ->get(),
        ]);
    }

    // Assign a lecturer to a unit.
    public function store(Request $request)
    {
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

        if (!$lecturer || !$unit) {
            throw ValidationException::withMessages([
                'assignment' => 'Select an active lecturer and active unit.',
            ]);
        }

        if ($lecturer->department_id != $unit->department_id) {
            throw ValidationException::withMessages([
                'lecturer_id' => 'The lecturer must belong to the unit department.',
            ]);
        }

        $exists = DB::table('lecturer_units')
            ->where('lecturer_id', $lecturer->id)
            ->where('unit_id', $unit->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This lecturer is already assigned to this unit.',
            ], 409);
        }

        DB::table('lecturer_units')->insert([
            'lecturer_id' => $lecturer->id,
            'unit_id' => $unit->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Lecturer assigned successfully.',
        ], 201);
    }

    // Only removes the lecturer-unit link; student records remain unchanged.
    public function destroy(int $id)
    {
        $assignment = DB::table('lecturer_units')->where('id', $id)->first();
        if (!$assignment) {
            return response()->json(['message' => 'Assignment not found.'], 404);
        }
        DB::table('lecturer_units')->where('id', $id)->delete();
        return response()->json(['message' => 'Lecturer unassigned successfully.']);
    }
}