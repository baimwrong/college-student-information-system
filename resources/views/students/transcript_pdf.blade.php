<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Academic Transcript - {{ $student->student_id }}</title>
    <style>
        @page {
            margin: 25mm 20mm 25mm 20mm;
            size: a4 portrait;
        }
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 4px 0;
            color: #0f172a;
            letter-spacing: 1px;
        }
        .header p {
            margin: 0;
            font-size: 10px;
            color: #64748b;
        }
        .doc-badge {
            display: inline-block;
            background: #1e3a8a;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 4px 14px;
            margin-top: 8px;
            letter-spacing: 1px;
        }
        .student-info {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .student-info td {
            padding: 6px 10px;
            vertical-align: top;
            font-size: 10px;
            width: 25%;
        }
        .info-label {
            color: #64748b;
            text-transform: uppercase;
            font-size: 8px;
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
        }
        .info-value {
            font-weight: bold;
            color: #0f172a;
        }
        .term-section {
            margin-bottom: 14px;
            page-break-inside: avoid;
        }
        .term-header {
            background: #334155;
            color: #ffffff;
            font-weight: bold;
            padding: 5px 8px;
            font-size: 10px;
        }
        .term-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .term-table th {
            background: #f1f5f9;
            color: #334155;
            text-align: left;
            padding: 5px 8px;
            font-size: 9px;
            font-weight: bold;
            border-bottom: 1px solid #cbd5e1;
        }
        .term-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .summary-box {
            width: 100%;
            margin-top: 15px;
            margin-bottom: 20px;
            border: 2px solid #2563eb;
            background: #eff6ff;
            border-collapse: collapse;
        }
        .summary-box td {
            padding: 10px;
            text-align: center;
            width: 33.33%;
            border-right: 1px solid #bfdbfe;
        }
        .summary-box td:last-child {
            border-right: none;
        }
        .summary-label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1e40af;
            display: block;
            margin-bottom: 3px;
        }
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
        }
        .signatures {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }
        .signatures td {
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 15px;
        }
        .sig-line {
            border-bottom: 1px solid #0f172a;
            margin-bottom: 6px;
        }
        .verification-footer {
            margin-top: 20px;
            padding: 8px 12px;
            border: 1px dashed #64748b;
            background: #f8fafc;
            font-size: 8px;
            color: #475569;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>University College Academic Registry</h1>
        <p>Office of Records & Matriculation • Official Permanent Transcript</p>
        <div class="doc-badge">Official Certified Record of Grades</div>
    </div>

    <!-- Student Bio Table -->
    <table class="student-info">
        <tr>
            <td>
                <span class="info-label">Student Name</span>
                <span class="info-value">{{ $student->full_name }}</span>
            </td>
            <td>
                <span class="info-label">Matriculation ID</span>
                <span class="info-value" style="color: #2563eb;">{{ $student->student_id }}</span>
            </td>
            <td>
                <span class="info-label">Degree Program</span>
                <span class="info-value">{{ $student->program->name ?? 'N/A' }}</span>
            </td>
            <td>
                <span class="info-label">Department</span>
                <span class="info-value">{{ $student->department->name ?? 'N/A' }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="info-label">Admission Date</span>
                <span class="info-value">{{ $student->admission_date ? $student->admission_date->format('M d, Y') : 'N/A' }}</span>
            </td>
            <td>
                <span class="info-label">Academic Status</span>
                <span class="info-value">{{ ucfirst($student->academic_status) }}</span>
            </td>
            <td>
                <span class="info-label">Credits Required</span>
                <span class="info-value">{{ $student->program->total_credits_required ?? 120 }} Credits</span>
            </td>
            <td>
                <span class="info-label">Issue Date</span>
                <span class="info-value">{{ date('F d, Y') }}</span>
            </td>
        </tr>
    </table>

    <!-- Academic History Broken Down by Term -->
    @foreach($termsData as $tData)
        <div class="term-section">
            <div class="term-header">
                Academic Session: {{ $tData['term']->name ?? 'Session' }} ({{ $tData['term']->academic_year ?? '' }}) 
                — Term Credits: {{ $tData['credits'] }} | Term SGPA: {{ number_format($tData['sgpa'], 2) }}
            </div>
            <table class="term-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Course Code</th>
                        <th style="width: 45%;">Course Title</th>
                        <th class="text-center" style="width: 10%;">Credits</th>
                        <th class="text-center" style="width: 10%;">Score</th>
                        <th class="text-center" style="width: 10%;">Grade</th>
                        <th class="text-center" style="width: 10%;">Grade Point</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tData['enrollments'] as $enr)
                        <tr>
                            <td style="font-weight: bold;">{{ $enr->courseSection->course->code ?? 'N/A' }}</td>
                            <td>{{ $enr->courseSection->course->title ?? 'N/A' }}</td>
                            <td class="text-center">{{ $enr->courseSection->course->credits ?? 0 }}</td>
                            <td class="text-center">
                                @if($enr->grade_status === 'published' && $enr->total_score !== null)
                                    {{ number_format($enr->total_score, 1) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center" style="font-weight: bold;">
                                @if($enr->grade_status === 'published' && $enr->letter_grade)
                                    {{ $enr->letter_grade }}
                                @else
                                    In Progress
                                @endif
                            </td>
                            <td class="text-center">
                                @if($enr->grade_status === 'published' && $enr->grade_point !== null)
                                    {{ number_format($enr->grade_point, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <!-- Cumulative Summary Box -->
    <table class="summary-box">
        <tr>
            <td>
                <span class="summary-label">Total Earned Credits</span>
                <span class="summary-value">{{ $student->total_credits_earned }}</span>
            </td>
            <td>
                <span class="summary-label">Cumulative GPA (CGPA)</span>
                <span class="summary-value" style="color: #1e3a8a;">{{ number_format($student->cgpa, 2) }} / 4.00</span>
            </td>
            <td>
                <span class="summary-label">Academic Graduation Standing</span>
                <span class="summary-value" style="font-size: 13px; color: {{ in_array($student->academic_status, ['probation', 'suspended']) ? '#b91c1c' : '#15803d' }};">
                    @if($student->academic_status === 'graduated' || ($student->total_credits_earned >= ($student->program->total_credits_required ?? 120) && !in_array($student->academic_status, ['probation', 'suspended'])))
                        Graduated
                    @elseif($student->academic_status === 'probation')
                        Academic Probation
                    @elseif($student->academic_status === 'suspended')
                        Suspended
                    @else
                        In Good Standing
                    @endif
                </span>
            </td>
        </tr>
    </table>

    <!-- Official Certification Signatures -->
    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line"></div>
                <strong style="font-size: 9px;">Dean of Academic Affairs</strong>
            </td>
            <td>
                <div class="sig-line"></div>
                <strong style="font-size: 9px;">University Registrar</strong>
            </td>
            <td>
                <div style="border: 1px dashed #64748b; padding: 6px; display: inline-block;">
                    <strong style="font-size: 8px; color: #1e3a8a;">OFFICIAL REGISTRAR EMBOSSED SEAL</strong>
                </div>
            </td>
        </tr>
    </table>

    <!-- Tamper-Evident Verification Footer -->
    <div class="verification-footer">
        <strong>TAMPER-EVIDENT DOCUMENT VERIFICATION:</strong><br>
        Document Security Token: <strong>{{ $student->transcript_token }}</strong><br>
        This official academic transcript can be independently authenticated by third parties and credential evaluators at:
        <span style="color: #2563eb; text-decoration: underline;">{{ $verificationUrl }}</span>
    </div>

</body>
</html>
