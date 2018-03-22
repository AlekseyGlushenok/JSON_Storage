<?php

namespace App\Services;

use SimpleXMLElement;
use Symfony\Component\HttpFoundation\Response;

class JsonService
{
    private function arrayOrObjectToXml($array, $xml)
    {
        foreach ($array as $key => $value)
        {
            if (is_array($value) || is_object($value))
            {
                $subxml = $xml->addChild("item$key");
                $this->arrayOrObjectToXml($value, $subxml);
            }else{
                $xml->addChild("item$key", $value);
            }
        }
    }

    public function toXml($content)
    {
        $root = "<?xml version='1.0' standalone='yes'?><body></body>";
        $xml = new SimpleXMLElement($root);
        $data = json_decode($content);
        if (is_array($data)||is_object($data))
            $this->arrayOrObjectToXml($data, $xml);
        return $xml;
    }

    public function is_json($content)
    {
        json_decode($content);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}