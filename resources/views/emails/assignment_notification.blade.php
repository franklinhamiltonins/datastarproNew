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
        <div class="heading">Representative Assignment Notification</div>
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
            <tr>
                <th>Assigned To</th>
                <td>{{ $data['assigned_user_name'] }}</td>
            </tr>
            <tr>
                <th>Pipedrive Status</th>
                <td>{{ $data['statusname'] }}</td>
            </tr>
            @if(!empty($data['stagename']))
                <tr>
                    <th>Bind Management Stage</th>
                    <td>{{ $data['stagename'] }}</td>
                </tr>
            @endif

        </table>
    </div>
</body>
</html>