Laravel 7 boilerplate with AdminLTE 3 and toastr notifications

composer install

Uncomment all lines in seeders/DatabaseSeeder.php

Run php artisan migrate:fresh --seed

Seed adds default:
Permissions
Demo Leads
User & Roles
- super admin - super@admin.com / GoSocial2014 - all permissions granted
- admin - admin@admin.com / GoSocial2014 -  access to everything except for User Management
- user - user@admin.com / GoSocial2014 -  access to everything except for User Management. Can’t export leads CSVs and can’t delete leads.
- guest  - guest@admin.com / GoSocial2014 - view Only access 



