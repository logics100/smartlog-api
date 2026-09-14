<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FaceVerificationService
{
    public function healthCheck(): array
    {
        try {
            $response = Http::timeout(5)
                ->get('http://127.0.0.1:5001/health');

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' =>
                        'Face service returned an unsuccessful response.',
                    'status_code' =>
                        $response->status(),
                ];
            }

            return [
                'success' => true,
                'message' =>
                    'Face service is reachable.',
                'service_response' =>
                    $response->json(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' =>
                    'Unable to connect to face service.',
                'error' =>
                    $e->getMessage(),
            ];
        }
    }

   public function compare(
    string $referenceFacePath,
    string $capturedFacePath
): array {
    $referenceDisk = Storage::disk('public');

    if (!$referenceDisk->exists($referenceFacePath)) {
        return [
            'success' => false,
            'message' => 'Reference face image was not found.',
        ];
    }

    if (!$referenceDisk->exists($capturedFacePath)) {
        return [
            'success' => false,
            'message' => 'Captured face image was not found.',
        ];
    }

    try {
        $referenceFullPath =
            $referenceDisk->path($referenceFacePath);

        $capturedFullPath =
            $referenceDisk->path($capturedFacePath);

        $response = Http::timeout(20)
            ->attach(
                'reference_face',
                file_get_contents($referenceFullPath),
                basename($referenceFullPath)
            )
            ->attach(
                'captured_face',
                file_get_contents($capturedFullPath),
                basename($capturedFullPath)
            )
            ->post(
                'http://127.0.0.1:5001/compare'
            );

        if (!$response->successful()) {
            return [
                'success' => false,
                'message' =>
                    'Face comparison service returned an error.',
                'status_code' =>
                    $response->status(),
                'service_response' =>
                    $response->json(),
            ];
        }

        return $response->json();
    } catch (\Throwable $e) {
        return [
            'success' => false,
            'message' =>
                'Unable to send face images to comparison service.',
            'error' =>
                $e->getMessage(),
        ];
    }
}
}