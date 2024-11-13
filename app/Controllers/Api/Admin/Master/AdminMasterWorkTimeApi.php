<?php

namespace App\Controllers\Api\Admin\Master;

use App\Controllers\Api\BaseApi;
use App\Models\WorkTimeModel;
use DateTime;

class AdminMasterWorkTimeApi extends BaseApi
{
    protected $modelName = WorkTimeModel::class;
    public function index()
    {
        $input = $this->request->getGet();
        $year = $input['year'] ?? date("Y");
        $month = $input['month'] ?? date("m");
        $now = new DateTime(strtotime("$year-$month-01"));
        $now->modify('last day of this month');
        $worktimes = model(WorkTimeModel::class)->where(['year' => $year,'month' => $month,'day >=' => '01','day <=' => (int)($now->format('d'))])->findAll();
        return $this->respond([
            'status' => '00',
            'message' => "Berhasil Mengambil Data Times",
            'data' => $worktimes
        ], 200);
    }
}