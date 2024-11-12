<?php

namespace App\Controllers\Api\Admin\Master;

use App\Controllers\Api\BaseApi;
use App\Models\EmployeeModel;

class AdminMasterEmployeeApi extends BaseApi
{
    protected $modelName = EmployeeModel::class;
    public function index()
    {
        $employees = $this->model->select("ROW_NUMBER() OVER (ORDER BY employee_name) AS no,employees.*")->findAll();
        return $this->respond([
            'status' => '00',
            'message' => "Berhasil Mengambil Data Employees",
            'data' => $employees
        ], 200);
    }
    public function add_data()
    {
        $input = $this->request->getJSON();
        $rules = [
            'time_name' => [
                'label' => 'Nama Time',
                'rules' => "required|is_unique[times.time_name]"
            ],
            'time_start' => [
                'label' => 'Time Start',
                'rules' => "required"
            ],
            'time_end' => [
                'label' => 'Time End',
                'rules' => "required"
            ],
            'color' => [
                'label' => 'Color',
                'rules' => "required"
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
        $this->model->save([
            'time_name' => $input['time_name'],
            'time_start' => $input['time_start'],
            'time_end' => $input['time_end'],
            'color' => $input['color'],
        ]);
        if (db_connect()->transStatus() == false) {
            db_connect()->transRollback();
            return $this->respond([
                'status' => '40',
                'message' => "Gagal Menambah Data Time"
            ], 400);
        } else {
            db_connect()->transCommit();
            return $this->respond([
                'status' => '00',
                'message' => "Berhasil Menambah Data Time"
            ], 200);
        }
    }
    public function detail_data($id)
    {
        $time = $this->model->find($id);
        if(!$time){
            return $this->respond([
                'status' => '44',
                'message' => "Data Time Tidak Ditemukan"
            ], 404);
        }
        return $this->respond([
            'status' => '00',
            'message' => "Berhasil Mengambil Data Time",
            'data' => $time
        ], 200);
    }
    public function update_data($id)
    {
        $input = $this->request->getJSON();
        $time = $this->model->find($id);
        if(!$time){
            return $this->respond([
                'status' => '44',
                'message' => "Data Time Tidak Ditemukan"
            ], 404);
        }
        $rules = [
            'time_name' => [
                'label' => 'Nama Time',
                'rules' => "required|is_unique[times.time_name,time_id,{$id}]"
            ],
            'time_start' => [
                'label' => 'Time Start',
                'rules' => "required"
            ],
            'time_end' => [
                'label' => 'Time End',
                'rules' => "required"
            ],
            'color' => [
                'label' => 'Color',
                'rules' => "required"
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
        $this->model->update($id, [
            'time_name' => $input['time_name'],
            'time_start' => $input['time_start'],
            'time_end' => $input['time_end'],
            'color' => $input['color'],
        ]);
        if (db_connect()->transStatus() == false) {
            db_connect()->transRollback();
            return $this->respond([
                'status' => '40',
                'message' => "Gagal Mengupdate Data Time"
            ], 400);
        } else {
            db_connect()->transCommit();
            return $this->respond([
                'status' => '00',
                'message' => "Berhasil Mengupdate Data Time",
            ], 200);
        }
    }
    public function delete_data($id)
    {
        $time = $this->model->find($id);
        if(!$time){
            return $this->respond([
                'status' => '44',
                'message' => "Data Time Tidak Ditemukan"
            ], 404);
        }
        try {
            db_connect()->transBegin();
            $this->model->delete($id);
            if (db_connect()->transStatus() == false) {
                db_connect()->transRollback();
                return $this->respond([
                    'status' => '40',
                    'message' => "Gagal Menghapus Data Time (Sementara)"
                ], 400);
            } else {
                db_connect()->transCommit();
                return $this->respond([
                    'status' => '00',
                    'message' => "Berhasil Menghapus Data Time (Sementara)"
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
        $time = $this->model->withDeleted(true)->find($id);
        if(!$time){
            return $this->respond([
                'status' => '44',
                'message' => "Data Time Tidak Ditemukan"
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
                'message' => "Gagal Mengembalikan Data Time"
            ], 400);
        } else {
            db_connect()->transCommit();
            return $this->respond([
                'status' => '00',
                'message' => "Berhasil Mengembalikan Data Time"
            ], 200);
        }
    }
    public function purge_data($id)
    {
        $time = $this->model->withDeleted(true)->find($id);
        if(!$time){
            return $this->respond([
                'status' => '44',
                'message' => "Data Time Tidak Ditemukan"
            ], 404);
        }
        try {
            db_connect()->transBegin();
            $this->model->delete($id,true);
            if (db_connect()->transStatus() == false) {
                db_connect()->transRollback();
                return $this->respond([
                    'status' => '40',
                    'message' => "Gagal Menghapus Data Time Selamanya"
                ], 400);
            } else {
                db_connect()->transCommit();
                return $this->respond([
                    'status' => '00',
                    'message' => "Berhasil Menghapus Data Time Selamanya"
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