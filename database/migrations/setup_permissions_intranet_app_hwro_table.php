<?php

use Hwkdo\IntranetAppBase\IntranetAppBase;
use Hwkdo\IntranetAppRaumverwaltung\IntranetAppRaumverwaltung;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      $permissions = IntranetAppBase::getRequiredPermissionsFromAppConfig(
        config("intranet-app-hwro")
      );
      
      foreach ($permissions as $permission) {
        Permission::findOrCreate($permission);
      }
      
      $roles = IntranetAppBase::getRolesWithPermissionsFromAppConfig(
        config("intranet-app-hwro")
      );
      
      foreach ($roles as $rolee) {
        $role = Role::findOrCreate($rolee["name"]);
        $role->givePermissionTo($rolee["permissions"]);
      }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      $permissions = IntranetAppBase::getRequiredPermissionsFromAppConfig(
        config("intranet-app-hwro")
      );

      foreach($permissions as $permission) {
        Permission::where('name', $permission)->delete();
      }

      $roles = IntranetAppBase::getRolesWithPermissionsFromAppConfig(
        config("intranet-app-hwro")
      );

      foreach($roles as $role) {
        Role::where('name', $role['name'])->delete();
      }
    }
};
