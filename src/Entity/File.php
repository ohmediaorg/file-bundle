<?php

namespace OHMedia\FileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use OHMedia\FileBundle\Repository\FileRepository;
use OHMedia\SecurityBundle\Entity\Entity;
use Symfony\Component\HttpFoundation\File\File as HttpFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass=FileRepository::class)
 * @ORM\Table(name="files")
 */
class File extends Entity
{
    const PATH_INITIAL = 'initial';
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $temporary;
  
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mime_type;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $width;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $height;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getTemporary(): ?bool
    {
        return $this->temporary;
    }

    public function setTemporary(?bool $temporary): self
    {
        $this->temporary = $temporary;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mime_type;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mime_type = $mimeType;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): self
    {
        $this->height = $height;

        return $this;
    }
    
    private $file;
    private $oldPath;
    
    public function setFile(HttpFile $file = null): self
    {
        $this->file = $file;
        
        if ($file instanceof UploadedFile) {
            $this->name = $file->getClientOriginalName();
        }
        
        // check if we have an old image path
        if (isset($this->path) && (self::PATH_INITIAL !== $this->path)) {
            // store the old name to delete after the update
            $this->oldPath = $this->path;
            $this->path = null;
        }
        else {
            // set it to something not null
            $this->path = self::PATH_INITIAL;
        }
        
        return $this;
    }
    
    public function getFile(): ?HttpFile
    {
        return $this->file;
    }
    
    public function clearFile()
    {
        $this->file = null;
        
        return $this;
    }
    
    public function setOldPath(?string $oldPath): self
    {
        $this->oldPath = $oldPath;
        
        return $this;
    }
    
    public function getOldPath(): ?string
    {
        return $this->oldPath;
    }
}
