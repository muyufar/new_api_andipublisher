<?php

class InfoApp_model extends CI_Model
{
    public function getInfoApp()
    {
        $this->db->select('version_android, version_ios, status_maintenance, desc_maintenance');
        $query  = $this->db->get('info_app');
        $row = $query->row_array();

        return $row;
    }
}
