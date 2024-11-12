<?php

namespace App\Controllers\Api\Admin\Master;

use App\Controllers\Api\BaseApi;
use App\Models\PermissionModel;

class AdminMasterPermissionApi extends BaseApi
{
    protected $modelName = PermissionModel::class;
    public function index()
    {
        $permissions = $this->model->select("ROW_NUMBER() OVER (ORDER BY permission_name) AS no,permissions.*")->findAll();
        return $this->respond([
            'status' => '00',
            'message' => "Berhasil Mengambil Data Permissions",
            'data' => $permissions
        ], 200);
    }
    public function add_data()
    {
        $input = $this->request->getJSON();
        $rules = [
            'permission_name' => [
                'label' => 'Nama Permission',
                'rules' => "required|is_unique[permission.permission_name]"
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
            'permission_name' => $input['permission_name']
        ]);
        if (db_connect()->transStatus() == false) {
            db_connect()->transRollback();
            return $this->respond([
                'status' => '40',
                'message' => "Gagal Menambah Data Permission"
            ], 400);
        } else {
            db_connect()->transCommit();
            return $this->respond([
                'status' => '00',
                'message' => "Berhasil Menambah Data Permission"
            ], 200);
        }
    }
    public function detail_data($id)
    {
        $permission = $this->model->find($id);
        if(!$permission){
            return $this->respond([
                'status' => '44',
                'message' => "Data Permission Tidak Ditemukan"
            ], 404);
        }
        return $this->respond([
            'status' => '00',
            'message' => "Berhasil Mengambil Data Permission",
            'data' => $permission
        ], 200);
    }
    public function update_data($id)
    {
        $input = $this->request->getJSON();
        $permission = $this->model->find($id);
        if(!$permission){
            return $this->respond([
                'status' => '44',
                'message' => "Data Permission Tidak Ditemukan"
            ], 404);
        }
        $rules = [
            'permission_name' => [
                'label' => 'Nama Permission',
                'rules' => "required|is_unique[permission.permission_name,permission_id,{$id}]"
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
            'permission_name' => $input['permission_name'],
        ]);
        if (db_connect()->transStatus() == false) {
            db_connect()->transRollback();
            return $this->respond([
                'status' => '40',
                'message' => "Gagal Mengupdate Data Permission"
            ], 400);
        } else {
            db_connect()->transCommit();
            return $this->respond([
                'status' => '00',
                'message' => "Berhasil Mengupdate Data Permission",
            ], 200);
        }
    }
    public function delete_data($id)
    {
        $permission = $this->model->find($id);
        if(!$permission){
            return $this->respond([
                'status' => '44',
                'message' => "Data Permission Tidak Ditemukan"
            ], 404);
        }
        try {
            db_connect()->transBegin();
            $this->model->delete($id);
            if (db_connect()->transStatus() == false) {
                db_connect()->transRollback();
                return $this->respond([
                    'status' => '40',
                    'message' => "Gagal Menghapus Data Permission (Sementara)"
                ], 400);
            } else {
                db_connect()->transCommit();
                return $this->respond([
                    'status' => '00',
                    'message' => "Berhasil Menghapus Data Permission (Sementara)"
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
        $permission = $this->model->withDeleted(true)->find($id);
        if(!$permission){
            return $this->respond([
                'status' => '44',
                'message' => "Data Permission Tidak Ditemukan"
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
                'message' => "Gagal Mengembalikan Data Permission"
            ], 400);
        } else {
            db_connect()->transCommit();
            return $this->respond([
                'status' => '00',
                'message' => "Berhasil Mengembalikan Data Permission"
            ], 200);
        }
    }
    public function purge_data($id)
    {
        $permission = $this->model->withDeleted(true)->find($id);
        if(!$permission){
            return $this->respond([
                'status' => '44',
                'message' => "Data Permission Tidak Ditemukan"
            ], 404);
        }
        try {
            db_connect()->transBegin();
            $this->model->delete($id,true);
            if (db_connect()->transStatus() == false) {
                db_connect()->transRollback();
                return $this->respond([
                    'status' => '40',
                    'message' => "Gagal Menghapus Data Permission Selamanya"
                ], 400);
            } else {
                db_connect()->transCommit();
                return $this->respond([
                    'status' => '00',
                    'message' => "Berhasil Menghapus Data Permission Selamanya"
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