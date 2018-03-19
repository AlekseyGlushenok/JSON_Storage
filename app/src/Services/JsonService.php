<?php

namespace App\Services;



class JsonService
{
    public function toXml()
    {
        $XML = '';
        return $XML;
    }

    public function fromXml()
    {
        $result = '';
        return $result;
    }

    public function is_json($content)
    {
        $data = json_decode($content);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}