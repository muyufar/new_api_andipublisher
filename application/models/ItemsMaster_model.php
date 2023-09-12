<?php

class ItemsMaster_model extends CI_Model
{
    public function getItems($keyword = '', $limit = 10, $offset = 0, $kdkategori = array(), $oderBy = 0)
    {
        $this->db->select('itemmaster.id_barang, itemmaster.slug_barang, itemmaster.gambar1, itemmaster.judul, itemmaster.harga, itemmaster.diskon');
        $this->db->select("(SELECT CAST(SUM(cabang_item_stok.qty_stok) > 0 AS UNSIGNED) FROM cabang_item_stok WHERE cabang_item_stok.idbarang = itemmaster.id_barang) AS status_stok");
        $this->db->from('itemmaster');
        $this->db->like('itemmaster.judul', $keyword); // Pencarian berdasarkan judul

        // Filter berdasarkan kdkategori jika kdkategori tidak kosong
        if (!empty($kdkategori)) {
            $this->db->where_in('itemmaster.kdkategori', $kdkategori);
        }

        // Mengurutkan data berdasarkan id_barang secara descending (terbaru)
        switch ($oderBy) {
            case 1:
                $this->db->order_by('itemmaster.id_barang', 'DESC');
                break;
            case 2:
                $this->db->order_by('itemmaster.jumlah_dibeli', 'DESC');
                break;
        }


        // Membatasi jumlah data yang diambil dan offset
        $this->db->limit($limit, $offset);

        $query = $this->db->get();
        $results = $query->result_array();

        foreach ($results as &$result) {
            $result['gambar1'] = URL_IMAGE_PRODUK . $result['gambar1'];
            $result['harga'] = intval($result['harga']);
            $result['diskon'] = intval($result['diskon']);
            $result['status_stok'] = boolval($result['status_stok']);
        }

        return $results;
    }

