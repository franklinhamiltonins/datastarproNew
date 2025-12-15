<?php
  

  
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
  
class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

 

 //  dashboard page
        Permission::create([
            'name'=>'dashboard-list',
            'page'=>'Dashboard Page',
        ]);
        Permission::create([
            'name'=>'dashboard-create',
            'page'=>'Dashboard Page',
        ]);
        Permission::create([
            'name'=>'dashboard-edit',
            'page'=>'Dashboard Page',
        ]);
        Permission::create([
            'name'=>'dashboard-delete',
            'page'=>'Dashboard Page',
        ]);


 //  role page
        Permission::create([
            'name'=>'role-list',
            'page'=>'Roles Page',
        ]);
        Permission::create([
            'name'=>'role-create',
            'page'=>'Roles Page',
        ]);
        Permission::create([
            'name'=>'role-edit',
            'page'=>'Roles Page',
        ]);
        Permission::create([
            'name'=>'role-delete',
            'page'=>'Roles Page',
        ]);

//  user page
        Permission::create([
            'name'=>'user-list',
            'page'=>'Users Page',
        ]);
        Permission::create([
            'name'=>'user-create',
            'page'=>'Users Page',
        ]);
        Permission::create([
            'name'=>'user-edit',
            'page'=>'Users Page',
        ]);
        Permission::create([
            'name'=>'user-delete',
            'page'=>'Users Page',
        ]);



        

// leads file uploads
        Permission::create([
            'name'=>'lead-file-list',
            'page'=>'Leads Files',
        ]);
        Permission::create([
            'name'=>'lead-file-upload',
            'page'=>'Leads Files',
        ]);
        Permission::create([
            'name'=>'lead-file-download',
            'page'=>'Leads Files',
        ]);
        Permission::create([
            'name'=>'lead-file-delete',
            'page'=>'Leads Files',
        ]);

 //lead page
        
        Permission::create([
            'name'=>'lead-list',
            'page'=>'Leads Page',
        ]);
        Permission::create([
            'name'=>'lead-create',
            'page'=>'Leads Page',
        ]);
        Permission::create([
            'name'=>'lead-edit',
            'page'=>'Leads Page',
        ]);
        Permission::create([
            'name'=>'lead-delete',
            'page'=>'Leads Page',
        ]);
        Permission::create([
            'name'=>'lead-import',
            'page'=>'Leads Page',
        ]);
        Permission::create([
            'name'=>'lead-export',
            'page'=>'Leads Page',
        ]);
        Permission::create([
            'name'=>'lead-action',
            'page'=>'Leads Page',
        ]);
//marketing page
        Permission::create([
            'name'=>'campaign-list',
            'page'=>'Marketing Campaigns Page',
        ]);
        Permission::create([
            'name'=>'campaign-update',
            'page'=>'Marketing Campaigns Page',
        ]);
        Permission::create([
            'name'=>'campaign-delete',
            'page'=>'Marketing Campaigns Page',
        ]);

    }
}