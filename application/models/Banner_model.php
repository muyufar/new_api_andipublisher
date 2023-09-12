<?php

class Banner_model extends CI_Model
{

    public function getBunner()
    {
        $query =  $this->db->select('idbanner, nama_banner, gambar_banner, link_banner')->from('promo_banner')
            ->where('tgl_exp_banner >', date('Y-m-d'))
            ->order_by('tgl_exp_banner', 'DESC')
            ->get();

        $rows = $query->result_array();

        foreach ($rows as &$row) {
            $row['gambar_banner'] = URL_IMAGE_BANNER . $row['gambar_banner'];
        }

        return $rows;
    }
}
