<?php

namespace App\Controller;

use App\Services\EntityManager;
use App\Services\JsonService;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class MainController extends Controller
{
    /**
     * @Route("/", name="index")
     */
	public function index_page(EntityManager $em)
    {
        $files = $em->getPublicFiles();
        return $this->render('files.html.twig', ['files' => $files]);
	}

    /**
     * @Route("/other", name="other")
     */
	public function other_page(){
	    return $this->render('base.html.twig');
    }
}