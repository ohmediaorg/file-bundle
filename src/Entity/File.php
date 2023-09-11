<?php

namespace OHMedia\FileBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(length: self::TOKEN_LENGTH, nullable: true)]
    private ?string $token = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $ext = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $path = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $browser = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $locked = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mime_type = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['unsigned' => true])]
    private ?int $size = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true, options: ['unsigned' => true])]
    private ?int $width = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true, options: ['unsigned' => true])]
    private ?int $height = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $alt = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $image = false;

    #[ORM\ManyToOne(inversedBy: 'files')]
    private ?FileFolder $folder = null;

    #[ORM\ManyToOne(inversedBy: 'resizes', targetEntity: self::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?self $resize_parent = null;

    #[ORM\OneToMany(mappedBy: 'resize_parent', targetEntity: self::class, orphanRemoval: true)]
    private Collection $resizes;

    private $cloned = false;

    public function __construct()
    {
        $this->resizes = new ArrayCollection();
    }

    public function __clone()
    {
        $this->id = null;
        $this->cloned = true;
        $this->browser = false;
        $this->locked = false;
        $this->folder = null;

        $this->resizes = new ArrayCollection();
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

    public function isBrowser(): bool
    {
        if ($this->browser) {
            return true;
        }

        if ($this->folder) {
            return $this->folder->isBrowser();
        }

        return false;
    }

    public function setBrowser(bool $browser): self
    {
        $this->browser = $browser;

        return $this;
    }

    public function isLocked(): bool
    {
        if ($this->locked) {
            return true;
        }

        if ($this->folder) {
            return $this->folder->isLocked();
        }

        return false;
    }

    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;

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

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(?string $alt): self
    {
        $this->alt = $alt;

        return $this;
    }

    public function isImage(): bool
    {
        return $this->image;
    }

    public function setImage(bool $image): self
    {
        $this->image = $image;

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

    public function getResizeParent(): ?self
    {
        return $this->resize_parent;
    }

    public function setResizeParent(?self $resize_parent): self
    {
        $this->resize_parent = $resize_parent;

        return $this;
    }

    /**
     * @return Collection<int, File>
     */
    public function getResizes(): Collection
    {
        return $this->resizes;
    }

    public function addResize(File $resize): self
    {
        if (!$this->resizes->contains($resize)) {
            $this->resizes->add($resize);
            $resize->setImage($this);
        }

        return $this;
    }

    public function removeResize(File $resize): self
    {
        if ($this->resizes->removeElement($resize)) {
            // set the owning side to null (unless already changed)
            if ($resize->getImage() === $this) {
                $resize->setImage(null);
            }
        }

        return $this;
    }

    public function getResize(int $width, int $height)
    {
        foreach ($this->resizes as $resize) {
            if ($width === $resize->getWidth() && $height === $resize->getHeight()) {
                return $resize;
            }
        }

        return null;
    }

    private $file;
    private $oldPath;

    public function setFile(HttpFile $file = null): self
    {
        $this->file = $file;

        // check if we have an old file path
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
