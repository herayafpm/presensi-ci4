<?php

namespace App\Controllers\Api\Admin\Master;

use App\Controllers\Api\BaseApi;
use App\Models\RoleHasPermissionModel;
use App\Models\RoleModel;

class AdminMasterRoleApi extends BaseApi
{
    protected $modelName = RoleModel::class;
    public function index()
    {
        $roles = $this->model->select("ROW_NUMBER() OVER (ORDER BY role_name) AS no,roles.*")->findAll();
        return $this->respond([
            'status' => '00',
            'message' => "Berhasil Mengambil Data Roles",
            'data' => $roles
        ], 200);
    }
    public function add_data()
    {
        $input = $this->request->getJSON();
        $rules = [
            'role_name' => [
                'label' => 'Nama Role',
                'rules' => "required|is_unique[role.role_name]"
            ]
        ];
        if (!$this->validateData($input, $rules)) {
            return $this->respond([
                'status' => '40',
                'message' => "Validasi Gagal",
                'data' => $this->validator->getErrors()
            ], 400);
        }
        db_connect()->transBegin();
        $this->model->save([
            'role_name' => $input['role_name']
        ]);
        if (db_connect()->transStatus() == false) {
            db_connect()->transRollback();
            return $this->respond([
                'status' => '40',
                'message' => "Gagal Menambah Data Role"
            ], 400);
        } else {
            db_connect()->transCommit();
            return $this->respond([
                'status' => '00',
                'message' => "Berhasil Menambah Data Role"
            ], 200);
        }
    }
    public function detail_data($id)
    {
        $role = $this->model->find($id);
        if(!$role){
            return $this->respond([
                'status' => '44',
                'message' => "Data Role Tidak Ditemukan"
            ], 404);
        }
        $role['permissions'] = model(RoleHasPermissionModel::class)->select('permissions.permission_name')->join('permissions','role_has_permissions.permission_id = permissions.permission_id')->where(['role_id' => $role['role_id']])->findAll();
        return $this->respond([
            'status' => '00',
            'message' => "Berhasil Mengambil Data Role",
            'data' => $role
        ], 200);
    }
    public function update_data($id)
    {
        $input = $this->request->getJSON();
        $role = $this->model->find($id);
        if(!$role){
            return $this->respond([
                'status' => '44',
                'message' => "Data Role Tidak Ditemukan"
            ], 404);
        }
        $rules = [
            'role_name' => [
                'label' => 'Nama Role',
                'rules' => "required|is_unique[role.role_name,role_id,{$id}]"
            ]
        ];
        if (!$this->validateData($input, $rules)) {
            return $this->respond([
                'status' => '40',
                'message' => "Validasi Gagal",
                'data' => $this->validator->getErrors()
            ], 400);
        }
        db_connect()->transBegin();
        $this->model->update($id, [
            'role_name' => $input['role_name'],
        ]);
        if (db_connect()->transStatus() == false) {
            db_connect()->transRollback();
            return $this->respond([
                'status' => '40',
                'message' => "Gagal Mengupdate Data Role"
            ], 400);
        } else {
            db_connect()->transCommit();
            return $this->respond([
                'status' => '00',
                'message' => "Berhasil Mengupdate Data Role",
            ], 200);
        }
    }
    public function delete_data($id)
    {
        $role = $this->model->find($id);
        if(!$role){
            return $this->respond([
                'status' => '44',
                'message' => "Data Role Tidak Ditemukan"
            ], 404);
        }
        try {
            db_connect()->transBegin();
            $this->model->delete($id);
            if (db_connect()->transStatus() == false) {
                db_connect()->transRollback();
                return $this->respond([
                    'status' => '40',
                    'message' => "Gagal Menghapus Data Role (Sementara)"
                ], 400);
            } else {
                db_connect()->transCommit();
                return $this->respond([
                    'status' => '00',
                    'message' => "Berhasil Menghapus Data Role (Sementara)"
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
        $role = $this->model->withDeleted(true)->find($id);
        if(!$role){
            return $this->respond([
                'status' => '44',
                'message' => "Data Role Tidak Ditemukan"
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
                'message' => "Gagal Mengembalikan Data Role"
            ], 400);
        } else {
            db_connect()->transCommit();
            return $this->respond([
                'status' => '00',
                'message' => "Berhasil Mengembalikan Data Role"
            ], 200);
        }
    }
    public function purge_data($id)
    {
        $role = $this->model->withDeleted(true)->find($id);
        if(!$role){
            return $this->respond([
                'status' => '44',
                'message' => "Data Role Tidak Ditemukan"
            ], 404);
        }
        try {
            db_connect()->transBegin();
            $this->model->delete($id,true);
            if (db_connect()->transStatus() == false) {
                db_connect()->transRollback();
                return $this->respond([
                    'status' => '40',
                    'message' => "Gagal Menghapus Data Role Selamanya"
                ], 400);
            } else {
                db_connect()->transCommit();
                return $this->respond([
                    'status' => '00',
                    'message' => "Berhasil Menghapus Data Role Selamanya"
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
        $role = $this->model->find($id);
        if(!$role){
            return $this->respond([
                'status' => '44',
                'message' => "Data Role Tidak Ditemukan"
            ], 404);
        }
        $input_permissions = $input['permissions'];
        $permissions = model(RoleHasPermissionModel::class)->select('permissions.permission_id')->where([
            'role_id' => $role['role_id'],
        ])->findAll();
        $permissions = array_column($permissions,'permission_id');
        try {
            db_connect()->transBegin();
            // delete permissions
            foreach($permissions as $permission){
                if(!in_array($permission,$input_permissions)){
                    model(RoleHasPermissionModel::class)->where([
                        'role_id' => $role['role_id'],
                        'permission_id' => $permission
                    ])->delete();
                }
            }
            // add permissions
            foreach($input_permissions as $inp_permission){
                if(!in_array($inp_permission,$permissions)){
                    model(RoleHasPermissionModel::class)->save([
                        'role_id' => $role['role_id'],
                        'permission_id' => $inp_permission
                    ]);
                }
            }
            if (db_connect()->transStatus() == false) {
                db_connect()->transRollback();
                return $this->respond([
                    "status" => "40",
                    "message" => "Gagal Assign Permission ke Role"
                ], 400);
            } else {
                db_connect()->transCommit();
                return $this->respond([
                    "status" => "00",
                    "message" => "Berhasil Assign Permission ke Role"
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