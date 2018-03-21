<?php
/**
 * Created by PhpStorm.
 * User: elama
 * Date: 19.03.18
 * Time: 14:52
 */

namespace App\Services;

use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Tests\Compiler\F;

class EntityManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function saveFile($path, $name, $size)
    {
        $file = new File();
        $file->setName($name);
        $file->setPath($path);
        $file->setUrl(hash('md5', $path . $name));
        $file->setSize($size);
        $this->entityManager->persist($file);
        $this->entityManager->flush();
        return $file;
    }

    public function getPublicFiles()
    {
        return $this->entityManager->getRepository(File::class)->findBy(['public' => true]);
    }

    public function getFileByUrl($url){
        return $this->entityManager->getRepository(File::class)->findOneBy(['url' => $url]);
    }

    public function deleteFileByUrl($url){
        $file = $this->entityManager->getRepository(File::class)->findOneBy(['url' => $url]);
        $this->entityManager->remove($file);
        $this->entityManager->flush();
        return $file;
    }
}