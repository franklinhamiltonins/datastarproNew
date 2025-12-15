<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .info-table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
            margin-top: 15px;
        }
        .info-table th, .info-table td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 10px;
        }
        .info-table th {
            background-color: #f8f9fa;
            color: #333;
        }
        .info-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-row td {
            background-color: #eaf4ff;
            font-weight: bold;
            color: #0d6efd;
        }
        .heading {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
        a {
            color: #0d6efd;
            text-decoration: none;
        }
    </style>
</head>
<body style="margin:0; padding:20px; font-family: Arial, sans-serif; background: #ffffff;">
    <div>
        <div class="heading">Bind Management Stage Changes Notification</div>
        <table class="info-table">
            <tr>
                <th>Lead ID</th>
                <td>{{ $data['leadId'] }}</td>
            </tr>
            <tr>
                <th>Lead Name</th>
                <td><a href="{{ route('leads.edit',base64_encode($data['leadId'])) }}">{{ $data['leadName'] }}</a></td>
            </tr>
            <tr>
                <th>Changed By</th>
                <td>{{ $data['agent_name'] }}</td>
            </tr>
            <tr class="status-row">
                <th>Stage</th>
                <td>
                    @if(!empty($data['old_stage_name']))
                        {{ $data['old_stage_name'] }} &#10132;
                    @endif
                    {{ $data['new_stage_name'] }}
                </td>
            </tr>

        </table>
    </div>
</body>
</html>