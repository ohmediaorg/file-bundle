<?php

namespace JstnThms\FileBundle\Service;

use JstnThms\FileBundle\Entity\File as FileEntity;
use JstnThms\FileBundle\Entity\ImageResize;
use JstnThms\FileBundle\Util\ImageUtil;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File as HttpFile;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

class FileManager
{
    private $absolutePublicDir;
    private $absoluteUploadDir;
    private $filesystem;
    private $uploadDir;
    
    public function __construct(string $projectDir, string $uploadDir)
    {
        $this->absolutePublicDir = $projectDir . '/public';
        $this->absoluteUploadDir = $this->absolutePublicDir . $uploadDir;
        $this->filesystem = new FileSystem();
        $this->uploadDir = $uploadDir;
    }
    
    public function copy(FileEntity $file): FileEntity
    {
        $path = null;
        
        if (null !== $file->getFile()) {
            $path = $file->getFile()->getPathname();
        }
        else {
            $path = $this->getAbsolutePath($file);
        }
        
        if (null !== $path) {
            $contents = file_get_contents($path);
            
            $copy = $this->createFromContents($contents);
            $copy->setName($file->getName());
            
            return $copy;
        }
        
        return null;
    }
    
    public function createFromContents(string $contents): FileEntity
    {
        $path = @tempnam($this->absoluteUploadDir, 'tmp');
        
        $handle = fopen($path, 'w');
        fwrite($handle, $contents);
        fclose($handle);
        
        $httpfile = new HttpFile($path);
        
        $file = new FileEntity();
        $file->setFile($httpfile);
        
        return $file;
    }
    
    public function getMimeType(FileEntity $file)
    {
        if (null !== $file->getFile()) {
            return $file->getFile()->getMimeType();
        }
        
        $path = $this->getAbsolutePath($file);
        
        if (null === $path) {
            return null;
        }
        
        $guesser = MimeTypeGuesser::getInstance();

        return $guesser->guess($path);
    }
    
    public function getAbsolutePath(FileEntity $file): ?string
    {
        return null !== $file->getPath()
            ? $this->absoluteUploadDir . '/' . $file->getPath()
            : null;
    }
    
    public function getWebPath(FileEntity $file): ?string
    {
        return null !== $file->getPath()
            ? $this->uploadDir . '/' . $file->getPath()
            : null;
    }
    
    public function preSaveFile(FileEntity $file)
    {
        if (null === $file->getFile()) {
            return;
        }
        
        $width = $height = null;
        
        $imageSize = @getimagesize($file->getFile()->getRealPath());
        
        if ($imageSize) {
            $width = $imageSize[0];
            $height = $imageSize[1];
        }
        
        $file
            ->setWidth($width)
            ->setHeight($height);
        
        $filename = $this->generateUniqueFilename();
        $path = $filename . '.' . $file->getFile()->guessExtension();
        
        $file->setPath($path);
        
        $mimeType = $this->getMimeType($file);
        
        $file->setMimeType($mimeType);
    }
    
    public function postSaveFile(FileEntity $file)
    {
        if (null === $file->getPath() || null === $file->getFile()) {
            return;
        }
        
        $path = explode('/', $file->getPath());
        $name = array_pop($path);
        $path = implode('/', $path);
        
        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $file->getFile()->move($this->absoluteUploadDir . '/' . $path, $name);
        
        $this->doImageProcessing($file);
        
        // check if we have an old file
        if ($file->getOldPath()) {
            // delete the old file
            $this->filesystem->remove($this->absoluteUploadDir . '/' . $file->getOldPath());
            // clear the temp file path
            $file->setOldPath(null);
        }
        
        $file->clearFile();
    }
    
    public function postRemoveFile(FileEntity $file)
    {
        $filepath = $this->getAbsolutePath($file);
        
        $this->filesystem->remove($filepath);
    }
    
    public function postSaveImageResize(ImageResize $resize)
    {
        $sourceFile = $resize->getImage()->getFile();
        
        $sourceFilepath = $this->getAbsolutePath($sourceFile);
        
        $ext = ImageUtil::getExtension($sourceFilepath);
      
        $im = ImageUtil::getImageResource($sourceFilepath, $ext);
        
        if (!$im) {
            return;
        }
        
        $imW = imagesx($im);
        $imH = imagesy($im);
        
        $width = $resize->getWidth();
        $height = $resize->getHeight();
        
        $resizedIm = ImageUtil::resizeAndCropImage($im, $imW, $imH, $width, $height);
        
        $file = $resize->getFile();
      
        $filepath = $this->getAbsolutePath($file);
        
        ImageUtil::saveImage($resizedIm, $filepath, $ext);
    }
    
    private function generateUniqueFilename()
    {
        $path = [];
        for ($i = 0; $i < 4; $i++) {
            $path[] = $this->generateRandomString(2);
        }
        
        $path = implode('/', $path);
        
        $fullPath = $this->absoluteUploadDir . '/' . $path;
            
        $this->filesystem->mkdir($fullPath);
        
        $file = null;
        
        do {
            $file = $this->generateRandomString(3);
        } while(glob("$fullPath/$file.*"));
        
        return $path . '/' . $file;
    }
    
    private function generateRandomString($len)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $cLen = strlen($chars) - 1;
        
        srand();
        
        $str = '';
        for ($i = 0; $i < $len; $i++) {
            $str .= $chars[rand(0, $cLen)];
        }
        
        return $str;
    }
    
    private function doImageProcessing(FileEntity $file)
    {
        $filepath = $this->getAbsolutePath($file);
      
        $ext = ImageUtil::getExtension($filepath);
        
        $im = ImageUtil::getImageResource($filepath, $ext);
      
        if (null === $im) {
            return;
        }
      
        $im = ImageUtil::doFlipRotate($im, $filepath);
        
        ImageUtil::saveImage($im, $filepath, $ext);
    }
}
