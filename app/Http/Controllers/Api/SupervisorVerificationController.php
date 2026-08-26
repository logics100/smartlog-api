<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupervisorVerificationController extends Controller
{
    public function verifyClinicalEntry(Request $request, $entryId)
    {
        $student = $request->user();

        /*
        |--------------------------------------------------------------------------
        | 1. Confirm this clinical entry belongs to the logged-in student
        |--------------------------------------------------------------------------
        */

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
            ->select(
                'clinical_entries.*'
            )
            ->first();

        if (!$entry) {
            return response()->json([
                'message' => 'Clinical entry not found.'
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Prevent verification of an already verified entry
        |--------------------------------------------------------------------------
        */

        if ($entry->status === 'VERIFIED') {
            return response()->json([
                'message' => 'This clinical entry has already been verified.'
            ], 409);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Validate verification information
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'clinical_supervisor_id' => 'nullable|integer|exists:clinical_supervisors,id',

            'supervisor_name' => 'required|string|max:150',

            'facility_name' => 'nullable|string|max:200',

            'signature_path' => 'required|string|max:255',

            'face_capture_path' => 'nullable|string|max:255',

            'face_match_score' => 'nullable|numeric|min:0|max:1',

            'face_match_passed' => 'nullable|boolean',

            'verification_timestamp' => 'required|date',

            'time_check_passed' => 'nullable|boolean',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 4. Decide verification status
        |--------------------------------------------------------------------------
        |
        | For the prototype:
        |
        | Face passed + time passed
        |       ↓
        | APPROVED
        |
        | Missing face check / uncertain evidence
        |       ↓
        | MANUAL_REVIEW
        |
        | Explicit failed face/time check
        |       ↓
        | REJECTED
        |--------------------------------------------------------------------------
        */

        $verificationStatus = 'MANUAL_REVIEW';

        if (
            $request->face_match_passed === true &&
            $request->time_check_passed === true
        ) {
            $verificationStatus = 'APPROVED';
        }

        if (
            $request->face_match_passed === false ||
            $request->time_check_passed === false
        ) {
            $verificationStatus = 'REJECTED';
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Store verification
        |--------------------------------------------------------------------------
        */

        $verificationId = DB::table('supervisor_verifications')
            ->insertGetId([
                'clinical_entry_id' => $entry->id,

                'attendance_record_id' => null,

                'clinical_supervisor_id' =>
                    $request->clinical_supervisor_id,

                'supervisor_name' =>
                    $request->supervisor_name,

                'facility_name' =>
                    $request->facility_name,

                'signature_path' =>
                    $request->signature_path,

                'face_capture_path' =>
                    $request->face_capture_path,

                'face_match_score' =>
                    $request->face_match_score,

                'face_match_passed' =>
                    $request->face_match_passed,

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
                    $verificationStatus === 'REJECTED'
                        ? 'Verification checks failed.'
                        : null,

                'created_at' => now(),
                'updated_at' => now(),
            ]);

        /*
        |--------------------------------------------------------------------------
        | 6. Update clinical entry status
        |--------------------------------------------------------------------------
        */

        if ($verificationStatus === 'APPROVED') {
            DB::table('clinical_entries')
                ->where('id', $entry->id)
                ->update([
                    'status' => 'VERIFIED',
                    'updated_at' => now(),
                ]);
        }

        if ($verificationStatus === 'REJECTED') {
            DB::table('clinical_entries')
                ->where('id', $entry->id)
                ->update([
                    'status' => 'REJECTED',
                    'updated_at' => now(),
                ]);
        }

        return response()->json([
            'message' => 'Supervisor verification recorded.',

            'verification_id' => $verificationId,

            'verification_status' =>
                $verificationStatus,

            'clinical_entry_status' =>
                $verificationStatus === 'APPROVED'
                    ? 'VERIFIED'
                    : (
                        $verificationStatus === 'REJECTED'
                            ? 'REJECTED'
                            : 'PENDING_VERIFICATION'
                    ),
        ], 201);
    }
    public function verifyAttendance(Request $request, $attendanceId)
{
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
        ->where('attendance_records.id', $attendanceId)
        ->where('enrollments.student_id', $student->id)
        ->select('attendance_records.*')
        ->first();

    if (!$attendance) {
        return response()->json([
            'message' => 'Attendance record not found.'
        ], 404);
    }

    if ($attendance->status === 'VERIFIED') {
        return response()->json([
            'message' => 'This attendance record is already verified.'
        ], 409);
    }

    $request->validate([
        'clinical_supervisor_id' => 'nullable|integer|exists:clinical_supervisors,id',
        'supervisor_name' => 'required|string|max:150',
        'facility_name' => 'nullable|string|max:200',
        'signature_path' => 'required|string|max:255',
        'face_capture_path' => 'nullable|string|max:255',
        'face_match_score' => 'nullable|numeric|min:0|max:1',
        'face_match_passed' => 'nullable|boolean',
        'verification_timestamp' => 'required|date',
        'time_check_passed' => 'nullable|boolean',
    ]);

    $verificationStatus = 'MANUAL_REVIEW';

    if (
        $request->boolean('face_match_passed') &&
        $request->boolean('time_check_passed')
    ) {
        $verificationStatus = 'APPROVED';
    }

    if (
        $request->has('face_match_passed') &&
        !$request->boolean('face_match_passed')
    ) {
        $verificationStatus = 'REJECTED';
    }

    if (
        $request->has('time_check_passed') &&
        !$request->boolean('time_check_passed')
    ) {
        $verificationStatus = 'REJECTED';
    }

    $verificationId = DB::table('supervisor_verifications')
        ->insertGetId([
            'clinical_entry_id' => null,
            'attendance_record_id' => $attendance->id,
            'clinical_supervisor_id' => $request->clinical_supervisor_id,
            'supervisor_name' => $request->supervisor_name,
            'facility_name' => $request->facility_name,
            'signature_path' => $request->signature_path,
            'face_capture_path' => $request->face_capture_path,
            'face_match_score' => $request->face_match_score,
            'face_match_passed' => $request->face_match_passed,
            'activity_timestamp' =>
                $attendance->attendance_date . ' ' . $attendance->finish_time,
            'verification_timestamp' => $request->verification_timestamp,
            'time_check_passed' => $request->time_check_passed,
            'verification_status' => $verificationStatus,
            'verification_method' => 'STYLUS_FACE_TIME',
            'rejection_reason' =>
                $verificationStatus === 'REJECTED'
                    ? 'Verification checks failed.'
                    : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    if ($verificationStatus === 'APPROVED') {
        DB::table('attendance_records')
            ->where('id', $attendance->id)
            ->update([
                'status' => 'VERIFIED',
                'updated_at' => now(),
            ]);
    }

    if ($verificationStatus === 'REJECTED') {
        DB::table('attendance_records')
            ->where('id', $attendance->id)
            ->update([
                'status' => 'REJECTED',
                'updated_at' => now(),
            ]);
    }

    return response()->json([
        'message' => 'Attendance verification recorded.',
        'verification_id' => $verificationId,
        'verification_status' => $verificationStatus,
        'attendance_status' =>
            $verificationStatus === 'APPROVED'
                ? 'VERIFIED'
                : (
                    $verificationStatus === 'REJECTED'
                        ? 'REJECTED'
                        : 'PENDING_VERIFICATION'
                ),
    ], 201);
    }
}