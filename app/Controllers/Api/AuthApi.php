<?php

namespace App\Controllers\Api;

use App\Models\MerchantModel;
use App\Models\MerchantRoleModel;
use App\Models\UserModel;
use App\Models\UserResetPasswordModel;
use Config\Services;

class AuthApi extends BaseApi
{
    protected $modelName = '';

    public function login()
    {
        $rules = [
            'username' => [
                'label'  => "Username",
                'rules'  => 'required',
                'errors' => []
            ],
            'password' => [
                'label'  => "Password",
                'rules'  => 'required',
                'errors' => []
            ],
        ];
        $input = $this->request->getJSON();
        if (!$this->validate($rules)) {
            return $this->respond([
                'status' => '44',
                'message' => "Username / Password Salah",
                'data' => []
            ], 200);
        }
        if ($this->_auth->attempt($input)) {
            $message = $this->_auth->message;
            $user = $this->_auth->user;
            $resdata = [
                'username' => $user['username'],
                'email' => $user['email'],
                'name' => $user['name'],
                'user_pp' => base_url('storage/photo/' . $user['user_pp']),
                'access_token' => $this->_auth->generateAccessToken([
                    'username' => $user['username']
                ])
            ];
            return $this->respond([
                'status' => '00',
                'message' => $message,
                'data' => $resdata
            ], 200);
        } else {
            $message = $this->_auth->message;
            return $this->respond([
                'status' => '40',
                'message' => $message
            ], 200);
        }
    }

    protected function getRandomInt($n)
    {
        $characters = '0123456789';
        $randomInt = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomInt .= $characters[$index];
        }

        return $randomInt;
    }

    public function forgotpass()
    {
        $rules = [
            'email' => [
                'label'  => "Email",
                'rules'  => 'required',
                'errors' => []
            ],
        ];
        if (!$this->validate($rules)) {
            return $this->respond([
                'status' => '40',
                'message' => "Validasi Gagal",
                'data' => $this->validator->getErrors()
            ], 200);
        }
        $input = $this->request->getJSON();
        $user = model(UserModel::class)->where(['email' => $input['email']])->first();
        if (!$user) {
            return $this->respond([
                'status' => '44',
                'message' => 'User Tidak Ditemukan',
            ], 200);
        }
        $throttler = Services::throttler();
        if ($throttler->check(md5($this->request->getIPAddress() . "_forgotpass"), 1, MINUTE) === false) {
            return $this->respond([
                'status' => '43',
                'message' => 'Silahkan Tunggu ' . $throttler->getTokentime() . ' detik untuk Meminta Request Kembali',
            ], 200);
        }
        $token = $this->getRandomInt(6);
        model(UserResetPasswordModel::class)->where(['email' => $user['email']])->delete();
        model(UserResetPasswordModel::class)->save([
            'user_id' => $user['user_id'],
            'email' => $user['email'],
            'token' => $token,
            'sended' => 0,
        ]);
        return $this->respond([
            'status' => '00',
            'message' => 'Berhasil Dikirimkan, Silahkan Cek di Inbox Email atau di Spam',
        ], 200);
    }
    public function forgotpass_code()
    {
        $rules = [
            'email' => [
                'label'  => "Email",
                'rules'  => 'required',
                'errors' => []
            ],
            'code' => [
                'label'  => "Kode",
                'rules'  => 'required',
                'errors' => []
            ],
        ];
        if (!$this->validate($rules)) {
            return $this->respond([
                'status' => '40',
                'message' => "Validasi Gagal",
                'data' => $this->validator->getErrors()
            ], 200);
        }
        $input = $this->request->getJSON();
        $user = model(UserModel::class)->where(['email' => $input['email']])->first();
        if (!$user) {
            return $this->respond([
                'status' => '44',
                'message' => 'User Tidak Ditemukan',
            ], 200);
        }
        $user_reset = model(UserResetPasswordModel::class)->where(['email' => $input['email']])->first();
        if (!$user_reset) {
            return $this->respond([
                'status' => '44',
                'message' => 'Anda belum melakukan reset kata sandi',
            ], 200);
        }
        $throttler = Services::throttler();
        if ($throttler->check(md5($this->request->getIPAddress() . "_forgotpass_code"), 3, MINUTE) === false) {
            return $this->respond([
                'status' => '43',
                'message' => 'Permintaan Terlalu Banyak, Silahkan Tunggu ' . $throttler->getTokentime() . ' detik.',
            ], 200);
        }
        if ($user_reset['code'] != $input['code']) {
            return $this->respond([
                'status' => '44',
                'message' => 'Kode yang dimasukkan salah',
            ], 200);
        }
        return $this->respond([
            'status' => '00',
            'message' => 'Kode yang anda masukkan Benar, silahkan lanjut ke langkah berikutnya',
        ], 200);
    }
    public function forgotpass_code_reset()
    {
        $rules = [
            'email' => [
                'label'  => "Email",
                'rules'  => 'required',
                'errors' => []
            ],
            'code' => [
                'label'  => "Kode",
                'rules'  => 'required',
                'errors' => []
            ],
            'password' => [
                'label' => 'Password Baru',
                'rules' => 'required|min_length[6]|matches[password_confirm]'
            ],
            'password_confirm' => [
                'label' => 'Konfirmasi Password Baru',
                'rules' => 'required|min_length[6]'
            ],
        ];
        if (!$this->validate($rules)) {
            return $this->respond([
                'status' => '40',
                'message' => "Validasi Gagal",
                'data' => $this->validator->getErrors()
            ], 200);
        }
        $input = $this->request->getJSON();
        $user = model(UserModel::class)->where(['email' => $input['email']])->first();
        if (!$user) {
            return $this->respond([
                'status' => '44',
                'message' => 'User Tidak Ditemukan',
            ], 200);
        }
        $user_reset = model(UserResetPasswordModel::class)->where(['email' => $input['email']])->first();
        if (!$user_reset) {
            return $this->respond([
                'status' => '44',
                'message' => 'Anda belum melakukan proses awal reset kata sandi',
            ], 200);
        }
        $throttler = Services::throttler();
        if ($throttler->check(md5($this->request->getIPAddress() . "_forgotpass_code"), 3, MINUTE) === false) {
            return $this->respond([
                'status' => '43',
                'message' => 'Permintaan Terlalu Banyak, Silahkan Tunggu ' . $throttler->getTokentime() . ' detik.',
            ], 200);
        }
        if ($user_reset['code'] != $input['code']) {
            return $this->respond([
                'status' => '44',
                'message' => 'Kode yang dimasukkan salah',
            ], 200);
        }
        db_connect()->transBegin();
        $set = [
            'password' => $input['password'],
        ];
        if (model(UserModel::class)->update($user['user_id'], $set)) {
            model(UserResetPasswordModel::class)->where(['email' => $input['email']])->delete();
        }
        if (db_connect()->transStatus() == false) {
            db_connect()->transRollback();
            return $this->respond([
                'status' => '40',
                'message' => "Gagal Mengubah Kata Sandi"
            ], 200);
        } else {
            db_connect()->transCommit();

            return $this->respond([
                'status' => '00',
                'message' => "Berhasil Mengubah Kata Sandi"
            ], 200);
        }
    }
}