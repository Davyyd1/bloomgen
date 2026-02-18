<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resumes</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h1 {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
        }

        th {
            background: #f3f3f3;
            text-align: left;
        }
    </style>
</head>
<body>

<h1>Resumes</h1>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Text</th>
        </tr>
    </thead>
    <tbody>
        @foreach($resumes as $resume)
            <tr>
                <td>{{ $resume->name }}</td>
                <td>{{ $resume->text }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
