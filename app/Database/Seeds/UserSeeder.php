<?php

namespace App\Database\Seeds;

use App\Models\EmployeeModel;
use App\Models\RoleModel;
use App\Models\UserHasRoleModel;
use App\Models\UserModel;
use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $avatar = new \LasseRafn\InitialAvatarGenerator\InitialAvatar();
        $datas = [
            [
                'username' => 'superadmin',
                'email' => 'herayafpm@gmail.com',
                'name' => 'Heraya Fitra',
                'roles' => ['Superadmin']
            ],
            [
                'username' => 'admin',
                'email' => 'admin@test.com',
                'name' => 'Admin',
                'roles' => ['Admin']
            ],
            [
                'username' => 'karyawan1',
                'email' => 'karyawan1@test.com',
                'name' => 'Karyawan 1',
                'roles' => ['User'],
                'employee' => [
                    'gender' => 'L',
                    'datebirth' => '2000-01-01',
                    'placebirth' => 'Banjarnegara'
                ] 
            ],
            [
                'username' => 'karyawan2',
                'email' => 'karyawan2@test.com',
                'name' => 'Karyawan 2',
                'roles' => ['User'],
                'employee' => [
                    'gender' => 'L',
                    'datebirth' => '2000-01-01',
                    'placebirth' => 'Banjarnegara'
                ] 
            ],
            [
                'username' => 'karyawan3',
                'email' => 'karyawan3@test.com',
                'name' => 'Karyawan 3',
                'roles' => ['User'],
                'employee' => [
                    'gender' => 'L',
                    'datebirth' => '2000-01-01',
                    'placebirth' => 'Banjarnegara'
                ] 
            ],
        ];
        foreach($datas as $data){
            $image = $avatar->name($data['name'])->autoColor()->generate();
            $pp_name = uniqid().time().".png";
            $image->save(APPPATH."../storage/photo/".$pp_name);
            $data['user_pp'] = $pp_name;
            $data['password'] = "123456";
            if(model(UserModel::class)->save($data)){
                $user_id = model(UserModel::class)->getInsertID();
                foreach($data['roles'] as $r){
                    $role = model(RoleModel::class)->where(['role_name' => $r])->first();
                    if($role){
                        model(UserHasRoleModel::class)->save([
                            'user_id' => $user_id,
                            'role_id' => $role['role_id']
                        ]);
                    }
                    if(isset($data['employee'])){
                        $data['employee']['user_id'] = $user_id;
                        model(EmployeeModel::class)->save($data['employee']);
                    }
                }
            }
        }
    }
}
