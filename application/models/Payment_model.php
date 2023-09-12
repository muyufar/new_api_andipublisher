<?php

class Payment_model extends CI_Model
{

    public function midtrans_post($no_invoice, $total_tagihan, $data_pembeli)
    {
        $mtrans['transaction_details']['order_id'] = $no_invoice;
        $mtrans['transaction_details']['gross_amount'] = $total_tagihan;
        $mtrans['credit_card']['secure'] = true;
        $mtrans['customer_details']['first_name'] = $data_pembeli['nama_user'];
        $mtrans['customer_details']['last_name'] = '';
        $mtrans['customer_details']['email'] = $data_pembeli['email_user'];
        $mtrans['customer_details']['phone'] = nomor_plus_62($data_pembeli['telepon_user']);

        $mtrans_json = json_encode($mtrans);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => MTRANS_PATH,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $mtrans_json,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic ' . base64_encode(MTRANS_SERVER_KEY),
                'Content-Type: application/json'
            ),
        ));

        $response_curl = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response_curl, true);
        return $response;
    }
}
