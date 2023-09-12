<?php

class User_model extends CI_Model
{
    public function getUser($id)
    {
        $this->db->select('id_user, nama_user, username_user, email_user, telepon_user, foto_user, poin_user');
        $this->db->where('id_user', $id);
        $this->db->limit(1);
        $query = $this->db->get('loginuser');
        $row = $query->row_array();

        if ($query->num_rows() > 0) {
            // Menambahkan URL pada kolom foto_user
            $row['foto_user'] = URL_IMAGE_PROFIL . $row['foto_user'];

            $row['poin_user'] = intval($row['poin_user']);
        }

        return $row;
    }

    public function postRegister($data)
    {

        $this->db->insert('loginuser', $data);

        if ($this->db->affected_rows() == 0) {
            return null;
        }

        $this->db->select('id_user, nama_user, username_user, email_user, telepon_user, foto_user, poin_user');
        $query = $this->db->from('loginuser')->order_by('id_user', 'DESC')->limit(1)->get();
        $row = $query->row_array();

        if ($query->num_rows() > 0) {
            // Menambahkan URL pada kolom foto_user
            $row['foto_user'] = URL_IMAGE_PROFIL . $row['foto_user'];

            $row['poin_user'] = intval($row['poin_user']);
        }

        return $row;
    }


    public function postLogin($email, $password)
    {
        $this->db->select('id_user, nama_user, username_user, email_user, telepon_user, foto_user, poin_user, password_user');
        $query  = $this->db->get_where('loginuser', ['email_user' => $email]);
        $row = $query->row_array();

        if ($query->num_rows() == 0) {
            return null;
        }

        $storedPassword = $row['password_user'];

        if (password_verify($password, $storedPassword)) {
            // Password cocok
            // Menambahkan URL pada kolom foto_user
            $row['foto_user'] = URL_IMAGE_PROFIL . $row['foto_user'];

            unset($row['password_user']);
            $row['poin_user'] = intval($row['poin_user']);
        } else {
            // Password tidak cocok
            return null;
        }

        return $row;
    }

    public function putUpdateUser($id, $data)
    {
        $this->db->update('loginuser', $data, ['id_user' => $id]);
        $this->getUser($id);
    }

    public function putChangePassword($id, $password, $data)
    {
        $this->db->select('id_user, nama_user, username_user, email_user, telepon_user, foto_user, poin_user, password_user');
        $query  = $this->db->get_where('loginuser', ['id_user' => $id]);
        $row = $query->row_array();

        if ($query->num_rows() == 0) {
            return null;
        }

        $storedPassword = $row['password_user'];

        if (password_verify($password, $storedPassword)) {
            // Password cocok
            // Update password
            $this->db->update('loginuser', $data, ['id_user' => $id]);

            // Menambahkan URL pada kolom foto_user
            $row['foto_user'] = URL_IMAGE_PROFIL . $row['foto_user'];

            unset($row['password_user']);
            $row['poin_user'] = intval($row['poin_user']);
        } else {
            // Password tidak cocok
            return null;
        }

        return $row;
    }
}
