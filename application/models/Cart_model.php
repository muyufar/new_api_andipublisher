<?php

class Cart_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->load->helper('feature');
    }

    public function getCart($idUser)
    {
        $this->db->select('ldk.id_detail_keranjang, ldk.id_user, ldk.cabang_stok_id, ldk.id_barang, ldk.jumlah_keranjang,itm.slug_barang, itm.judul, itm.gambar1, cis.qty_stok, cis.idcabang, cis.idbarang');
        $this->db->from('loginuser_detail_keranjang as ldk');
        $this->db->join('itemmaster as itm', 'ldk.id_barang = itm.id_barang', 'inner');
        $this->db->join('cabang_item_stok as cis', 'ldk.id_barang = cis.idbarang', 'inner');
        $this->db->where('cis.idcabang = ldk.cabang_stok_id', NULL, FALSE);
        $this->db->where('ldk.id_user', $idUser);
        $results = $this->db->get()->result_array();

        $groupedData = array();

        foreach ($results as $row) {
            $detailKeranjangId = $row['id_detail_keranjang'];
            $cabangStokId = $row['cabang_stok_id'];

            // Check if this cabang_stok_id is already in groupedData
            if (!isset($groupedData[$cabangStokId])) {
                $groupedData[$cabangStokId] = array(
                    'id_detail_keranjang' => $detailKeranjangId,
                    'id_user' => $row['id_user'],
                    'cabang_stok_id' => $cabangStokId,
                    'barang' => array()
                );
            }

            // Add the current barang to the barang array of this cabang_stok_id
            $groupedData[$cabangStokId]['barang'][] = array(
                'id_barang' => $row['id_barang'],
                'slug_barang' => $row['slug_barang'],
                'gambar1' => URL_IMAGE_PRODUK . $row['gambar1'],
                'judul' => $row['judul'],
                'jumlah_keranjang' => intval($row['jumlah_keranjang']),
                'qty_stok' => intval($row['qty_stok']),
            );
        }

        // Convert the grouped data into a numeric array
        $finalResult = array_values($groupedData);

        return $finalResult;
    }
    public function getCount($idUser)
    {
        $this->db->select('*');
        $this->db->from('loginuser_detail_keranjang');
        $this->db->where('id_user', $idUser);
        $query = $this->db->get();


        $totalRows = $query->num_rows();
        $totalRows = ($totalRows > 99) ? '99+' : strval($totalRows);

        return  $totalRows;
    }

    public function addCart($idUser, $idBarang, $resultCIS, $qty)
    {
        $this->db->select('*');
        $this->db->from('loginuser_detail_keranjang');
        $this->db->where('id_user', $idUser);
        $this->db->where('cabang_stok_id', $resultCIS['idcabang']);
        $this->db->where('id_barang', $idBarang);
        $resultLDK = $this->db->get()->row_array();

        $id_detail_keranjang = createID($this->db, 'id_detail_keranjang', 'loginuser_detail_keranjang', 'KD');
        $data = array(
            'id_detail_keranjang' => $id_detail_keranjang,
            'id_user' => $idUser,
            'id_barang' => $idBarang,
            'cabang_stok_id' => $resultCIS['idcabang'],
            'jumlah_keranjang' => $qty
        );
        $status = '';
        if ($resultLDK != null) {
            $data['jumlah_keranjang'] = $resultLDK['jumlah_keranjang'] + $qty;
            if ($data['jumlah_keranjang'] > $resultCIS['qty_stok']) {
                $status = '404';
                return $status;
            }
            $this->db->where('id_user', $idUser);
            $this->db->where('cabang_stok_id', $resultCIS['idcabang']);
            $this->db->where('id_barang', $idBarang);
            $this->db->update('loginuser_detail_keranjang', $data);
        } else {
            $this->db->insert('loginuser_detail_keranjang', $data);
        }

        return $status;
    }


    public function qtyCart($id_detail_keranjang, $qty)
    {
    }
}
