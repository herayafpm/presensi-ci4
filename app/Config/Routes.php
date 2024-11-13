<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');
$routes->group('auth',['namespace' => 'App\Controllers\Api'],function($routes){
    $routes->post('login','AuthApi::login');
    $routes->post('forgotpass','AuthApi::forgotpass');
    $routes->post('forgotpass_code','AuthApi::forgotpass_code');
    $routes->post('forgotpass_code_reset','AuthApi::forgotpass_code_reset');
    $routes->post('logout','AuthApi::logout',['namespace' => 'App\Controllers\Api','filter' => 'auth:api']);
});
$routes->group('',['namespace' => 'App\Controllers\Api','filters' => 'auth'],function($routes){
    $routes->group('user',function($routes){
        $routes->get('profile','UserApi::profile');
        $routes->post('update_profile','UserApi::update_profile');
        $routes->post('update_password','UserApi::update_password');
        $routes->post('update_photo','UserApi::update_photo');
    });
    $routes->group('presence',['namespace' => 'App\Controllers\Api','filters' => 'auth:User'],function($routes){
        $routes->get('','UserPresenceApi::index',['filters' => 'auth:list_me_worktimes']);
        $routes->post('','UserPresenceApi::add_data',['filters' => 'auth:add_me_presence']);
        $routes->group('(:segment)',function($routes){
            $routes->get('','UserPresenceApi::detail_data/$1',['filters' => 'auth:list_me_worktimes']);
        });
    });
    $routes->group('admin',['namespace' => 'App\Controllers\Admin','filters' => 'auth'],function($routes){
        $routes->group('master',['namespace' => 'App\Controllers\Api\Admin\Master','filters' => 'auth:Admin'],function($routes){
            $routes->group('role',function($routes){
                $routes->get('','AdminMasterRoleApi::index',['filters' => 'auth:list_roles']);
                $routes->post('','AdminMasterRoleApi::add_data',['filters' => 'auth:add_role']);
                $routes->group('(:segment)',function($routes){
                    $routes->get('','AdminMasterRoleApi::detail_data/$1',['filters' => 'auth:list_roles']);
                    $routes->post('assign_permission','AdminMasterRoleApi::assign_permission_data/$1',['filters' => 'auth:assign_role_permission']);
                    $routes->post('update','AdminMasterRoleApi::update_data/$1',['filters' => 'auth:update_role']);
                    $routes->post('delete','AdminMasterRoleApi::delete_data/$1',['filters' => 'auth:delete_role']);
                    $routes->post('restore','AdminMasterRoleApi::restore_data/$1',['filters' => 'auth:restore_role']);
                    $routes->post('purge','AdminMasterRoleApi::purge_data/$1',['filters' => 'auth:purge_role']);
                });
            });
            $routes->group('permission',function($routes){
                $routes->get('','AdminMasterPermissionApi::index',['filters' => 'auth:list_permissions']);
                $routes->post('','AdminMasterPermissionApi::add_data',['filters' => 'auth:add_permission']);
                $routes->group('(:segment)',function($routes){
                    $routes->get('','AdminMasterPermissionApi::detail_data/$1',['filters' => 'auth:list_permissions']);
                    $routes->post('update','AdminMasterPermissionApi::update_data/$1',['filters' => 'auth:update_permission']);
                    $routes->post('delete','AdminMasterPermissionApi::delete_data/$1',['filters' => 'auth:delete_permission']);
                    $routes->post('restore','AdminMasterPermissionApi::restore_data/$1',['filters' => 'auth:restore_permission']);
                    $routes->post('purge','AdminMasterPermissionApi::purge_data/$1',['filters' => 'auth:purge_permission']);
                });
            });
            $routes->group('time',function($routes){
                $routes->get('','AdminMasterTimeApi::index',['filters' => 'auth:list_permissions']);
                $routes->post('','AdminMasterTimeApi::add_data',['filters' => 'auth:add_permission']);
                $routes->group('(:segment)',function($routes){
                    $routes->get('','AdminMasterTimeApi::detail_data/$1',['filters' => 'auth:list_permissions']);
                    $routes->post('update','AdminMasterTimeApi::update_data/$1',['filters' => 'auth:update_permission']);
                    $routes->post('delete','AdminMasterTimeApi::delete_data/$1',['filters' => 'auth:delete_permission']);
                    $routes->post('restore','AdminMasterTimeApi::restore_data/$1',['filters' => 'auth:restore_permission']);
                    $routes->post('purge','AdminMasterTimeApi::purge_data/$1',['filters' => 'auth:purge_permission']);
                });
            });
            $routes->group('employee',function($routes){
                $routes->get('','AdminMasterEmployeeApi::index',['filters' => 'auth:list_employees']);
                $routes->post('','AdminMasterEmployeeApi::add_data',['filters' => 'auth:add_employee']);
                $routes->group('(:segment)',function($routes){
                    $routes->get('','AdminMasterEmployeeApi::detail_data/$1',['filters' => 'auth:list_employees']);
                    $routes->post('update','AdminMasterEmployeeApi::update_data/$1',['filters' => 'auth:update_employee']);
                    $routes->post('update_password','AdminMasterEmployeeApi::update_password_data/$1',['filters' => 'auth:update_password_employee']);
                    $routes->post('delete','AdminMasterEmployeeApi::delete_data/$1',['filters' => 'auth:delete_employee']);
                    $routes->post('restore','AdminMasterEmployeeApi::restore_data/$1',['filters' => 'auth:restore_employee']);
                    $routes->post('purge','AdminMasterEmployeeApi::purge_data/$1',['filters' => 'auth:purge_employee']);
                });
            });
            $routes->group('user',function($routes){
                $routes->get('','AdminMasterUserApi::index',['filters' => 'auth:list_users']);
                $routes->post('','AdminMasterUserApi::add_data',['filters' => 'auth:add_user']);
                $routes->group('(:segment)',function($routes){
                    $routes->get('','AdminMasterUserApi::detail_data/$1',['filters' => 'auth:list_users']);
                    $routes->post('assign_role','AdminMasterUserApi::assign_role_data/$1',['filters' => 'auth:assign_user_role']);
                    $routes->post('assign_permission','AdminMasterUserApi::assign_permission_data/$1',['filters' => 'auth:assign_user_permission']);
                    $routes->post('assign_permission_denied','AdminMasterUserApi::assign_permission_denied_data/$1',['filters' => 'auth:assign_user_permission']);
                    $routes->post('update','AdminMasterUserApi::update_data/$1',['filters' => 'auth:update_user']);
                    $routes->post('update_password','AdminMasterUserApi::update_password_data/$1',['filters' => 'auth:update_password_user']);
                    $routes->post('delete','AdminMasterUserApi::delete_data/$1',['filters' => 'auth:delete_user']);
                    $routes->post('restore','AdminMasterUserApi::restore_data/$1',['filters' => 'auth:restore_user']);
                    $routes->post('purge','AdminMasterUserApi::purge_data/$1',['filters' => 'auth:purge_user']);
                });
            });
        });
        $routes->group('worktime',['namespace' => 'App\Controllers\Api\Admin','filters' => 'auth:Admin'],function($routes){
            $routes->get('','AdminWorkTimeApi::index',['filters' => 'auth:list_worktimes']);
            $routes->post('','AdminWorkTimeApi::add_data',['filters' => 'auth:add_worktime']);
            $routes->group('(:segment)',function($routes){
                $routes->get('','AdminWorkTimeApi::detail_data/$1',['filters' => 'auth:list_worktimes']);
                $routes->post('update','AdminWorkTimeApi::update_data/$1',['filters' => 'auth:update_worktime']);
                $routes->post('delete','AdminWorkTimeApi::delete_data/$1',['filters' => 'auth:delete_worktime']);
                $routes->post('restore','AdminWorkTimeApi::restore_data/$1',['filters' => 'auth:restore_worktime']);
                $routes->post('purge','AdminWorkTimeApi::purge_data/$1',['filters' => 'auth:purge_worktime']);
            });
        });
        $routes->group('presence',['namespace' => 'App\Controllers\Api\Admin','filters' => 'auth:Admin'],function($routes){
            $routes->get('','AdminPresenceApi::index',['filters' => 'auth:list_worktimes']);
            $routes->post('','AdminPresenceApi::add_data',['filters' => 'auth:add_worktime']);
            $routes->group('(:segment)',function($routes){
                $routes->get('','AdminPresenceApi::detail_data/$1',['filters' => 'auth:list_worktimes']);
                $routes->post('update','AdminPresenceApi::update_data/$1',['filters' => 'auth:update_worktime']);
                $routes->post('delete','AdminPresenceApi::delete_data/$1',['filters' => 'auth:delete_worktime']);
                $routes->post('restore','AdminPresenceApi::restore_data/$1',['filters' => 'auth:restore_worktime']);
                $routes->post('purge','AdminPresenceApi::purge_data/$1',['filters' => 'auth:purge_worktime']);
            });
        });
    });
});
