<?php

class Category_model extends CI_Model
{
    public function getCategoryMain($idKategori = array())
    {
        $this->db->select('id_kategori, nama_kategori, icon');
        $this->db->where('status_remove_kategori', 'N');
        $this->db->where("parent_kategori = ''");


        if (!empty($idKategori)) {
            $this->db->where_in('id_kategori', $idKategori);
        }

        $query = $this->db->get('itemkategorinew');
        $row = $query->result_array();

        return $row;
    }

    public function getCategorySub()
    {
        $this->db->select('id_kategori, parent_kategori, nama_kategori, icon');
        $this->db->where('status_remove_kategori', 'N');
        $this->db->where("parent_kategori != ''");

        $query = $this->db->get('itemkategorinew');
        $row = $query->result_array();

        return $row;
    }

    public function getCategorySelect()
    {
        $results = $this->getCategoryMain(['KT202201100000000008']);

        return $results;
    }
}
