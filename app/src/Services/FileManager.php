<?php
/**
 * Created by PhpStorm.
 * User: elama
 * Date: 19.03.18
 * Time: 14:52
 */

namespace App\Services;

use App\Entity\File;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\ORM\EntityManagerInterface;

class FileManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    private function saveFileInDir($path, $content)
    {
        if(!file_exists(pathinfo($path,PATHINFO_DIRNAME))){
            mkdir(pathinfo($path, PATHINFO_DIRNAME) , 0777, true);
        }
        $fstr = fopen(trim($path), 'w');
        fwrite($fstr, $content);
        fclose($fstr);
    }

    private function deleteDirIfEmpty($path)
    {
        $pathToDir = pathinfo($path, PATHINFO_DIRNAME);
        if (empty(glob($pathToDir . '/*')))
        {
            rmdir($pathToDir);
            $this->deleteDirIfEmpty($pathToDir);
        }
    }

    private function deleteFileFromDir($path)
    {
        $path = trim($path);
        if(file_exists($path))
        {
            unlink($path);
            $this->deleteDirIfEmpty($path);
        }
    }

    private function generatePath($content)
    {
        $tmpPath = md5($content);
        $path = $path = '/' . substr($tmpPath, 0, 4) .
            '/' . substr($tmpPath, 4, 4) .
            '/' . substr($tmpPath, 8);
        return $path;
    }

    private function canDelete($path)
    {
        $files = $this->entityManager->getRepository(File::class)->findBy(['path' => $path]);
        if (!$files)
            return true;
        return false;
    }

    public function forceDelete($file)
    {
        $this->entityManager->remove($file);
        $this->entityManager->flush();
    }

    public function saveFile($uploadPath, $content, $name, $size, $public = true)
    {
        try {
            $path = $this->generatePath($content);
            $file = new File();
            $file->setName($name);
            $file->setPath($path);
            $file->setUrl(hash('md4', $path . random_bytes(10)));
            $file->setSize($size);
            $file->setPublic($public);
            $this->saveFileInDir($uploadPath . $file->getPath(), $content);
            $this->entityManager->persist($file);
            $this->entityManager->flush();
            return true;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

    public function getFilesByPublic($public)
    {
        return $this->entityManager->getRepository(File::class)->findBy(['public' => $public]);
    }

    public function getFileByUrl($url)
    {
        return $this->entityManager->getRepository(File::class)->findOneBy(['url' => $url]);
    }

    public function updateFile($content, $file, $uploadPath)
    {
        try
        {
            $path = $this->generatePath($content);
            $oldPath = $file->getPath();
            $file->setPath($path);
            $file->setSize(strlen($content));
            $this->entityManager->flush();

            if ($this->canDelete($oldPath))
                $this->deleteFileFromDir($uploadPath . $oldPath);

            $this->saveFileInDir($uploadPath . $file->getPath(), $content);
            return true;
        }
        catch(\Exception $e)
        {
            return false;
        }
    }

    public function deleteFileByUrl($url, $uploadPath){
        try
        {
            $file = $this->entityManager->getRepository(File::class)->findOneBy(['url' => $url]);
            $this->entityManager->remove($file);
            $this->entityManager->flush();
            if ($this->canDelete($file->getPath()))
            {
                $this->deleteFileFromDir($uploadPath . $file->getPath());
            }
            return true;
        }
        catch(\Exception $e)
        {
            return false;
        }
    }

    public function getFileContent($file, $uploadPath)
    {
        return file_get_contents(trim( $uploadPath .$file->getPath()));
    }
}