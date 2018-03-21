<?php

namespace App\Controller;

use App\Services\EntityManager;
use App\Services\JsonService;

use Doctrine\DBAL\Driver\PDOException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class MainController extends Controller
{
    public function getFilesSize($files)
    {
        $size = 0;
        foreach ($files as $file)
        {
            $size += $file->getSize();
        }
        return $size;
    }
    /**
     * @Route("/", name="index")
     */
	public function index_page(EntityManager $em)
    {
        $files = $em->getFilesByPublic(true);
        $size = 0;
        if ($files)
            $size = $this->getFilesSize($files);
        return $this->render('files.html.twig', ['files' => $files, 'size' => $size]);
	}

    /**
     * @Route("/private", name="privateFiles")
     */
	public function privateFiles(EntityManager $em)
    {
        $files = $em->getFilesByPublic(false);
        $size = 0;
        if ($files)
            $size = $this->getFilesSize($files);
        return $this->render('files.html.twig', ['files' => $files, 'size' => $size]);
    }

    /**
     * @Route("/other", name="other")
     */
	public function other_page(){
	    return $this->render('base.html.twig');
    }
}