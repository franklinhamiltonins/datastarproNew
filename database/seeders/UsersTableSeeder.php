<?php



use Illuminate\Database\Seeder;
use App\Model\User;
use Illuminate\Support\Facades\Hash;
use App\Model\Role;
use Spatie\Permission\Models\Permission;

class UsersTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{

		//create Super Admin User
		$userSuperAdmin = User::create([
			'name' => 'Super Admin',
			'email' => 'super@admin.com',
			'password' => bcrypt('GoSocial2014')
		]);


		$roleSuperAdmin = Role::create(['name' => 'Super Admin']);
		$permissionsSuperAdmin = Permission::pluck('id', 'id')->all();
		$roleSuperAdmin->syncPermissions($permissionsSuperAdmin);
		$userSuperAdmin->assignRole([$roleSuperAdmin->id]);

		//create Admin user
		$userRolesAdmin = User::create([
			'name' => 'Admin',
			'email' => 'admin@admin.com',
			'password' => bcrypt('GoSocial2014')
		]);


		$roleRolesAdmin = Role::create(['name' => 'Admin']);
		$roleRolesAdmin->syncPermissions('dashboard-list', 'dashboard-create', 'dashboard-edit', 'dashboard-delete', 'lead-list', 'lead-create', 'lead-edit', 'lead-delete', 'contact-delete', 'lead-import', 'campaign-list', 'lead-file-list', 'lead-file-upload', 'lead-file-download', 'lead-file-delete', 'lead-action', 'campaign-update', 'campaign-delete', 'lead-export');
		$userRolesAdmin->assignRole([$roleRolesAdmin->id]);

		//create Users Admin user
		$userUsersAdmin = User::create([
			'name' => 'User',
			'email' => 'user@admin.com',
			'password' => bcrypt('GoSocial2014')
		]);


		$roleUsersAdmin = Role::create(['name' => 'User']);
		$roleUsersAdmin->syncPermissions('dashboard-list', 'dashboard-create', 'dashboard-edit', 'dashboard-delete', 'lead-list', 'lead-create', 'lead-edit', 'lead-import', 'campaign-list', 'lead-file-list', 'lead-file-upload', 'lead-file-download', 'lead-file-delete', 'lead-action', 'campaign-update', 'contact-delete', 'campaign-delete');
		$userUsersAdmin->assignRole([$roleUsersAdmin->id]);

		//create Dashboard Admin user
		$userDashboardAdmin = User::create([
			'name' => 'Guest',
			'email' => 'guest@admin.com',
			'password' => bcrypt('GoSocial2014')
		]);


		$roleDashboardAdmin = Role::create(['name' => 'Dashboard Admin']);
		$roleDashboardAdmin->syncPermissions('dashboard-list', 'user-list', 'lead-list', 'campaign-list', 'lead-file-list');
		$userDashboardAdmin->assignRole([$roleDashboardAdmin->id]);
	}
}
