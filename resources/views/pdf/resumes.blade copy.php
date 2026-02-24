<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $resume['name'] ?? 'CV' }} - CV</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
    margin: 0;
}

body {
    font-family: Arial, sans-serif;
    font-size: 10px;
    color: #000000;
    line-height: 1.4;
    padding: 0;
}

        /* ── TITLE ── */
        .cv-title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 24px;
            margin-top: 10px;
        }

        /* ── SECTION HEADING ── */
        .section-heading {
            font-size: 24px;
            font-weight: bold;
            color: #e20000;
            border-bottom: 2px solid #e20000;
            margin-bottom: 10px;
            margin-top: 20px;
        }

        /* ── SYNTHESIS TEXT ── */
        .synthesis-text {
            font-size: 17px;
            text-align: justify;
            margin-bottom: 12px;
        }

        /* ── NATIONALITY / LANGUAGES ── */
        table.info-table {
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table.info-table td {
            font-size: 17px;
            padding: 1px 4px 1px 0;
            vertical-align: top;
        }

        table.info-table td:first-child {
            font-weight: bold;
            padding-right: 8px;
            white-space: nowrap;
        }

        /* ── EDUCATION TABLE ── */
        table.edu-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #999;
            margin-bottom: 10px;
        }

        table.edu-table td {
            border: 1px solid #999;
            padding: 5px 8px;
            font-size: 17px;
            vertical-align: top;
        }

        table.edu-table td:first-child {
            width: 90px;
            white-space: nowrap;
        }

        table.edu-table td:nth-child(2) {
            width: 230px;
        }

        /* ── SKILLS BOX ── */
        table.skills-box {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #999;
            margin-bottom: 10px;
        }

        table.skills-box td {
            padding: 8px 10px;
            font-size: 17px;
            vertical-align: top;
        }

        .skill-line {
            margin-bottom: 4px;
        }

        .skill-line:last-child {
            margin-bottom: 0;
        }

        /* ── CERTS TABLE ── */
        table.certs-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #999;
            margin-bottom: 10px;
        }

        table.certs-table tr {
            border-bottom: 1px solid #999;
        }

        table.certs-table tr:last-child {
            border-bottom: none;
        }

        table.certs-table td {
            padding: 5px 10px;
            font-size: 17px;
        }

        /* ── EXPERIENCE BOX ── */
        table.exp-box {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #999;
            margin-bottom: 10px;
        }

        table.exp-box > tr > td {
            padding: 10px 12px;
            font-size: 17px;
            vertical-align: top;
        }

        /* two-col header inside exp box */
        table.job-top-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        table.job-top-table td {
            padding: 0;
            font-size: 17px;
            vertical-align: middle;
        }

        table.job-top-table .left-col {
            width: 50%;
        }

        table.job-top-table .right-col {
            width: 50%;
            text-align: center;
        }

        /* ── RESPONSIBILITIES ── */
        .resp-label {
            font-size: 17px;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 4px;
        }

        ul.resp-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        ul.resp-list li {
            padding-left: 20px;
            position: relative;
            margin-bottom: 4px;
            text-align: justify;
            font-size: 17px;
        }

        ul.resp-list li::before {
            content: "\27A4";
            position: absolute;
            left: 0;
            font-size: 8pt;
            top: 2px;
            color: #000;
        }

        /* ── PROJECT BOX ── */
        table.proj-box {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #999;
            margin-bottom: 10px;
            font-size: 17px;
        }

        table.proj-box > tr > td {
            padding: 10px 12px;
            font-size: 17px;
            vertical-align: top;
        }

        .proj-name {
            font-weight: bold;
            padding-left: 16px;
            position: relative;
            margin-bottom: 5px;
            margin-top: 8px;
            font-size: 17px;
        }

        .proj-name:first-child {
            margin-top: 0;
        }

        .proj-name::before {
            content: "\2022";
            position: absolute;
            left: 0;
            font-size: 17px;
            line-height: 1;
            top: -1px;
        }

        /* ── DOMPDF HEADER/FOOTER via @page ── */
        /* @page {
            margin:0!imporant;
        } */

        /* header {
            position: fixed;
            top: -120px;
            left: 0;
            right: 0;
            height: 110px;
        } */

        footer {
            position: fixed;
            bottom: -70px;
            left: 0;
            right: 0;
            height: 60px;
            border-top: 1px solid #ddd;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            padding: 0;
            vertical-align: middle;
        }
        .header-vgc {
            font-size: 37px;
            /* margin-top: -40px; */
            /* font-weight: bold;
            text-align: right;
            padding-right: 10px;
            vertical-align: bottom;
            padding-bottom: 6px; */
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            padding-top: 5px;
        }
        .footer-table td {
            padding: 0;
            vertical-align: top;
            font-size: 7pt;
            color: #333;
        }
        .footer-page {
            text-align: right;
            font-size: 8pt;
            font-weight: bold;
            white-space: nowrap;
        }
        .page-counter::after {
            content: counter(page);
        }
        .page-total::after {
            content: counter(pages);
        }

    </style>
</head>
<body>
    <header>
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:230px; vertical-align:top; padding-top:20px; padding-left:30px;">
                <img src="{{ asset('images/logoEDO.png') }}" alt="Logo" width="220" height="140">
            </td>
            <td style="vertical-align:middle; text-align:center; padding-top:60px;">
                <div style="font-size:36px; color:#8f8f8f; font-weight:bold; margin-bottom:5px;">{{ $resume['name'] }}</div>
                <div style="font-size:24px; color:#232743; font-weight:bold;">{{ $resume['title'] }}</div>
            </td>
            <td style="width:180px; vertical-align:top; padding:0; text-align:right;">
                <img src="{{ asset('images/logoEDO2.png') }}" alt="Logo2" width="180" height="150">
            </td>
        </tr>
    </table>
