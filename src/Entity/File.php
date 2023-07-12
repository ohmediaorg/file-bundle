<?php

namespace OHMedia\FileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use OHMedia\FileBundle\Repository\FileRepository;
use OHMedia\SecurityBundle\Entity\Traits\Blameable;
use Symfony\Component\HttpFoundation\File\File as HttpFile;

#[ORM\Entity(repositoryClass: FileRepository::class)]
class File
{
    use Blameable;

    public const PATH_INITIAL = 'initial';

    #[ORM\Id()]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 20)]
    private $token;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 20)]
    private $ext;

    #[ORM\Column(type: 'string', length: 255)]
    private $path;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $temporary;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $private;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $hidden;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $mime_type;

    #[ORM\Column(type: 'bigint', nullable: true)]
    private $size;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private $width;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private $height;

    #[ORM\ManyToOne(targetEntity: FileFolder::class, inversedBy: 'files')]
    private $folder;

    private $cloned = false;

    public function __clone()
    {
        $this->id = null;
        $this->cloned = true;
    }

    public function isCloned(): bool
    {
        return $this->cloned;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getExt(): ?string
    {
        return $this->ext;
    }

    public function setExt(string $ext): self
    {
        $this->ext = $ext;

        return $this;
    }

    public function getFilename(): string
    {
        if ($this->ext) {
            return $this->name . '.' . $this->ext;
        }

        return $this->name;
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

    public function getPrivate(): ?bool
    {
        return $this->private;
    }

    public function setPrivate(?bool $private): self
    {
        $this->private = $private;

        return $this;
    }

    public function getHidden(): ?bool
    {
        return $this->hidden;
    }

    public function setHidden(?bool $hidden): self
    {
        $this->hidden = $hidden;

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

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;

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

    public function getFolder(): ?FileFolder
    {
        return $this->folder;
    }

    public function setFolder(?self $folder): self
    {
        $this->folder = $folder;

        return $this;
    }

    private $file;
    private $oldPath;

    public function setFile(HttpFile $file = null): self
    {
        $this->file = $file;

        // check if we have an old image path
        if (isset($this->path) && (self::PATH_INITIAL !== $this->path)) {
            // store the old name to delete after the update
            $this->oldPath = $this->path;
            $this->path = null;
        } else {
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
