<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Model\Role;
use App\Model\Campaign;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Action;
use App\Model\File;
use Redirect,Response;
use Carbon\Carbon;

class CampaignController extends Controller
{
    
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    function __construct()
    {       
        
        $this->middleware('permission:campaign-list|campaign-update|campaign-delete', ['only' => ['index','store']]);
        $this->middleware('permission:campaign-update', ['only' => ['edit','update']]);
        $this->middleware('permission:campaign-delete', ['only' => ['destroy']]);
       
    }
    public function index(Request $request)
    {   
      
        return view('campaigns.index'); 
    }

    public function campaigns_table(){

        $campaignQuery = Campaign::query(); //start query
     
        $campaign = $campaignQuery->select('*');
        
        // return the datatable
        return datatables()->of($campaign)
        ->addIndexColumn()

        ->editColumn('created_at', function ($campaignQuery ){
            if(isset($campaignQuery ->created_at)){//Fix for removing default date(01/01/1970) that Yajra adds to table when doesn't find any date in db
                return date('m/d/Y', strtotime($campaignQuery ->created_at) ); //format date
            }
        })
        ->filterColumn('created_at', function ($query, $keyword) {
            $query->whereRaw("DATE_FORMAT(created_at,'%m/%d/%Y') like ?", ["%$keyword%"]);//filter date
        })
        ->editColumn('campaign_date', function ($campaignQuery ){
            if(isset($campaignQuery ->campaign_date)){//Fix for removing default date(01/01/1970) that Yajra adds to table when doesn't find any date in db
                return date('m/d/Y', strtotime($campaignQuery ->campaign_date) ); //format date
            }
        })
        ->filterColumn('campaign_date', function ($query, $keyword) {
            $query->whereRaw("DATE_FORMAT(campaign_date,'%m/%d/%Y') like ?", ["%$keyword%"]);//filter date
        })
        ->addColumn('user_actions', function($row) {
            $campaign = Campaign::find($row->id);//get the campaign
            $actions = "No Campaign Date"; // set default 
            if($campaign->campaign_date && $campaign->status == "COMPLETED"){
               $actions = $campaign->lead_actions ? $campaign->lead_actions : 0;
            }else if ($campaign->campaign_date && $campaign->status == "PENDING"){
                $actions = "Campaign Pending";
            }
            return  $actions ;
        })
        
        ->addColumn('action', function($row) {
                //send the params to campaign-action-buttons.blade.php
                $updateCampaign      = 'campaign-update';
                $deleteCampaign      = 'campaign-delete';
                $crudRoutePart = 'campaign';
             //return the partial having action buttons
            return view('campaigns.partials.campaign-action-buttons',compact('updateCampaign','deleteCampaign','row'));
        })
        ->rawColumns(['action'])        
        ->make(true);
        
        

    }


     /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {   
        //get the role
        $campaign = Campaign::find($id);
        if(!$campaign){

            toastr()->error('This Campaign doesn\'t exist');
            return back();
          }
        
    
        return view('campaigns.edit',compact('campaign'));
    }
     /**
       * Update the specified resource in storage. 
       * Campaign
       * @param  \Illuminate\Http\Request  $request
       * @param  int  $id
       * @return \Illuminate\Http\Response
       */
      public function update(Request $request, $id)
      {
          //validate form
       
        $rules = [
            'name' => 'required|string|max:191',
            'status' => 'required|string|max:191',
            'campaign_date' => 'required|string|max:191|date_format:Y-m-d',
            'type' => 'nullable|string|max:191',
            'size' => 'nullable|string|max:191',
            
          ];  
          $niceNames = [
            'name' => 'Campaign Name',
            'status' => 'Status',
            'campaign_date' => 'Campaign Date',
            'type' => 'Type',
            'size' => 'Size',
          ]; 
           
            //validate fields using nice name in error messages
            $this->validate($request, $rules, [], $niceNames);
          
           
            $input = $request->all();
            //if pass is not empty, update it
            
            // get the user and update it
            $campaign = Campaign::find($id);
            if(!$campaign){

                toastr()->error('Something went wrong');
                return back();
                
                }
            // if(count($campaign->files) == 0){ //if there is no file in db for this campaign
        
            //         toastr()->error('The Creative file is required');
            //         return back();
            //     }
            
            $campaign->update($input);

            update_leadActions($campaign);      
                   
          
            // return to campaigns page with success
            toastr()->success('Campaign <b>'. $campaign->name.'</b> updated successfully');
            return redirect()->route('campaigns.index');
      }
     
 

      /**
       * Display the specified resource.
       *
       * @param  int  $id
       * @return \Illuminate\Http\Response
       */
      public function show($id)
      {     
          //get campaign
          $campaign = Campaign::find($id);
          if(!$campaign){

            toastr()->error('This Campaign doesn\'t exist');
            return back();
          }
          $actions = "No Campaign Date"; // set default 
          if($campaign->campaign_date){
            $actions = $campaign->lead_actions ? $campaign->lead_actions : 0;
          }
          return view('campaigns.show',compact('campaign','actions'));
      }




      /**
       * Remove the specified resource from storage.
       *
       * @param  int  $id
       * @return \Illuminate\Http\Response
       */
      public function destroy($id)
      {
        //get the campaign
        $campaign = Campaign::findOrFail($id);
        if(!$campaign){

            toastr()->error('The User was removed previously');
            return back();
          }
        // find and remove from db and storage the uploaded files
        if(count($campaign->files) > 0){
            foreach($campaign->files as $file)
            {
                // unlink(storage_path($file->file_path)); // this model is using soft delete , this line is not longer necesary
                $file->delete();
            }
        }
         
        $campaign->delete(); // remove the campaign
        toastr()->success('Campaign <b>'. $campaign->name.'</b> Deleted!');
        return back();      
  
      }

    
      
}
