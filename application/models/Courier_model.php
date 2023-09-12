<?php

class Courier_model extends CI_Model
{

    public function ajax_get_courier_cost($idCabang, $idAddressUser, $weight)
    {
        // var_dump($_POST);die();
        // $anteraja = $this->getServiceRates_AJ($_POST['postalcode_penjual'], $_POST['postalcode_pembeli'], $_POST['berat']);
        // $jne = $this->getServiceRates_JNE($_POST['cabang_stok_id'], $_POST['postalcode_pembeli'], $_POST['berat']);
        // $sicepat = $this->getServiceRates_SC($_POST['cabang_stok_id'], $_POST['postalcode_pembeli'], $_POST['berat']);
        // $lionParcel = $this->getServiceRateLionParcel($_POST['cabang_stok_id'], $_POST['postalcode_pembeli'], $_POST['berat']);

        // $anteraja = $this->getServiceRates_AJ(55281, 13870, 1000);
        // $jne = $this->getServiceRates_JNE($_POST['cabang_stok_id'], $_POST['postalcode_pembeli'], $_POST['berat']);
        $jne = $this->getServiceRates_JNE($idCabang, $idAddressUser, $weight);
        // $sicepat = $this->getServiceRates_SC($_POST['cabang_stok_id'], $_POST['postalcode_pembeli'], $_POST['berat']);
        // $lionParcel = $this->getServiceRateLionParcel($_POST['cabang_stok_id'], $_POST['postalcode_pembeli'], $_POST['berat']);
        $result = [];
        // if ($anteraja['status'] == 200) {
        //     $result['stts'] = 200;
        //     foreach ($anteraja['content']['services'] as $val) {
        //         $result['content'][] = [
        //             'layanan' => 'ANTER AJA',
        //             'kode' => $val['product_code'],
        //             'produk' => $val['product_name'],
        //             'estimasi' => str_replace(' Day', '', $val['etd']),
        //             'harga' => $val['rates']
        //         ];
        //     }
        // } else {
        //     if (!is_null($anteraja)) {
        //         $result['stts'] = 400;
        //         if ($anteraja['status'] == 400 and $anteraja['info'] == 'Origin is Mandatory') {
        //             $result['content'] = "Alamat Warehouse Tidak Ditemukan!";
        //         } elseif ($anteraja['status'] == 400 and $anteraja['info'] == 'Destination is Mandatory') {
        //             $result['content'] = "Alamat Tidak Ditemukan! Mohon Isi Kode Pos dengan Benar.";
        //         } else {
        //             $result['content'] = "Terjadi Kesalahan";
        //         }
        //     }
        // }
        if (!isset($jne['error'])) {
            foreach ($jne['price'] as $val) {
                if ($val['goods_type'] == 'Document/Paket') {
                    $result[] = [
                        'layanan' => 'JNE',
                        'kode' => $val['service_code'],
                        'produk' => $val['service_display'],
                        'estimasi' => $val['etd_from'] == $val['etd_thru'] ? (is_null($val['etd_from']) ? '-' : $val['etd_from']) : $val['etd_from'] . '-' . $val['etd_thru'],
                        'harga' => intval($val['price'])
                    ];
                }
            }
        }

        // if ($sicepat['sicepat']['status']['code'] == 200) {
        //     $result['stts'] = 200;
        //     foreach ($sicepat['sicepat']['results'] as $val) {
        //         $result['content'][] = [
        //             'layanan' => 'SiCepat',
        //             'kode' => $val['service'],
        //             'produk' => $val['description'],
        //             'estimasi' => str_replace(' hari', '', $val['etd']),
        //             'harga' => $val['tariff']
        //         ];
        //     }
        // }

        // if ($lionParcel['status'] == 200) {
        //     $result['stts'] = 200;
        //     foreach ($lionParcel['response']['result'] as $val) {
        //         $result['content'][] = [
        //             'layanan' => 'Lion Parcel',
        //             'kode' => $val['product'],
        //             'produk' => $val['product'],
        //             'estimasi' => $val['estimasi_sla'],
        //             'harga' => $val['total_tariff_after_discount']
        //         ];
        //     }
        // }

        // echo json_encode($result);
        return $result;
    }

