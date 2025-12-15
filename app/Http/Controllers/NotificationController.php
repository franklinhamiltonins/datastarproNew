<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class NotificationController extends Controller
{
    public $hideColumns = ['created_at', 'updated_at', 'deleted_at'];
    // public $tableHeaders = ['created_at', 'updated_at', 'deleted_at'];
    public $tableHeaders = [
        [
            "columnName" => "id",
            "niceName" => "Id"
        ],
        [
            "columnName" => "smscontent",
            "niceName" => "SMS Content"
        ],
        [
            "columnName" => "contact_id",
            "niceName" => "Contact Name"
        ],
        [
            "columnName" => "user_id",
            "niceName" => "User Name"
        ]

    ];
    public $showActionLink = false;

    public function list(Request $request)
    {

        $request->showActionLink = $this->showActionLink;
        $request->tableHeaders = $this->tableHeaders;
        $request->hideColumns = $this->hideColumns;
        // $request->notificationData = $data;
        return parent::list($request);
    }
}