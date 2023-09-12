<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

require APPPATH . 'libraries/RestController.php';

class Cart extends RestController
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Cart_model');
        $this->load->model('Templates_model');
    }

    //Get card user
    public function index_get()
    {
        //Parameter
        $idUser = $this->get('idUser');

        //Validator
        if ($idUser == null) {
            $this->response(
                $this->Templates_model->error('Parameter idUser, Tidak Ada atau Tidak Valid'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        }
        // Check user status
        $userStatus = $this->db->get_where('loginuser', ['id_user' => $idUser])->row_array()['status_delete_user'];
        if ($userStatus === 'Y') {
            $this->response(
                $this->Templates_model->error('Akun sudah dihapus. Silakan hubungi admin untuk konfirmasi lebih lanjut'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        } else if (empty($this->db->get_where('loginuser', ['id_user' => $idUser])->row_array())) {
            $this->response(
                $this->Templates_model->error('Akun tidak ada'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        }

        //code
        $data = $this->Cart_model->getCart($idUser);
        $result = $this->Templates_model->success($data);

        $this->response(
            $result,
            RestController::HTTP_OK
        );
    }
    //Get cart count user
    public function count_get()
    {
        //Parameter
        $idUser = $this->get('idUser');

        //Validator
        if ($idUser == null) {
            $this->response(
                $this->Templates_model->error('Parameter idUser, Tidak Ada atau Tidak Valid'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        }
        // Check user status
        $userStatus = $this->db->get_where('loginuser', ['id_user' => $idUser])->row_array()['status_delete_user'];
        if ($userStatus === 'Y') {
            $this->response(
                $this->Templates_model->error('Akun sudah dihapus. Silakan hubungi admin untuk konfirmasi lebih lanjut'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        } else if (empty($this->db->get_where('loginuser', ['id_user' => $idUser])->row_array())) {
            $this->response(
                $this->Templates_model->error('Akun tidak ada'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        }

        //code
        $data = $this->Cart_model->getCount($idUser);
        $result = $this->Templates_model->success($data);

        $this->response(
            $result,
            RestController::HTTP_OK
        );
    }

    public function add_post()
    {
        //Parameter
        $idUser = $this->post('idUser');
        $idBarang = $this->post('idBarang');
        $idCabang = $this->post('idCabang');
        $qty = $this->post('qty');

        //Validator

        if ($idUser == null || $qty == null || $idBarang == null || $idCabang == null) {
            $this->response(
                $this->Templates_model->error('Parameter idUser, idBarang, idCabang qty atau Tidak Ada atau Tidak Valid'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        }
        // Check user status
        $userStatus = $this->db->get_where('loginuser', ['id_user' => $idUser])->row_array()['status_delete_user'];
        if ($userStatus === 'Y') {
            $this->response(
                $this->Templates_model->error('Akun sudah dihapus. Silakan hubungi admin untuk konfirmasi lebih lanjut'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        } else if (empty($this->db->get_where('loginuser', ['id_user' => $idUser])->row_array())) {
            $this->response(
                $this->Templates_model->error('Akun tidak ada'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        }

        $this->db->select('*');
        $this->db->from('cabang_item_stok');
        $this->db->where('idbarang', $idBarang);
        $this->db->where('idcabang', $idCabang);
        $resultCIS = $this->db->get()->row_array();
        if ($resultCIS == null) {
            $this->response(
                $this->Templates_model->error('Barang atau Cabbang tidak ada'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        }
        if ($qty > $resultCIS['qty_stok']) {
            $this->response(
                $this->Templates_model->error('Out of stock'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        }



        //code
        $data = $this->Cart_model->addCart($idUser, $idBarang, $resultCIS, $qty);
        $result = $this->Templates_model->success($data);

        if ($data == '404') {
            $this->response(
                $this->Templates_model->error('Out of stock'),
                RestController::HTTP_BAD_REQUEST
            );
        } else {

            $this->response(
                $result,
                RestController::HTTP_OK
            );
        }
    }
}
