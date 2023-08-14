<?php

namespace OHMedia\FileBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\FileBundle\Repository\FileRepository;
use OHMedia\SecurityBundle\Entity\Traits\BlameableTrait;
use Symfony\Component\HttpFoundation\File\File as HttpFile;

#[ORM\Entity(repositoryClass: FileRepository::class)]
#[ORM\Index(columns: ['token'])]
class File
{
    use BlameableTrait;

    public const PATH_INITIAL = 'initial';
    public const TOKEN_LENGTH = 30;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $token = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $ext = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $path = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $private = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $hidden = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mime_type = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $size = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $width = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $height = null;

    #[ORM\ManyToOne(inversedBy: 'files')]
    private ?FileFolder $folder = null;

    #[ORM\OneToOne(mappedBy: 'file', cascade: ['persist', 'remove'])]
    private ?Image $image = null;

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

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getExt(): ?string
    {
        return $this->ext;
    }

    public function setExt(?string $ext): self
    {
        $this->ext = $ext;

        return $this;
    }

    public function getFilename(): string
    {
        if ($this->ext) {
            return $this->name.'.'.$this->ext;
        }

        return $this->name;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): self
    {
        $this->private = $private;

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mime_type;
    }

    public function setMimeType(?string $mime_type): self
    {
        $this->mime_type = $mime_type;

        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(?string $size): self
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

    public function setFolder(?FileFolder $folder): self
    {
        $this->folder = $folder;

        return $this;
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): self
    {
        // unset the owning side of the relation if necessary
        if (null === $image && null !== $this->image) {
            $this->image->setFile(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $image && $image->getFile() !== $this) {
            $image->setFile($this);
        }

        $this->image = $image;

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

            // set everything else to null
            $this->setNull();
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

    /**
     * Meant for when the File object is to be blanked out
     * because it cannot be deleted due to DB relations.
     */
    public function setNull(): self
    {
        $this->token = null;
        $this->name = null;
        $this->ext = null;
        $this->path = null;
        $this->mime_type = null;
        $this->size = null;
        $this->width = null;
        $this->height = null;

        return $this;
    }
}
