<?php


class Transaction_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->load->helper('feature');
        $this->load->model('Payment_model');
    }



    public function getCheckout($idUser, $idBarang, $idCabang, $quantityOrderDireck = null)
    {
        $this->db->select('im.id_barang,im.slug_barang, im.gambar1, im.judul, im.berat, im.harga, im.diskon');
        $this->db->from('itemmaster as im');
        $this->db->where_in('im.id_barang', $idBarang);
        $queryItem = $this->db->get();
        $resultItem = $queryItem->result_array();

        $this->db->select('c.idcabang, c.nama_cabang, c.alamat_cabang');
        $this->db->from('cabang as c');
        $this->db->where('c.idcabang', $idCabang);
        $querywarehouse = $this->db->get();
        $resultwarehouse = $querywarehouse->row_array();

        $this->db->select('lu.poin_user, lad.id_alamat_user, lad.alamat_user, lad.telepon_user, lad.nama_penerima_user, lad.label_alamat_user');
        $this->db->from('loginuser as lu');
        $this->db->join('loginuser_detail_alamat as lad', 'lu.id_user = lad.id_user');
        $this->db->where('lu.id_user', $idUser);
        $this->db->where('lad.status_utama_alamat_user', 'Y');
        $this->db->limit(1);
        $queryUser = $this->db->get();
        $resultUser['data_alamat_user'] = $queryUser->row_array();

        $this->db->select('pf.persen_poin, pf.biaya_penanganan');
        $this->db->from('profile as pf');
        $this->db->where('pf.id', '1');
        $queryProfile = $this->db->get();
        $resultProfile = $queryProfile->row_array();

        $data = array();

        foreach ($resultItem as $row) {
            $itemObj = new stdClass();
            $itemObj->items = [
                array(
                    'id_barang' => $row['id_barang'],
                    'slug_barang' => $row['slug_barang'],
                    'gambar1' => URL_IMAGE_PRODUK . $row['gambar1'],
                    'judul' => $row['judul'],
                    'berat' => intval($row['berat']),
                    'harga' => intval($row['harga']),
                    'harga' => intval($row['harga']),
                    'harga_promo' =>   $row['harga'] * ((100 - $row['diskon']) / 100),
                    'diskon' => intval($row['diskon']),
                    'quantityOrder' => intval($quantityOrderDireck),
                    'subtotal' => ($row['harga'] * ((100 - $row['diskon']) / 100)) * $quantityOrderDireck,
                )
            ];


            // Mengambil data warehouse
            $itemObj->warehouse_item = array(
                'idcabang' => $resultwarehouse['idcabang'],
                'nama_cabang' => $resultwarehouse['nama_cabang'],
                'alamat_cabang' => $resultwarehouse['alamat_cabang']
            );

            $data[] = $itemObj;
        }

        $resultUser['poin_user'] = intval($resultUser['data_alamat_user']['poin_user']);
        unset($resultUser['data_alamat_user']['poin_user']);
        $resultProfile = array(
            'persen_poin' => intval($resultProfile['persen_poin']),
            'biaya_penanganan' => intval($resultProfile['biaya_penanganan'])
        );

        $result = array(
            'data_user' => $resultUser,
            'data_profile' => $resultProfile,
            'data_checkout' => $data
        );

        return $result;
    }

    public function postTransaction($dataUser, $dataCheckout, $dataAddress, $dataCourierServices, $usePoinUser, $dataVoucherProduct = null, $dataVoucherCourier = null)
    {
        // GET BIAYA PENANGANAN
        $biaya_penanganan = $this->db->query("SELECT biaya_penanganan FROM profile LIMIT 1")->row_array();

        // INSERT INTO DATABASE
        $this->db->trans_begin(); // Memulai transaksi

        foreach ($dataCheckout  as $valCheckoutItem) {
            // CEK BARANG BELANJAAN
            $total_berat = 0;
            $total_belanja = 0;
            $total_belanja_original = 0;
            $voucher_ongkir = 0;
            $voucher_harga = 0;

            $stok_sub_total = 0;
            $stok_disc_total = 0;
            $stok_grand_total = 0;

            $alamat_pembeli_lengkap = $dataAddress['alamat_user'] . ", " . $dataAddress['kelurahan_user'] . ", " . $dataAddress['kecamatan_user'] . ", " . $dataAddress['kota_user'] . ", " . $dataAddress['provinsi_user'] . ", " . $dataAddress['kodepos_user'];

            $valProductIncremeant = 0;
            $data_dibeli = [];
            foreach ($valCheckoutItem['products'] as $valProduct) {

                $dataProducts = $this->db->get_where('itemmaster', ['id_barang' => $valProduct['idProduct']])->row_array();
                $total_berat += ($dataProducts['berat'] * $valProduct['quantity']);
                $total_belanja_original += ($dataProducts['harga'] * $valProduct['quantity']);

                //  Buat query untuk mencari flashsale_detail yang memenuhi kondisi
                $this->db->select('fd.kd_barang, fd.diskon, f.waktu_mulai, f.waktu_selesai');
                $this->db->from('flashsale_detail fd');
                $this->db->join('flashsale f', 'fd.kd_flashsale = f.id_flashsale');
                $this->db->where('fd.kd_barang', $valProduct['idProduct']);
                $this->db->where('fd.status_remove_detailflashsale', 'N');
                $this->db->where('f.status_remove_flashsale', 'N');
                $this->db->where('f.waktu_mulai <= NOW()');
                $this->db->where('f.waktu_selesai >= NOW()');
                $productFlashsale = $this->db->get()->row_array();

                //  Buat query untuk mencari campaign2_detail yang memenuhi kondisi
                $this->db->select('cd.kd_itemmaster, cd.nominal_harga, c.jenis_campaign2');
                $this->db->from('campaign2_detail cd');
                $this->db->join('campaign2 c', 'cd.kd_campaign2 = c.id_campaign2');
                $this->db->where('cd.kd_itemmaster', $valProduct['idProduct']);
                $this->db->where('cd.status_aktif', 'Y');
                $this->db->where('cd.current_stok < cd.max_stok');
                $this->db->where('c.status_remove_campaign2', 'N');
                $this->db->where('c.waktu_mulai2 <= NOW()');
                $this->db->where('c.waktu_selesai2 >= NOW()');
                $productCampaign = $this->db->get()->row_array();


                if (!empty($productFlashsale)) {
                    $diskon_perbarang = $productFlashsale['diskon_flashsale'];
                    $harga_perbarang = $dataProducts['harga'] - round($dataProducts['harga'] * ($productFlashsale['diskon_flashsale'] / 100));
                    $ttl_diskon_ = round($dataProducts['harga'] * ($productFlashsale['diskon_flashsale'] / 100));
                } elseif (!empty($productCampaign)) {
                    $diskon_perbarang = $productCampaign['diskon_detail'];
                    $harga_perbarang = $dataProducts['harga'] - round($dataProducts['harga'] * ($productCampaign['diskon_detail'] / 100));
                    $ttl_diskon_ = round($dataProducts['harga'] * ($productCampaign['diskon_detail'] / 100));
                } elseif ($dataProducts['diskon'] != "0") {
                    $diskon_perbarang = $dataProducts['diskon'];
                    $harga_perbarang = $dataProducts['harga'] - round($dataProducts['harga'] * ($dataProducts['diskon'] / 100));
                    $ttl_diskon_ = round($dataProducts['harga'] * ($dataProducts['diskon'] / 100));
                } else {
                    $diskon_perbarang = 0;
                    $harga_perbarang = $dataProducts['harga'];
                    $ttl_diskon_ = 0;
                }

                // stock
                $stok_sub_total += ($dataProducts['harga'] * $valProduct['quantity']);
                $stok_disc_total += $ttl_diskon_;

                $stok_grand_total += (($dataProducts['harga'] * $valProduct['quantity']) - $ttl_diskon_);
                $subtotal = $harga_perbarang * $valProduct['quantity'];
                $total_belanja  += $subtotal;

                $data_dibeli[$valProductIncremeant]['id_barang'] =  $valProduct['idProduct'];
                $data_dibeli[$valProductIncremeant]['jml_beli'] =  $valProduct['quantity'];
                $data_dibeli[$valProductIncremeant]['harga_sebelum_diskon'] = $dataProducts['harga'];
                $data_dibeli[$valProductIncremeant]['kd_kategori'] = $dataProducts['kdkategori'];
                $data_dibeli[$valProductIncremeant]['diskon'] = $diskon_perbarang;
                $data_dibeli[$valProductIncremeant]['harga_setelah_diskon'] = $harga_perbarang;
                $data_dibeli[$valProductIncremeant]['subtotal'] = $subtotal;
                $data_dibeli[$valProductIncremeant]['stok_sub_total'] = ($dataProducts['harga'] * $valProduct['quantity']);
                $data_dibeli[$valProductIncremeant]['stok_disc_total'] = $ttl_diskon_ *  $valProduct['quantity'];
                $data_dibeli[$valProductIncremeant]['stok_grand_total'] = (($dataProducts['harga'] * $valProduct['quantity']) - $ttl_diskon_);
                $data_dibeli[$valProductIncremeant]['stok_nama_item'] = $dataProducts['judul'];

                $valProductIncremeant++;
            }

            foreach ($dataCourierServices as $dataCourierService) {
                if ($dataCourierService['idWarehouse'] == $valCheckoutItem['idWarehouse']) {
                    $dataCourier = $dataCourierService;
                }
            }

            // JUMLAH_AKHIR
            $total_tagihan = round(($total_belanja + ($dataCourier['harga'] - $voucher_ongkir) - $voucher_harga) + $biaya_penanganan['biaya_penanganan']);

            $date_now = date("Y-m-d H:i:s");
            $id_transaksi = createID($this->db, 'id_transaksi', 'transaksi', 'TR');
            $id_transaksi_new = createID($this->db, 'idtransaksi_new', 'transaksi_new', 'TN');
            $no_invoice = id_ke_struk($id_transaksi);
            $genereate_referral = genereate_referral($id_transaksi);
            $dataTransaksi = array(
                'id_transaksi' => $id_transaksi,
                'tanggal_transaksi' => $date_now,
                'id_invoice' => $no_invoice,
                'id_user' => $dataUser['id_user'],
                'catatan_pembeli' => $valCheckoutItem['noteUserBuy'],
                'label_alamat' => $dataAddress['label_alamat_user'],
                'alamat_pengiriman' => $alamat_pembeli_lengkap,
                'nama_penerima' => $dataAddress['nama_penerima_user'],
                'telepon_penerima' => $dataAddress['telepon_user'],
                'total_harga_sebelum_diskon' => $total_belanja_original,
                'total_harga_setelah_diskon' => $total_belanja,
                'total_berat' => $total_berat,
                'harga_ongkir' => $dataCourier['harga'],
                'voucher_harga' => $voucher_harga,
                'voucher_harga_persen' => 0,
                'voucher_ongkir' => $voucher_ongkir,
                'point_user' => ($usePoinUser) ? $dataUser['poin_user'] : 0,
                'total_harga_final' => $total_tagihan,
                'kurir_pengiriman' => $dataCourier['kode'],
                'kurir_service' => $dataCourier['layanan'],
                'metode_pembayaran' => '2',
                'biaya_penanganan' => $biaya_penanganan['biaya_penanganan']
            );
            $this->db->insert('transaksi', $dataTransaksi);

            $data = array(
                'idtransaksi_new' => $id_transaksi_new,
                'id_transaksi_lama' => $no_invoice,
                'tanggal_transaksi' => $date_now,
                'keterangan' => 'PENJUALAN INVOICE ' . $no_invoice,
                'jenis_transaksi' => 'SL',
                'iduser' => $dataUser['id_user'],
                'sub_total' => $stok_sub_total,
                'disc_total' => $stok_disc_total,
                'grand_total' => $stok_grand_total,
                'idcabang' => $valCheckoutItem['idWarehouse']
            );

            $this->db->insert('transaksi_new', $data);


            // input detail
            $pengurangan_wishlist = 0;



            foreach ($data_dibeli as $value) {
                $pengurangan_wishlist += 1;

                $this->db->query("INSERT INTO transaksi_detail_barang SET id_detail_transaksi=UUID(), id_transaksi='$id_transaksi', id_barang='$value[id_barang]', harga_normal='$value[harga_sebelum_diskon]',  diskon='$value[diskon]', harga_setelah_diskon='$value[harga_setelah_diskon]', jumlah_beli='$value[jml_beli]', sub_total='$value[subtotal]'");

                //stok
                $this->db->query("DELETE FROM loginuser_detail_keranjang WHERE id_user='$dataUser[id_user]' AND id_barang='$value[id_barang]' AND cabang_stok_id='$valCheckoutItem[idWarehouse]' ");

                $checkStok = $this->db->query("SELECT idcabang, idbarang, qty_stok FROM cabang_item_stok WHERE idcabang='$valCheckoutItem[idWarehouse]' AND idbarang='$value[id_barang]' AND qty_stok>$value[jml_beli]")->row_array();

                if (is_null($checkStok)) {
                    $this->db->rollback();

                    $dataError['status'] = false;
                    $dataError['content'] = 'Stok tidak ada';
                    return $dataError;
                }
                $rsQtyStok = $checkStok['qty_stok'] - $value['jml_beli'];

                $this->db->query("UPDATE cabang_item_stok SET qty_stok='$rsQtyStok' WHERE idcabang='" . $checkStok['idcabang'] . "' AND idbarang='" . $checkStok['idbarang'] . "'");

                $this->db->query("INSERT INTO transaksi_new_detail SET idtransaksi_new='$id_transaksi_new', tanggal_transaksi='$date_now', iditem='$value[id_barang]', jenis_transaksi='-1', nama_item='$value[stok_nama_item]', satuan='Buah', jumlah='$value[jml_beli]', before_qty='$checkStok[qty_stok]', after_qty='$rsQtyStok', harga_satuan_jual='$value[harga_sebelum_diskon]', sub_total='$value[stok_sub_total]', disc_total='$value[stok_disc_total]', grand_total='$value[stok_grand_total]', keterangan='PENJUALAN INVOICE $no_invoice' ");
            }
        }



        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback(); // Rollback jika terjadi kegagalan
            $dataError['status'] = false;
            $dataError['content'] = 'Rollback jika terjadi kegagalan';
            return $dataError;
        } else {
            $this->db->trans_commit(); // Commit jika transaksi berhasil

            $response =   $this->Payment_model->midtrans_post($no_invoice, $total_tagihan, $dataUser);

            if (!isset($response['token'])) {
                $this->db->query("DELETE FROM transaksi WHERE id_transaksi='$id_transaksi' ");
                $this->db->query("DELETE FROM transaksi_detail_barang WHERE id_transaksi='$id_transaksi' ");
                $dataError['status'] = false;
                $dataError['content'] =  json_encode($response);
                return $dataError;
            }

            $payment_url = $response['redirect_url'];
            $payment_token = $response['token'];
            $this->db->query("UPDATE transaksi SET payment_token='$payment_token', payment_redirect='$payment_url' WHERE id_transaksi='$id_transaksi' ");

            $data['status'] = true;
            $data['id'] = $id_transaksi;
            $data['token'] = $payment_token;
            $data['url'] = $payment_url;

            return $data;
        }
    }

    public function getHistoryTransactio($offset, $limit, $tag, $idUser)
    {
        switch ($tag) {
            case '1':
                $data = $this->getHistoryTransactioTag($offset, $limit, ['1', '2'], $idUser);
                return $data;
                break;
            case '2':
                $data = $this->getHistoryTransactioTag($offset, $limit, ['3'], $idUser);
                return $data;
                break;
            case '3':
                $data = $this->getHistoryTransactioTag($offset, $limit, ['5', '6'], $idUser);
                return $data;
                break;
            case '4':
                $data = $this->getHistoryTransactioTag($offset, $limit, ['7'], $idUser);
                return $data;
                break;
            case '5':
                $data = $this->getHistoryTransactioTag($offset, $limit, ['8', '9', '10' . '11'], $idUser);
                return $data;
                break;
            default:
                return;
                break;
        }
    }

    public function getHistoryTransactioTag($offset, $limit, $status_transaksi, $idUser)
    {
        $this->db->select('tr.id_transaksi, tr.tanggal_transaksi, tr.total_harga_final, tr.status_transaksi, trd.id_barang, trd.jumlah_beli, trd.sub_total, itm.slug_barang, itm.gambar1, itm.judul');
        $this->db->select('(SELECT COUNT(*) FROM transaksi_detail_barang AS trd2 WHERE trd2.id_transaksi = tr.id_transaksi) AS jumlah_barang', FALSE);
        $this->db->from('transaksi as tr');
        $this->db->join('transaksi_detail_barang as trd', 'tr.id_transaksi = trd.id_transaksi', 'inner');
        $this->db->join(
            'itemmaster as itm',
            'trd.id_barang = itm.id_barang AND trd.id_barang = (SELECT MAX(id_barang) FROM transaksi_detail_barang WHERE id_transaksi = tr.id_transaksi)',
            'inner'
        );
        $this->db->where('tr.id_user', $idUser);
        $this->db->where_in('tr.status_transaksi', $status_transaksi);
        $this->db->limit($limit, $offset);
        $results = $this->db->get()->result_array();

        $dataFormatted = array();
        foreach ($results as $row) {
            $dataFormatted[] = array(
                "id_transaksi" => $row["id_transaksi"],
                "status_transaksi" => $row["status_transaksi"],
                "tanggal_transaksi" => $row["tanggal_transaksi"],
                "total_harga_final" => intval($row["total_harga_final"]),
                "jumlah_barang" =>  $row["jumlah_barang"] . ' produk',
                "barang" => array(
                    "id_barang" => $row["id_barang"],
                    "slug_barang" => $row["slug_barang"],
                    "gambar1" => URL_IMAGE_PRODUK . $row["gambar1"],
                    "judul" => $row["judul"],
                    "jumlah_beli" => intval($row["jumlah_beli"]),
                    "sub_total" => intval($row["sub_total"])
                )
            );
        }

        return $dataFormatted;
    }

    public function getDetailHistoryTransactio($idUser, $idTransaksi)
    {
        $this->db->select('tr.id_transaksi, tr.tanggal_transaksi, tr.total_harga_final, tr.status_transaksi,tr.id_invoice,tr.catatan_pembeli, tr.label_alamat, tr.alamat_pengiriman, 
        tr.nama_penerima,tr.telepon_penerima, tr.total_harga_sebelum_diskon, tr.total_berat,tr.harga_ongkir,tr.voucher_harga,tr.voucher_ongkir, tr.biaya_penanganan,
        tr.point_user,tr.total_harga_final,tr.kurir_pengiriman,tr.kurir_service,tr.nomor_resi,tr.status_transaksi,tr.tanggal_dibayar, tr.metode_pembayaran,tr.payment_redirect');
        $this->db->select('trd.id_barang, trd.harga_normal,trd.diskon, trd.harga_setelah_diskon,trd.jumlah_beli, trd.sub_total,itm.slug_barang, itm.gambar1, itm.judul');
        $this->db->from('transaksi as tr');
        $this->db->join('transaksi_detail_barang as trd', 'tr.id_transaksi = trd.id_transaksi', 'inner');
        $this->db->join('itemmaster as itm', 'trd.id_barang = itm.id_barang', 'inner');
        $this->db->where('tr.id_user', $idUser);
        $this->db->where('tr.id_transaksi', $idTransaksi);
        $this->db->order_by('tr.tanggal_transaksi', 'desc'); // Menambahkan pengurutan berdasarkan tanggal_transaksi terbaru
        $results = $this->db->get()->result_array();


        // Mendapatkan berat dalam bentuk gram
        $beratGram = intval($results[0]['total_berat']);
        // Mengonversi berat menjadi kilogram jika melebihi 1000 gram
        if ($beratGram >= 1000) {
            $beratKg = $beratGram / 1000;
            $results[0]['total_berat'] = $beratKg . ' kg';
        } else {
            $results[0]['total_berat'] = $beratGram . ' g';
        }


        $dataFormatted = array(
            "transaksi" => array(
                "id_transaksi" => $results[0]["id_transaksi"],
                "id_invoice" => $results[0]["id_invoice"],
                "status_transaksi" => $results[0]["status_transaksi"],
                "tanggal_transaksi" => $results[0]["tanggal_transaksi"],
                "tanggal_dibayar" => $results[0]["tanggal_dibayar"],
                "metode_pembayaran" => $results[0]["metode_pembayaran"],
                "payment_redirect" => $results[0]["payment_redirect"],
            ),
            "alamat_user" => array(
                "label_alamat" => $results[0]["label_alamat"],
                "alamat_pengiriman" => $results[0]["alamat_pengiriman"],
                "nama_penerima" => $results[0]["nama_penerima"],
                "telepon_penerima" => $results[0]["telepon_penerima"],
            ),
            "kurir" => array(
                "kurir_pengiriman" => $results[0]["kurir_pengiriman"],
                "kurir_service" => $results[0]["kurir_service"],
                "nomor_resi" => $results[0]["nomor_resi"],
                "total_berat" => $results[0]["total_berat"],
                "catatan_pembeli" => $results[0]["catatan_pembeli"],
            ),
            "barang" => array(),
            "rincian_harga" => array(
                "total_harga_sebelum_diskon" => intval($results[0]["total_harga_sebelum_diskon"]),
                "harga_ongkir" => intval($results[0]["harga_ongkir"]),
                "voucher_harga" => intval($results[0]["voucher_harga"]),
                "voucher_ongkir" => intval($results[0]["voucher_ongkir"]),
                "biaya_penanganan" => intval($results[0]["biaya_penanganan"]),
                "point_user" => intval($results[0]["point_user"]),
                "total_harga_final" => intval($results[0]["total_harga_final"]),
            ),
        );

        foreach ($results as $row) {
            $dataFormatted['barang'][] = array(
                "id_barang" => $row["id_barang"],
                "slug_barang" => $row["slug_barang"],
                "judul" => $row["judul"],
                "gambar1" => URL_IMAGE_PRODUK . $row["gambar1"],
                "harga_normal" => intval($row["harga_normal"]),
                "diskon" => intval($row["diskon"]),
                "harga_setelah_diskon" => intval($row["harga_setelah_diskon"]),
                "jumlah_beli" => intval($row["jumlah_beli"]),
                "sub_total" => intval($row["sub_total"]),
            );
        }

        return  $dataFormatted;
    }
}
