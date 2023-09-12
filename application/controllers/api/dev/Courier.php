<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

require APPPATH . 'libraries/RestController.php';

class Courier extends RestController
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Courier_model');
        $this->load->model('Templates_model');
    }

    public function index_post()
    {
        //Parameter
        $idCabang = $this->post('idCabang');
        $idAddressUser = $this->post('idAddressUser');
        $weight = $this->post('weight');

        //Validator
        if ($idCabang == '' || $idAddressUser == '' || $weight == '') {
            $this->response(
                $this->Templates_model->error('Parameter idCabang, idAddressUser atau weight Tidak Ada'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        } else if (empty($this->db->get_where('cabang', ['idcabang' => $idCabang])->row_array())) {
            $this->response(
                $this->Templates_model->error('idcabang Yang Di Pakai Tidak Ada'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        } else if (empty($this->db->get_where('loginuser_detail_alamat', ['id_alamat_user' => $idAddressUser])->row_array())) {
            $this->response(
                $this->Templates_model->error('idAddressUser Yang Di Pakai Tidak Ada'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        }
        if (!is_numeric($weight)) {
            $this->response(
                $this->Templates_model->error('Parameter weight harus berupa angka'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        }


        $data = $this->Courier_model->ajax_get_courier_cost($idCabang, $idAddressUser, $weight);
        $result = $this->Templates_model->success($data);

        if ($data == null) {
            $this->response(
                $this->Templates_model->error('Tidak ada Kurir yang tersedia coba ubah alamat anda'),
                RestController::HTTP_NOT_FOUND
            );
        } else {
            $this->response(
                $result,
                RestController::HTTP_OK
            );
        }
    }
}
