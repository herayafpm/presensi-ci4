<?php

namespace App\Controllers\Api;

use App\Models\UserModel;

class UserApi extends BaseApi
{
    protected $max_size_file = 1024;
    protected $besaran_file = 'KB';
    protected $ext_in_file = 'jpg,jpeg,png';
    public function profile()
    {
        $user = $this->_auth->user;
        $roles = $this->_auth->getRoles();
        $resdata = [
            'username' => $user['username'],
            'email' => $user['email'],
            'name' => $user['name'],
            'user_pp' => base_url('storage/photo/' . $user['user_pp']),
            'roles' => $roles,
        ];
        return $this->respond([
            'status' => '00',
            'message' => "Berhasil Mengambil Data Profile",
            'data' => $resdata
        ], 200);
    }
    function update_profile()
    {
        $user = $this->_auth->user;
        $input = $this->request->getJSON();
        $rules = [
            'username' => [
                'label' => 'Username',
                'rules' => "required|is_unique[users.username,user_id,{$user['user_id']}]"
            ],
            'name' => [
                'label' => 'Nama',
                'rules' => 'required'
            ],
            'email' => [
                'label' => 'Email',
                'rules' => "required|is_unique[users.email,user_id,{$user['user_id']}]"
            ],
        ];
        if (!$this->validateData($input, $rules)) {
            return $this->respond([
                'status' => '40',
                'message' => "Validasi Gagal",
                'data' => $this->validator->getErrors()
            ], 400);
        }


        if (!(bool) preg_match('/^[\w]+$/', $input['username'])) {
            return $this->respond([
                'status' => '40',
                'message' => "Validasi Gagal",
                'data' => [
                    'username' => 'Username Tidak Boleh Ada Karakter Khusus'
                ]
            ], 400);
        }

        db_connect()->transBegin();
        $set = [
            'username' => $input['username'],
            'name' => $input['name'],
            'email' => $input['email']
        ];
        model(UserModel::class)->update($user['user_id'], $set);

        if (db_connect()->transStatus() == false) {
            db_connect()->transRollback();

            return $this->respond([
                'status' => '40',
                'message' => "Gagal Mengupdate Profile"
            ], 400);
        } else {
            db_connect()->transCommit();
            return $this->respond([
                'status' => '00',
                'message' => "Berhasil Mengupdate Profile"
            ], 200);
        }
    }

    function update_password()
    {
        $input = $this->request->getJSON();
        $user = $this->_auth->user;
        $rules = [
            'password' => [
                'label' => 'Password Saat ini',
                'rules' => "required|current_password"
            ],
            'new_password' => [
                'label' => 'Password Baru',
                'rules' => 'required|min_length[6]|matches[new_password_confirm]'
            ],
            'new_password_confirm' => [
                'label' => 'Konfirmasi Password Baru',
                'rules' => 'required|min_length[6]'
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
        $set = [
            'password' => $input['new_password'],
        ];
        model(UserModel::class)->update($user['user_id'], $set);
        if (db_connect()->transStatus() == false) {
            db_connect()->transRollback();

            return $this->respond([
                'status' => '40',
                'message' => "Gagal Mengupdate Password"
            ], 400);
        } else {
            db_connect()->transCommit();
            $this->_auth->logout();
            return $this->respond([
                'status' => '00',
                'message' => "Berhasil Mengupdate Password",
                'data' => [
                    'logout' => true
                ]
            ], 200);
        }
    }
    function update_photo()
    {
        $input = $this->request->getPost();
        $user = $this->_auth->user;
        $rules = [
            'image' => [
                'label'  => "Photo",
                'rules'  => "uploaded[image]|max_size[image,{$this->max_size_file}]|ext_in[image,{$this->ext_in_file}]",
                'errors' => [
                    'max_size' => "Maksimal Ukuran {$this->max_size_file}{$this->besaran_file}",
                    'ext_in' => "Ekstensi File Harus {$this->ext_in_file}",
                ]
            ]
        ];
        if (!$this->validateData($input, $rules)) {
            return $this->respond([
                'status' => '40',
                'message' => "Validasi Gagal",
                'data' => $this->validator->getErrors()
            ], 400);
        }
        $photo_image = null;
        if ($input['image']->isValid()) {
            $file = $input['image'];
            $file_name = $file->getRandomName();
            $file->move(APPPATH . '../storage/photo', $file_name);
            $photo_image = $file_name;
            db_connect()->transBegin();
            model(UserModel::class)->update($user['user_id'], ['user_pp' => $photo_image]);
            if (db_connect()->transStatus() == false) {
                db_connect()->transRollback();
                if (file_exists(APPPATH . '../storage/photo/' . $photo_image)) {
                    unlink(APPPATH . '../storage/photo/' . $photo_image);
                }
                return $this->respond([
                    'status' => '40',
                    'message' => "Gagal Mengupdate Photo"
                ], 400);
            } else {
                db_connect()->transCommit();
                if (!empty($user['user_pp'])) {
                    if (file_exists(APPPATH . '../storage/photo/' . $user['user_pp'])) {
                        unlink(APPPATH . '../storage/photo/' . $user['user_pp']);
                    }
                }
                return $this->respond([
                    'status' => '00',
                    'message' => "Berhasil Mengupdate Photo",
                    'data' => [
                        'user_pp' => base_url('storage/photo/' . $photo_image),
                    ]
                ], 200);
            }
        }
        return $this->respond([
            'status' => '40',
            'message' => "Gagal Mengupdate Photo"
        ], 400);
    }
}