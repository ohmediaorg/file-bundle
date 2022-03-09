<?php

namespace OHMedia\FileBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\FileBundle\Repository\ImageRepository;
use OHMedia\SecurityBundle\Entity\Entity;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image extends Entity
{
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $alt;

    #[ORM\OneToOne(targetEntity: File::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn()]
    private $file;

    #[ORM\OneToMany(targetEntity: ImageResize::class, mappedBy: 'image', orphanRemoval: true)]
    private $resizes;

    public function __construct()
    {
        $this->resizes = new ArrayCollection();
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

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->file ? $this->file->getWidth() : null;
    }

    public function getHeight(): ?int
    {
        return $this->file ? $this->file->getHeight() : null;
    }

    /**
     * @return Collection|ImageResize[]
     */
    public function getResizes(): Collection
    {
        return $this->resizes;
    }

    public function addResize(ImageResize $resize): self
    {
        if (!$this->resizes->contains($resize)) {
            $this->resizes[] = $resize;
            $resize->setImage($this);
        }

        return $this;
    }

    public function removeResize(ImageResize $resize): self
    {
        if ($this->resizes->contains($resize)) {
            $this->resizes->removeElement($resize);
            // set the owning side to null (unless already changed)
            if ($resize->getImage() === $this) {
                $resize->setImage(null);
            }
        }

        return $this;
    }

    public function getResize(string $name)
    {
        foreach ($this->resizes as $resize) {
            if ($name === $resize->getName()) {
                return $resize;
            }
        }

        return null;
    }
}
