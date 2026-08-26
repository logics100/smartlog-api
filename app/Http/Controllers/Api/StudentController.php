<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function myUnits(Request $request)
    {
        $student = $request->user();

        $units = DB::table('enrollments')
            ->join('units', 'enrollments.unit_id', '=', 'units.id')
            ->join('departments', 'units.department_id', '=', 'departments.id')
            ->join('year_levels', 'units.year_level_id', '=', 'year_levels.id')
            ->join('semesters', 'units.semester_id', '=', 'semesters.id')
            ->leftJoin(
                'student_logbooks',
                'enrollments.id',
                '=',
                'student_logbooks.enrollment_id'
            )
            ->where('enrollments.student_id', $student->id)
            ->where('enrollments.status', 'ACTIVE')
            ->select(
                'units.id',
                'units.unit_code',
                'units.unit_name',
                'departments.department_name',
                'year_levels.year_name',
                'semesters.semester_name',
                'enrollments.id as enrollment_id',
                'student_logbooks.id as student_logbook_id',
                'student_logbooks.status as logbook_status',
                'student_logbooks.completion_percentage'
            )
            ->get();

        return response()->json([
            'student' => [
                'id' => $student->id,
                'dwu_id' => $student->dwu_id,
                'name' => $student->name,
            ],
            'units' => $units,
        ]);
    }

    public function myLogbooks(Request $request)
    {
        $student = $request->user();

        $logbooks = DB::table('student_logbooks')
            ->join(
                'enrollments',
                'student_logbooks.enrollment_id',
                '=',
                'enrollments.id'
            )
            ->join(
                'logbook_templates',
                'student_logbooks.logbook_template_id',
                '=',
                'logbook_templates.id'
            )
            ->join(
                'units',
                'enrollments.unit_id',
                '=',
                'units.id'
            )
            ->where('enrollments.student_id', $student->id)
            ->select(
                'student_logbooks.id',
                'student_logbooks.status',
                'student_logbooks.completion_percentage',
                'student_logbooks.assigned_date',
                'student_logbooks.due_date',
                'logbook_templates.template_name',
                'units.id as unit_id',
                'units.unit_code',
                'units.unit_name'
            )
            ->get();

        return response()->json([
            'logbooks' => $logbooks,
        ]);
    }

    public function showLogbook(Request $request, $logbookId)
    {
        $student = $request->user();

        $logbook = DB::table('student_logbooks')
            ->join(
                'enrollments',
                'student_logbooks.enrollment_id',
                '=',
                'enrollments.id'
            )
            ->join(
                'logbook_templates',
                'student_logbooks.logbook_template_id',
                '=',
                'logbook_templates.id'
            )
            ->join(
                'units',
                'enrollments.unit_id',
                '=',
                'units.id'
            )
            ->where('student_logbooks.id', $logbookId)
            ->where('enrollments.student_id', $student->id)
            ->select(
                'student_logbooks.id',
                'student_logbooks.status',
                'student_logbooks.completion_percentage',
                'student_logbooks.assigned_date',
                'student_logbooks.due_date',
                'logbook_templates.id as template_id',
                'logbook_templates.template_name',
                'units.unit_code',
                'units.unit_name'
            )
            ->first();

        if (!$logbook) {
            return response()->json([
                'message' => 'Logbook not found.'
            ], 404);
        }

        $sections = DB::table('logbook_sections')
            ->where('logbook_template_id', $logbook->template_id)
            ->orderBy('display_order')
            ->get();

        foreach ($sections as $section) {
            $section->items = DB::table('logbook_items')
                ->where('logbook_section_id', $section->id)
                ->where('is_active', true)
                ->orderBy('display_order')
                ->get();

            foreach ($section->items as $item) {
                $item->requirements = DB::table('logbook_item_requirements')
                    ->where('logbook_item_id', $item->id)
                    ->orderBy('sequence_number')
                    ->get();
            }
        }

        return response()->json([
            'logbook' => $logbook,
            'sections' => $sections,
        ]);
    }
    public function createClinicalEntry(Request $request, $logbookId)
{
    $student = $request->user();

    // Make sure this logbook belongs to this student.
    $studentLogbook = DB::table('student_logbooks')
        ->join(
            'enrollments',
            'student_logbooks.enrollment_id',
            '=',
            'enrollments.id'
        )
        ->where('student_logbooks.id', $logbookId)
        ->where('enrollments.student_id', $student->id)
        ->select('student_logbooks.*')
        ->first();

    if (!$studentLogbook) {
        return response()->json([
            'message' => 'Logbook not found.'
        ], 404);
    }

    $request->validate([
        'logbook_item_id' => 'required|integer|exists:logbook_items,id',
        'logbook_item_requirement_id' =>
            'nullable|integer|exists:logbook_item_requirements,id',
        'activity_date' => 'required|date',
        'activity_time' => 'nullable',
        'facility_name' => 'required|string|max:200',
        'clinical_area' => 'nullable|string|max:200',
        'activity_details' => 'nullable|string',
    ]);

    // Check item really belongs to this logbook template.
    $item = DB::table('logbook_items')
        ->join(
            'logbook_sections',
            'logbook_items.logbook_section_id',
            '=',
            'logbook_sections.id'
        )
        ->where('logbook_items.id', $request->logbook_item_id)
        ->where(
            'logbook_sections.logbook_template_id',
            $studentLogbook->logbook_template_id
        )
        ->select('logbook_items.*')
        ->first();

    if (!$item) {
        return response()->json([
            'message' => 'This item does not belong to your logbook.'
        ], 422);
    }

    $competencyLevel = null;

    if ($request->logbook_item_requirement_id) {
        $requirement = DB::table('logbook_item_requirements')
            ->where(
                'id',
                $request->logbook_item_requirement_id
            )
            ->where(
                'logbook_item_id',
                $item->id
            )
            ->first();

        if (!$requirement) {
            return response()->json([
                'message' =>
                    'The selected requirement does not belong to this item.'
            ], 422);
        }

        $competencyLevel = $requirement->requirement_code;
    }

    $entryId = DB::table('clinical_entries')->insertGetId([
        'student_logbook_id' => $studentLogbook->id,
        'logbook_item_id' => $item->id,
        'logbook_item_requirement_id' =>
            $request->logbook_item_requirement_id,

        'activity_date' => $request->activity_date,
        'activity_time' => $request->activity_time,

        'facility_name' => $request->facility_name,
        'clinical_area' => $request->clinical_area,
        'activity_details' => $request->activity_details,

        'competency_level' => $competencyLevel,

        'status' => 'PENDING_VERIFICATION',

        'created_offline' => false,
        'sync_status' => 'SYNCED',

        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json([
        'message' => 'Clinical entry created successfully.',
        'clinical_entry_id' => $entryId,
        'status' => 'PENDING_VERIFICATION',
    ], 201);
    }
     public function clinicalEntries(Request $request, $logbookId)
{
    $student = $request->user();

    // Make sure the logbook belongs to the logged-in student.
    $studentLogbook = DB::table('student_logbooks')
        ->join(
            'enrollments',
            'student_logbooks.enrollment_id',
            '=',
            'enrollments.id'
        )
        ->where('student_logbooks.id', $logbookId)
        ->where('enrollments.student_id', $student->id)
        ->select('student_logbooks.*')
        ->first();

    if (!$studentLogbook) {
        return response()->json([
            'message' => 'Logbook not found.'
        ], 404);
    }

    $entries = DB::table('clinical_entries')
        ->leftJoin(
            'logbook_items',
            'clinical_entries.logbook_item_id',
            '=',
            'logbook_items.id'
        )
        ->leftJoin(
            'logbook_item_requirements',
            'clinical_entries.logbook_item_requirement_id',
            '=',
            'logbook_item_requirements.id'
        )
        ->leftJoin(
            'supervisor_verifications',
            'clinical_entries.id',
            '=',
            'supervisor_verifications.clinical_entry_id'
        )
        ->where(
            'clinical_entries.student_logbook_id',
            $studentLogbook->id
        )
        ->select(
            'clinical_entries.id',
            'clinical_entries.activity_date',
            'clinical_entries.activity_time',
            'clinical_entries.facility_name',
            'clinical_entries.clinical_area',
            'clinical_entries.activity_details',
            'clinical_entries.competency_level',
            'clinical_entries.status',
            'clinical_entries.created_offline',
            'clinical_entries.sync_status',

            'logbook_items.item_name',

            'logbook_item_requirements.requirement_code',
            'logbook_item_requirements.requirement_label',

            'supervisor_verifications.id as verification_id',
            'supervisor_verifications.supervisor_name',
            'supervisor_verifications.verification_status',
            'supervisor_verifications.verification_timestamp'
        )
        ->orderByDesc('clinical_entries.activity_date')
        ->orderByDesc('clinical_entries.activity_time')
        ->get();

    return response()->json([
        'logbook_id' => $studentLogbook->id,
        'entries' => $entries,
    ]);
    }
    public function createAttendance(Request $request, $logbookId)
{
    $student = $request->user();

    $studentLogbook = DB::table('student_logbooks')
        ->join(
            'enrollments',
            'student_logbooks.enrollment_id',
            '=',
            'enrollments.id'
        )
        ->where('student_logbooks.id', $logbookId)
        ->where('enrollments.student_id', $student->id)
        ->select('student_logbooks.*')
        ->first();

    if (!$studentLogbook) {
        return response()->json([
            'message' => 'Logbook not found.'
        ], 404);
    }

    $request->validate([
        'attendance_date' => 'required|date',
        'facility_name' => 'required|string|max:200',
        'clinical_unit' => 'nullable|string|max:200',
        'start_time' => 'required|date_format:H:i:s',
        'finish_time' => 'required|date_format:H:i:s',
    ]);

    $start = \Carbon\Carbon::createFromFormat(
        'H:i:s',
        $request->start_time
    );

    $finish = \Carbon\Carbon::createFromFormat(
        'H:i:s',
        $request->finish_time
    );

    if ($finish->lessThanOrEqualTo($start)) {
        return response()->json([
            'message' => 'Finish time must be after start time.'
        ], 422);
    }

    $totalHours = round(
        $start->diffInMinutes($finish) / 60,
        2
    );

    $attendanceId = DB::table('attendance_records')
        ->insertGetId([
            'student_logbook_id' => $studentLogbook->id,

            'attendance_date' => $request->attendance_date,
            'facility_name' => $request->facility_name,
            'clinical_unit' => $request->clinical_unit,

            'start_time' => $request->start_time,
            'finish_time' => $request->finish_time,
            'total_hours' => $totalHours,

            'status' => 'PENDING_VERIFICATION',

            'created_offline' => false,
            'sync_status' => 'SYNCED',

            'created_at' => now(),
            'updated_at' => now(),
        ]);

    return response()->json([
        'message' => 'Attendance record created successfully.',
        'attendance_record_id' => $attendanceId,
        'total_hours' => $totalHours,
        'status' => 'PENDING_VERIFICATION',
    ], 201);
   }
   public function attendanceRecords(Request $request, $logbookId)
{
    $student = $request->user();

    $studentLogbook = DB::table('student_logbooks')
        ->join(
            'enrollments',
            'student_logbooks.enrollment_id',
            '=',
            'enrollments.id'
        )
        ->where('student_logbooks.id', $logbookId)
        ->where('enrollments.student_id', $student->id)
        ->select('student_logbooks.*')
        ->first();

    if (!$studentLogbook) {
        return response()->json([
            'message' => 'Logbook not found.'
        ], 404);
    }

    $records = DB::table('attendance_records')
        ->leftJoin(
            'supervisor_verifications',
            'attendance_records.id',
            '=',
            'supervisor_verifications.attendance_record_id'
        )
        ->where(
            'attendance_records.student_logbook_id',
            $studentLogbook->id
        )
        ->select(
            'attendance_records.id',
            'attendance_records.attendance_date',
            'attendance_records.facility_name',
            'attendance_records.clinical_unit',
            'attendance_records.start_time',
            'attendance_records.finish_time',
            'attendance_records.total_hours',
            'attendance_records.status',
            'attendance_records.created_offline',
            'attendance_records.sync_status',

            'supervisor_verifications.supervisor_name',
            'supervisor_verifications.verification_status',
            'supervisor_verifications.verification_timestamp'
        )
        ->orderByDesc('attendance_records.attendance_date')
        ->get();

    return response()->json([
        'logbook_id' => $studentLogbook->id,
        'attendance_records' => $records,
    ]);
    }
}