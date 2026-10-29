<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class TranscriptVerificationController extends Controller
{
    /**
     * Public verification portal for official academic transcripts.
     */
    public function verify(string $token)
    {
        $student = Student::where('transcript_token', $token)->first();

        // If not found by direct token, check legacy/deterministic hash
        if (! $student) {
            $allStudents = Student::all();
            foreach ($allStudents as $st) {
                if ($st->transcript_token === $token) {
                    $student = $st;
                    break;
                }
            }
        }

        if (! $student) {
            return view('transcripts.verify', [
                'isValid' => false,
                'token' => $token,
                'student' => null,
            ]);
        }

        $student->load(['department', 'program']);

        return view('transcripts.verify', [
            'isValid' => true,
            'token' => $token,
            'student' => $student,
        ]);
    }
}
