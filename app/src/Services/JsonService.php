<?php

namespace App\Services;

use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;



class JsonService
{
    public function toXml($content)
    {
        return '';
    }

    public function is_json($content)
    {
        json_decode($content);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}