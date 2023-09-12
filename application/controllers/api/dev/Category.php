<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

require APPPATH . 'libraries/RestController.php';

class Category extends RestController
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Category_model');
        $this->load->model('Templates_model');
    }

    //Get Category Main
    public function index_get()
    {

        $data = $this->Category_model->getCategoryMain();
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

    //Get Category Sub
    public function sub_get()
    {

        $data = $this->Category_model->getCategorySub();
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

    //Get Category Select
    public function select_get()
    {

        $data = $this->Category_model->getCategorySelect();
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
