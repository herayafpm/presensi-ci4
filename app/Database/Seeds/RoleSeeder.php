<?php

namespace App\Database\Seeds;

use App\Models\PermissionModel;
use App\Models\RoleHasPermissionModel;
use App\Models\RoleModel;
use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $datas = [
            [
                'role_name' => 'Superadmin',
                'permissions' => [
                    // User
                    'list_users',
                    'add_user',
                    'update_user',
                    'delete_user',
                    'restore_user',
                    'purge_user',
                    // User
                    // Roles
                    'list_roles',
                    'add_role',
                    'update_role',
                    'delete_role',
                    'restore_role',
                    'purge_role',
                    'assign_user_role',
                    'release_user_role',
                    // Roles
                    // Permissions
                    'list_permissions',
                    'add_permission',
                    'update_permission',
                    'delete_permission',
                    'restore_permission',
                    'purge_permission',
                    'assign_role_permission',
                    'release_role_permission',
                    'assign_user_permission',
                    'release_user_permission',
                    // Permissions
                    // Employee
                    'list_employees',
                    'add_employee',
                    'update_employee',
                    'delete_employee',
                    'restore_employee',
                    'purge_employee',
                    // Employee
                    // Times
                    'list_times',
                    'add_time',
                    'update_time',
                    'delete_time',
                    'restore_time',
                    'purge_time',
                    // Times
                    // Work Times
                    'list_worktimes',
                    'add_worktime',
                    'update_worktime',
                    'delete_worktime',
                    'restore_worktime',
                    'purge_worktime',
                    // Work Times
                    // Presence
                    'list_presences',
                    'add_presence',
                    'update_presence',
                    'delete_presence',
                    'restore_presence',
                    'purge_presence',
                    // Presence
                ]
            ],
            [
                'role_name' => 'Admin',
                'permissions' => [
                    // User
                    'list_users',
                    // User
                    // Employee
                    'list_employees',
                    'add_employee',
                    'update_employee',
                    'delete_employee',
                    'restore_employee',
                    'purge_employee',
                    // Employee
                    // Times
                    'list_times',
                    'add_time',
                    'update_time',
                    'delete_time',
                    'restore_time',
                    'purge_time',
                    // Times
                    // Work Times
                    'list_worktimes',
                    'add_worktime',
                    'update_worktime',
                    'delete_worktime',
                    'restore_worktime',
                    'purge_worktime',
                    // Work Times
                    // Presence
                    'list_presences',
                    'add_presence',
                    'update_presence',
                    'delete_presence',
                    'restore_presence',
                    'purge_presence',
                    // Presence
                ]
            ],
            [
                'role_name' => 'User',
                'permissions' => [
                    'list_me_worktimes',
                    'add_me_presence'
                ]
            ],
        ];
        foreach($datas as $data){
            if(model(RoleModel::class)->save($data)){
                $role_id = model(RoleModel::class)->getInsertID();
                foreach($data['permissions'] as $perm){
                    $permission = model(PermissionModel::class)->where('permission_name',$perm)->first();
                    if(!$permission){
                        if(model(PermissionModel::class)->save(['permission_name' => $perm])){
                            $permission = model(PermissionModel::class)->where('permission_name',$perm)->first();
                        }
                    }
                    if($permission){
                        model(RoleHasPermissionModel::class)->save([
                            'role_id' => $role_id,
                            'permission_id' => $permission['permission_id'] 
                        ]);
                    }
                }
            }
        }
    }
}
