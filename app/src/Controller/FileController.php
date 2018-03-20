<?php

namespace App\Controller;


use App\Services\EntityManager;
use App\Services\FileSystemService;
use App\Services\JsonService;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
//form fieldTypes
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class FileController extends Controller
{
    /**
     * @Route("/upload", name="upload")
     */
    public function Upload(Request $request, JsonService $json, EntityManager $em)
    {
        $form = $this->createFormBuilder()
            ->add('file', FileType::class)
            ->add('save', SubmitType::class, array('label' => 'upload'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pathprefix = $this->container->getParameter('upload_path');
            $path = date('Y/m/d/');

            $tmpPath = $form->getData()['file']->getPathName();
            $originName = $form->getData()['file']->getClientOriginalName();
            $filename = $form->getData()['file']->getFileName();

            if($json->is_json(file_get_contents($tmpPath)))
            {
                if(!file_exists($pathprefix.$path)){
                    mkdir($pathprefix . $path , 0777, true);
                }
                move_uploaded_file($form->getData()['file']->getPathName(), $pathprefix . $path . $filename);
                $em->saveFile($path . $filename, $originName);

            }else{
                return $this->render('error.html.twig', [
                    'error_message' => 'Данный формат не поддерживается',
                    'back_url' => '/'
                ]);
            }
        }

        return $this->render('form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{url}", name="getFileContent")
     */
    public function updateFileContent($url,Request $request, EntityManager $em, JsonService $json)
    {
        $file = $em->getFileByUrl($url);
        $uploadPath = $this->container->getParameter('upload_path');
        $content = file_get_contents(trim($uploadPath.$file->getPath()));
        $form = $this->createFormBuilder()
            ->add('content', TextType::class, array('data' => $content))
            ->add('save', SubmitType::class, array('label' => 'upload'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if($json->is_json($form->getData()['content']))
            {
                $f = fopen(trim($uploadPath . $file->getPath()),'w');
                fwrite($f, $form->getData()['content']);
                fclose($f);
            }else{
                return $this->render('error.html.twig', [
                    'error_message' => 'Неверные данные',
                    'back_url' => '/'.$url
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