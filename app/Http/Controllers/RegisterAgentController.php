<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\LeadsModel\Lead;

use DataTables;
use Redirect, Response;
use Validator;
use DB;

class RegisterAgentController extends Controller
{
    public function index()
    {
        return view('registeragent.index');
    }

    public function data(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['message' => 'Invalid request'], 400);
        }

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $orderColumnIndex = $request->input('order')[0]['column'] ?? 0;
        $orderColumnName = $request->input("columns.{$orderColumnIndex}.data", 'id');
        $orderDirection = $request->input('order')[0]['dir'] ?? 'desc';
        $searchValue = $request->input('search.value', '');

        $baseQuery = Lead::query();

        if(!empty($request->sunbiz_registered_name)){
            $baseQuery = $baseQuery->where("leads.sunbiz_registered_name","like","%".$request->sunbiz_registered_name."%");
        }

        if(!empty($request->sunbiz_registered_address)){
            $baseQuery = $baseQuery->where("leads.sunbiz_registered_address","like","%".$request->sunbiz_registered_address."%");
        }

        $data = $baseQuery->whereNotNull("leads.sunbiz_registered_name")->whereNotNull("leads.sunbiz_registered_address")->groupBy("leads.sunbiz_registered_name")->groupBy("leads.sunbiz_registered_address")->select("leads.sunbiz_registered_name","leads.sunbiz_registered_address",DB::raw("COUNT(*) as associated_lead"))

        ->orderBy($orderColumnName, $orderDirection);

        return Datatables::of($data)
            ->make(true);
    }
}
