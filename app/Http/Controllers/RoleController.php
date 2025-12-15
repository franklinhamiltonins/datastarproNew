<?php
    
namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Role;
use App\Model\User;
use Spatie\Permission\Models\Permission;
use DB;
    
class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
 
    function __construct()
    {
         $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index','store']]);
         $this->middleware('permission:role-create', ['only' => ['create','store']]);
         $this->middleware('permission:role-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:role-delete', ['only' => ['destroy']]);
         
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        //paginate the roles table and sort it asc
        $roles = Role::orderBy('id','ASC')->paginate(10);
        return view('roles.index',compact('roles'))
            ->with('i', ($request->input('page', 1) - 1) * 10);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   //get the permissions from DB
        $permission = Permission::get();
        // get all pages column data from permissions table in order to sort permissions by page  , in blade
        $permissionPage =Permission::pluck('page','page')->all();
        return view('roles.create',compact('permission','permissionPage'));
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        //validate the form
        $this->validate($request, [
            'name' => 'required|string|max:191|unique:roles,name',
            'permission' => 'required',
        ]);
        //create role
        $role = Role::create(['name' => $request->input('name')]);
        //attach permissions to role
        $role->syncPermissions($request->input('permission'));
    
        
        toastr()->success('Role <b>'. $role->name.'</b> created successfully!');
        return redirect()->route('roles.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {   
        //get the role
        $id = base64_decode($id);
        $role = Role::find($id);
        if(!$role){

            toastr()->error('This Role doesn\'t exist');
            return back();
          }
        // get all pages column data from permissions table in order to sort permissions by page  , in blade
        $permissionPage =Permission::pluck('page','page')->all();
        //get the permissioins the role has
        $rolePermissions = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
            ->where("role_has_permissions.role_id",$id)
            ->get();
    
        return view('roles.show',compact('role','rolePermissions','permissionPage'));
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
        $id = base64_decode($id);
        $role = Role::find($id);
        if(!$role){

            toastr()->error("This Role doesn't exist");
            return back();
          }
        //get all permissions
        $permission = Permission::select('id','name','guard_name','page')->get();
        // get all pages column data from permissions table in order to sort permissions by page  , in blade
        $permissionPage =Permission::pluck('page','page')->all();
        //get the permissioins the role has
        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id",$id)
            ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
            ->all();
        // echo "<pre>";print_r($rolePermissions);exit;

        $isClone = request()->query('action') === 'clone';
        if ($isClone) {
            // Modify the name just for the form (not saved yet)
            $role->name = $role->name . ' clone';
        }
    
        return view('roles.edit',compact('role','permission','rolePermissions','permissionPage','isClone'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //validate form
        // echo "<pre>";print_r($request->input('permission'));exit;
        $this->validate($request, [
            'name' => 'required|string|max:191',
            'permission' => 'required',
        ]);
        //get the role 
        $isClone = $request->input('is_clone', false);

        if ($isClone) {
            $role = Role::create([
                'name' => $request->input('name'),
                'guard_name' => 'web',
            ]);
        } else {
            $role = Role::find($id);
            if(!$role){
                toastr()->error('Something went wrong');
                return redirect()->back();
            }
            $role->name = $request->input('name');
            $role->save();
        }
        // Attach permissions
        $role->syncPermissions($request->input('permission'));

        toastr()->success('Role <b>'.$role->name.'</b> updated successfully!');
        return redirect()->route('roles.index');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
        //get the role 
        $role = Role::find($id);
        if(!$role){
            toastr()->error('The Roles was removed previously');
            return back();
        }
        $roleName = $role->name;
        //search if any user has this role
        $users=User::role($roleName)->get() ;
        // if the role it's assigned , don't delete it
        if($users->count() > 0){
            toastr()->error('You cannot delete assigned roles!');
            return redirect()->back();
        }
        // rename role if deleted - to fix the Unique issue
        $role->update([
            'name' => time() . '::' . $role->name
            ]);
        $role->delete();
      
        toastr()->success('Role <b>'. $role->name.'</b> deleted successfully!');
          return redirect()->back();
    }
}