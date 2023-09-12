<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

require APPPATH . 'libraries/RestController.php';

class Test extends RestController
{

    public function index_get()
    {
        $this->response(
            [
                'status' => true,
                'message' => 'Succecs',
                'data' => 'Test Berhasil'
            ],
            RestController::HTTP_OK
        );
    }
}
