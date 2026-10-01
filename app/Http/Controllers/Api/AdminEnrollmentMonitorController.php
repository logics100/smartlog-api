<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminEnrollmentMonitorController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['unit_id' => 'nullable|integer|exists:units,id', 'search' => 'nullable|string|max:100']);
        $query = DB::table('enrollments as e')
            ->join('users as student', 'student.id', '=', 'e.student_id')
            ->join('units as unit', 'unit.id', '=', 'e.unit_id')
            ->leftJoin('users as lecturer', 'lecturer.id', '=', 'e.enrolled_by')
            ->select('e.id', 'e.student_id', 'e.unit_id', 'e.status',
                'e.enrollment_date', 'student.name as student_name',
                'student.dwu_id', 'student.department_id as student_department_id',
                'unit.unit_code', 'unit.unit_name', 'unit.department_id as unit_department_id',
                'lecturer.name as enrolled_by_name');
        if ($request->filled('unit_id')) $query->where('e.unit_id', (int) $request->query('unit_id'));
        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('student.name', 'like', '%' . $search . '%')
                  ->orWhere('student.dwu_id', 'like', '%' . $search . '%')
                  ->orWhere('unit.unit_code', 'like', '%' . $search . '%');
            });
        }
        return response()->json(['enrollments' => $query->orderByDesc('e.id')->paginate(30)]);
    }
}
