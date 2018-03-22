<?php

namespace App\Controller;


use App\Services\FileManager;
use App\Services\JsonService;

use App\Services\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

class FileController extends Controller
{

    private function checkAccess($accessMod, $fileAccess)
    {
        if (!$fileAccess && $fileAccess == ('public' == $accessMod))
            return false;
        return true;
    }
    /**
     * @Route("/{accessMod}/files", name="sendFiles")
     * @Method({"GET"})
     */
    public function sendFilesList($accessMod, FileManager $fileManager, ResponseService $response)
    {
        $files   = $fileManager->getFilesByPublic(('public' == $accessMod ));
        $content = [];

        foreach($files as $file)
        {
            $content[] = [
                'name' => trim($file->getName()),
                'size' => $file->getSize(),
                'url'  => trim($file->getUrl())
            ];
        }
        return $response->CreateJSONResponse(0, $content);
    }

    /**
     * @Route("/{accessMod}/file/{url}", name="sendFile")
     * @Method({"GET"})
     */
    public function sendFile($accessMod, $url, FileManager $fileManager, ResponseService $response)
    {
        $file       = $fileManager->getFileByUrl($url);
        $uploadPath = $this->container->getParameter('upload_path');
        $content    = [];

        if (!$file)
            return $response->CreateJSONResponse(31);

        if ($this->checkAccess($accessMod, $file->isPublic()))
            return $response->CreateJSONResponse(4);

        try {
            $content['name'] = trim($file->getName());
            $content['fileContent'] = $fileManager->getFileContent($file, $uploadPath);
            return $response->CreateJSONResponse(0, $content);
        }
        catch (\Exception $exception){
            $fileManager->forceDelete($file);
            return $response->CreateJSONResponse(32);
        }
    }

    /**
     * @Route("/{accessMod}/upload", name="upload")
     * @Method({"POST"})
     */
    public function upload($accessMod,
                           Request $request,
                           JsonService $json,
                           FileManager $fileManager,
                           ResponseService $response)
    {
        $uploadPath       = $this->container->getParameter('upload_path');
        $response_content = [];
        $access           = 'public' == $accessMod;

        if (!empty($request->files))
        {
            foreach($request->files as $file)
            {
                $content = file_get_contents($file->getPathName());
                $name = $file->getClientOriginalName();
                if ($json->is_json($content)){
                    if($fileManager->saveFile($uploadPath, $content, $name, $file->getSize(), $access))
                    {
                        $response_content[] = ['status' => 0, 'content' => $name];
                    }else{
                        $response_content[] = ['status' => 33, 'content' => $name];
                    }
                }else{
                    $response_content[] = ['status' => 22, 'content' => $name];
                }
            }

        }

        if($request->request->get('content'))
        {
            $name = $request->request->get('name');
            if (!$name)
                $name = 'unnamed';
            $content = $request->request->get('content');
            if ($json->is_json($content))
            {
                $size = strlen($content);
                if($fileManager->saveFile($uploadPath, $content, $name, $size, $access))
                {
                    $response_content[] = ['status' => 0, 'content' => $name];
                }else{
                    $response_content[] = ['status' => 33, 'content' => $name];
                }
            }else{
                $response_content[] = ['status' => 22, 'content' => $name];
            }

        }

        if (empty($response_content))
            return $response->CreateJSONResponse(11);

        return $response->CreateHardJSONResponse($response_content);
    }

    /**
     * @Route("/{accessMod}/update/{url}", name="update")
     * @Method({"POST"})
     */
    public function update($accessMod,
                           $url,
                           Request $request,
                           JsonService $json,
                           FileManager $fileManager,
                           ResponseService $response)
    {
        $uploadPath = $this->container->getParameter('upload_path');
        $content    = $request->request->get('content');
        $file       = $fileManager->getFileByUrl($url);

        if ($this->checkAccess($accessMod, $file->isPublic()))
            return $response->CreateJSONResponse(4);

        if (!$file)
            return $response->CreateJSONResponse(31);

        if (!$content)
            return $response->CreateJSONResponse(11);

        if(!$json->is_json($content))
            return $response->CreateJSONResponse(22);

        if(!$fileManager->updateFile($content, $file, $uploadPath))
            return $response->CreateJSONResponse(33);

        return $response->CreateJSONResponse(1, 'Файл обновлен');

    }

    /**
     * @Route("/{accessMod}/delete/{url}", name="delete")
     * @Method({"GET"})
     */
    public function delete($accessMod, $url,  FileManager $fileManager, ResponseService $response)
    {
        $uploadPath = $this->container->getParameter('upload_path');
        $file       = $fileManager->getFileByUrl($url);

        if(!$file)
            return $response->CreateJSONResponse(31);

        if ($this->checkAccess($accessMod, $file->isPublic()))
            return $response->CreateJSONResponse(4);

        if (!$fileManager->deleteFileByUrl($url, $uploadPath))
            return $response->CreateJSONResponse(3);

        return $response->CreateJSONResponse(0, 'Файл был удален');
    }

    /**
     * @Route("/{accessMod}/download/{url}/{fmt}", name="download")
     * @Method({"GET"})
     */
    public function download($accessMod,
                             $url,
                             $fmt = 'json',
                             JsonService $json,
                             FileManager $fileManager,
                             ResponseService $response)
    {
        $file       = $fileManager->getFileByUrl($url);
        $uploadPath = $this->container->getParameter('upload_path');

        if (!$file)
            return $response->CreateJSONResponse(31);

        if ($this->checkAccess($accessMod, $file->isPublic()))
            return $response->CreateJSONResponse(4);

        try {
            $content = $fileManager->getFileContent($file, $uploadPath);

            if (!$file->isPublic())
                $fileManager->deleteFileByUrl($url, $uploadPath);
        }
        catch(\Exception $e)
        {
            $fileManager->forceDelete($file);
            return $response->CreateJSONResponse(32);
        }

        if ('xml' == $fmt)
            $content = $json->toXml($content)->asXML();


        $response = new Response;
        $response->headers->set('Content-Disposition', 'attachment; filename='.$file->getName());
        $response->setContent($content);
        return $response;
    }
}