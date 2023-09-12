<?php

class Templates_model extends CI_Model
{
    public function success($result)
    {
        $data = [
            'status' => true,
            'message' => 'Success',
            'data' => $result
        ];

        return $data;
    }

    public function error($message)
    {
        $data = [
            'status' => false,
            'message' => $message,
            'data' => null
        ];

        return $data;
    }
}
