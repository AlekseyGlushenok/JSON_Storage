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
use Symfony\Component\DependencyInjection\Tests\Compiler\F;

class EntityManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
        {
            return true;
        }
        return false;
    }

    public function saveFile($uploadPath, $content, $name, $size, $public = true)
    {
        $path = $this->generatePath($content);
        $file = new File();
        $file->setName($name);
        $file->setPath($path);
        $file->setUrl(hash('md4', $path . random_bytes(10)));
        $file->setSize($size);
        $file->setPublic($public);
        $file->saveFile($uploadPath, $content);

        $this->entityManager->persist($file);
        $this->entityManager->flush();
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
        $path = $this->generatePath($content);
        $oldPath = $file->getPath();
        $file->setPath($path);
        $file->setSize(strlen($content));
        $this->entityManager->flush();
        if ($this->canDelete($oldPath))
        {
            $file->deleteFile($uploadPath . $oldPath);
        }
        $file->saveFile($uploadPath, $content);
    }

    public function deleteFileByUrl($url, $uploadPath){
        $file = $this->entityManager->getRepository(File::class)->findOneBy(['url' => $url]);
        $this->entityManager->remove($file);
        $this->entityManager->flush();
        if ($this->canDelete($file->getPath()))
        {
            $file->deleteFile($uploadPath . $file->getPath());
        }
    }
}