<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\File;
use App\Model\Campaign;
use App\Model\LeadsModel\Lead;
use Illuminate\Support\Facades\Storage;
// use Redirect,Response;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;
use Symfony\Component\Mime\MimeTypes;

class FileUploadController extends Controller
{
    
    
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    function __construct()
    {       
        
        $this->middleware('permission:lead-file-upload|lead-file-download|lead-file-delete', ['only' => ['index','store']]);
        $this->middleware('permission:lead-file-upload', ['only' => ['fileUpload']]);
        $this->middleware('permission:lead-file-download', ['only' => ['file_download']]);
        $this->middleware('permission:lead-file-delete', ['only' => ['destroy']]);
       
    }

    
     /**
     * Upload file to Lead
     *
     *  * @return \Illuminate\Http\Response
     */
    public function fileUpload(Request $req,$id){
    
        
        $lead= Lead::find($id); //get the lead by id
        if(!$lead){
            toastr()->error('Something went wrong');
            return back();
        }
        
        if($req->file('files')){
            foreach($req->file('files') as $file){
                $fileName = time().'_'.$file->getClientOriginalName(); //create a timestamp filename
                $filePath = $file->storeAs('uploads', $fileName, 'public'); //create a file path for the file
                // associate the lead where the file is uploaded
                $lead->files()->create([
                    'name'=>time().'_'.$file->getClientOriginalName(),
                    'description'=>$req->description,
                    'file_path'=>'/app/public/' . $filePath
                ]);
                            
                //create log for this action
                create_log( $lead, 'Upload File: '.$fileName,'');
                //show success message
            }
            
            toastr()->success('File' .$fileName. ' has been uploaded.');
            
            return back()->with('file', $fileName);
        }
      
        //show the error message
        toastr()->error('You need to select a file in order to be uploaded!');
        return back();
    }


    /**
     * Download file
     *
     *  * @return \Illuminate\Http\Response
     */
    public function file_download(Request $req,$id){
        //get the file   
        // $file = File::find($id);
        // if(!$file->file_path){

            
        //     return false;
        //   }
        // return response()->download(storage_path($file->file_path));

        $file = File::find($id);

        if (!$file || !$file->file_path) {
            abort(404, 'File path missing.');
        }

        // Clean the DB path by removing leading /storage/
        $cleanPath = preg_replace('#^/?(storage|app/public)/#', '', $file->file_path);

        // Build full path
        $absolutePath = storage_path('app/public/' . $cleanPath);

        // echo $absolutePath;exit;

        if (!file_exists($absolutePath)) {
            abort(404, 'File does not exist at ' . $absolutePath);
        }

        return response()->download($absolutePath);
    }
    



   /**
       * Remove the specified resource from storage.
       *
       * @param  int  $id
       * @return \Illuminate\Http\Response
       */
    public function file_destroy_onlead($id){
        $file = File::findOrFail($id);
     
        $lead= $file->uploaded_files;
      
        create_log($lead, 'Delete File : '. $file->name,'');

        $file->delete();
        // unlink(storage_path($file->file_path)); // this model is using soft delete , this line is not longer necesary
       
        toastr()->success('file <b>'.$file->name.'</b> Deleted!');
        return redirect()->back();

    }

     /**
       * Remove the specified resource from storage.
       *
       * @param  int  $id
       * @return \Illuminate\Http\Response
       */
      public function file_destroy_oncampaign(Request $req){
        $id = (!empty($_GET["id"])) ? ($_GET["id"]) : ('');
       
        $file = File::findOrFail($id);
     
        $campaign= $file->uploaded_files;
      

        $file->delete();
        // unlink(storage_path($file->file_path)); // this model is using soft delete , this line is not longer necesary
        //Storage::delete($file->path);
        toastr()->success('File <b>'.$file->name.'</b> Deleted!');
    

    }

