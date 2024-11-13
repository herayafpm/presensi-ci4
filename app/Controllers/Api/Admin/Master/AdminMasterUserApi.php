<?php

namespace App\Controllers\Api\Admin\Master;

use App\Controllers\Api\BaseApi;
use App\Models\RoleHasPermissionModel;
use App\Models\RoleModel;
use App\Models\UserHasPermissionModel;
use App\Models\UserHasRoleModel;
use App\Models\UserModel;

class AdminMasterUserApi extends BaseApi
{
    protected $modelName = UserModel::class;
    public function index()
    {
        $users = $this->model->select("ROW_NUMBER() OVER (ORDER BY user_name) AS no,users.*")->findAll();
        return $this->respond([
            'status' => '00',
            'message' => "Berhasil Mengambil Data Users",
            'data' => $users
        ], 200);
    }
    public function add_data()
    {
        $input = $this->request->getJSON();
        $rules = [
            'username' => [
                'label' => 'Username',
                'rules' => "required|is_unique[users.username]"
            ],
            'name' => [
                'label' => 'Nama',
                'rules' => "required"
            ],
            'email' => [
                'label' => 'Email',
                'rules' => "required|is_unique[users.email]"
            ],
            'password' => [
                'label' => 'Password',
                'rules' => "required|min_length[8]"
            ],
            'confirm_password' => [
                'label' => 'Konfirmasi Password',
                'rules' => "required|min_length[8]|matches[password]"
            ],
        ];
        if (!$this->validateData($input, $rules)) {
            return $this->respond([
                'status' => '40',
                'message' => "Validasi Gagal",
                'data' => $this->validator->getErrors()
            ], 400);
        }
        $avatar = new \LasseRafn\InitialAvatarGenerator\InitialAvatar();
        $image = $avatar->name($input['name'])->autoColor()->generate();
        $pp_name = uniqid().time().".png";
        $image->save(APPPATH."../storage/photo/".$pp_name);
        db_connect()->transBegin();
        model(UserModel::class)->save([
            'username' => $input['username'],
            'email' => $input['email'],
            'name' => $input['name'],
            'password' => $input['password'],
            'user_pp' => $pp_name
        ]);
        if (db_connect()->transStatus() == false) {
            db_connect()->transRollback();
            if(file_exists(APPPATH."../storage/photo/".$pp_name)){
                unlink(APPPATH."../storage/photo/".$pp_name);
            }
            return $this->respond([
                'status' => '40',
                'message' => "Gagal Menambah Data User"
            ], 400);
        } else {
            db_connect()->transCommit();
            return $this->respond([
                'status' => '00',
                'message' => "Berhasil Menambah Data User"
            ], 200);
        }
    }
    public function detail_data($id)
    {
        $user = $this->model->select("users.*")->find($id);
        if(!$user){
            return $this->respond([
                'status' => '44',
                'message' => "Data Users Tidak Ditemukan"
            ], 404);
        }
        $roles = model(UserHasRoleModel::class)->select('roles.role_id,roles.role_name')->join('roles','user_has_roles.role_id = roles.role_id')->where(['user_id' => $user['user_id']])->findAll();
        $user['permissions'] = array_merge(model(RoleHasPermissionModel::class)->select('permissions.permission_name,0 as enabled')->join('roles','role_has_permissions.role_id = roles.role_id')->whereIn('roles.role_id',array_column($roles,'role_id'))->findAll(),model(UserHasPermissionModel::class)->select("permissions.permission_name,1 as enabled")->join('permissions','user_has_permissions.permission_id = permissions.permission_id')->where(['user_id' => $user['user_id'],'is_denied' => 0])->findAll());
        $user['permission_denieds'] = model(UserHasPermissionModel::class)->select("permissions.permission_name")->join('permissions','user_has_permissions.permission_id = permissions.permission_id')->where(['user_id' => $user['user_id'],'is_denied' => 1])->findAll();
        return $this->respond([
            'status' => '00',
            'message' => "Berhasil Mengambil Data Users",
            'data' => $user
        ], 200);
    }
    public function update_data($id)
    {
        $input = $this->request->getJSON();
        $user = $this->model->select("users.*")->find($id);
        if(!$user){
            return $this->respond([
                'status' => '44',
                'message' => "Data User Tidak Ditemukan"
            ], 404);
        }
        $rules = [
            'username' => [
                'label' => 'Username',
                'rules' => "required|is_unique[users.username,user_id,{$user['user_id']}]"
            ],
            'name' => [
                'label' => 'Nama',
                'rules' => "required"
            ],
            'email' => [
                'label' => 'Email',
                'rules' => "required|is_unique[users.email,user_id,{$user['user_id']}]"
            ],
            'gender' => [
                'label' => 'Jenis Kelamin',
                'rules' => "required"
            ],
            'datebirth' => [
                'label' => 'Tanggal Lahir',
                'rules' => "required"
            ],
            'placebirth' => [
                'label' => 'Tempat Lahir',
                'rules' => "required"
            ]
        ];
        if (!$this->validateData($input, $rules)) {
            return $this->respond([
                'status' => '40',
                'message' => "Validasi Gagal",
                'data' => $this->validator->getErrors()
            ], 400);
        }
        if($user['name'] != $input['name']){
            $avatar = new \LasseRafn\InitialAvatarGenerator\InitialAvatar();
            $image = $avatar->name($input['name'])->autoColor()->generate();
            $pp_name = uniqid().time().".png";
            $image->save(APPPATH."../storage/photo/".$pp_name);
        }
        db_connect()->transBegin();
        $set_user = [
            'username' => $input['username'],
            'email' => $input['email'],
            'name' => $input['name']
        ];
        if(isset($pp_name)){
            $set_user['user_pp'] = $pp_name;
        }
        model(UserModel::class)->update($user['user_id'],$set_user);
        if (db_connect()->transStatus() == false) {
            db_connect()->transRollback();
            if(file_exists(APPPATH."../storage/photo/".$pp_name)){
                unlink(APPPATH."../storage/photo/".$pp_name);
            }
            return $this->respond([
                'status' => '40',
                'message' => "Gagal Mengupdate Data User"
            ], 400);
        } else {
            db_connect()->transCommit();
            if(isset($pp_name)){
                if(file_exists(APPPATH."../storage/photo/".$user['user_pp'])){
                    unlink(APPPATH."../storage/photo/".$user['user_pp']);
                }
            }
            return $this->respond([
                'status' => '00',
                'message' => "Berhasil Mengupdate Data User",
            ], 200);
        }
    }
    public function update_password_data($id)
    {
        $input = $this->request->getJSON();
        $user = $this->model->select("users.*")->find($id);
        if(!$user){
            return $this->respond([
                'status' => '44',
                'message' => "Data User Tidak Ditemukan"
            ], 404);
        }
        $rules = [
            'password' => [
                'label' => 'Password',
                'rules' => "required|min_length[8]"
            ],
            'confirm_password' => [
                'label' => 'Konfirmasi Password',
                'rules' => "required|min_length[8]|matches[password]"
            ],
        ];
        if (!$this->validateData($input, $rules)) {
            return $this->respond([
                'status' => '40',
                'message' => "Validasi Gagal",
                'data' => $this->validator->getErrors()
            ], 400);
        }
        db_connect()->transBegin();
        $set_user = [
            'password' => $input['password']
        ];
        model(UserModel::class)->update($user['user_id'],$set_user);
        if (db_connect()->transStatus() == false) {
            db_connect()->transRollback();
            return $this->respond([
                'status' => '40',
                'message' => "Gagal Mengupdate Password User"
            ], 400);
        } else {
            db_connect()->transCommit();
            return $this->respond([
                'status' => '00',
                'message' => "Berhasil Mengupdate Password User",
            ], 200);
        }
    }
    public function delete_data($id)
    {
        $user = $this->model->find($id);
        if(!$user){
            return $this->respond([
                'status' => '44',
                'message' => "Data User Tidak Ditemukan"
            ], 404);
        }
        try {
            db_connect()->transBegin();
            $this->model->delete($id);
            if (db_connect()->transStatus() == false) {
                db_connect()->transRollback();
                return $this->respond([
                    'status' => '40',
                    'message' => "Gagal Menghapus Data User (Sementara)"
                ], 400);
            } else {
                db_connect()->transCommit();
                return $this->respond([
                    'status' => '00',
                    'message' => "Berhasil Menghapus Data User (Sementara)"
                ], 200);
            }
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            if(strpos($th->getFile(),'Database') !== false && $th->getCode() == 1451){
                $message = "Data Masih Digunakan, tidak bisa dihapus";
            }
            return $this->respond([
                "status" => "50",
                "message" => $message
            ], 500);
        }
    }
    public function restore_data($id)
    {
        $user = $this->model->withDeleted(true)->find($id);
        if(!$user){
            return $this->respond([
                'status' => '44',
                'message' => "Data User Tidak Ditemukan"
            ], 404);
        }
        db_connect()->transBegin();
        $this->model->withDeleted(true)->update($id,[
            'deleted_at' => null
        ]);
        if (db_connect()->transStatus() == false) {
            db_connect()->transRollback();
            return $this->respond([
                'status' => '40',
                'message' => "Gagal Mengembalikan Data User"
            ], 400);
        } else {
            db_connect()->transCommit();
            return $this->respond([
                'status' => '00',
                'message' => "Berhasil Mengembalikan Data User"
            ], 200);
        }
    }
    public function purge_data($id)
    {
        $user = $this->model->withDeleted(true)->find($id);
        if(!$user){
            return $this->respond([
                'status' => '44',
                'message' => "Data User Tidak Ditemukan"
            ], 404);
        }
        try {
            db_connect()->transBegin();
            $this->model->delete($id,true);
            if (db_connect()->transStatus() == false) {
                db_connect()->transRollback();
                return $this->respond([
                    'status' => '40',
                    'message' => "Gagal Menghapus Data User Selamanya"
                ], 400);
            } else {
                db_connect()->transCommit();
                return $this->respond([
                    'status' => '00',
                    'message' => "Berhasil Menghapus Data User Selamanya"
                ], 200);
            }
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            if(strpos($th->getFile(),'Database') !== false && $th->getCode() == 1451){
                $message = "Data Masih Digunakan, tidak bisa dihapus";
            }
            return $this->respond([
                "status" => "50",
                "message" => $message
            ], 500);
        }
    }
    public function assign_role_data($id)
    {
        $input = $this->request->getJSON();
        $user = $this->model->find($id);
        if(!$user){
            return $this->respond([
                'status' => '44',
                'message' => "Data User Tidak Ditemukan"
            ], 404);
        }
        $input_roles = $input['roles'];
        $roles = model(UserHasRoleModel::class)->select('role_id')->where([
            'user_id' => $user['user_id'],
        ])->findAll();
        $roles = array_column($roles,'role_id');
        try {
            db_connect()->transBegin();
            // delete roles
            foreach($roles as $role){
                if(!in_array($role,$input_roles)){
                    model(UserHasRoleModel::class)->where([
                        'user_id' => $user['user_id'],
                        'role_id' => $role
                    ])->delete();
                }
            }
            // add roles
            foreach($input_roles as $inp_role){
                if(!in_array($inp_role,$roles)){
                    model(UserHasRoleModel::class)->save([
                        'user_id' => $user['user_id'],
                        'role_id' => $inp_role
                    ]);
                }
            }
            if (db_connect()->transStatus() == false) {
                db_connect()->transRollback();
                return $this->respond([
                    "status" => "40",
                    "message" => "Gagal Assign Role ke User"
                ], 400);
            } else {
                db_connect()->transCommit();
                return $this->respond([
                    "status" => "00",
                    "message" => "Berhasil Assign Role ke User"
                ], 200);
            }
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            if(strpos($th->getFile(),'Database') !== false && $th->getCode() == 1451){
                $message = "Data Masih Digunakan, tidak bisa dihapus";
            }
            return $this->respond([
                "status" => "50",
                "message" => $message
            ], 500);
        }
    }
    public function assign_permission_data($id)
    {
        $input = $this->request->getJSON();
        $user = $this->model->find($id);
        if(!$user){
            return $this->respond([
                'status' => '44',
                'message' => "Data User Tidak Ditemukan"
            ], 404);
        }
        $input_permissions = $input['permissions'];
        $roles = model(UserHasRoleModel::class)->select('roles.role_id,roles.role_name')->join('roles','user_has_roles.role_id = roles.role_id')->where(['user_id' => $user['user_id']])->findAll();
        $role_permissions = model(RoleHasPermissionModel::class)->whereIn('roles.role_id',array_column($roles,'role_id'))->findColumn('role_has_permissions.permission_id');
        foreach($input_permissions as &$inp_permission){
            if(in_array($inp_permission,$role_permissions)){
                unset($inp_permission);
            }
        }
        unset($inp_permission);
        $permissions = model(UserHasPermissionModel::class)->select('permission_id')->where([
            'user_id' => $user['user_id'],
            'is_denied' => 0
        ])->findAll();
        $permissions = array_column($permissions,'permission_id');
        try {
            db_connect()->transBegin();
            // delete permissions
            foreach($permissions as $permission){
                if(!in_array($permission,$input_permissions)){
                    model(UserHasPermissionModel::class)->where([
                        'user_id' => $user['user_id'],
                        'permission_id' => $permission
                    ])->delete();
                }
            }
            // add permissions
            foreach($input_permissions as $inp_permission){
                if(!in_array($inp_permission,$permissions)){
                    model(UserHasPermissionModel::class)->save([
                        'user_id' => $user['user_id'],
                        'permission_id' => $inp_permission,
                        'is_denied'     => 0
                    ]);
                }
            }
            if (db_connect()->transStatus() == false) {
                db_connect()->transRollback();
                return $this->respond([
                    "status" => "40",
                    "message" => "Gagal Assign Permission ke User"
                ], 400);
            } else {
                db_connect()->transCommit();
                return $this->respond([
                    "status" => "00",
                    "message" => "Berhasil Assign Permission ke User"
                ], 200);
            }
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            if(strpos($th->getFile(),'Database') !== false && $th->getCode() == 1451){
                $message = "Data Masih Digunakan, tidak bisa dihapus";
            }
            return $this->respond([
                "status" => "50",
                "message" => $message
            ], 500);
        }
    }
    public function assign_permission_denied_data($id)
    {
        $input = $this->request->getJSON();
        $user = $this->model->find($id);
        if(!$user){
            return $this->respond([
                'status' => '44',
                'message' => "Data User Tidak Ditemukan"
            ], 404);
        }
        $input_permissions = $input['permissions'];
        $permissions = model(UserHasPermissionModel::class)->select('permission_id')->where([
            'user_id' => $user['user_id'],
            'is_denied' => 1
        ])->findAll();
        $permissions = array_column($permissions,'permission_id');
        try {
            db_connect()->transBegin();
            // delete permissions
            foreach($permissions as $permission){
                if(!in_array($permission,$input_permissions)){
                    model(UserHasPermissionModel::class)->where([
                        'user_id' => $user['user_id'],
                        'permission_id' => $permission
                    ])->delete();
                }
            }
            // add permissions
            foreach($input_permissions as $inp_permission){
                if(!in_array($inp_permission,$permissions)){
                    $cek_permission = model(UserHasPermissionModel::class)->where([
                        'user_id' => $user['user_id'],
                        'permission_id' => $inp_permission
                    ])->first();
                    if($cek_permission){
                        model(UserHasPermissionModel::class)->update($cek_permission['user_has_permission_id'],[
                            'is_denied'     => 1
                        ]);
                    }else{
                        model(UserHasPermissionModel::class)->save([
                            'user_id' => $user['user_id'],
                            'permission_id' => $inp_permission,
                            'is_denied'     => 1
                        ]);
                    }
                }
            }
            if (db_connect()->transStatus() == false) {
                db_connect()->transRollback();
                return $this->respond([
                    "status" => "40",
                    "message" => "Gagal Assign Denied Permission ke User"
                ], 400);
            } else {
                db_connect()->transCommit();
                return $this->respond([
                    "status" => "00",
                    "message" => "Berhasil Assign Denied Permission ke User"
                ], 200);
            }
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            if(strpos($th->getFile(),'Database') !== false && $th->getCode() == 1451){
                $message = "Data Masih Digunakan, tidak bisa dihapus";
            }
            return $this->respond([
                "status" => "50",
                "message" => $message
            ], 500);
        }
    }
}