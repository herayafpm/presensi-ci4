<?php

namespace App\Services;

use App\Models\UserHasRoleModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use InvalidArgumentException;

class AuthService
{
    protected $request;
    protected $response;
    public $message = "";
    public $user = null;
    public $roles = [];
    protected $modelName = UserModel::class;
    protected $model = null;
    protected $deviceId = null;
    public function __construct(RequestInterface $request, ResponseInterface $response, $args = [])
    {
        $this->request = $request;
        $this->response = $response;
        $this->message = "";
        $this->model = model($this->modelName);
        $this->checkUser($args);
    }

    public function checkUser($args)
    {
        if ($this->request->hasHeader('H-Key')) {
            $key = $this->request->getHeader('H-Key')->getValue();
            if (strpos($key, 'Bearer ') === false) {
                $this->response->setJSON([
                    'status' => '41',
                    'message' => 'Key Is Invalid',
                ])->setStatusCode(401)->send();
                die();
            }
            try {
                $key = str_replace('Bearer ', '', $key);
                $decoded = JWT::decode($key, new Key(config('Encryption')->key, 'HS256'));
                $arr_decoded = [];
                $encrypter = \Config\Services::encrypter();
                foreach ((array)json_decode(json_encode($decoded), true) as $key => $value) {
                    if ($key == 'exp') {
                        $arr_decoded[$key] = $value;
                    } else {
                        $arr_decoded[$encrypter->decrypt(base64_decode($key))] = $encrypter->decrypt(base64_decode($value));
                    }
                }
                $this->fillUser($arr_decoded['username']);
            } catch (InvalidArgumentException $th) {
                // provided key/key-array is empty or malformed.
                $this->response->setJSON([
                    'status' => '41',
                    'message' => $th->getMessage(),
                    'data' => [
                        'logout' => true
                    ]
                ])->setStatusCode(401)->send();
                die();
            } catch (ExpiredException $th) {
                // provided JWT is trying to be used after "exp" claim.
                $this->response->setJSON([
                    'status' => '42',
                    'message' => $th->getMessage(),
                    'data' => [
                        'logout' => true
                    ]
                ])->setStatusCode(402)->send();
                die();
            } catch (\Throwable $th) {
                $code = '41';
                if ($th->getCode() != 0) {
                    $code = (string)$th->getCode();
                }
                $this->response->setJSON([
                    'status' => $code,
                    'message' => $th->getMessage(),
                    'data' => [
                        'logout' => true
                    ]
                ])->setStatusCode(401)->send();
                die();
            }
        } else {
            $session = service('session');
            if ($session->get('username')) {
                $this->fillUser($session->get('username'));
            }
        }
    }

    public function fillUser($user)
    {
        if (is_string($user)) {
            $this->user = $this->model->where(['username' => $user])->first();
            if(!$this->user){
                service('response')->setJSON([
                    'status' => '43',
                    'message' => 'User Not Found',
                    'data' => [
                        'logout' => true
                    ]
                ])->setStatusCode(403)->send();
                die();
            }
        } else {
            $this->user = $user;
        }
    }

    public function attempt($data)
    {
        $account = $this->model->where(['username' => $data['username']])->first();
        if ($account) {
            if (password_verify($data['password'], $account['password'])) {
                $this->fillUser($account);
                $this->message = "Selamat Datang Kembali, " . $account['name'];
                return true;
            }
        }
        $this->message = "Username / Password Salah";
        return false;
    }
    function generateAccessToken($payload)
    {
        $encrypter = \Config\Services::encrypter();
        $payload_enc = [];
        foreach ($payload as $key => $value) {
            if ($key == 'exp') {
                $payload_enc[$key] = $value;
            } else {
                $payload_enc[base64_encode($encrypter->encrypt($key))] = base64_encode($encrypter->encrypt($value));
            }
        }

        // $payload = $encrypter->encrypt($payload);
        return JWT::encode($payload_enc, config('Encryption')->key, 'HS256');
    }

    function getRoles() {
        return model(UserHasRoleModel::class)->join('roles', 'user_has_roles.role_id = roles.role_id')->where([
            'user_id' => $this->user['user_id'],
        ])->findColumn('role_name');
    }

    function hasRoles($roles) {
        $role_search = $roles;
        if(is_string($roles)){
            $role_search = [$roles];
        }
        $my_roles = $this->getRoles();
        foreach($role_search as $role){
            if(in_array($role,$my_roles)){
                return true;
            }
        }
        return false;
    }

}