    public function getDetail($id)
    {
        $this->db->select(
            '
    im.id_barang,
    im.slug_barang,
    im.judul,
    im.gambar1,
    im.gambar2,
    im.gambar3,
    im.deskripsi,
    im.harga,
    im.diskon,
    im.berat,
    im.penulis,
    im.tahun,
    im.status_pdf_google_play,
    im.link_pdf_google_play,
    im.isbn,
    im.edisi,
    im.halaman,
    im.status_book_non_book,
    ik.id_kategori,
    ik.nama_kategori,
    MAX(tbdc.waktu_selesai2) AS expired_campaign,
    COALESCE(MAX(tbdc.diskon_detail), 0) AS diskon_campaign,
    MAX(tbdc.nominal_harga) AS nominal_campaign,
    tbfs2.waktu_selesai AS expired_flashsale,
    COALESCE(tbfs2.diskon, 0) AS diskon_flashsale'
        );

        $this->db->from('itemmaster as im');
        $this->db->join('itemkategorinew as ik', 'im.kdkategori = ik.id_kategori', 'inner');
        $this->db->join(
            '(SELECT MAX(f.waktu_selesai) AS waktu_selesai, MAX(fd.diskon) AS diskon, fd.kd_barang FROM flashsale AS f JOIN flashsale_detail AS fd ON f.id_flashsale = fd.kd_flashsale JOIN itemmaster AS i ON fd.kd_barang = i.id_barang WHERE f.status_remove_flashsale = "N" AND f.waktu_mulai < CURRENT_TIMESTAMP() AND f.waktu_selesai > CURRENT_TIMESTAMP() GROUP BY fd.kd_barang) AS tbfs2',
            'im.id_barang = tbfs2.kd_barang',
            'left'
        );
        $this->db->join(
            '(SELECT waktu_mulai2, waktu_selesai2, status_tampil_waktu2, cd.kd_itemmaster, cd.nominal_harga, cd.diskon_detail FROM campaign2 AS c JOIN campaign2_detail AS cd ON c.id_campaign2 = cd.kd_campaign2 WHERE c.waktu_mulai2 < CURRENT_TIMESTAMP() AND c.waktu_selesai2 > CURRENT_TIMESTAMP() AND c.status_remove_campaign2 = "N" AND cd.status_aktif = "Y") AS tbdc',
            'im.id_barang = tbdc.kd_itemmaster',
            'left'
        );
        $this->db->where('im.status_display', 'Y');
        $this->db->where('im.status_remove_barang', 'N');
        $this->db->where('im.id_barang', $id);
        $this->db->group_by('im.id_barang');

        $query = $this->db->get();
        $result = $query->row_array();

        if (!empty($result)) {

            // Menggabungkan diskon dengan diskon_flashsale dan diskon_campaign
            $result['diskon'] = !empty($result['diskon_flashsale'])
                ? intval($result['diskon_flashsale'])
                : (!empty($result['diskon_campaign'])
                    ? intval($result['diskon_campaign'])
                    : intval($result['diskon']));

            $result['harga_promo'] =  $result['harga'] * ((100 - $result['diskon']) / 100);

            $result['status_promo'] = !empty($result['expired_flashsale'])
                ? 'flashsale'
                : (!empty($result['expired_campaign'])
                    ? 'campaign'
                    : (($result['diskon'] != 0)
                        ? 'diskon'
                        : 'none'));


            $result['status_diskon'] = null;

            $result['start_promo'] = null;
            switch ($result['status_promo']) {
                case 'flashsale':
                    $result['start_promo'] = null;
                    break;
                case 'campaign':
                    $result['start_promo'] = null;
                    break;
                default:
                    $result['start_promo'] = null;
                    break;
            }

            $result['expired_promo'] = null;
            switch ($result['status_promo']) {
                case 'flashsale':
                    $result['expired_promo'] = $result['expired_flashsale'];
                    break;
                case 'campaign':
                    $result['expired_promo'] = $result['expired_campaign'];
                    break;
                default:
                    $result['expired_promo'] = null;
                    break;
            }
            // Mengubah gambar1, gambar2, dan gambar3 menjadi daftar objek gambar
            $gambar = array();
            $gambarFields = ['gambar1', 'gambar2', 'gambar3'];
            foreach ($gambarFields as $field) {
                if (!empty($result[$field])) {
                    $gambar[] = array('gambar' => URL_IMAGE_PRODUK . $result[$field]);
                }
                unset($result[$field]);
            }
            $result['gambar'] = $gambar;

            // Menggabungkan id_kategori dan nama_kategori menjadi satu objek
            $kategori = array(
                'id_kategori' => $result['id_kategori'],
                'nama_kategori' => $result['nama_kategori']
            );
            $result['kategori'] = $kategori;
            unset($result['id_kategori']);
            unset($result['nama_kategori']);

            // Menggabungkan status_pdf_google_play dan link_pdf_google_play menjadi satu objek
            $pdf_info = isset($result['status_pdf_google_play']) ? explode('|', $result['status_pdf_google_play']) : ['', ''];
            $pdf_obj = array(
                'status_pdf_google_play' => $pdf_info[0],
                'link_pdf_google_play' => isset($pdf_info[1]) ? $pdf_info[1] : ''
            );
            $result['pdf_info'] = $pdf_obj;
            unset($result['status_pdf_google_play']);
            unset($result['link_pdf_google_play']);

            // Menggabungkan expired_campaign, nominal_campaign, diskon_campaign, expired_flashsale, dan diskon_flashsale menjadi satu objek
            // $campaign_info = array(
            //     'expired_campaign' => $result['expired_campaign'],
            //     'nominal_campaign' => $result['nominal_campaign'],
            //     'diskon_campaign' => $result['diskon_campaign'],
            //     'expired_flashsale' => $result['expired_flashsale'],
            //     'diskon_flashsale' => $result['diskon_flashsale']
            // );
            // $result['campaign_info'] = $campaign_info;
            // unset($result['expired_campaign']);
            // unset($result['nominal_campaign']);
            // unset($result['diskon_campaign']);
            // unset($result['expired_flashsale']);
            // unset($result['diskon_flashsale']);

            unset($result['expired_campaign']);
            unset($result['nominal_campaign']);
            unset($result['diskon_campaign']);
            unset($result['expired_flashsale']);
            unset($result['diskon_flashsale']);


            // Mengubah harga, diskon, berat, penulis, tahun, isbn, edisi, dan halaman menjadi objek label dan value
            $attributes = array();
            $attributesFields = array(
                'penulis' => 'Penulis',
                'tahun' => 'Tahun',
                'isbn' => 'ISBN',
                'edisi' => 'Edisi',
                'halaman' => 'Halaman'
            );
            foreach ($attributesFields as $field => $label) {
                if (isset($result[$field]) && !empty($result[$field])) {
                    $attributes[] = array('label' => $label, 'value' => $result[$field]);
                }
                unset($result[$field]);
            }
            $result['barang_info'] = $attributes;

            // Mendapatkan stok barang yang tersedia di setiap cabang
            $this->db->select('c.idcabang, c.nama_cabang, c.alamat_cabang, cis.qty_stok');
            $this->db->from('cabang_item_stok as cis');
            $this->db->join('cabang as c', 'cis.idcabang = c.idcabang', 'inner');
            $this->db->where('cis.idbarang', $result['id_barang']);
            $this->db->where('c.status_aktif_cabang', 'Y');
            $this->db->where('cis.qty_stok >', 0); // Menambahkan kondisi qty_stok > 0
            $this->db->order_by('c.nama_cabang', 'asc');
            $queryStok = $this->db->get();
            $stokBarang = $queryStok->result_array();
            $result['warehouse'] = $stokBarang;


            $result['harga'] = intval($result['harga']);
            $result['diskon'] = intval($result['diskon']);

            // Mendapatkan berat dalam bentuk gram
            $beratGram = intval($result['berat']);

            // Mengonversi qty_stok menjadi integer di setiap objek warehouse
            foreach ($result['warehouse'] as &$stok) {
                $stok['qty_stok'] = intval($stok['qty_stok']);
            }
            // var_dump($result['warehouse']);

            // Validasi dan mengubah nilai diskon_campaign, nominal_campaign, dan diskon_flashsale
            // $result['campaign_info']['nominal_campaign'] = !empty($result['campaign_info']['nominal_campaign']) ? intval($result['campaign_info']['nominal_campaign']) : 0;
            // $result['campaign_info']['diskon_campaign'] = !empty($result['campaign_info']['diskon_campaign']) ? intval($result['campaign_info']['diskon_campaign']) : 0;
            // $result['campaign_info']['diskon_flashsale'] = !empty($result['campaign_info']['diskon_flashsale']) ? intval($result['campaign_info']['diskon_flashsale']) : 0;

            // Mengonversi berat menjadi kilogram jika melebihi 1000 gram
            if ($beratGram >= 1000) {
                $beratKg = $beratGram / 1000;
                $result['berat'] = $beratKg . ' kg';
            } else {
                $result['berat'] = $beratGram . ' g';
            }

            // Mengonversi status_book_non_book menjadi boolean
            $result['status_book_non_book'] = ($result['status_book_non_book'] === 'Y') ? true : false;

            // Menentukan status stok
            $status_stok = false; // Default status stok

            foreach ($result['warehouse'] as $key => $value) {
                if (!$status_stok && $value['qty_stok'] > 0) {
                    $status_stok = true;
                    break;
                }
            }

            $result['status_stok'] = $status_stok;
        }

        return (object) $result;
    }

    public function getNewItems($keyword = '', $limit = 10, $offset = 0, $kdkategori = array())
    {
        $results = $this->getItems($keyword, $limit, $offset, $kdkategori, 1);

        return $results;
    }

    public function getBestSaller($keyword = '', $limit = 10, $offset = 0, $kdkategori = array())
    {
        $results = $this->getItems($keyword, $limit, $offset, $kdkategori, 2);

        return $results;
    }
}
