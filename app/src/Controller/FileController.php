<?php

namespace App\Controller;


use App\Services\EntityManager;
use App\Services\JsonService;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
            ->add('private', CheckboxType::class, array('required' => false))
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadPath = $this->container->getParameter('upload_path');
            $private = !$form->getData()['private'];
            if ($form->getData()['file'])
            {
                $uploadFile = $form->getData()['file'];
                $tmpPath    = $uploadFile->getPathName();
                $originName = $uploadFile->getClientOriginalName();
                $size       = $uploadFile->getSize();
                $content    = file_get_contents($tmpPath);

                if($json->is_json($content))
                {
                    $em->saveFile($uploadPath, $content, $originName, $size, $private);
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
                    $em->saveFile($uploadPath, $content,'unnamed', strlen($content), $private);
                    return $this->redirectToRoute('index');
                }
                return $this->render('error.html.twig', [
                    'error_message' => 'Данные не прошли валидацию',
                    'back_url' => '/upload'
                ]);
            }
            return $this->render('error.html.twig', [
                'error_message' => 'Выберете файл или отправте данные',
                'back_url' => '/upload'
            ]);
        }
        return $this->render('uploadform.html.twig', array(
            'form' => $form->createView(),
        ));

    }

    /**
     * @Route("/private/{url}", name="getPrivateFile")
     */
    public function privateFile($url, Request $request, EntityManager $em){
        $file = $em->getFileByUrl($url);
        $uploadPath = $this->container->getParameter('upload_path');
        if ($file)
        {
            $content = file_get_contents(trim($uploadPath . $file->getPath()));
            $em->deleteFileByUrl($url, $uploadPath);
            return $this->render('private.html.twig', ['content' => $content]);
        }
        return $this->render('error.html.twig', [
            'error_message' => "Файл не существует",
            'back_url' => '/'
        ]);
    }
    /**
     * @Route("/public/{url}", name="updateFile")
     */
    public function updateFileContent($url,Request $request, EntityManager $em, JsonService $json)
    {
        $file = $em->getFileByUrl($url);
        $error = [];
        if (true != $file->isPublic())
        {
            return $this->render('error.html.twig', [
                'error_message' => 'У вас нет доступа',
                'back_url' => '/'
            ]);
        }
        $uploadPath = $this->container->getParameter('upload_path');
        $content = file_get_contents(trim($uploadPath.$file->getPath()));
        $form = $this->createFormBuilder()
            ->add('content', TextareaType::class, array('data' => $content))
            ->add('update', SubmitType::class, array('label' => 'Обновить'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $content = $form->getData()['content'];
            if($json->is_json($content))
            {
                $em->updateFile($content, $file, $uploadPath);
                return $this->redirectToRoute('index');
            }else{
                return $this->render('error.html.twig', [
                    'error_message' => 'Данные не прошли валидацию',
                    'back_url' => '/public/'.$url
                ]);
            }
        }

        if(empty($error)){
            return $this->render('updateform.html.twig', array(
                'form' => $form->createView(),
            ));
        }
        return $this->render('error.html.twig', ['error' => $error]);
    }

    /**
     * @Route ("/download/{fmt}/{url}", name="downloadFile")
     */
    public function download($fmt, $url, EntityManager $em, JsonService $json)
    {
        $response = new Response;
        $file = $em->getFileByUrl($url);
        $uploadPath = $this->container->getParameter('upload_path');
        $content = file_get_contents(trim($uploadPath . $file->getPath()));
        if ('xml' == $fmt)
        {
            $content = $json->toXml($content);
        }
        $response->headers->set('Content-Disposition', 'attachment; filename='.$file->getName());
        $response->setContent($content);
        return $response;
    }

    /**
     * @Route("/delete/{url}", name="deleteFile")
     */
    public function delete($url, EntityManager $em)
    {
        $uploadPath = $this->container->getParameter('upload_path');
        $em->deleteFileByUrl($url, $uploadPath);
        return $this->redirectToRoute('index');
    }
}