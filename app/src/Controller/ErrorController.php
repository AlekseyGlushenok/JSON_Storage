<?php
/**
 * Created by PhpStorm.
 * User: elama
 * Date: 19.03.18
 * Time: 15:07
 */

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ErrorController extends Controller
{
    public function show_error_page($msg, $backurl)
    {
        return $this->render('error.html.twig', ['error_message' => $msg, 'back_url' => $backurl]);
    }
}