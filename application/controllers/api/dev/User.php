<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

require APPPATH . 'libraries/RestController.php';

class User extends RestController
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('User_model');
        $this->load->model('Templates_model');
    }

    //Get Data User
    public function index_get()
    {
        //Parameter
        $idUser = $this->get('idUser');

        //Validation
        if ($idUser == '') {
            $this->response(
                $this->Templates_model->error('Parameter idUser Tidak Ada'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        } else if ($this->db->get_where('loginuser', ['id_user' => $idUser])->row_array()['status_delete_user'] == 'Y') {
            $this->response(
                $this->Templates_model->error('Akun sudah di hapus silakan hubungi admin untuk konfirmasi lebih lanjut'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        }

        //qurey
        $data = $this->User_model->getUser($idUser);
        $result = $this->Templates_model->success($data);

        if ($data == null) {
            $this->response(
                $this->Templates_model->error('Tidak Ada User'),
                RestController::HTTP_BAD_REQUEST
            );
        } else {
            $this->response(
                $result,
                RestController::HTTP_OK
            );
        }
    }

    //Register
    public function index_post()
    {
        //Parameter
        $nama = htmlspecialchars($this->post('nama'));
        $email = htmlspecialchars($this->post('email'));
        $telepon = htmlspecialchars($this->post('telepon'));
        $password = $this->post('password');
        $confPassword  = $this->post('confPassword');

        // Validatoin
        if ($nama == '' || $email == '' || $telepon == '' || $password == '' || $confPassword == '') {
            $this->response(
                $this->Templates_model->error("Parameter nama, email, telepon, password atau confPassword Tidak Ada"),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->response(
                $this->Templates_model->error('Email tidak valid'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        } else if ($password != $confPassword) {
            $this->response(
                $this->Templates_model->error('Parameter password dan confPassword Tidak Cocok'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        } else if ($this->db->get_where('loginuser', ['email_user' => $email])->num_rows() > 0) {
            $this->response(
                $this->Templates_model->error('Email sudah ada'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        } else if ($this->db->get_where('loginuser', ['telepon_user' => $telepon])->num_rows() > 0) {
            $this->response(
                $this->Templates_model->error('Nomor Telepon sudah ada'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        }

        //fungsi buat id
        $id = $this->db->select_max('id_user')->get('loginuser')->row()->id_user;
        if (substr($id, 2, 8) != date('Ymd')) {
            $noUrut = 0;
        } else {
            $noUrut = (int) substr($id, 10, 10);
            if ($noUrut == 9999999999) {
                $this->response(
                    $this->Templates_model->error('Room sudah full untuk hari ini, coba lagi besok'),
                    RestController::HTTP_BAD_REQUEST
                );
                return;
            } else {
                $noUrut++;
            }
        }
        $id = 'US' . date('Ymd') . sprintf("%010s", $noUrut);

        //fungsi membuat created_user
        $currentDateTime = $this->db->select('NOW() as current_datetime')->get()->row()->current_datetime;

        // Fungsi upload gambar
        $foto_user = '';
        if (!empty($_FILES['foto']['name'])) {
            $config['upload_path'] = '.andipublisher.com/images/user/profile/';
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['max_size'] = 2048; // ukuran maksimal dalam kilobyte

            // $this->load->library('upload', $config);

            if (!$this->upload->do_upload('foto')) {
                $this->response(
                    $this->Templates_model->error($this->upload->display_errors()),
                    RestController::HTTP_BAD_REQUEST
                );
                return;
            } else {
                $foto_user = $this->upload->data('file_name');
            }
        }
        // $foto_user = '';
        // if (!empty($_FILES['foto']['name'])) {
        //     $temp_upload_path = './temp_upload_directory/'; // Path lokal sementara

        //     if (!is_dir($temp_upload_path)) {
        //         mkdir($temp_upload_path, 0777, true); // Membuat direktori jika belum ada
        //     }

        //     $temp_file_name = time() . '_' . $_FILES['foto']['name'];
        //     $temp_file_path = $temp_upload_path . $temp_file_name;

        //     if (!move_uploaded_file($_FILES['foto']['tmp_name'], $temp_file_path)) {
        //         $this->response(
        //             $this->Templates_model->error('Gagal mengunggah file gambar'),
        //             RestController::HTTP_BAD_REQUEST
        //         );
        //         return;
        //     }

        //     // Memindahkan file dari path lokal sementara ke direktori tujuan
        //     $final_upload_path = './path/to/upload/directory/'; // Path direktori tujuan
        //     $final_file_name = time() . '_' . $_FILES['foto']['name'];
        //     $final_file_path = $final_upload_path . $final_file_name;

        //     if (!rename($temp_file_path, $final_file_path)) {
        //         $this->response(
        //             $this->Templates_model->error('Gagal memindahkan file gambar'),
        //             RestController::HTTP_BAD_REQUEST
        //         );
        //         return;
        //     }

        //     $foto_user = $final_file_name;
        // }

        $dataParam = [
            'id_user' => $id,
            'nama_user' => $nama,
            'email_user' => $email,
            'telepon_user' => $telepon,
            'foto_user' => $foto_user != '' ? $foto_user : 'default.jpg',
            'password_user' => password_hash($password, PASSWORD_DEFAULT),
            'created_user' => $currentDateTime,
        ];

        //qurey
        $data = $this->User_model->postRegister($dataParam);
        $result = $this->Templates_model->success($data);
        $this->response(
            $result,
            RestController::HTTP_CREATED
        );
    }

    //Ubah Data User
    public function index_put()
    {
        //Parameter
        $idUser = $this->put('idUser');
        $nama = htmlspecialchars($this->post('nama'));
        $email = htmlspecialchars($this->post('email'));
        $telepon = htmlspecialchars($this->post('telepon'));
        $foto = $this->post('foto');

        //Validation
        if ($idUser == '') {
            $this->response(
                $this->Templates_model->error('Parameter idUser Tidak Ada'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->response(
                $this->Templates_model->error('Email tidak valid'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        } else if ($this->db->get_where('loginuser', ['id_user' => $idUser])->row_array()['status_delete_user'] == 'Y') {
            $this->response(
                $this->Templates_model->error('Akun sudah di hapus silakan hubungi admin untuk konfirmasi lebih lanjut'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        } else if ($this->db->get_where('loginuser', ['email_user' =>  $email])->num_rows() > 0) {
            $this->response(
                $this->Templates_model->error('Email sudah ada'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        } else if ($this->db->get_where('loginuser', ['telepon_user' => $telepon])->num_rows() > 0) {
            $this->response(
                $this->Templates_model->error('Nomor Telepon sudah ada'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        }

        $dataParam = [
            'nama_user' => $nama,
            'email_user' => $email,
            'telepon_user' => $telepon,
            'foto_user' => $foto
        ];

        //qurey
        $data = $this->User_model->putUpdateUser($idUser, $dataParam);
        $result = $this->Templates_model->success($data);

        if ($data == null) {
            $this->response(
                $this->Templates_model->error('Tidak Ada User'),
                RestController::HTTP_BAD_REQUEST
            );
        } else {
            $this->response(
                $result,
                RestController::HTTP_OK
            );
        }
    }

    //Login
    public function auth_post()
    {
        //Parameter
        $email = $this->post('email');
        $password = $this->post('password');

        //Validation
        if ($email == '' || $password == '') {
            $this->response(
                $this->Templates_model->error('Parameter email atau password Tidak Ada'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->response(
                $this->Templates_model->error('Email tidak valid'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        } else if ($this->db->get_where('loginuser', ['email_user' => $email])->row_array(0)['status_delete_user'] == 'Y') {
            $this->response(
                $this->Templates_model->error('Akun sudah di hapus silakan hubungi admin untuk konfirmasi lebih lanjut'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        }

        //qurey
        $data = $this->User_model->postLogin($email, $password);
        $result = $this->Templates_model->success($data);

        if ($data == null) {
            $this->response(
                $this->Templates_model->error('Email atau Password Salah'),
                RestController::HTTP_BAD_REQUEST
            );
        } else {
            $this->response(
                $result,
                RestController::HTTP_OK
            );
        }
    }

    //Ubah Password
    public function auth_put()
    {
        //Parameter
        $idUser = $this->put('idUser');
        $password  = $this->put('password');
        $newPassword  = $this->put('newPassword');
        $confNewPassword  = $this->put('confNewPassword');

        //Validation
        if ($password == '' || $newPassword == '' || $confNewPassword == '') {
            $this->response(
                $this->Templates_model->error('Parameter password, newPassword atau confNewPassword Tidak Ada'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        } else if ($newPassword != $confNewPassword) {
            $this->response(
                $this->Templates_model->error('Parameter newPassword dan confNewPassword Tidak Cocok'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        } else if ($this->db->get_where('loginuser', ['id_user' => $idUser])->row_array()['status_delete_user'] == 'Y') {
            $this->response(
                $this->Templates_model->error('Akun sudah di hapus silakan hubungi admin untuk konfirmasi lebih lanjut'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        }

        $dataParam = ['password_user' => password_hash($newPassword, PASSWORD_DEFAULT)];

        //qurey
        $data = $this->User_model->putChangePassword($idUser, $password, $dataParam);
        $result = $this->Templates_model->success($data);


        if ($data == null) {
            $this->response(
                $this->Templates_model->error('Password Anda Salah'),
                RestController::HTTP_BAD_REQUEST
            );
        } else {
            $this->response(
                $result,
                RestController::HTTP_OK
            );
        }
    }

    //Lupas Password
    public function forgotPassword_post()
    {
        //Parameter
        $email = $this->post('email');

        //Validation
        if ($email == '') {
            $this->response(
                $this->Templates_model->error('Parameter email Tidak Ada'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->response(
                $this->Templates_model->error('Email tidak valid'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        } else if ($this->db->get_where('loginuser', ['email_user' => $email])->row_array(0)['status_delete_user'] == 'Y') {
            $this->response(
                $this->Templates_model->error('Akun sudah di hapus silakan hubungi admin untuk konfirmasi lebih lanjut'),
                RestController::HTTP_BAD_REQUEST
            );
            return;
        }

        $this->response(
            $this->Templates_model->success(null),
            RestController::HTTP_OK
        );
    }
}