     /**
       * Upload the specified resource to storage.
       *
       * @param  int  $id
       * @return \Illuminate\Http\Response
       */
    public function upload_file_oncampaign(Request $request)
    {
       
       $id = $request->campaignId;//get the campaign id

      // if there is no file selected , toaster and return
       if(!$request->file()) { 
            toastr()->error('No file selected');
            $data='error';
        return response()->json(['error'=>'No file selected']);
        }   

        $extension = $request->file->getClientOriginalExtension();// get the file extension
        // if the file extension matches
        if($extension == 'png' ||  $extension == 'jpg' || $extension == 'jpeg' || $extension == 'pdf'){
         
            $campaign = Campaign::find($id);
            
            if(!$campaign){ // if there is not campaign id  

                toastr()->error('Something went wrong');
                return response()->json(['error'=>'Something went wrong, the campaign doesn\'t exist']);
                
                }
            
            $fileModel = new File; //new model file

            if(count($campaign->files) == 0){ //if there is no file in db for this campaign
                
                
                    //file details
                    $fileName = time().'_'.$request->file->getClientOriginalName(); //create a timestamp filename
                    $filePath = $request->file('file')->storeAs('uploads', $fileName, 'public'); //create a file path for the file
                    $fileModel->name = time().'_'.$request->file->getClientOriginalName(); //set the name in db
                    $fileModel->file_path = '/app/public/' . $filePath; //set the filepath 
                    $fileModel->save();   
                    // associate the file to this campaign
                    $campaign->files()->save($fileModel);
                
                
            }else if(count($campaign->files) > 0){ // if there are already files assigned to this campaign
            
                
                // if here is a file selected ( if there is not, update the rest of the fields)
                    $fileId = File::whereHasMorph('uploaded_files', [Campaign::class], function($query) use($id){
                                    $query->where('uploaded_files_id', $id);
                                })->first(); // get the existing file id 
                    //remove the old file from storage
                    // unlink(storage_path($fileId->file_path)); // this model is using soft delete , this line is not longer necesary

                    //new file details
                    $fileName = time().'_'.$request->file->getClientOriginalName(); //create a timestamp filename
                    $filePath = $request->file('file')->storeAs('uploads', $fileName, 'public'); //create a file path for the file
                    $fileModel->name = time().'_'.$request->file->getClientOriginalName(); //set the name in db
                    $fileModel->file_path = '/app/public/' . $filePath; //set the filepath 
                    $file = json_decode(json_encode($fileModel), true); // chnage the object to array so that it can be possible to use update fn
                    $campaign->files()->delete();
                       // associate the file to this campaign
                    $campaign->files()->save($fileModel);   
                
            }
            //if file was created , return success 
            toastr()->success('File <b/>' .$fileName. '</b> has been uploaded.');           
            return response()->json(['success'=>'File ' .$fileName. ' has been uploaded.']);
        }
        else{// if the file extension doesn't match, show error and erturn
      
        toastr()->error('The file must be a file of type: png,jpg or pdf.');
        return response()->json(['error'=>'The file must be a file of type: png, jpg or pdf.']);
        }
    
    }

    /**
     * Display datatables
     *
    
    */

    public function files_table(Request $request){

        $fileQuery = File::query(); //start query
        $leadId = (!empty($request->leadId)) ? $request->leadId : (''); //get the lead id
        
        // $fileQuery->where('lead_id', $leadId);// get the files for the specific lead id
        $fileQuery->whereHasMorph('uploaded_files', [Lead::class], function($query) use($leadId){
            $query->where('uploaded_files_id', $leadId);
        });
        
        $file = $fileQuery->select('*');

        // return the datatable
        return datatables()->of($file)
        ->addIndexColumn()

        ->editColumn('created_at', function ($fileQuery ){
            if(isset($fileQuery ->created_at)){//Fix for removing default date(01/01/1970) that Yajra adds to table when doesn't find any date in db
             return date('m/d/Y H:i:s', strtotime($fileQuery ->created_at) ); //format date
            }
        })
        ->filterColumn('created_at', function ($query, $keyword) {
            $query->whereRaw("DATE_FORMAT(created_at,'%m/%d/%Y%H:%i:%s') like ?", ["%$keyword%"]);//filter date
        })
        
        ->addColumn('action', function($row) {
                //send the params to upload-file-actions.blade
            $leadFileDownload     = 'lead-file-download';
            $leadFileDelete      = 'lead-file-delete';
                //return the partial
            return view('leads.partials.upload-file-actions',compact('leadFileDownload','leadFileDelete','row'));
        })
        ->rawColumns(['action'])        
        ->make(true);
        
        

    }

    /**
     * Retrieve the files from storage and make them accesible from browser
     * 
    * @param  int  $id, $filename
    * @return \Illuminate\Http\Response
    */
    public function retrieve_files_fromStorage($id,$filename){
     

        $file = File::find($id);//get the db file
        if(!$file){

            toastr()->error('The file was deleted previously');
            return back();
        }

        $ext = pathinfo($file->file_path, PATHINFO_EXTENSION);//get the extension
        $path = storage_path($file->file_path); //get the storage 
        //get the mime type to use it on showing the file
        $mimeTypes = new MimeTypes();
        $type= $mimeTypes->getMimeTypes($ext);
 
        //if the file is not fount in storage ,return with error
        if (!Storage::disk('public')->exists('/uploads/'.$file->name)) {
            
            toastr()->error('File not found on server');
            return back();
    
        }

        //get the file from storage
        $stFile=Storage::disk('public')->get('/uploads/'.$file->name);
        //create the file
        $response = Response::make( $stFile, 200);
        //add the content-type header
        $response->header("Content-Type", $type[0]);
        //return the file
        return $response;
    }
}
