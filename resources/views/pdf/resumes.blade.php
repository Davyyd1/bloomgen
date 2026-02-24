<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $resume['name'] ?? 'CV' }} - CV</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { margin: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 17px;
            color: #000;
            line-height: 1.4;
            margin-bottom: 50px; /* cel puțin cât înălțimea footer-ului */

        }

        .page-wrapper {
            max-width: 950px;
            margin: 0 auto;
            padding: 0 50px;
        }

        /* HEADER */
        .header {
            width: 100%;
            padding: 10px 50px;
        }

        .header table { margin-bottom: 0; }

        .header td { vertical-align: middle; padding: 0; }

        .header-logo { width: 210px; }

        .header-center {
            text-align: right;
            padding-right: 130px;
            padding-top: 140px;
            vertical-align: bottom; /* textul se duce jos */
        }

        .header-text {
            display: inline-block;
            text-align: center;
        }

        .header-name  { font-size: 36px; color: #8f8f8f; font-weight: bold; }
        .header-title { font-size: 28px; color: #232743; font-weight: bold; padding-right:20px;}

        .logo-right {
            position: fixed;
            top: 0;
            right: 0;
        }

        /* SECTIONS */
        .section-heading {
            font-size: 24px;
            font-weight: bold;
            color: #e20000;
            border-bottom: 2px solid #e20000;
            margin: 20px 0 10px;
        }

        /* TABLES */
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }

        .bordered, .bordered td { border: .5px solid #b7b7b7; }
        .bordered td { padding: 6px 8px; vertical-align: top; }

        .certs-row { border-bottom: 1px solid #999; }
        .certs-row:last-child { border-bottom: none; }
        .certs-row td { padding: 5px 10px; }

        .info td { padding: 2px 8px 2px 0; vertical-align: top; }
        .info td:first-child { font-weight: bold; white-space: nowrap; }

        .edu td:first-child { width: 120px; white-space: nowrap; }
        .edu td:nth-child(2) { width: 230px; }

        .job-header td { padding: 0; vertical-align: middle; }
        .job-header .left { width: 50%; }
        .job-header .right { width: 50%; text-align: center; }

        .resp-label { font-weight: bold; margin: 10px 0 4px; }

        ul.resp { list-style: none; padding: 0; margin: 0; }
        ul.resp li { padding-left: 20px; position: relative; margin-bottom: 4px; text-align: justify; }
        ul.resp li::before { content: "\27A4"; position: absolute; left: 0; font-size: 8pt; top: 2px; }

        .synthesis-text { text-align: justify; margin-bottom: 12px; }

        footer {
    position: fixed;
    bottom: -40px; /* negativ, egal cu height */
    left: 0;
    right: 0;
    height: 40px;
    border-top: 1px solid #ddd;
}

        .page-counter::after { content: counter(page); }
        .page-total::after   { content: counter(pages); }
    </style>
</head>
<body>
    <div class="logo-right">
        <img src="{{ asset('images/logoEDO2.png') }}" width="120" height="140">
    </div>

    <div class="header">
        <table style="height: 220px;">
            <tr>
                <td class="header-logo" style="vertical-align: top;">
                    <img src="{{ asset('images/logoEDO.png') }}" width="200" height="130">
                </td>
                <td class="header-center" style="vertical-align: bottom; padding-bottom: 10px;">
                    <div class="header-text">
                        <div class="header-name">{{ $resume['name'] }}</div>
                        <div class="header-title" style="margin-top:50px;">{{ $resume['title'] }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="page-wrapper">

        <div class="section-heading">SYNTHESIS</div>
        <p class="synthesis-text">{{ $resume['synthesis'] }}</p>

        <table class="info">
            <tr><td>Nationality:</td><td>{{ $resume['nationality'] }}</td></tr>
            <tr>
                <td>Languages:</td>
                <td>
                    {{ !empty($resume['spoken_languages']['mother_tongue'][0]) ?  $resume['spoken_languages']['mother_tongue'][0] : ''}}<br>
                    @foreach($resume['spoken_languages']['foreign_languages'] as $lang)
                        {{ $lang['language'] }} - {{ $lang['level'] }}<br>
                    @endforeach
                </td>
            </tr>
        </table>

        {{-- EDUCATION --}}
        @php
            $allEdu = array_filter(
                array_merge(
                    $resume['education']['general_school'] ?? [],
                    $resume['education']['university'] ?? [],
                    $resume['education']['master'] ?? [],
                    $resume['education']['phd'] ?? []
                ),
                fn($e) => !empty($e['institution'])
            );
        @endphp
        @if(count($allEdu) > 0)
        <div class="section-heading">EDUCATION</div>
        <table class="bordered edu">
            @foreach($allEdu as $edu)
            <tr>
                <td>{{ \Carbon\Carbon::parse($edu['start_date'])->format('M Y') }} – {{ $edu['end_date'] ? \Carbon\Carbon::parse($edu['end_date'])->format('M Y') : 'Present' }}</td>
                <td>{{ $edu['institution'] }}{{ !empty($edu['location']) ? ', '.$edu['location'] : '' }}</td>
                <td>{{ $edu['degree'] }}{{ !empty($edu['field_of_study']) ? ', '.$edu['field_of_study'] : '' }}</td>
            </tr>
            @endforeach
        </table>
        @endif

        {{-- SKILLS --}}
        @php
            $sg = $resume['skills_grouped'];
            $skillLines = [
                'Frontend'  => implode(', ', $sg['frontend']  ?? []),
                'Backend'   => implode(', ', $sg['backend']   ?? []),
                'Databases' => implode(', ', $sg['databases'] ?? []),
                'Tools'     => implode(', ', $sg['tools']     ?? []),
                'Other'     => implode(', ', $sg['other']     ?? []),
            ];
        @endphp
        <div class="section-heading">MAIN FUNCTIONAL / TECHNICAL SKILLS</div>
        <table class="bordered"><tr><td>
            @foreach($skillLines as $label => $value)
                @if($value)<div style="margin-bottom:4px"><strong>{{ $label }}:</strong> {{ $value }}</div>@endif
            @endforeach
        </td></tr></table>

        {{-- COURSES --}}
        @if(!empty($resume['courses']))
        <div class="section-heading">COURSES, CERTIFICATES AND AWARDS</div>
        <table class="bordered">
            @foreach($resume['courses'] as $course)
            <tr class="certs-row"><td>• {{ $course['name'] }}</td></tr>
            @endforeach
        </table>
        @endif

        {{-- EXPERIENCE --}}
        @if(!empty($resume['experience']))
        <div class="section-heading">PROFESSIONAL EXPERIENCE</div>
        @foreach($resume['experience'] as $job)
        <table class="bordered" style="margin-bottom:10px"><tr><td>
            <table class="job-header">
                <tr>
                    <td class="left">
                        
                        {{ $job['company_domain'] ?? '' }}
                    </td>
                    <td class="right"><strong>{{ $job['title'] }}</strong></td>
                </tr>
            </table>
            @if(!empty($job['highlights']))
            <div class="resp-label">Main responsibilities:</div>
            <ul class="resp">
                @foreach($job['highlights'] as $h)<li>{{ $h }}</li>@endforeach
            </ul>
            @endif
        </td></tr></table>
        @endforeach
        @endif

        {{-- PROJECTS --}}
        @if(!empty($resume['personal_projects']))
        <div class="section-heading">PERSONAL PROJECTS</div>
        <table class="bordered"><tr><td>
            @foreach($resume['personal_projects'] as $proj)
                @if(!empty($proj['description']))<p style="margin-bottom:6px; text-align:justify">{{ $proj['description'] }}</p>@endif
                @if(!empty($proj['technologies']))<p style="margin-bottom:10px"><em>Tech Stack: {{ implode(', ', $proj['technologies']) }}</em></p>@endif
            @endforeach
        </td></tr></table>
        @endif
    </div>
</body>
</html>