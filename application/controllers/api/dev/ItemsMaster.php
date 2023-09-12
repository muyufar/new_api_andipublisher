<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

require APPPATH . 'libraries/RestController.php';

class ItemsMaster extends RestController
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('ItemsMaster_model');
        $this->load->model('Templates_model');
    }

    //Get ItemMaster
    public function index_get()
    {

        $data = $this->ItemsMaster_model->getItems();
        $result = $this->Templates_model->success($data);

        if ($data == null) {
            $this->response(
                $this->Templates_model->success([]),
                RestController::HTTP_NO_CONTENT
            );
        } else {
            $this->response(
                $result,
                RestController::HTTP_OK
            );
        }
    }

    //Get ItemMaster
    public function detail_get()
    {
        //Parameter
        $idBarang = $this->get('idBarang');

        if ($idBarang == '') {
            $this->response(
                $this->Templates_model->error('Parameter idBarang Tidak Ada'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        } else if (empty($this->db->get_where('itemmaster', ['id_barang' => $idBarang])->row_array())) {
            $this->response(
                $this->Templates_model->error('idBarang Yang Di Pakai Tidak Ada'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        }

        $data = $this->ItemsMaster_model->getDetail($idBarang);
        $result = $this->Templates_model->success($data);

        if ($data == null) {
            $this->response(
                $this->Templates_model->success([]),
                RestController::HTTP_NO_CONTENT
            );
        } else {
            $this->response(
                $result,
                RestController::HTTP_OK
            );
        }
    }

    //Get data ItemMaster list terbaru
    public function new_get()
    {
        $data = $this->ItemsMaster_model->getNewItems();

        $dataModel = [
            'label' => 'Terbaru',
            'link' => '/ItemsMaster/showAllNew',
            'value' => $data
        ];

        $result = $this->Templates_model->success($dataModel);

        if ($data == null) {
            $this->response(
                $this->Templates_model->success([]),
                RestController::HTTP_NO_CONTENT
            );
        } else {
            $this->response(
                $result,
                RestController::HTTP_OK
            );
        }
    }

    //Get data ItemMaster lainnya list terbaru
    public function showAllNew_get()
    {
        $data = $this->ItemsMaster_model->getNewItems();
        $result = $this->Templates_model->success($data);

        if ($data == null) {
            $this->response(
                $this->Templates_model->success([]),
                RestController::HTTP_NO_CONTENT
            );
        } else {
            $this->response(
                $result,
                RestController::HTTP_OK
            );
        }
    }

    //Get data ItemMaster list terlaris
    public function bestSaller_get()
    {
        $data = $this->ItemsMaster_model->getBestSaller();

        $dataModel = [
            'label' => 'Terlaris',
            'link' => '/ItemsMaster/showAllBestSaller',
            'value' => $data
        ];

        $result = $this->Templates_model->success($dataModel);

        if ($data == null) {
            $this->response(
                $this->Templates_model->success([]),
                RestController::HTTP_NO_CONTENT
            );
        } else {
            $this->response(
                $result,
                RestController::HTTP_OK
            );
        }
    }

    //Get data ItemMaster lainnya list terbaru
    public function showAllBestSaller_get()
    {
        $data = $this->ItemsMaster_model->getNewItems();
        $result = $this->Templates_model->success($data);

        if ($data == null) {
            $this->response(
                $this->Templates_model->success([]),
                RestController::HTTP_NO_CONTENT
            );
        } else {
            $this->response(
                $result,
                RestController::HTTP_OK
            );
        }
    }
}
