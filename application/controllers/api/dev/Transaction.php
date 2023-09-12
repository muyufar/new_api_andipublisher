<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

require APPPATH . 'libraries/RestController.php';

class Transaction extends RestController
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Courier_model');
        $this->load->model('Transaction_model');
        $this->load->model('Templates_model');
    }


    public function checkout_post()
    {
        // Parameter
        $tag = $this->post('tag');
        $id = $this->post('id');

        //validator
        if (!is_array($id) || empty($id)) {
            $this->response(
                $this->Templates_model->error('Parameter id Tidak Ada atau Tidak Valid'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        }
        foreach ($id as $iddata) {
            // // Memeriksa setiap elemen dalam $id
            if (empty($iddata)) {
                $this->response(
                    $this->Templates_model->error('Salah satu ID Barang kosong'),
                    RestController::HTTP_BAD_REQUEST
                );
                return;
            }
        }

        switch ($tag) {
            case 'direck':
                // Parameter direck
                $idUser = $this->post('idUser');
                $idcabang = $this->post('idCabang');
                $quantityOrderDireck = $this->post('quantityOrderDireck');

                //validator
                if ($idUser == null || $idcabang == null || $quantityOrderDireck == null) {
                    $this->response(
                        $this->Templates_model->error('Parameter idUSer, idCabang atau quantityOrderDireck  Tidak Ada atau Tidak Valid'),
                        RestController::HTTP_BAD_REQUEST
                    );
                    return;
                } elseif ($quantityOrderDireck <= 0) {
                    $this->response(
                        $this->Templates_model->error("Parameter Tidak Boleh $quantityOrderDireck"),
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

                // Check warehouse status
                $warehouseStatus = $this->db->get_where('cabang', ['idcabang' => $idcabang])->row_array()['status_aktif_cabang'];
                if ($warehouseStatus === 'N') {
                    $this->response(
                        $this->Templates_model->error('Warehouse tidak tersedia'),
                        RestController::HTTP_BAD_REQUEST
                    );
                    return;
                } else if (empty($this->db->get_where('cabang', ['idcabang' => $idcabang])->row_array())) {
                    $this->response(
                        $this->Templates_model->error('Warehouse tidak ada'),
                        RestController::HTTP_BAD_REQUEST
                    );
                    return;
                }

                // Check stok barang
                $this->db->select('qty_stok, idbarang');
                $this->db->from('cabang_item_stok');
                $this->db->where('idcabang', $idcabang);
                $this->db->where_in('idbarang', $id);
                $queryStok = $this->db->get();
                $stokResult = $queryStok->result_array();

                if (empty($stokResult)) {
                    $this->response(
                        $this->Templates_model->error('Barang tidak ada'),
                        RestController::HTTP_BAD_REQUEST
                    );
                    return;
                }

                foreach ($stokResult as $row) {
                    if ($row['qty_stok'] <= 0) {
                        $this->response(
                            $this->Templates_model->error('Stok barang dengan id ' . $row['idbarang'] . ' tidak tersedia di warehouse ini'),
                            RestController::HTTP_BAD_REQUEST
                        );
                        return;
                    }
                }

                // Check barang aktif
                $this->db->select('status_display');
                $this->db->from('itemmaster');
                $this->db->where_in('id_barang', $id);
                $queryBarang = $this->db->get();
                $barangResult = $queryBarang->result_array();

                foreach ($barangResult as $row) {
                    if ($row['status_display'] === 'N') {
                        $this->response(
                            $this->Templates_model->error('Ada barang yang sudah tidak aktif'),
                            RestController::HTTP_BAD_REQUEST
                        );
                        return;
                    }
                }


                $data = $this->Transaction_model->getCheckout($idUser, $id, $idcabang, $quantityOrderDireck);
                $result = $this->Templates_model->success($data);

                $this->response(
                    $result,
                    RestController::HTTP_OK
                );

                break;
            case 'cart':

                break;
            default:
                $this->response(
                    $this->Templates_model->error('Parameter tag Tidak Ada atau Tidak Valid'),
                    RestController::HTTP_BAD_REQUEST
                );
                break;
        }
    }

    public function transaction_post()
    {
        // Parameter
        $data = file_get_contents('php://input');
        $jsonData = json_decode($data, true);
        $messageError = null;

        //Validations
        // Cek apakah data user ada dan berupa array
        $messageError .= !(isset($jsonData['user']) && is_array($jsonData['user'])) ? 'user = [required][object] | ' : null;
        $valUser = $jsonData['user'];
        $messageError .= !(isset($valUser['idUser']) && is_string($valUser['idUser'])) ? 'idUser = [required][string] | ' : null; // Pengecekan atribut idUser
        $messageError .= !(isset($valUser['idAddressUser']) && is_string($valUser['idAddressUser'])) ? 'idAddressUser = [required][string] | ' : null; // Pengecekan atribut idAddressUser
        $messageError .= !(isset($valUser['usePoinUser']) && is_bool($valUser['usePoinUser'])) ? 'usePoinUser = [required][string] | ' : null; // Pengecekan atribut usePoinUser
        // Cek apakah data dataCheckout ada dan berupa array
        $messageError .= !(isset($jsonData['dataCheckout']) && is_array($jsonData['dataCheckout'])) ? 'dataCheckout = [required][array] | ' : null;
        $valDataCheckout = $jsonData['dataCheckout'];
        foreach ($valDataCheckout as $valCheckoutItem) { // Loop melalui setiap elemen dataCheckout
            $messageError .= !(isset($valCheckoutItem['idWarehouse']) && is_string($valCheckoutItem['idWarehouse'])) ? 'idWarehouse = [required][string] | ' : null; // Pengecekan atribut idWarehouse
            $messageError .= !(isset($valCheckoutItem['courier']) && is_array($valCheckoutItem['courier'])) ? 'courier = [required][object] | ' : null; // Pengecekan atribut courier
            $valCourier = $valCheckoutItem['courier'];
            $messageError .= !(isset($valCourier['courierService']) && is_string($valCourier['courierService'])) ? 'courierService = [required][string] | ' : null;  // Pengecekan atribut courierService
            $messageError .= !(isset($valCourier['courierCode']) && is_string($valCourier['courierCode'])) ? 'courierCode = [required][string] | ' : null;  // Pengecekan atribut courierCode
            $messageError .= !(isset($valCheckoutItem['products']) && is_array($valCheckoutItem['products'])) ? 'products = [required][array] | ' : null; // Pengecekan atribut products
            $valProducts = $valCheckoutItem['products'];
            foreach ($valProducts as $valProduct) {  // Loop melalui setiap elemen products
                $messageError .= !(isset($valProduct['idProduct']) && is_string($valProduct['idProduct'])) ? 'idProduct = [required][string] | ' : null; // Pengecekan atribut idProduct
                $messageError .= !(isset($valProduct['quantity']) && is_numeric($valProduct['quantity'])) ? 'idProduct = [required][string] | ' : null; // Pengecekan atribut quantity
            }
        }
        if ($messageError != null) {
            $this->response(
                $this->Templates_model->error($messageError),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        }

        $idUser = $jsonData['user']['idUser'];
        $idAddressUser = $jsonData['user']['idAddressUser'];
        $usePoinUser = $jsonData['user']['usePoinUser'];
        $idVoucherProduct = (isset($jsonData['voucher']['idVoucherProduct'])) ? $jsonData['voucher']['idVoucherProduct'] :  $messageError;
        $idVoucherCourier = (isset($jsonData['voucher']['idVoucherCourier'])) ? $jsonData['voucher']['idVoucherCourier'] :  $messageError;
        $dataCheckout = $jsonData['dataCheckout'];

        $dataCourierServices = [];

        // Validation Data
        // Check dataUser
        $dataUser = $this->db->get_where('loginuser', ['id_user' => $idUser])->row_array();
        $messageError = empty($dataUser) ? 'Akun tidak ada | ' :  $messageError;
        $messageError = $dataUser['status_delete_user'] == 'Y' || $dataUser['status_aktif_user'] == 'N' ? 'Akun sudah dihapus atau tidak aktid. Silakan hubungi admin untuk konfirmasi lebih lanjut' :  $messageError;
        // Check dataAddress
        $dataAddress = $this->db->get_where('loginuser_detail_alamat', ['id_alamat_user' => $idAddressUser])->row_array();
        $messageError = empty($dataAddress) ? 'Alamat tidak ada | ' :  $messageError;
        $messageError =  $dataAddress['delete_status'] == 'Y' ? 'Alamat sudah di hapus' :  $messageError;
        $messageError = (!is_numeric($dataAddress['kodepos_user']) || floor(log10($dataAddress['kodepos_user'])) + 1 < 5) || $dataAddress['kodepos_user'] < 0  ? 'Alamat ini belum punya kode pos' :  $messageError;
        //Check VoucherProduct
        if (!empty($idVoucherProduct)) {
            $dataVoucherProduct = $this->db->get_where('list_voucher', ['id_voucher' => $idVoucherProduct])->row_array();
            if ($dataVoucherProduct['status_berulang'] == 'N') {
                $dataUserVoucherProduct = $this->db->get_where('list_voucher_recipient', ['id_voucher' => $idVoucherProduct, 'id_user' => $idUser])->result_array();
                $messageError = (!empty($dataUserVoucherProduct)) ? 'Voucher sudah pernah di pakai' :  $messageError;
            }
            $messageError = ($dataVoucherProduct['kategori_voucher'] != 'Y') ? 'Voucher ini kusus kurir' :  $messageError;
            // Ubah tanggal menjadi detik
            $tglMulaiDetik = strtotime($dataVoucherProduct['tgl_mulai_voucher']);
            $tglBerakhirDetik = strtotime($dataVoucherProduct['tgl_berakhir_voucher']);
            $messageError = ($tglMulaiDetik >= time()) ? 'Voucher aktif di tanggal ' . $dataVoucherProduct['tgl_mulai_voucher'] :  $messageError;
            $messageError = ($tglBerakhirDetik <= time()) ? 'Voucher sudah expired' :  $messageError;
            $messageError = ($dataVoucherProduct['quota'] <= 0) ? 'Voucher sudah habis' :  $messageError;
        }
        //Check VoucherCourier
        if (!empty($idVoucherCourier)) {
            $dataVoucherCourier = $this->db->get_where('list_voucher', ['id_voucher' => $idVoucherCourier])->row_array();
            if ($dataVoucherCourier['status_berulang'] == 'N') {
                $dataVoucherCourier = $this->db->get_where('list_voucher_recipient', ['id_voucher' => $idVoucherCourier, 'id_user' => $idUser])->result_array();
                $messageError = (!empty($dataVoucherCourier)) ? 'Voucher sudah pernah di pakai' :  $messageError;
            }
            $messageError = ($dataVoucherCourier['kategori_voucher'] != 'N') ? 'Voucher ini kusus produk' :  $messageError;
            // Ubah tanggal menjadi detik
            $tglMulaiDetik = strtotime($dataVoucherCourier['tgl_mulai_voucher']);
            $tglBerakhirDetik = strtotime($dataVoucherCourier['tgl_berakhir_voucher']);
            $messageError = ($tglMulaiDetik >= time()) ? 'Voucher aktif di tanggal ' . $dataVoucherCourier['tgl_mulai_voucher'] :  $messageError;
            $messageError = ($tglBerakhirDetik <= time()) ? 'Voucher sudah expired' :  $messageError;
            $messageError = ($dataVoucherCourier['quota'] <= 0) ? 'Voucher sudah habis' :  $messageError;
        }
        // Check dataCheckout
        foreach ($dataCheckout as $valCheckoutItem) {
            $weight = 0;

            $dataWarehouse = $this->db->get_where('cabang', ['idcabang' => $valCheckoutItem['idWarehouse']])->row_array();
            $messageError = empty($dataWarehouse) ? 'Warehouse tidak ada' : $messageError;
            $messageError = $dataWarehouse['status_aktif_cabang'] == 'N' ? 'Warehouse Non-Aktif' : $messageError;
            //Check dataProducts
            foreach ($valCheckoutItem['products'] as $valProduct) {
                $dataWarehouseItemStok = $this->db->get_where('cabang_item_stok', ['idcabang' => $valCheckoutItem['idWarehouse'], 'idbarang' => $valProduct['idProduct']])->row_array();
                $messageError = empty($dataWarehouseItemStok) ? 'Ada data produk yang salah' : $messageError;
                $messageError = $dataWarehouseItemStok['qty_stok'] < $valProduct['quantity'] ? 'Ada produk yang stok kurang dari stok yang mau di beli' : $messageError;
                $dataProducts = $this->db->get_where('itemmaster', ['id_barang' => $valProduct['idProduct']])->row_array();
                $weight += ($dataProducts['berat'] * $valProduct['quantity']);
            }
            //Check dataCourier
            $dataCouriers = $this->Courier_model->ajax_get_courier_cost($valCheckoutItem['idWarehouse'], $idAddressUser, $weight);
            if (!empty($dataCouriers)) {
                foreach ($dataCouriers as $dataCourier) {
                    if ($dataCourier['layanan'] == $valCheckoutItem['courier']['courierService'] && $dataCourier['kode'] == $valCheckoutItem['courier']['courierCode']) {
                        $valueCourier = $dataCourier;
                        $valueCourier['idWarehouse'] = $valCheckoutItem['idWarehouse'];
                        $dataCourierServices[] = $valueCourier;
                    }
                }
            } else {
                $messageError = 'Kurir tidak tersedia';
            }
        }
        if ($messageError != null) {
            $this->response(
                $this->Templates_model->error($messageError),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        }

        //Code
        $data = $this->Transaction_model->postTransaction($dataUser, $dataCheckout, $dataAddress, $dataCourierServices, $usePoinUser, $dataVoucherProduct ?? null, $dataVoucherCourier ?? null);

        if ($data['status']) {
            $this->response(
                $this->Templates_model->success($data),
                RestController::HTTP_OK
            );
        } else {
            $this->response(
                $this->Templates_model->error($data['content']),
                RestController::HTTP_INTERNAL_ERROR
            );
        }
    }

    public function historyTransaction_get()
    {
        // Parameter
        $offset = $this->get('offset');
        $limit = $this->get('limit') ?? '10';
        $tag = $this->get('tag');
        $idUser = $this->get('idUser');
        $idTransaksi = $this->get('idTransaksi');


        //validator
        if ($idUser == null) {
            $this->response(
                $this->Templates_model->error('Parameter idUser Tidak Ada atau Tidak Valid'),
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

        //Code
        if ($idTransaksi != '') {
            $data = $this->Transaction_model->getDetailHistoryTransactio($idUser, $idTransaksi);
            $result = $this->Templates_model->success($data);
        } else {
            $data = $this->Transaction_model->getHistoryTransactio($offset, $limit, $tag, $idUser);
            $result = $this->Templates_model->success($data);
        }

        $this->response(
            $result,
            RestController::HTTP_OK
        );
    }
}
