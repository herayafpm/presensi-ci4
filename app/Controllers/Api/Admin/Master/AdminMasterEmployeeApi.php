<?php

namespace App\Controllers\Api\Admin\Master;

use App\Controllers\Api\BaseApi;
use App\Models\EmployeeModel;
use App\Models\UserModel;

class AdminMasterEmployeeApi extends BaseApi
{
    protected $modelName = EmployeeModel::class;
    public function index()
    {
        $employees = $this->model->select("ROW_NUMBER() OVER (ORDER BY employee_name) AS no,employees.*,users.username,users.name,users.email,users.user_pp")->join('users','employees.user_id = users.user_id')->findAll();
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
        if(model(UserModel::class)->save([
            'username' => $input['username'],
            'email' => $input['email'],
            'name' => $input['name'],
            'password' => $input['password'],
            'user_pp' => $pp_name
        ])){
            $user_id = model(UserModel::class)->getInsertID();
            $this->model->save([
                'user_id' => $user_id,
                'gender' => $input['gender'],
                'datebirth' => $input['datebirth'],
                'placebirth' => $input['placebirth']
            ]);
        }
        if (db_connect()->transStatus() == false) {
            db_connect()->transRollback();
            if(file_exists(APPPATH."../storage/photo/".$pp_name)){
                unlink(APPPATH."../storage/photo/".$pp_name);
            }
            return $this->respond([
                'status' => '40',
                'message' => "Gagal Menambah Data Employee"
            ], 400);
        } else {
            db_connect()->transCommit();
            return $this->respond([
                'status' => '00',
                'message' => "Berhasil Menambah Data Employee"
            ], 200);
        }
    }
    public function detail_data($id)
    {
        $employee = $this->model->select("employees.*,users.username,users.name,users.email,users.user_pp")->join('users','employees.user_id = users.user_id')->find($id);
        if(!$employee){
            return $this->respond([
                'status' => '44',
                'message' => "Data Employee Tidak Ditemukan"
            ], 404);
        }
        return $this->respond([
            'status' => '00',
            'message' => "Berhasil Mengambil Data Employee",
            'data' => $employee
        ], 200);
    }
    public function update_data($id)
    {
        $input = $this->request->getJSON();
        $employee = $this->model->select("employees.*,users.username,users.name,users.email,users.user_pp")->join('users','employees.user_id = users.user_id')->find($id);
        if(!$employee){
            return $this->respond([
                'status' => '44',
                'message' => "Data Employee Tidak Ditemukan"
            ], 404);
        }
        $rules = [
            'username' => [
                'label' => 'Username',
                'rules' => "required|is_unique[users.username,user_id,{$employee['user_id']}]"
            ],
            'name' => [
                'label' => 'Nama',
                'rules' => "required"
            ],
            'email' => [
                'label' => 'Email',
                'rules' => "required|is_unique[users.email,user_id,{$employee['user_id']}]"
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
        if($employee['name'] != $input['name']){
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
        if(model(UserModel::class)->update($employee['user_id'],$set_user)){
            $this->model->update($employee['employee_id'],[
                'gender' => $input['gender'],
                'datebirth' => $input['datebirth'],
                'placebirth' => $input['placebirth']
            ]);
        }
        if (db_connect()->transStatus() == false) {
            db_connect()->transRollback();
            if(file_exists(APPPATH."../storage/photo/".$pp_name)){
                unlink(APPPATH."../storage/photo/".$pp_name);
            }
            return $this->respond([
                'status' => '40',
                'message' => "Gagal Mengupdate Data Employee"
            ], 400);
        } else {
            db_connect()->transCommit();
            if(isset($pp_name)){
                if(file_exists(APPPATH."../storage/photo/".$employee['user_pp'])){
                    unlink(APPPATH."../storage/photo/".$employee['user_pp']);
                }
            }
            return $this->respond([
                'status' => '00',
                'message' => "Berhasil Mengupdate Data Employee",
            ], 200);
        }
    }
    public function update_password_data($id)
    {
        $input = $this->request->getJSON();
        $employee = $this->model->select("employees.*,users.username,users.name,users.email,users.user_pp")->join('users','employees.user_id = users.user_id')->find($id);
        if(!$employee){
            return $this->respond([
                'status' => '44',
                'message' => "Data Employee Tidak Ditemukan"
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
        model(UserModel::class)->update($employee['user_id'],$set_user);
        if (db_connect()->transStatus() == false) {
            db_connect()->transRollback();
            return $this->respond([
                'status' => '40',
                'message' => "Gagal Mengupdate Password Employee"
            ], 400);
        } else {
            db_connect()->transCommit();
            return $this->respond([
                'status' => '00',
                'message' => "Berhasil Mengupdate Password Employee",
            ], 200);
        }
    }
    public function delete_data($id)
    {
        $employee = $this->model->find($id);
        if(!$employee){
            return $this->respond([
                'status' => '44',
                'message' => "Data Employee Tidak Ditemukan"
            ], 404);
        }
        try {
            db_connect()->transBegin();
            if($this->model->delete($id)){
                model(UserModel::class)->delete($employee['user_id']);
            }
            if (db_connect()->transStatus() == false) {
                db_connect()->transRollback();
                return $this->respond([
                    'status' => '40',
                    'message' => "Gagal Menghapus Data Employee (Sementara)"
                ], 400);
            } else {
                db_connect()->transCommit();
                return $this->respond([
                    'status' => '00',
                    'message' => "Berhasil Menghapus Data Employee (Sementara)"
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
        $employee = $this->model->withDeleted(true)->find($id);
        if(!$employee){
            return $this->respond([
                'status' => '44',
                'message' => "Data Employee Tidak Ditemukan"
            ], 404);
        }
        db_connect()->transBegin();
        if($this->model->withDeleted(true)->update($id,[
            'deleted_at' => null
        ])){
            model(UserModel::class)->withDeleted(true)->update($employee['user_id'],[
                'deleted_at' => null
            ]);
        }
        if (db_connect()->transStatus() == false) {
            db_connect()->transRollback();
            return $this->respond([
                'status' => '40',
                'message' => "Gagal Mengembalikan Data Employee"
            ], 400);
        } else {
            db_connect()->transCommit();
            return $this->respond([
                'status' => '00',
                'message' => "Berhasil Mengembalikan Data Employee"
            ], 200);
        }
    }
    public function purge_data($id)
    {
        $employee = $this->model->withDeleted(true)->find($id);
        if(!$employee){
            return $this->respond([
                'status' => '44',
                'message' => "Data Employee Tidak Ditemukan"
            ], 404);
        }
        try {
            db_connect()->transBegin();
            if($this->model->delete($id,true)){
                model(UserModel::class)->delete($employee['user_id'],true);
            }
            if (db_connect()->transStatus() == false) {
                db_connect()->transRollback();
                return $this->respond([
                    'status' => '40',
                    'message' => "Gagal Menghapus Data Employee Selamanya"
                ], 400);
            } else {
                db_connect()->transCommit();
                return $this->respond([
                    'status' => '00',
                    'message' => "Berhasil Menghapus Data Employee Selamanya"
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