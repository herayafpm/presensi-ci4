<?php

namespace App\Database\Seeds;

use App\Models\EmployeeModel;
use App\Models\TimeModel;
use App\Models\WorkTimeModel;
use CodeIgniter\Database\Seeder;
use DateTime;

class WorkTimeSeeder extends Seeder
{
    public function run()
    {
        $employees = model(EmployeeModel::class)->findAll();
        $year = date("Y");
        $month = date("m");
        $now = new DateTime(date("Y-m-d"));
        $now->modify('last day of this month');
        foreach($employees as $employee){
            $time = model(TimeModel::class)->orderBy('RAND()')->first();
            for ($i=1; $i <= (int)($now->format('d')); $i++) {
                $day = date("w",strtotime("$year-$month-$i"));
                $in = [
                    'employee_id' => $employee['employee_id'],
                    'time_id' => $time['time_id'],
                    'year' => $year,
                    'month' => $month,
                    'day' => $i
                ];
                if($day == 0){
                    unset($in['time_id']);
                }
                model(WorkTimeModel::class)->save($in);
            }
        }
    }
}