</header>

<div style="padding: 0 50px;">
    {{-- tot restul continutului de aici --}}
</div>

    {{-- ══ SYNTHESIS ══ --}}
    <div class="section-heading">SYNTHESIS</div>
    <p class="synthesis-text">{{ $resume['synthesis'] }}</p>

    <table class="info-table">
        <tr>
            <td>Nationality:</td>
            <td>{{ $resume['nationality'] }}</td>
        </tr>
        <tr>
            <td>Languages:</td>
            <td>
                {{ $resume['spoken_languages']['mother_tongue'][0] }}<br>
                @foreach($resume['spoken_languages']['foreign_languages'] as $lang)
                    {{ $lang['language'] }} - {{ $lang['level'] }}<br>
                @endforeach
            </td>
        </tr>
    </table>

    {{-- ══ EDUCATION ══ --}}
    @php
        $allEdu = array_merge(
            $resume['education']['general_school'] ?? [],
            $resume['education']['university'] ?? [],
            $resume['education']['master'] ?? [],
            $resume['education']['phd'] ?? []
        );
        $allEdu = array_filter($allEdu, fn($e) => !empty($e['institution']));
    @endphp
    @if(count($allEdu) > 0)
    <div class="section-heading">EDUCATION</div>
    <table class="edu-table">
        @foreach($allEdu as $edu)
        <tr>
            <td>
                {{ \Carbon\Carbon::parse($edu['start_date'])->format('M Y') }}
                –
                {{ $edu['end_date'] ? \Carbon\Carbon::parse($edu['end_date'])->format('M Y') : 'Present' }}
            </td>
            <td>{{ $edu['institution'] }}@if(!empty($edu['location'])), {{ $edu['location'] }}@endif</td>
            <td>{{ $edu['degree'] }}@if(!empty($edu['field_of_study'])), {{ $edu['field_of_study'] }}@endif</td>
        </tr>
        @endforeach
    </table>
    @endif

    {{-- ══ MAIN FUNCTIONAL / TECHNICAL SKILLS ══ --}}
    @php
        $sg = $resume['skills_grouped'];
        $skillLines = [
            ['label' => 'Frontend',  'value' => implode(', ', $sg['frontend'] ?? [])],
            ['label' => 'Backend',   'value' => implode(', ', $sg['backend'] ?? [])],
            ['label' => 'Databases', 'value' => implode(', ', $sg['databases'] ?? [])],
            ['label' => 'Tools',     'value' => implode(', ', $sg['tools'] ?? [])],
            ['label' => 'Other',     'value' => implode(', ', $sg['other'] ?? [])],
        ];
    @endphp
    <div class="section-heading">MAIN FUNCTIONAL / TECHNICAL SKILLS</div>
    <table class="skills-box">
        <tr>
            <td>
                @foreach($skillLines as $line)
                    @if(!empty($line['value']))
                    <div class="skill-line"><strong>{{ $line['label'] }}:</strong> {{ $line['value'] }}</div>
                    @endif
                @endforeach
            </td>
        </tr>
    </table>

    {{-- ══ COURSES, CERTIFICATES AND AWARDS ══ --}}
    @if(!empty($resume['courses']))
    <div class="section-heading">COURSES, CERTIFICATES AND AWARDS</div>
    <table class="certs-table">
        @foreach($resume['courses'] as $course)
        <tr><td>• &nbsp;{{ $course['name'] }}</td></tr>
        @endforeach
    </table>
    @endif

    {{-- ══ PROFESSIONAL EXPERIENCE ══ --}}
    @if(!empty($resume['experience']))
    <div class="section-heading">PROFESSIONAL EXPERIENCE</div>
    @foreach($resume['experience'] as $job)
    <table class="exp-box">
        <tr>
            <td>
                <table class="job-top-table">
                    <tr>
                        <td class="left-col">
                            @if(empty($job['end_date']))<strong>Since</strong><br>@endif
                            <strong>
                                {{ \Carbon\Carbon::parse($job['start_date'])->format('M Y') }}
                                –
                                {{ empty($job['end_date']) ? 'Present' : \Carbon\Carbon::parse($job['end_date'])->format('M Y') }}
                            </strong>
                            <br>
                            <!-- @if(!empty($job['company']))<br>{{ $job['company'] }}@endif -->
                            @if(!empty($job['company_domain'])) {{ $job['company_domain'] }}@endif
                        </td>
                        <td class="right-col"><strong>{{ $job['title'] }}</strong></td>
                    </tr>
                </table>

                @if(!empty($job['highlights']))
                <p class="resp-label">Main responsibilities:</p>
                <ul class="resp-list">
                    @foreach($job['highlights'] as $highlight)
                    <li>{{ $highlight }}</li>
                    @endforeach
                </ul>
                @endif
            </td>
        </tr>
    </table>
    @endforeach
    @endif

    {{-- ══ PERSONAL PROJECTS ══ --}}
    @if(!empty($resume['personal_projects']))
    <div class="section-heading">PERSONAL PROJECTS</div>
    <table class="proj-box">
        <tr>
            <td>
                @foreach($resume['personal_projects'] as $proj)
                    @if(!empty($proj['description']))
                    <p style="margin-bottom: 6px; text-align: justify; font-size: 17px;">{{ $proj['description'] }}</p>
                    @endif
                    @if(!empty($proj['technologies']))
                    <p style="margin-bottom: 10px; font-size: 17px;"><em>Tech Stack: {{ implode(', ', $proj['technologies']) }}</em></p>
                    @endif
                @endforeach
            </td>
        </tr>
    </table>
    @endif

</body>
</html>