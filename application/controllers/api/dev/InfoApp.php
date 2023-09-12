<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

require APPPATH . 'libraries/RestController.php';

class InfoApp extends RestController
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('InfoApp_model');
        $this->load->model('Templates_model');
    }

    //Get Infi App
    public function index_get()
    {
        $data = $this->InfoApp_model->getInfoApp();
        $result = $this->Templates_model->success($data);

        if ($data == null) {
            $this->response(
                $this->Templates_model->error('Data Info tidak ada'),
                RestController::HTTP_NOT_FOUND
            );
        } else {
            $this->response(
                $result,
                RestController::HTTP_OK
            );
        }
    }
}
