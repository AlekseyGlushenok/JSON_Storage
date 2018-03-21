<?php

namespace App\Controller;


use App\Services\EntityManager;
use App\Services\JsonService;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class FileController extends Controller
{
    /**
     * @Route("/upload", name="upload")
     */
    public function Upload(Request $request, JsonService $json, EntityManager $em)
    {
        $form = $this->createFormBuilder()
            ->add('content', TextareaType::class)
            ->add('file', FileType::class, array('required' => false))
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadPath = $this->container->getParameter('upload_path');
            if ($form->getData()['file'])
            {
                $uploadFile = $form->getData()['file'];


                $tmpPath    = $uploadFile->getPathName();
                $originName = $uploadFile->getClientOriginalName();
                $size       = $uploadFile->getSize();
                if($json->is_json(file_get_contents($tmpPath)))
                {
                    $unsplitPath = md5_file($tmpPath);
                    $path = '/' . substr($unsplitPath, 0, 4) .
                            '/' . substr($unsplitPath, 4, 4) .
                            '/' . substr($unsplitPath, 8);
                    $file = $em->saveFile($path, $originName, $size);
                    $file->moveUploadFileTo($tmpPath, $uploadPath);
                    return $this->redirectToRoute('index');
                }
                return $this->render('error.html.twig', [
                    'error_message' => 'Данный формат не поддерживается',
                    'back_url' => '/upload'
                ]);
            }
            if ($form->getData()['content']){
                $content = $form->getData()['content'];
                if($json->is_json($content))
                {
                    $unsplitPath = md5($content);
                    $path = '/' . substr($unsplitPath, 0, 4) .
                            '/' . substr($unsplitPath, 4, 4) .
                            '/' . substr($unsplitPath, 8);
                    $file = $em->saveFile($path, 'unnamed', strlen($content));
                    $file->saveFile($uploadPath, $content);
                    return $this->redirectToRoute('index');
                }
                return $this->render('error.html.twig', [
                    'error_message' => 'Это не JSON',
                    'back_url' => '/upload'
                ]);
            }


//
//
            return $this->render('error.html.twig', [
                'error_message' => 'Ничего не пришло',
                'back_url' => '/upload'
            ]);
        }
        return $this->render('form.html.twig', array(
            'form' => $form->createView(),
        ));

    }

    /**
     * @Route("/public/{url}", name="getFileContent")
     */
    public function updateFileContent($url,Request $request, EntityManager $em, JsonService $json)
    {
        $file = $em->getFileByUrl($url);
        $uploadPath = $this->container->getParameter('upload_path');
        $content = file_get_contents(trim($uploadPath.$file->getPath()));
        $form = $this->createFormBuilder()
            ->add('content', TextareaType::class, array('data' => $content))
            ->add('save', SubmitType::class, array('label' => 'upload'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $content = $form->getData()['content'];
            if($json->is_json($content))
            {
                $file->saveFile($uploadPath, $content);
            }else{
                return $this->render('error.html.twig', [
                    'error_message' => 'Неверные данные',
                    'back_url' => '/public'.$url
                ]);
            }
        }

        return $this->render('form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route ("/download/{url}", name="downloadFile")
     */
    public function download($url, EntityManager $em)
    {
        $response = new Response;
        $file = $em->getFileByUrl($url);
        $uploadPath = $this->container->getParameter('upload_path');
        $content = file_get_contents(trim($uploadPath.$file->getPath()));
        $response->headers->set('Content-Disposition', 'attachment; filename='.$file->getName());
        $response->setContent($content);
        return $response;
    }

    /**
     * @Route("/delete/{url}", name="deleteFile")
     */
    public function delete($url, EntityManager $em)
    {
        $file = $em->deleteFileByUrl($url);
        $uploadPath = $this->container->getParameter('upload_path');
        unlink(trim($uploadPath.$file->getPath()));
        return $this->redirectToRoute('index');
    }
}