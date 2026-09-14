<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\FaceVerificationService;

class SupervisorVerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Verify Clinical Entry
    |--------------------------------------------------------------------------
    */

    public function verifyClinicalEntry(
        Request $request,
        $entryId,
        FaceVerificationService $faceVerificationService
    ) {
        $student = $request->user();

        $entry = DB::table('clinical_entries')
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
            ->where('clinical_entries.id', $entryId)
            ->where('enrollments.student_id', $student->id)
            ->select('clinical_entries.*')
            ->first();

        if (!$entry) {
            return response()->json([
                'message' => 'Clinical entry not found.'
            ], 404);
        }

        if ($entry->status === 'VERIFIED') {
            return response()->json([
                'message' =>
                    'This clinical entry has already been verified.'
            ], 409);
        }

        $request->validate([
            'clinical_supervisor_id' =>
                'nullable|integer|exists:clinical_supervisors,id',

            'supervisor_name' =>
                'required|string|max:150',

            'facility_name' =>
                'nullable|string|max:200',

            'signature' =>
                'required|image|mimes:png,jpg,jpeg|max:5120',

            'face_capture' =>
                'nullable|image|mimes:jpg,jpeg,png|max:5120',

            'face_match_score' =>
                'nullable|numeric|min:0|max:1',

            'face_match_passed' =>
                'nullable|boolean',

            'verification_timestamp' =>
                'required|date',

            'time_check_passed' =>
                'nullable|boolean',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Store Signature
        |--------------------------------------------------------------------------
        */

        $signaturePath = $request
            ->file('signature')
            ->store('signatures', 'public');

        /*
        |--------------------------------------------------------------------------
        | Store Face Capture
        |--------------------------------------------------------------------------
        */

        $faceCapturePath = null;

        if ($request->hasFile('face_capture')) {
            $faceCapturePath = $request
                ->file('face_capture')
                ->store('faces', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Keep Final Verification In Manual Review
        |--------------------------------------------------------------------------
        |
        | Face comparison is prototype evidence only.
        | It must NOT automatically approve or reject the verification.
        |
        */

        $verificationStatus = 'MANUAL_REVIEW';

        /*
        |--------------------------------------------------------------------------
        | Save Verification
        |--------------------------------------------------------------------------
        */

        $verificationId = DB::table('supervisor_verifications')
            ->insertGetId([
                'clinical_entry_id' =>
                    $entry->id,

                'attendance_record_id' =>
                    null,

                'clinical_supervisor_id' =>
                    $request->clinical_supervisor_id,

                'supervisor_name' =>
                    $request->supervisor_name,

                'facility_name' =>
                    $request->facility_name,

                'signature_path' =>
                    $signaturePath,

                'face_capture_path' =>
                    $faceCapturePath,

                /*
                 * Do not automatically set these fields.
                 * They remain null during the biometric prototype.
                 */
                'face_match_score' =>
                    null,

                'face_match_passed' =>
                    null,

                /*
                 * Prototype comparison evidence.
                 * Initially null until Python comparison completes.
                 */
                'face_lbph_distance' =>
                    null,

                'face_comparison_decision' =>
                    null,

                'activity_timestamp' =>
                    $entry->activity_date . ' ' .
                    ($entry->activity_time ?? '00:00:00'),

                'verification_timestamp' =>
                    $request->verification_timestamp,

                'time_check_passed' =>
                    $request->time_check_passed,

                'verification_status' =>
                    $verificationStatus,

                'verification_method' =>
                    'STYLUS_FACE_TIME',

                'rejection_reason' =>
                    null,

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ]);

        /*
        |--------------------------------------------------------------------------
        | Automatic Prototype Face Comparison
        |--------------------------------------------------------------------------
        |
        | Run only when:
        | 1. Registered supervisor was selected
        | 2. Face capture was supplied
        | 3. Supervisor has a reference face
        |
        | Result is evidence only.
        | verification_status remains MANUAL_REVIEW.
        |
        */

        $faceComparisonResult = null;

        if (
            $request->clinical_supervisor_id &&
            $faceCapturePath
        ) {
            $supervisor = DB::table('clinical_supervisors')
                ->where(
                    'id',
                    $request->clinical_supervisor_id
                )
                ->first();

            if (
                $supervisor &&
                $supervisor->reference_face_path
            ) {
                try {
                    $faceComparisonResult =
                        $faceVerificationService->compare(
                            $supervisor->reference_face_path,
                            $faceCapturePath
                        );

                    if (
                        isset($faceComparisonResult['success']) &&
                        $faceComparisonResult['success'] === true
                    ) {
                        DB::table('supervisor_verifications')
                            ->where(
                                'id',
                                $verificationId
                            )
                            ->update([
                                'face_lbph_distance' =>
                                    $faceComparisonResult[
                                        'lbph_distance'
                                    ] ?? null,

                                'face_comparison_decision' =>
                                    $faceComparisonResult[
                                        'comparison_decision'
                                    ] ?? null,

                                'updated_at' =>
                                    now(),
                            ]);
                    }
                } catch (\Throwable $exception) {
                    /*
                     * Do not reject or destroy the verification if
                     * the prototype face service is unavailable.
                     *
                     * The record remains MANUAL_REVIEW.
                     */
                    $faceComparisonResult = [
                        'success' => false,
                        'message' =>
                            'Automatic face comparison could not be completed.',
                    ];
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' =>
                'Supervisor verification recorded.',

            'verification_id' =>
                $verificationId,

            'verification_status' =>
                'MANUAL_REVIEW',

            'clinical_entry_status' =>
                'PENDING_VERIFICATION',

            'signature_path' =>
                $signaturePath,

            'face_capture_path' =>
                $faceCapturePath,

            'face_comparison' =>
                $faceComparisonResult,
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Verify Attendance
    |--------------------------------------------------------------------------
    */

    public function verifyAttendance(
        Request $request,
        $attendanceId
    ) {
        $student = $request->user();

        $attendance = DB::table('attendance_records')
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
            ->where(
                'attendance_records.id',
                $attendanceId
            )
            ->where(
                'enrollments.student_id',
                $student->id
            )
            ->select('attendance_records.*')
            ->first();

        if (!$attendance) {
            return response()->json([
                'message' =>
                    'Attendance record not found.'
            ], 404);
        }

        if ($attendance->status === 'VERIFIED') {
            return response()->json([
                'message' =>
                    'This attendance record is already verified.'
            ], 409);
        }

        $request->validate([
            'clinical_supervisor_id' =>
                'nullable|integer|exists:clinical_supervisors,id',

            'supervisor_name' =>
                'required|string|max:150',

            'facility_name' =>
                'nullable|string|max:200',

            'signature' =>
                'required|image|mimes:png,jpg,jpeg|max:5120',

            'face_capture' =>
                'nullable|image|mimes:jpg,jpeg,png|max:5120',

            'face_match_score' =>
                'nullable|numeric|min:0|max:1',

            'face_match_passed' =>
                'nullable|boolean',

            'verification_timestamp' =>
                'required|date',

            'time_check_passed' =>
                'nullable|boolean',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Store Signature
        |--------------------------------------------------------------------------
        */

        $signaturePath = $request
            ->file('signature')
            ->store('signatures', 'public');

        /*
        |--------------------------------------------------------------------------
        | Store Face Capture
        |--------------------------------------------------------------------------
        */

        $faceCapturePath = null;

        if ($request->hasFile('face_capture')) {
            $faceCapturePath = $request
                ->file('face_capture')
                ->store('faces', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Manual Review
        |--------------------------------------------------------------------------
        */

        $verificationStatus = 'MANUAL_REVIEW';

        /*
        |--------------------------------------------------------------------------
        | Save Attendance Verification
        |--------------------------------------------------------------------------
        */

        $verificationId = DB::table('supervisor_verifications')
            ->insertGetId([
                'clinical_entry_id' =>
                    null,

                'attendance_record_id' =>
                    $attendance->id,

                'clinical_supervisor_id' =>
                    $request->clinical_supervisor_id,

                'supervisor_name' =>
                    $request->supervisor_name,

                'facility_name' =>
                    $request->facility_name,

                'signature_path' =>
                    $signaturePath,

                'face_capture_path' =>
                    $faceCapturePath,

                'face_match_score' =>
                    null,

                'face_match_passed' =>
                    null,

                'face_lbph_distance' =>
                    null,

                'face_comparison_decision' =>
                    null,

                'activity_timestamp' =>
                    $attendance->attendance_date . ' ' .
                    $attendance->finish_time,

                'verification_timestamp' =>
                    $request->verification_timestamp,

                'time_check_passed' =>
                    $request->time_check_passed,

                'verification_status' =>
                    $verificationStatus,

                'verification_method' =>
                    'STYLUS_FACE_TIME',

                'rejection_reason' =>
                    null,

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ]);

        return response()->json([
            'message' =>
                'Attendance verification recorded.',

            'verification_id' =>
                $verificationId,

            'verification_status' =>
                'MANUAL_REVIEW',

            'attendance_status' =>
                'PENDING_VERIFICATION',

            'signature_path' =>
                $signaturePath,

            'face_capture_path' =>
                $faceCapturePath,
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Search Clinical Supervisors
    |--------------------------------------------------------------------------
    */

    public function searchSupervisors(Request $request)
    {
        $request->validate([
            'search' =>
                'required|string|min:1|max:100',
        ]);

        $search = trim($request->search);

        $supervisors = DB::table('clinical_supervisors')
            ->where('is_active', true)
            ->where(function ($query) use ($search) {
                $query
                    ->where(
                        'full_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'registration_number',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'facility_name',
                        'like',
                        "%{$search}%"
                    );
            })
            ->select(
                'id',
                'full_name',
                'registration_number',
                'profession',
                'facility_name'
            )
            ->orderBy('full_name')
            ->limit(20)
            ->get();

        return response()->json([
            'supervisors' =>
                $supervisors,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Register Supervisor Reference Face
    |--------------------------------------------------------------------------
    */

    public function registerReferenceFace(
        Request $request,
        $supervisorId
    ) {
        $supervisor = DB::table('clinical_supervisors')
            ->where(
                'id',
                $supervisorId
            )
            ->where(
                'is_active',
                true
            )
            ->first();

        if (!$supervisor) {
            return response()->json([
                'message' =>
                    'Clinical supervisor not found.'
            ], 404);
        }

        $request->validate([
            'reference_face' =>
                'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $referenceFacePath = $request
            ->file('reference_face')
            ->store(
                'supervisor_reference_faces',
                'public'
            );

        DB::table('clinical_supervisors')
            ->where(
                'id',
                $supervisor->id
            )
            ->update([
                'reference_face_path' =>
                    $referenceFacePath,

                'updated_at' =>
                    now(),
            ]);

        return response()->json([
            'message' =>
                'Supervisor reference face registered successfully.',

            'supervisor' => [
                'id' =>
                    $supervisor->id,

                'full_name' =>
                    $supervisor->full_name,

                'registration_number' =>
                    $supervisor->registration_number,

                'reference_face_path' =>
                    $referenceFacePath,
            ],
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Manual Face Comparison Test Endpoint
    |--------------------------------------------------------------------------
    |
    | This endpoint can still be used for testing an existing verification.
    |
    */

    public function testFaceComparison(
        Request $request,
        $verificationId,
        FaceVerificationService $faceVerificationService
    ) {
        $student = $request->user();

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
                ->where(
                    'supervisor_verifications.id',
                    $verificationId
                )
                ->where(
                    'enrollments.student_id',
                    $student->id
                )
                ->select(
                    'supervisor_verifications.*'
                )
                ->first();

        if (!$verification) {
            return response()->json([
                'message' =>
                    'Verification record not found.',
            ], 404);
        }

        if (
            !$verification->clinical_supervisor_id
        ) {
            return response()->json([
                'message' =>
                    'No registered clinical supervisor is linked to this verification.',
            ], 422);
        }

        $supervisor =
            DB::table('clinical_supervisors')
                ->where(
                    'id',
                    $verification->clinical_supervisor_id
                )
                ->first();

        if (!$supervisor) {
            return response()->json([
                'message' =>
                    'Clinical supervisor record not found.',
            ], 404);
        }

        if (
            !$supervisor->reference_face_path
        ) {
            return response()->json([
                'message' =>
                    'Supervisor does not have a registered reference face.',
            ], 422);
        }

        if (
            !$verification->face_capture_path
        ) {
            return response()->json([
                'message' =>
                    'Verification does not have a captured face image.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Run Face Comparison
        |--------------------------------------------------------------------------
        */

        $result =
            $faceVerificationService->compare(
                $supervisor->reference_face_path,
                $verification->face_capture_path
            );

        /*
        |--------------------------------------------------------------------------
        | Store Prototype Evidence
        |--------------------------------------------------------------------------
        */

        if (
            isset($result['success']) &&
            $result['success'] === true
        ) {
            DB::table('supervisor_verifications')
                ->where(
                    'id',
                    $verification->id
                )
                ->update([
                    'face_lbph_distance' =>
                        $result[
                            'lbph_distance'
                        ] ?? null,

                    'face_comparison_decision' =>
                        $result[
                            'comparison_decision'
                        ] ?? null,

                    'updated_at' =>
                        now(),
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Return Test Result
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'verification_id' =>
                $verification->id,

            'clinical_supervisor_id' =>
                $supervisor->id,

            'supervisor_name' =>
                $supervisor->full_name,

            'result' =>
                $result,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Face Service Health
    |--------------------------------------------------------------------------
    */

    public function faceServiceHealth(
        FaceVerificationService $faceVerificationService
    ) {
        $result =
            $faceVerificationService->healthCheck();

        return response()->json(
            $result
        );
    }
}