    public function getServiceRates_AJ($postal_origin, $postal_destini, $weight)
    {
        // $postal_origin = $this->db->antiSQLi($postal_origin);
        // $postal_destini = $this->db->antiSQLi($postal_destini);
        // $weight = $$this->db->antiSQLi($weight);
        // $weight = $weight < 1000 ? 1000 : $weight;

        // $origin = $this->db->get("SELECT district_code FROM data_wilayah WHERE postal_code='$postal_origin'");
        // $destination = $this->db->get("SELECT district_code FROM data_wilayah WHERE postal_code='$postal_destini'");
        // $arr = ['origin' => $origin['district_code'], 'destination' => $destination['district_code'], 'weight' => $weight];

        // $curl = curl_init();
        // curl_setopt($curl, CURLOPT_URL, ANTERAJA_BASEPATH . "/serviceRates");
        // curl_setopt($curl, CURLOPT_POST, 1);
        // curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($arr));
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($curl, CURLOPT_VERBOSE, true);
        // curl_setopt($curl, CURLOPT_TIMEOUT, 3);
        // curl_setopt($curl, CURLOPT_HTTPHEADER, [
        //     "Content-Type: application/json; charset=utf-8",
        //     "Access-Key-Id: " . ANTERAJA_ACCESS_KEY_ID,
        //     "Secret-Access-Key: " . ANTERAJA_SECRET_ACCESS_KEY,
        // ]);

        // $response = curl_exec($curl);
        // curl_close($curl);
        // $result = json_decode($response, true);

        // return $result;

        // $originQuery = $this->db->query("SELECT district_code FROM data_wilayah WHERE postal_code='$postal_origin'");
        $this->db->select('district_code');
        $this->db->from('data_wilayah');
        $this->db->where('postal_code', $postal_origin);
        $originQuery = $this->db->get()->row();

        // $destinationQuery = $this->db->query("SELECT district_code FROM data_wilayah WHERE postal_code='$postal_destini'");
        $this->db->select('district_code');
        $this->db->from('data_wilayah');
        $this->db->where('postal_code', $postal_destini);
        $destinationQuery = $this->db->get()->row();

        // if ($originQuery->num_rows() > 0 && $destinationQuery->num_rows() > 0) {
        // $origin = $originQuery->row()->district_code;
        // $destination = $destinationQuery->row()->district_code;

        $arr = [
            'origin' => $originQuery,
            'destination' => $destinationQuery,
            'weight' => $weight
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, ANTERAJA_BASEPATH . "/serviceRates");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($arr));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 50);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded",
            "Access-Key-Id: " . ANTERAJA_ACCESS_KEY_ID,
            "Secret-Access-Key: " . ANTERAJA_SECRET_ACCESS_KEY
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response, true);

        return $result;
        // } else {
        //     echo 'sas';
        // }
    }

    public function getServiceRates_JNE($id_origin, $idAddressUser, $weight)
    {

        // $id_origin = $this->db->antiSQLi($id_origin);
        // $idAddressUser = $this->db->antiSQLi($idAddressUser);
        // $weight = $this->db->antiSQLi($weight);
        $weight = $weight / 1000;

        $postalcodePembeli = $this->db->query("SELECT kodepos_user FROM loginuser_detail_alamat WHERE id_alamat_user='$idAddressUser'")->row_array();
        $origin = $this->db->query("SELECT kode_jne FROM cabang WHERE idcabang='$id_origin'")->row_array();
        $destination = $this->db->query("SELECT tariff_code FROM courier_jne_dest WHERE postal_code='" . $postalcodePembeli['kodepos_user'] . "'")->row_array();
        $post = 'username=' . JNE_USERNAME . '&api_key=' . JNE_API_KEY . '&from=' . $origin['kode_jne'] . '&thru=' . $destination['tariff_code'] . '&weight=' . $weight;
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, JNE_BASEPATH . "pricedev");
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);


        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response, true);
        return $result;
    }

    public function getServiceRates_SC($id_origin, $postal_destini, $weight)
    {

        $id_origin = $this->db->antiSQLi($id_origin);
        $postal_destini = $this->db->antiSQLi($postal_destini);
        $weight = $this->db->antiSQLi($weight);
        $weight = $weight / 1000;

        $origin = $this->db->get_data("SELECT kode_sicepat FROM cabang WHERE idcabang='$id_origin'");
        $destination = $this->db->get_data("SELECT kode_tarif FROM courier_sicepat WHERE kode_pos=$postal_destini");

        $data = [
            'origin' => $origin['kode_sicepat'],
            'destination' => $destination['kode_tarif'],
            'weight' => $weight
        ];

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, SICEPAT_BASEPATH . "/customer/tariff?" . http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'api-key: ' . SICEPAT_APIKEY_PROD
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response, true);

        return $result;
    }

    public function getServiceRateLionParcel($id_origin, $postal_destini, $weight)
    {


        $id_origin = $this->db->antiSQLi($id_origin);
        $postal_destini = $this->db->antiSQLi($postal_destini);
        $weight = $this->db->antiSQLi($weight);
        $weight = $weight / 1000;


        $origin = $this->db->get_data("SELECT kecamatan_cabang, kota_cabang FROM `cabang` WHERE idcabang='$id_origin'");
        $destination = $this->db->get_data("SELECT * FROM `data_wilayah` WHERE postal_code='$postal_destini'");

        $data = [
            'origin' =>  $origin['kecamatan_cabang'] . ', ' . $origin['kota_cabang'],
            'destination' => $destination['district'] . ', ' . $destination['city'],
            'weight' => $weight,
            'commodity' => 'GEN-GENERAL%20OTHERS-GENERAL%20LAINNYA'
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api-stg-middleware.thelionparcel.com/v3/tariffv3?' . http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic bGlvbnBhcmNlbDpsaW9ucGFyY2VsQDEyMw=='
            ),
        ));

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $res = ['status' => $httpcode, 'response' => json_decode($response, true)];
        return $res;
    }
}
