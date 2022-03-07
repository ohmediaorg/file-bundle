<?php

namespace OHMedia\FileBundle\Controller;

use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Service\FileManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File as HttpFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class FileController extends Controller
{
    public function uploadAction(Request $request, FileManager $manager)
    {
        if (!$this->getUser()) {
            exit();
        }
        
        if (!$httpfiles = $request->files->get('files')) {
            exit;
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $files = [];
        foreach ($httpfiles as $httpfile) {
            if (!$httpfile instanceof HttpFile) {
                continue;
            }
            
            $file = new File();
            $file->setFile($httpfile)
                ->setTemporary(true);
            
            $em->persist($file);
            
            $files[] = $file;
        }
        
        $em->flush();
        
        $json = ['files' => []];
        foreach ($files as $file) {
            $json['files'][] = [
                'id' => $file->getId(),
                'name' => $file->getName(),
                'path' => $manager->getWebPath($file)
            ];
        }
        
        return new JsonResponse($json);
    }
}
