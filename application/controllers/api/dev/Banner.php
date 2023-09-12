<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

require APPPATH . 'libraries/RestController.php';

class Banner extends RestController
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Banner_model');
        $this->load->model('Templates_model');
    }

    //Get Banner
    public function index_get()
    {

        $data = $this->Banner_model->getBunner();
        $result = $this->Templates_model->success($data);

        if ($data == null) {
            $this->response(
                $this->Templates_model->success([]),
                RestController::HTTP_NO_CONTENT
            );
        } else {
            $this->response(
                $result,
                RestController::HTTP_OK
            );
        }
    }
}
