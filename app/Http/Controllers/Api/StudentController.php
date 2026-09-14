<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    // -------------------------------------------------------------------------
    // Student - My Units
    // -------------------------------------------------------------------------

    public function myUnits(Request $request)
    {
        $student = $request->user();

        $units = DB::table('enrollments')
            ->join(
                'units',
                'enrollments.unit_id',
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
            ->leftJoin(
                'student_logbooks',
                'enrollments.id',
                '=',
                'student_logbooks.enrollment_id'
            )
            ->where(
                'enrollments.student_id',
                $student->id
            )
            ->where(
                'enrollments.status',
                'ACTIVE'
            )
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

    // -------------------------------------------------------------------------
    // Student - My Logbooks
    // -------------------------------------------------------------------------

    public function myLogbooks(Request $request)
    {
        $student = $request->user();

        $studentLogbookIds =
            DB::table('student_logbooks')
                ->join(
                    'enrollments',
                    'student_logbooks.enrollment_id',
                    '=',
                    'enrollments.id'
                )
                ->where(
                    'enrollments.student_id',
                    $student->id
                )
                ->pluck('student_logbooks.id');

        foreach (
            $studentLogbookIds
            as $studentLogbookId
        ) {
            $this->recalculateLogbookCompletion(
                (int) $studentLogbookId
            );
        }

        $logbooks =
            DB::table('student_logbooks')
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
                ->where(
                    'enrollments.student_id',
                    $student->id
                )
                ->select(
                    'student_logbooks.id',
                    'student_logbooks.status',
                    'student_logbooks.completion_percentage',
                    'student_logbooks.assigned_date',
                    'student_logbooks.due_date',

                    'logbook_templates.template_name',
                    'logbook_templates.minimum_completion_percentage',

                    'units.id as unit_id',
                    'units.unit_code',
                    'units.unit_name'
                )
                ->get();

        $logbooks->transform(
            function ($logbook) {
                $completionPercentage =
                    (float) (
                        $logbook->completion_percentage ?? 0
                    );

                $minimumCompletionPercentage =
                    (float) (
                        $logbook
                            ->minimum_completion_percentage ??
                        100
                    );

                $completionRequirementMet =
                    $completionPercentage >=
                    $minimumCompletionPercentage;

                if ($completionRequirementMet) {
                    $completionStatus =
                        'COMPLETION_REQUIREMENT_MET';
                } elseif (
                    $completionPercentage > 0
                ) {
                    $completionStatus =
                        'IN_PROGRESS';
                } else {
                    $completionStatus =
                        'NOT_STARTED';
                }

                $logbook->completion_percentage =
                    $completionPercentage;

                $logbook
                    ->minimum_completion_percentage =
                    $minimumCompletionPercentage;

                $logbook
                    ->completion_requirement_met =
                    $completionRequirementMet;

                $logbook->completion_status =
                    $completionStatus;

                return $logbook;
            }
        );

        return response()->json([
            'logbooks' => $logbooks,
        ]);
    }

    // -------------------------------------------------------------------------
    // Student - Show Logbook with Requirement Status
    // -------------------------------------------------------------------------

    public function showLogbook(
        Request $request,
        $logbookId
    ) {
        $student = $request->user();

        $logbook =
            DB::table('student_logbooks')
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
                ->where(
                    'student_logbooks.id',
                    $logbookId
                )
                ->where(
                    'enrollments.student_id',
                    $student->id
                )
                ->select(
                    'student_logbooks.id',
                    'student_logbooks.status',
                    'student_logbooks.completion_percentage',
                    'student_logbooks.assigned_date',
                    'student_logbooks.due_date',

                    'logbook_templates.id as template_id',
                    'logbook_templates.template_name',
                    'logbook_templates.minimum_completion_percentage',

                    'units.id as unit_id',
                    'units.unit_code',
                    'units.unit_name'
                )
                ->first();

        if (!$logbook) {
            return response()->json([
                'message' =>
                    'Logbook not found or it does not belong to you.'
            ], 404);
        }

        $completionPercentage =
            $this->recalculateLogbookCompletion(
                (int) $logbook->id
            );

        $logbook =
            DB::table('student_logbooks')
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
                ->where(
                    'student_logbooks.id',
                    $logbookId
                )
                ->where(
                    'enrollments.student_id',
                    $student->id
                )
                ->select(
                    'student_logbooks.id',
                    'student_logbooks.status',
                    'student_logbooks.completion_percentage',
                    'student_logbooks.assigned_date',
                    'student_logbooks.due_date',

                    'logbook_templates.id as template_id',
                    'logbook_templates.template_name',
                    'logbook_templates.minimum_completion_percentage',

                    'units.id as unit_id',
                    'units.unit_code',
                    'units.unit_name'
                )
                ->first();

        $sections =
            DB::table('logbook_sections')
                ->where(
                    'logbook_template_id',
                    $logbook->template_id
                )
                ->orderBy('display_order')
                ->get();

        foreach ($sections as $section) {
            $items =
                DB::table('logbook_items')
                    ->where(
                        'logbook_section_id',
                        $section->id
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->orderBy(
                        'display_order'
                    )
                    ->get();

            foreach ($items as $item) {
                $requirements =
                    DB::table(
                        'logbook_item_requirements'
                    )
                        ->where(
                            'logbook_item_id',
                            $item->id
                        )
                        ->orderBy(
                            'sequence_number'
                        )
                        ->get();

                foreach (
                    $requirements
                    as $requirement
                ) {
                    $verifiedEntry =
                        DB::table(
                            'clinical_entries'
                        )
                            ->where(
                                'student_logbook_id',
                                $logbook->id
                            )
                            ->where(
                                'logbook_item_requirement_id',
                                $requirement->id
                            )
                            ->where(
                                'status',
                                'VERIFIED'
                            )
                            ->orderByDesc(
                                'id'
                            )
                            ->first();

                    if ($verifiedEntry) {
                        $requirement
                            ->requirement_status =
                            'COMPLETED';

                        $requirement
                            ->clinical_entry_status =
                            'VERIFIED';

                        $requirement
                            ->latest_entry_id =
                            $verifiedEntry->id;

                        continue;
                    }

                    $latestEntry =
                        DB::table(
                            'clinical_entries'
                        )
                            ->where(
                                'student_logbook_id',
                                $logbook->id
                            )
                            ->where(
                                'logbook_item_requirement_id',
                                $requirement->id
                            )
                            ->orderByDesc(
                                'id'
                            )
                            ->first();

                    if (!$latestEntry) {
                        $requirement
                            ->requirement_status =
                            'NOT_COMPLETED';

                        $requirement
                            ->clinical_entry_status =
                            null;

                        $requirement
                            ->latest_entry_id =
                            null;

                        continue;
                    }

                    switch (
                        $latestEntry->status
                    ) {
                        case 'PENDING_VERIFICATION':
                            $requirement
                                ->requirement_status =
                                'PENDING';
                            break;

                        case 'REJECTED':
                            $requirement
                                ->requirement_status =
                                'REJECTED';
                            break;

                        case 'VERIFIED':
                            $requirement
                                ->requirement_status =
                                'COMPLETED';
                            break;

                        default:
                            $requirement
                                ->requirement_status =
                                'NOT_COMPLETED';
                            break;
                    }

                    $requirement
                        ->clinical_entry_status =
                        $latestEntry->status;

                    $requirement
                        ->latest_entry_id =
                        $latestEntry->id;
                }

                $item->requirements =
                    $requirements;

                $item
                    ->completed_requirements =
                    $requirements
                        ->where(
                            'requirement_status',
                            'COMPLETED'
                        )
                        ->count();

                $item
                    ->pending_requirements =
                    $requirements
                        ->where(
                            'requirement_status',
                            'PENDING'
                        )
                        ->count();

                $item
                    ->rejected_requirements =
                    $requirements
                        ->where(
                            'requirement_status',
                            'REJECTED'
                        )
                        ->count();

                $item
                    ->not_completed_requirements =
                    $requirements
                        ->where(
                            'requirement_status',
                            'NOT_COMPLETED'
                        )
                        ->count();

                $item->total_requirements =
                    $requirements->count();

                if (
                    $item->total_requirements > 0 &&
                    $item
                        ->completed_requirements ===
                        $item
                            ->total_requirements
                ) {
                    $item
                        ->item_progress_status =
                        'COMPLETED';
                } elseif (
                    $item
                        ->completed_requirements > 0 ||
                    $item
                        ->pending_requirements > 0 ||
                    $item
                        ->rejected_requirements > 0
                ) {
                    $item
                        ->item_progress_status =
                        'IN_PROGRESS';
                } else {
                    $item
                        ->item_progress_status =
                        'NOT_STARTED';
                }
            }

            $section->items = $items;
        }

        $minimumCompletionPercentage =
            (float) (
                $logbook
                    ->minimum_completion_percentage ??
                100
            );

        $completionRequirementMet =
            $completionPercentage >=
            $minimumCompletionPercentage;

        if ($completionRequirementMet) {
            $completionStatus =
                'COMPLETION_REQUIREMENT_MET';
        } elseif (
            $completionPercentage > 0
        ) {
            $completionStatus =
                'IN_PROGRESS';
        } else {
            $completionStatus =
                'NOT_STARTED';
        }

        return response()->json([
            'logbook' => [
                'id' =>
                    $logbook->id,

                'template_id' =>
                    $logbook->template_id,

                'template_name' =>
                    $logbook->template_name,

                'unit_id' =>
                    $logbook->unit_id,

                'unit_code' =>
                    $logbook->unit_code,

                'unit_name' =>
                    $logbook->unit_name,

                'status' =>
                    $logbook->status,

                'completion_percentage' =>
                    $completionPercentage,

                'minimum_completion_percentage' =>
                    $minimumCompletionPercentage,

                'completion_requirement_met' =>
                    $completionRequirementMet,

                'completion_status' =>
                    $completionStatus,

                'assigned_date' =>
                    $logbook->assigned_date,

                'due_date' =>
                    $logbook->due_date,
            ],

            'sections' =>
                $sections,
        ]);
    }

    // -------------------------------------------------------------------------
    // Student - Logbook Progress
    // -------------------------------------------------------------------------

    public function logbookProgress(
        Request $request,
        $logbookId
    ) {
        $student = $request->user();

        $logbook =
            DB::table('student_logbooks')
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
                ->where(
                    'student_logbooks.id',
                    $logbookId
                )
                ->where(
                    'enrollments.student_id',
                    $student->id
                )
                ->select(
                    'student_logbooks.id',
                    'student_logbooks.enrollment_id',
                    'student_logbooks.logbook_template_id',
                    'student_logbooks.status',
                    'student_logbooks.completion_percentage',
                    'student_logbooks.assigned_date',
                    'student_logbooks.due_date',

                    'logbook_templates.template_name',
                    'logbook_templates.minimum_completion_percentage',

                    'units.id as unit_id',
                    'units.unit_code',
                    'units.unit_name'
                )
                ->first();

        if (!$logbook) {
            return response()->json([
                'message' =>
                    'Logbook not found or it does not belong to you.'
            ], 404);
        }

        $completionPercentage =
            $this->recalculateLogbookCompletion(
                (int) $logbook->id
            );

        $logbook =
            DB::table('student_logbooks')
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
                ->where(
                    'student_logbooks.id',
                    $logbookId
                )
                ->where(
                    'enrollments.student_id',
                    $student->id
                )
                ->select(
                    'student_logbooks.id',
                    'student_logbooks.enrollment_id',
                    'student_logbooks.logbook_template_id',
                    'student_logbooks.status',
                    'student_logbooks.completion_percentage',
                    'student_logbooks.assigned_date',
                    'student_logbooks.due_date',

                    'logbook_templates.template_name',
                    'logbook_templates.minimum_completion_percentage',

                    'units.id as unit_id',
                    'units.unit_code',
                    'units.unit_name'
                )
                ->first();

        $minimumCompletionPercentage =
            (float) (
                $logbook
                    ->minimum_completion_percentage ??
                100
            );

        $completionRequirementMet =
            $completionPercentage >=
            $minimumCompletionPercentage;

        if ($completionRequirementMet) {
            $completionStatus =
                'COMPLETION_REQUIREMENT_MET';
        } elseif (
            $completionPercentage > 0
        ) {
            $completionStatus =
                'IN_PROGRESS';
        } else {
            $completionStatus =
                'NOT_STARTED';
        }

        $totalRequirements =
            DB::table(
                'logbook_item_requirements'
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
                    'logbook_sections.logbook_template_id',
                    $logbook
                        ->logbook_template_id
                )
                ->where(
                    'logbook_items.is_active',
                    true
                )
                ->count(
                    'logbook_item_requirements.id'
                );

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
                    $logbook->id
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
                    $logbook
                        ->logbook_template_id
                )
                ->where(
                    'logbook_items.is_active',
                    true
                )
                ->distinct()
                ->count(
                    'clinical_entries.logbook_item_requirement_id'
                );

        $remainingRequirements =
            max(
                0,
                $totalRequirements -
                    $completedRequirements
            );

        $clinicalEntries =
            DB::table('clinical_entries')
                ->where(
                    'student_logbook_id',
                    $logbook->id
                )
                ->select(
                    'id',
                    'activity_date',
                    'activity_details',
                    'facility_name',
                    'status',
                    'created_at',
                    'updated_at'
                )
                ->orderByDesc(
                    'activity_date'
                )
                ->orderByDesc('id')
                ->get();

        return response()->json([
            'student' => [
                'id' => $student->id,
                'dwu_id' =>
                    $student->dwu_id,
                'name' =>
                    $student->name,
            ],

            'unit' => [
                'id' =>
                    $logbook->unit_id,

                'unit_code' =>
                    $logbook->unit_code,

                'unit_name' =>
                    $logbook->unit_name,
            ],

            'logbook' => [
                'id' =>
                    $logbook->id,

                'template_name' =>
                    $logbook->template_name,

                'status' =>
                    $logbook->status,

                'completion_percentage' =>
                    $completionPercentage,

                'minimum_completion_percentage' =>
                    $minimumCompletionPercentage,

                'completion_requirement_met' =>
                    $completionRequirementMet,

                'completion_status' =>
                    $completionStatus,

                'assigned_date' =>
                    $logbook->assigned_date,

                'due_date' =>
                    $logbook->due_date,
            ],

            'progress' => [
                'completion_percentage' =>
                    $completionPercentage,

                'minimum_completion_percentage' =>
                    $minimumCompletionPercentage,

                'completion_status' =>
                    $completionStatus,

                'completion_requirement_met' =>
                    $completionRequirementMet,

                'completed_requirements' =>
                    $completedRequirements,

                'total_requirements' =>
                    $totalRequirements,

                'remaining_requirements' =>
                    $remainingRequirements,
            ],

            'summary' => [
                'total_entries' =>
                    $clinicalEntries->count(),

                'draft_entries' =>
                    $clinicalEntries
                        ->where(
                            'status',
                            'DRAFT'
                        )
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
                        ->where(
                            'status',
                            'VERIFIED'
                        )
                        ->count(),

                'rejected_entries' =>
                    $clinicalEntries
                        ->where(
                            'status',
                            'REJECTED'
                        )
                        ->count(),
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // Student - Create Clinical Entry
    // -------------------------------------------------------------------------

    public function createClinicalEntry(
        Request $request,
        $logbookId
    ) {
        $student = $request->user();

        $studentLogbook =
            DB::table('student_logbooks')
                ->join(
                    'enrollments',
                    'student_logbooks.enrollment_id',
                    '=',
                    'enrollments.id'
                )
                ->where(
                    'student_logbooks.id',
                    $logbookId
                )
                ->where(
                    'enrollments.student_id',
                    $student->id
                )
                ->select(
                    'student_logbooks.*'
                )
                ->first();

        if (!$studentLogbook) {
            return response()->json([
                'message' =>
                    'Logbook not found.'
            ], 404);
        }

        $request->validate([
            'logbook_item_id' =>
                'required|integer|exists:logbook_items,id',

            'logbook_item_requirement_id' =>
                'nullable|integer|exists:logbook_item_requirements,id',

            'activity_date' =>
                'required|date',

            'activity_time' =>
                'nullable',

            'facility_name' =>
                'required|string|max:200',

            'clinical_area' =>
                'nullable|string|max:200',

            'activity_details' =>
                'nullable|string',
        ]);

        $item =
            DB::table('logbook_items')
                ->join(
                    'logbook_sections',
                    'logbook_items.logbook_section_id',
                    '=',
                    'logbook_sections.id'
                )
                ->where(
                    'logbook_items.id',
                    $request->logbook_item_id
                )
                ->where(
                    'logbook_sections.logbook_template_id',
                    $studentLogbook
                        ->logbook_template_id
                )
                ->select(
                    'logbook_items.*'
                )
                ->first();

        if (!$item) {
            return response()->json([
                'message' =>
                    'This item does not belong to your logbook.'
            ], 422);
        }

        $competencyLevel = null;

        if (
            $request
                ->logbook_item_requirement_id
        ) {
            $requirement =
                DB::table(
                    'logbook_item_requirements'
                )
                    ->where(
                        'id',
                        $request
                            ->logbook_item_requirement_id
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

            $competencyLevel =
                $requirement
                    ->requirement_code;
        }

        $entryId =
            DB::table('clinical_entries')
                ->insertGetId([
                    'student_logbook_id' =>
                        $studentLogbook->id,

                    'logbook_item_id' =>
                        $item->id,

                    'logbook_item_requirement_id' =>
                        $request
                            ->logbook_item_requirement_id,

                    'activity_date' =>
                        $request
                            ->activity_date,

                    'activity_time' =>
                        $request
                            ->activity_time,

                    'facility_name' =>
                        $request
                            ->facility_name,

                    'clinical_area' =>
                        $request
                            ->clinical_area,

                    'activity_details' =>
                        $request
                            ->activity_details,

                    'competency_level' =>
                        $competencyLevel,

                    'status' =>
                        'PENDING_VERIFICATION',

                    'created_offline' =>
                        false,

                    'sync_status' =>
                        'SYNCED',

                    'created_at' =>
                        now(),

                    'updated_at' =>
                        now(),
                ]);

        return response()->json([
            'message' =>
                'Clinical entry created successfully.',

            'clinical_entry_id' =>
                $entryId,

            'status' =>
                'PENDING_VERIFICATION',
        ], 201);
    }

    // -------------------------------------------------------------------------
    // Student - Clinical Entries with Verification History
    // -------------------------------------------------------------------------

    public function clinicalEntries(
        Request $request,
        $logbookId
    ) {
        $student = $request->user();

        $studentLogbook =
            DB::table('student_logbooks')
                ->join(
                    'enrollments',
                    'student_logbooks.enrollment_id',
                    '=',
                    'enrollments.id'
                )
                ->where(
                    'student_logbooks.id',
                    $logbookId
                )
                ->where(
                    'enrollments.student_id',
                    $student->id
                )
                ->select(
                    'student_logbooks.*'
                )
                ->first();

        if (!$studentLogbook) {
            return response()->json([
                'message' =>
                    'Logbook not found.'
            ], 404);
        }

        /*
         * Load each clinical entry only once.
         *
         * We deliberately do not directly join
         * supervisor_verifications here because a clinical
         * entry may have more than one verification attempt.
         * A direct join would duplicate the clinical entry.
         */
        $entries =
            DB::table('clinical_entries')
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
                    'clinical_entries.created_at',
                    'clinical_entries.updated_at',

                    'logbook_items.item_name',

                    'logbook_item_requirements.id as requirement_id',
                    'logbook_item_requirements.requirement_code',
                    'logbook_item_requirements.requirement_label'
                )
                ->orderByDesc(
                    'clinical_entries.activity_date'
                )
                ->orderByDesc(
                    'clinical_entries.activity_time'
                )
                ->orderByDesc(
                    'clinical_entries.id'
                )
                ->get();

        foreach ($entries as $entry) {
            /*
             * Get every supervisor verification attempt for
             * this clinical entry.
             *
             * Newest attempt appears first.
             */
            $verificationHistory =
                DB::table(
                    'supervisor_verifications'
                )
                    ->leftJoin(
                        'users as reviewer',
                        'supervisor_verifications.reviewed_by',
                        '=',
                        'reviewer.id'
                    )
                    ->where(
                        'supervisor_verifications.clinical_entry_id',
                        $entry->id
                    )
                    ->select(
                        'supervisor_verifications.id',
                        'supervisor_verifications.clinical_entry_id',
                        'supervisor_verifications.clinical_supervisor_id',

                        'supervisor_verifications.supervisor_name',
                        'supervisor_verifications.facility_name',

                        'supervisor_verifications.verification_status',
                        'supervisor_verifications.verification_timestamp',
                        'supervisor_verifications.verification_method',

                        'supervisor_verifications.face_lbph_distance',
                        'supervisor_verifications.face_comparison_decision',

                        'supervisor_verifications.reviewed_by',
                        'supervisor_verifications.reviewed_at',
                        'supervisor_verifications.review_comment',

                        'reviewer.name as reviewer_name',
                        'reviewer.dwu_id as reviewer_dwu_id'
                    )
                    ->orderByDesc(
                        'supervisor_verifications.id'
                    )
                    ->get();

            /*
             * Convert numeric biometric evidence to a normal
             * floating-point value for Flutter.
             */
            $verificationHistory->transform(
                function ($verification) {
                    if (
                        $verification
                            ->face_lbph_distance !==
                        null
                    ) {
                        $verification
                            ->face_lbph_distance =
                            (float) $verification
                                ->face_lbph_distance;
                    }

                    /*
                     * Human-readable student-side review state.
                     */
                    if (
                        $verification
                            ->verification_status ===
                        'APPROVED'
                    ) {
                        $verification
                            ->review_status =
                            'APPROVED';
                    } elseif (
                        $verification
                            ->verification_status ===
                        'REJECTED'
                    ) {
                        $verification
                            ->review_status =
                            'REJECTED';
                    } else {
                        $verification
                            ->review_status =
                            'WAITING_FOR_LECTURER_REVIEW';
                    }

                    return $verification;
                }
            );

            $latestVerification =
                $verificationHistory->first();

            /*
             * Attach the complete history.
             */
            $entry->verification_history =
                $verificationHistory;

            $entry->verification_attempt_count =
                $verificationHistory->count();

            /*
             * Keep convenient top-level fields so the current
             * Flutter screen remains compatible.
             */
            if ($latestVerification) {
                $entry->verification_id =
                    $latestVerification->id;

                $entry->supervisor_name =
                    $latestVerification
                        ->supervisor_name;

                $entry
                    ->verification_status =
                    $latestVerification
                        ->verification_status;

                $entry
                    ->verification_timestamp =
                    $latestVerification
                        ->verification_timestamp;

                $entry
                    ->verification_method =
                    $latestVerification
                        ->verification_method;

                $entry
                    ->face_comparison_decision =
                    $latestVerification
                        ->face_comparison_decision;

                $entry->face_lbph_distance =
                    $latestVerification
                        ->face_lbph_distance;

                $entry->review_status =
                    $latestVerification
                        ->review_status;

                $entry->reviewed_by =
                    $latestVerification
                        ->reviewed_by;

                $entry->reviewer_name =
                    $latestVerification
                        ->reviewer_name;

                $entry->reviewer_dwu_id =
                    $latestVerification
                        ->reviewer_dwu_id;

                $entry->reviewed_at =
                    $latestVerification
                        ->reviewed_at;

                $entry->review_comment =
                    $latestVerification
                        ->review_comment;
            } else {
                $entry->verification_id =
                    null;

                $entry->supervisor_name =
                    null;

                $entry->verification_status =
                    null;

                $entry->verification_timestamp =
                    null;

                $entry->verification_method =
                    null;

                $entry
                    ->face_comparison_decision =
                    null;

                $entry->face_lbph_distance =
                    null;

                $entry->review_status =
                    'NOT_SUBMITTED';

                $entry->reviewed_by =
                    null;

                $entry->reviewer_name =
                    null;

                $entry->reviewer_dwu_id =
                    null;

                $entry->reviewed_at =
                    null;

                $entry->review_comment =
                    null;
            }
        }

        return response()->json([
            'logbook_id' =>
                $studentLogbook->id,

            'entries' =>
                $entries,
        ]);
    }

    // -------------------------------------------------------------------------
    // Student - Create Attendance
    // -------------------------------------------------------------------------

    public function createAttendance(
        Request $request,
        $logbookId
    ) {
        $student = $request->user();

        $studentLogbook =
            DB::table('student_logbooks')
                ->join(
                    'enrollments',
                    'student_logbooks.enrollment_id',
                    '=',
                    'enrollments.id'
                )
                ->where(
                    'student_logbooks.id',
                    $logbookId
                )
                ->where(
                    'enrollments.student_id',
                    $student->id
                )
                ->select(
                    'student_logbooks.*'
                )
                ->first();

        if (!$studentLogbook) {
            return response()->json([
                'message' =>
                    'Logbook not found.'
            ], 404);
        }

        $request->validate([
            'attendance_date' =>
                'required|date',

            'facility_name' =>
                'required|string|max:200',

            'clinical_unit' =>
                'nullable|string|max:200',

            'start_time' =>
                'required|date_format:H:i:s',

            'finish_time' =>
                'required|date_format:H:i:s',
        ]);

        $start =
            \Carbon\Carbon::createFromFormat(
                'H:i:s',
                $request->start_time
            );

        $finish =
            \Carbon\Carbon::createFromFormat(
                'H:i:s',
                $request->finish_time
            );

        if (
            $finish
                ->lessThanOrEqualTo(
                    $start
                )
        ) {
            return response()->json([
                'message' =>
                    'Finish time must be after start time.'
            ], 422);
        }

        $totalHours =
            round(
                $start
                    ->diffInMinutes(
                        $finish
                    ) / 60,
                2
            );

        $attendanceId =
            DB::table(
                'attendance_records'
            )
                ->insertGetId([
                    'student_logbook_id' =>
                        $studentLogbook->id,

                    'attendance_date' =>
                        $request
                            ->attendance_date,

                    'facility_name' =>
                        $request
                            ->facility_name,

                    'clinical_unit' =>
                        $request
                            ->clinical_unit,

                    'start_time' =>
                        $request
                            ->start_time,

                    'finish_time' =>
                        $request
                            ->finish_time,

                    'total_hours' =>
                        $totalHours,

                    'status' =>
                        'PENDING_VERIFICATION',

                    'created_offline' =>
                        false,

                    'sync_status' =>
                        'SYNCED',

                    'created_at' =>
                        now(),

                    'updated_at' =>
                        now(),
                ]);

        return response()->json([
            'message' =>
                'Attendance record created successfully.',

            'attendance_record_id' =>
                $attendanceId,

            'total_hours' =>
                $totalHours,

            'status' =>
                'PENDING_VERIFICATION',
        ], 201);
    }

    // -------------------------------------------------------------------------
    // Student - Attendance Records
    // -------------------------------------------------------------------------

    public function attendanceRecords(
        Request $request,
        $logbookId
    ) {
        $student = $request->user();

        $studentLogbook =
            DB::table('student_logbooks')
                ->join(
                    'enrollments',
                    'student_logbooks.enrollment_id',
                    '=',
                    'enrollments.id'
                )
                ->where(
                    'student_logbooks.id',
                    $logbookId
                )
                ->where(
                    'enrollments.student_id',
                    $student->id
                )
                ->select(
                    'student_logbooks.*'
                )
                ->first();

        if (!$studentLogbook) {
            return response()->json([
                'message' =>
                    'Logbook not found.'
            ], 404);
        }

        $records =
            DB::table(
                'attendance_records'
            )
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
                ->orderByDesc(
                    'attendance_records.attendance_date'
                )
                ->get();

        return response()->json([
            'logbook_id' =>
                $studentLogbook->id,

            'attendance_records' =>
                $records,
        ]);
    }

    // -------------------------------------------------------------------------
    // Shared Student Logbook Completion Calculation
    // -------------------------------------------------------------------------

    private function recalculateLogbookCompletion(
        int $studentLogbookId
    ): float {
        $studentLogbook =
            DB::table('student_logbooks')
                ->where(
                    'id',
                    $studentLogbookId
                )
                ->first();

        if (!$studentLogbook) {
            return 0;
        }

        $template =
            DB::table('logbook_templates')
                ->where(
                    'id',
                    $studentLogbook
                        ->logbook_template_id
                )
                ->first();

        $minimumCompletionPercentage =
            (float) (
                $template
                    ->minimum_completion_percentage ??
                100
            );

        $totalRequirements =
            DB::table(
                'logbook_item_requirements'
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
                    'logbook_sections.logbook_template_id',
                    $studentLogbook
                        ->logbook_template_id
                )
                ->where(
                    'logbook_items.is_active',
                    true
                )
                ->count(
                    'logbook_item_requirements.id'
                );

        if ($totalRequirements <= 0) {
            DB::table(
                'student_logbooks'
            )
                ->where(
                    'id',
                    $studentLogbookId
                )
                ->update([
                    'completion_percentage' =>
                        0,

                    'status' =>
                        'ACTIVE',

                    'updated_at' =>
                        now(),
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
                    $studentLogbook
                        ->logbook_template_id
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
                min(
                    100,
                    $completionPercentage
                )
            );

        $newStatus =
            $completionPercentage >=
            $minimumCompletionPercentage
                ? 'COMPLETED'
                : 'ACTIVE';

        DB::table(
            'student_logbooks'
        )
            ->where(
                'id',
                $studentLogbookId
            )
            ->update([
                'completion_percentage' =>
                    $completionPercentage,

                'status' =>
                    $newStatus,

                'updated_at' =>
                    now(),
            ]);

        return (float)
            $completionPercentage;
    }
}