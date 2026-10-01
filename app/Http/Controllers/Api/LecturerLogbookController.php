<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LecturerLogbookController extends Controller
{
    public function index(Request $request)
    {
        $lecturer = $request->user();

        $unitIds = DB::table('lecturer_units')
            ->where('lecturer_id', $lecturer->id)
            ->pluck('unit_id');

        $templates = DB::table('logbook_templates as lt')
            ->join('units as u', 'lt.unit_id', '=', 'u.id')
            ->leftJoin('year_levels as yl', 'u.year_level_id', '=', 'yl.id')
            ->leftJoin('semesters as s', 'u.semester_id', '=', 's.id')
            ->whereIn('lt.unit_id', $unitIds)
            ->select(
                'lt.*',
                'u.unit_code',
                'u.unit_name',
                'yl.year_name',
                's.semester_name'
            )
            ->orderBy('u.unit_code')
            ->orderByDesc('lt.id')
            ->get();

        foreach ($templates as $template) {
            $template->sections_count =
                DB::table('logbook_sections')
                    ->where('logbook_template_id', $template->id)
                    ->count();

            $template->items_count =
                DB::table('logbook_items as li')
                    ->join(
                        'logbook_sections as ls',
                        'li.logbook_section_id',
                        '=',
                        'ls.id'
                    )
                    ->where('ls.logbook_template_id', $template->id)
                    ->count();

            $template->requirements_count =
                DB::table('logbook_item_requirements as lir')
                    ->join(
                        'logbook_items as li',
                        'lir.logbook_item_id',
                        '=',
                        'li.id'
                    )
                    ->join(
                        'logbook_sections as ls',
                        'li.logbook_section_id',
                        '=',
                        'ls.id'
                    )
                    ->where('ls.logbook_template_id', $template->id)
                    ->count();

            $template->student_logbooks_count =
                DB::table('student_logbooks')
                    ->where('logbook_template_id', $template->id)
                    ->count();
        }

        return response()->json([
            'logbooks' => $templates,
        ]);
    }

    public function show(Request $request, int $templateId)
    {
        $lecturer = $request->user();

        $template = $this->templateForLecturer(
            $lecturer->id,
            $templateId
        );

        if (!$template) {
            return response()->json([
                'message' =>
                    'Clinical logbook not found or you are not assigned to its unit.',
            ], 404);
        }

        $sections = DB::table('logbook_sections')
            ->where('logbook_template_id', $template->id)
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        foreach ($sections as $section) {
            $items = DB::table('logbook_items')
                ->where('logbook_section_id', $section->id)
                ->orderBy('display_order')
                ->orderBy('id')
                ->get();

            foreach ($items as $item) {
                $item->requirements =
                    DB::table('logbook_item_requirements')
                        ->where('logbook_item_id', $item->id)
                        ->orderBy('sequence_number')
                        ->orderBy('id')
                        ->get();
            }

            $section->items = $items;
        }

        $assignedCount = DB::table('student_logbooks')
            ->where('logbook_template_id', $template->id)
            ->count();

        $eligibleEnrollments = DB::table('enrollments')
            ->where('unit_id', $template->unit_id)
            ->where('status', 'ACTIVE')
            ->count();

        return response()->json([
            'template' => $template,
            'sections' => $sections,
            'assigned_count' => $assignedCount,
            'eligible_enrollments' => $eligibleEnrollments,
        ]);
    }

    public function store(Request $request)
    {
        $lecturer = $request->user();

        $validated = $request->validate([
            'unit_id' => 'required|integer|exists:units,id',
            'template_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'minimum_completion_percentage' =>
                'required|numeric|min:0|max:100',
            'is_active' => 'required|boolean',
        ]);

        if (!$this->lecturerHasUnit(
            $lecturer->id,
            (int) $validated['unit_id']
        )) {
            return response()->json([
                'message' => 'You are not assigned to this unit.',
            ], 403);
        }

        $unit = DB::table('units')
            ->where('id', $validated['unit_id'])
            ->first();

        if (!$unit || !(bool) $unit->requires_logbook) {
            return response()->json([
                'message' =>
                    'This unit is not configured to use a clinical logbook.',
            ], 422);
        }

        $id = DB::table('logbook_templates')->insertGetId([
            'unit_id' => (int) $validated['unit_id'],
            'template_name' => trim($validated['template_name']),
            'description' =>
                isset($validated['description']) &&
                trim($validated['description']) !== ''
                    ? trim($validated['description'])
                    : null,
            'minimum_completion_percentage' =>
                (float) $validated['minimum_completion_percentage'],
            'is_active' => (bool) $validated['is_active'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' =>
                'Clinical logbook template created successfully.',
            'template_id' => $id,
        ], 201);
    }

    public function update(
        Request $request,
        int $templateId
    ) {
        $lecturer = $request->user();

        $template = $this->templateForLecturer(
            $lecturer->id,
            $templateId
        );

        if (!$template) {
            return response()->json([
                'message' => 'Clinical logbook not found.',
            ], 404);
        }

        $validated = $request->validate([
            'template_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'minimum_completion_percentage' =>
                'required|numeric|min:0|max:100',
            'is_active' => 'required|boolean',
        ]);

        DB::table('logbook_templates')
            ->where('id', $template->id)
            ->update([
                'template_name' =>
                    trim($validated['template_name']),
                'description' =>
                    isset($validated['description']) &&
                    trim($validated['description']) !== ''
                        ? trim($validated['description'])
                        : null,
                'minimum_completion_percentage' =>
                    (float)
                    $validated['minimum_completion_percentage'],
                'is_active' =>
                    (bool) $validated['is_active'],
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' =>
                'Clinical logbook template updated successfully.',
        ]);
    }

    public function assign(
        Request $request,
        int $templateId
    ) {
        $lecturer = $request->user();

        $template = $this->templateForLecturer(
            $lecturer->id,
            $templateId
        );

        if (!$template) {
            return response()->json([
                'message' => 'Clinical logbook not found.',
            ], 404);
        }

        if (!(bool) $template->is_active) {
            return response()->json([
                'message' =>
                    'Activate the template before assigning it to students.',
            ], 422);
        }

        $requirements =
            DB::table('logbook_item_requirements as r')
                ->join(
                    'logbook_items as i',
                    'r.logbook_item_id',
                    '=',
                    'i.id'
                )
                ->join(
                    'logbook_sections as s',
                    'i.logbook_section_id',
                    '=',
                    's.id'
                )
                ->where(
                    's.logbook_template_id',
                    $template->id
                )
                ->where('i.is_active', 1)
                ->count();

        if ($requirements < 1) {
            return response()->json([
                'message' =>
                    'Add at least one active item requirement before assigning this logbook.',
            ], 422);
        }

        $enrollments = DB::table('enrollments')
            ->where('unit_id', $template->unit_id)
            ->where('status', 'ACTIVE')
            ->pluck('id');

        $created = 0;

        DB::transaction(function () use (
            $enrollments,
            $template,
            &$created
        ) {
            foreach ($enrollments as $enrollmentId) {
                $exists =
                    DB::table('student_logbooks')
                        ->where(
                            'enrollment_id',
                            $enrollmentId
                        )
                        ->where(
                            'logbook_template_id',
                            $template->id
                        )
                        ->exists();

                if (!$exists) {
                    DB::table('student_logbooks')->insert([
                        'enrollment_id' => $enrollmentId,
                        'logbook_template_id' =>
                            $template->id,
                        'assigned_date' =>
                            now()->toDateString(),
                        'due_date' => null,
                        'status' => 'ACTIVE',
                        'completion_percentage' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $created++;
                }
            }
        });

        return response()->json([
            'message' =>
                "$created student logbook(s) assigned. Existing student logbooks were left unchanged.",
            'created' => $created,
        ]);
    }

    public function storeSection(
        Request $request,
        int $templateId
    ) {
        $lecturer = $request->user();

        $template = $this->templateForLecturer(
            $lecturer->id,
            $templateId
        );

        if (!$template) {
            return response()->json([
                'message' => 'Clinical logbook not found.',
            ], 404);
        }

        $validated = $request->validate([
            'section_title' =>
                'required|string|max:255',
            'section_type' =>
                'required|string|in:ATTENDANCE,PROCEDURE,CHECKLIST,PATIENT_LOG,CASELOAD,SKILLS,REFLECTION,ASSESSMENT,GENERAL',
            'instructions' =>
                'nullable|string|max:2000',
            'display_order' =>
                'required|integer|min:0',
            'requires_supervisor_verification' =>
                'required|boolean',
        ]);

        $id = DB::table('logbook_sections')
            ->insertGetId([
                'logbook_template_id' =>
                    $template->id,
                'section_title' =>
                    trim($validated['section_title']),
                'section_type' =>
                    trim($validated['section_type']),
                'instructions' =>
                    isset($validated['instructions']) &&
                    trim($validated['instructions']) !== ''
                        ? trim($validated['instructions'])
                        : null,
                'display_order' =>
                    (int) $validated['display_order'],
                'requires_supervisor_verification' =>
                    (bool)
                    $validated[
                        'requires_supervisor_verification'
                    ],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Section added successfully.',
            'section_id' => $id,
        ], 201);
    }

    public function updateSection(
        Request $request,
        int $templateId,
        int $sectionId
    ) {
        $lecturer = $request->user();

        $template = $this->templateForLecturer(
            $lecturer->id,
            $templateId
        );

        if (!$template) {
            return response()->json([
                'message' => 'Clinical logbook not found.',
            ], 404);
        }

        if (!$this->sectionBelongsToTemplate(
            $sectionId,
            $template->id
        )) {
            return response()->json([
                'message' =>
                    'Section not found in this logbook.',
            ], 404);
        }

        $validated = $request->validate([
            'section_title' =>
                'required|string|max:255',
            'section_type' =>
                'required|string|in:ATTENDANCE,PROCEDURE,CHECKLIST,PATIENT_LOG,CASELOAD,SKILLS,REFLECTION,ASSESSMENT,GENERAL',
            'instructions' =>
                'nullable|string|max:2000',
            'display_order' =>
                'required|integer|min:0',
            'requires_supervisor_verification' =>
                'required|boolean',
        ]);

        DB::table('logbook_sections')
            ->where('id', $sectionId)
            ->update([
                'section_title' =>
                    trim($validated['section_title']),
                'section_type' =>
                    trim($validated['section_type']),
                'instructions' =>
                    isset($validated['instructions']) &&
                    trim($validated['instructions']) !== ''
                        ? trim($validated['instructions'])
                        : null,
                'display_order' =>
                    (int) $validated['display_order'],
                'requires_supervisor_verification' =>
                    (bool)
                    $validated[
                        'requires_supervisor_verification'
                    ],
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' =>
                'Section updated successfully.',
        ]);
    }

    public function storeItem(
        Request $request,
        int $templateId,
        int $sectionId
    ) {
        $lecturer = $request->user();

        $template = $this->templateForLecturer(
            $lecturer->id,
            $templateId
        );

        if (!$template) {
            return response()->json([
                'message' => 'Clinical logbook not found.',
            ], 404);
        }

        if (!$this->sectionBelongsToTemplate(
            $sectionId,
            $template->id
        )) {
            return response()->json([
                'message' =>
                    'Section not found in this logbook.',
            ], 404);
        }

        $validated = $request->validate([
            'item_name' =>
                'required|string|max:255',
            'item_description' =>
                'nullable|string|max:2000',
            'required_level' =>
                'nullable|string|max:100',
            'required_count' =>
                'required|integer|min:1|max:999',
            'requires_supervisor_verification' =>
                'required|boolean',
            'display_order' =>
                'required|integer|min:0',
            'is_active' =>
                'required|boolean',
        ]);

        $itemId = DB::table('logbook_items')
            ->insertGetId([
                'logbook_section_id' => $sectionId,
                'item_name' =>
                    trim($validated['item_name']),
                'item_description' =>
                    isset($validated['item_description']) &&
                    trim($validated['item_description']) !== ''
                        ? trim($validated['item_description'])
                        : null,
                'required_level' =>
                    isset($validated['required_level']) &&
                    trim($validated['required_level']) !== ''
                        ? trim($validated['required_level'])
                        : null,
                'required_count' =>
                    (int) $validated['required_count'],
                'requires_supervisor_verification' =>
                    (bool)
                    $validated[
                        'requires_supervisor_verification'
                    ],
                'display_order' =>
                    (int) $validated['display_order'],
                'is_active' =>
                    (bool) $validated['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        for (
            $i = 1;
            $i <= (int) $validated['required_count'];
            $i++
        ) {
            DB::table(
                'logbook_item_requirements'
            )->insert([
                'logbook_item_id' => $itemId,
                'sequence_number' => $i,
                'requirement_code' => 'R' . $i,
                'requirement_label' => 'Attempt ' . $i,
                'is_simulation' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'message' =>
                'Clinical item added successfully.',
            'item_id' => $itemId,
        ], 201);
    }

    public function updateItem(
        Request $request,
        int $templateId,
        int $itemId
    ) {
        $lecturer = $request->user();

        $template = $this->templateForLecturer(
            $lecturer->id,
            $templateId
        );

        if (!$template) {
            return response()->json([
                'message' => 'Clinical logbook not found.',
            ], 404);
        }

        $item = $this->itemForTemplate(
            $itemId,
            $template->id
        );

        if (!$item) {
            return response()->json([
                'message' =>
                    'Clinical item not found in this logbook.',
            ], 404);
        }

        $validated = $request->validate([
            'item_name' =>
                'required|string|max:255',
            'item_description' =>
                'nullable|string|max:2000',
            'required_level' =>
                'nullable|string|max:100',
            'requires_supervisor_verification' =>
                'required|boolean',
            'display_order' =>
                'required|integer|min:0',
            'is_active' =>
                'required|boolean',
        ]);

        DB::table('logbook_items')
            ->where('id', $item->id)
            ->update([
                'item_name' =>
                    trim($validated['item_name']),
                'item_description' =>
                    isset($validated['item_description']) &&
                    trim($validated['item_description']) !== ''
                        ? trim($validated['item_description'])
                        : null,
                'required_level' =>
                    isset($validated['required_level']) &&
                    trim($validated['required_level']) !== ''
                        ? trim($validated['required_level'])
                        : null,
                'requires_supervisor_verification' =>
                    (bool)
                    $validated[
                        'requires_supervisor_verification'
                    ],
                'display_order' =>
                    (int) $validated['display_order'],
                'is_active' =>
                    (bool) $validated['is_active'],
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' =>
                'Clinical item updated successfully.',
        ]);
    }

    public function storeRequirement(
        Request $request,
        int $templateId,
        int $itemId
    ) {
        $lecturer = $request->user();

        $template = $this->templateForLecturer(
            $lecturer->id,
            $templateId
        );

        if (!$template) {
            return response()->json([
                'message' => 'Clinical logbook not found.',
            ], 404);
        }

        $item = $this->itemForTemplate(
            $itemId,
            $template->id
        );

        if (!$item) {
            return response()->json([
                'message' =>
                    'Clinical item not found in this logbook.',
            ], 404);
        }

        $validated = $request->validate([
            'requirement_code' =>
                'required|string|max:100',
            'requirement_label' =>
                'required|string|max:255',
            'is_simulation' =>
                'required|boolean',
        ]);

        $next =
            (int) (
                DB::table('logbook_item_requirements')
                    ->where(
                        'logbook_item_id',
                        $item->id
                    )
                    ->max('sequence_number') ?? 0
            ) + 1;

        $id = DB::table(
            'logbook_item_requirements'
        )->insertGetId([
            'logbook_item_id' => $item->id,
            'sequence_number' => $next,
            'requirement_code' =>
                trim($validated['requirement_code']),
            'requirement_label' =>
                trim($validated['requirement_label']),
            'is_simulation' =>
                (bool) $validated['is_simulation'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $count = DB::table(
            'logbook_item_requirements'
        )
            ->where(
                'logbook_item_id',
                $item->id
            )
            ->count();

        DB::table('logbook_items')
            ->where('id', $item->id)
            ->update([
                'required_count' => $count,
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' =>
                'Requirement added successfully.',
            'requirement_id' => $id,
        ], 201);
    }

    public function updateRequirement(
        Request $request,
        int $templateId,
        int $requirementId
    ) {
        $lecturer = $request->user();

        $template = $this->templateForLecturer(
            $lecturer->id,
            $templateId
        );

        if (!$template) {
            return response()->json([
                'message' => 'Clinical logbook not found.',
            ], 404);
        }

        $requirement =
            DB::table(
                'logbook_item_requirements as r'
            )
                ->join(
                    'logbook_items as i',
                    'r.logbook_item_id',
                    '=',
                    'i.id'
                )
                ->join(
                    'logbook_sections as s',
                    'i.logbook_section_id',
                    '=',
                    's.id'
                )
                ->where('r.id', $requirementId)
                ->where(
                    's.logbook_template_id',
                    $template->id
                )
                ->select('r.*')
                ->first();

        if (!$requirement) {
            return response()->json([
                'message' =>
                    'Requirement not found in this logbook.',
            ], 404);
        }

        $validated = $request->validate([
            'requirement_code' =>
                'required|string|max:100',
            'requirement_label' =>
                'required|string|max:255',
            'is_simulation' =>
                'required|boolean',
        ]);

        DB::table('logbook_item_requirements')
            ->where('id', $requirementId)
            ->update([
                'requirement_code' =>
                    trim($validated['requirement_code']),
                'requirement_label' =>
                    trim($validated['requirement_label']),
                'is_simulation' =>
                    (bool) $validated['is_simulation'],
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' =>
                'Requirement updated successfully.',
        ]);
    }

    private function lecturerHasUnit(
        int $lecturerId,
        int $unitId
    ): bool {
        return DB::table('lecturer_units')
            ->where('lecturer_id', $lecturerId)
            ->where('unit_id', $unitId)
            ->exists();
    }

    private function templateForLecturer(
        int $lecturerId,
        int $templateId
    ) {
        return DB::table('logbook_templates as lt')
            ->join(
                'units as u',
                'lt.unit_id',
                '=',
                'u.id'
            )
            ->join(
                'lecturer_units as lu',
                'u.id',
                '=',
                'lu.unit_id'
            )
            ->where('lt.id', $templateId)
            ->where(
                'lu.lecturer_id',
                $lecturerId
            )
            ->select(
                'lt.*',
                'u.unit_code',
                'u.unit_name'
            )
            ->first();
    }

    private function sectionBelongsToTemplate(
        int $sectionId,
        int $templateId
    ): bool {
        return DB::table('logbook_sections')
            ->where('id', $sectionId)
            ->where(
                'logbook_template_id',
                $templateId
            )
            ->exists();
    }

    private function itemForTemplate(
        int $itemId,
        int $templateId
    ) {
        return DB::table('logbook_items as i')
            ->join(
                'logbook_sections as s',
                'i.logbook_section_id',
                '=',
                's.id'
            )
            ->where('i.id', $itemId)
            ->where(
                's.logbook_template_id',
                $templateId
            )
            ->select('i.*')
            ->first();
    }
}
