<?php
/**
 * Created by PhpStorm.
 * User: leha
 * Date: 22.03.18
 * Time: 23:16
 */

namespace App\Services;

use Symfony\Component\HttpFoundation\Response;

class ResponseService
{
    private $status;

    public function __construct()
    {
        $this->status = [
            0 => [
                'status' => 0,
                'data'   => 'OK'
            ],
            1 => [
                'status' => 1,
                'data'   => 'Неверный запрос'
            ],
            11 => [
                'status' => 11,
                'data'   => 'Тело запроса пустое'
            ],
            2 => [
                'status' => 2,
                'data'   => 'Ошибка обработки данных'
            ],
            21 => [
                'status' => 21,
                'data'   => 'Ошибка при конвертации файла'
            ],
            22 => [
                'status' => 22,
                'data'   => 'Данные не прошли валидацию'
            ],
            3 => [
                'status' => 3,
                'data'   => 'Ошибка в работе с файлами'
            ],
            31 => [
                'status' => 31,
                'data'   => 'Файл не найден'
            ],
            32 => [
                'status' => 32,
                'data'   => 'Файл не существеут'
            ],
            33 => [
                'status' => 33,
                'data'   => 'Ошибка при записи файла'
            ],
            4 => [
                'status' => 4,
                'data'   => 'Нет прав на выполнение операции'
            ]
        ];
    }

    public function CreateHardJSONResponse($content)
    {
        $data = [];
        foreach($content as $element)
        {
            $data[] = $this->status[$element['status']] + $element['content'];
        }
        return new Response(json_encode($data));
    }

    public function CreateJSONResponse($status, $content = ''){
        if($status)
        {
            $response = new Response(json_encode($this->status[$status]));
            $response->setStatusCode(500);
            return $response;
        }else{
            $response = new Response();
            $response->setStatusCode(200);
            $data = $this->status[$status] + [ 'content' => $content];
            return $response->setContent(json_encode($data));
        }
    }
}