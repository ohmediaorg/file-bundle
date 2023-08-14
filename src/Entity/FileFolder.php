<?php

namespace OHMedia\FileBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\FileBundle\Repository\FileFolderRepository;
use OHMedia\SecurityBundle\Entity\Traits\BlameableTrait;

#[ORM\Entity(repositoryClass: FileFolderRepository::class)]
class FileFolder
{
    use BlameableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $browser = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $locked = false;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'folders')]
    private ?self $folder = null;

    #[ORM\OneToMany(mappedBy: 'folder', targetEntity: self::class)]
    private Collection $folders;

    #[ORM\OneToMany(mappedBy: 'folder', targetEntity: File::class)]
    private Collection $files;

    public function __construct()
    {
        $this->folders = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function isBrowser(): bool
    {
        if ($this->folder) {
            return $this->folder->isBrowser();
        }

        return $this->browser;
    }

    public function setBrowser(bool $private): self
    {
        $this->browser = $browser;

        return $this;
    }

    public function isLocked(): bool
    {
        if ($this->folder) {
            return $this->folder->isLocked();
        }

        return $this->locked;
    }

    public function setLocked(bool $hidden): self
    {
        $this->locked = $locked;

        return $this;
    }

    public function getFolder(): ?self
    {
        return $this->folder;
    }

    public function setFolder(?self $folder): self
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getFolders(): Collection
    {
        return $this->folders;
    }

    public function addFolder(self $folder): self
    {
        if (!$this->folders->contains($folder)) {
            $this->folders->add($folder);
            $folder->setFolder($this);
        }

        return $this;
    }

    public function removeFolder(self $folder): self
    {
        if ($this->folders->removeElement($folder)) {
            // set the owning side to null (unless already changed)
            if ($folder->getFolder() === $this) {
                $folder->setFolder(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, File>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setFolder($this);
        }

        return $this;
    }

    public function removeFile(File $file): self
    {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getFolder() === $this) {
                $file->setFolder(null);
            }
        }

        return $this;
    }
}
