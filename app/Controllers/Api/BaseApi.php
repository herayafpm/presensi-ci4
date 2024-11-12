<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class BaseApi extends ResourceController
{
    protected $modelName = '';
    protected $format    = 'json';
    protected $_auth = null;
    public function __construct() {
        $this->_auth = service('auth');
    }

    protected function datatable_get($params = [], $model = null)
    {
        if ($model === null) {
            $model = $this->model;
        }
        $rules = [
            'length' => [
                'label'  => "Length",
                'rules'  => "required",
                'errors' => []
            ],
            'start' => [
                'label'  => "Start",
                'rules'  => "required",
                'errors' => []
            ],
            'order' => [
                'label'  => "Order",
                'rules'  => "required",
                'errors' => []
            ],
            'columns' => [
                'label'  => "Columns",
                'rules'  => "required",
                'errors' => []
            ],
        ];

        if (!$this->validate($rules)) {
            return $this->respond(["status" => false, "message" => "Validasi Gagal", "data" => $this->validator->getErrors()],400);
        }
        $data = $this->request->getPost();
        $limit = $data['length']; // Ambil data limit per page
        $start = $data['start']; // Ambil data start
        $order_index = $data['order'][0]['column']; // Untuk mengambil index yg menjadi acuan untuk sorting
        $orderBy = $data['columns'][$order_index]['data']; // Untuk mengambil nama field yg menjadi acuan untuk sorting
        $ordered = $data['order'][0]['dir']; // Untuk menentukan order by "ASC" atau "DESC"
        $sql_total = $model->count_all($params); // Panggil fungsi count_all pada Admin
        $sql_data = $model->filter($limit, $start, $orderBy, $ordered, $params);
        $sql_filter = $model->count_all($params); // Panggil fungsi count_filter pada Admin
        $callback = [
            'draw' => $data['draw'], // Ini dari datatablenya
            'recordsTotal' => $sql_total,
            'recordsFiltered' => $sql_filter,
            'data' => $sql_data
        ];
        return $callback;
    }
}