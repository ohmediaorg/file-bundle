<?php

namespace OHMedia\FileBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\FileBundle\Repository\ImageRepository;
use OHMedia\SecurityBundle\Entity\Traits\BlameableTrait;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image
{
    use BlameableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $alt = null;

    #[ORM\OneToOne(inversedBy: 'image', cascade: ['persist', 'remove'])]
    private ?File $file = null;

    #[ORM\OneToMany(mappedBy: 'image', targetEntity: ImageResize::class, orphanRemoval: true)]
    private Collection $resizes;

    public function __construct()
    {
        $this->resizes = new ArrayCollection();
    }

    public function __clone()
    {
        $this->id = null;

        if ($this->file) {
            $this->file = clone $this->file;
        }

        $resizes = $this->resizes;
        $this->resizes = new ArrayCollection();

        foreach ($resizes as $resize) {
            $clone = clone $resize;

            $this->addResize($clone);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function setFile(?File $file): self
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
     * @return Collection<int, ImageResize>
     */
    public function getResizes(): Collection
    {
        return $this->resizes;
    }

    public function addResize(ImageResize $resize): self
    {
        if (!$this->resizes->contains($resize)) {
            $this->resizes->add($resize);
            $resize->setImage($this);
        }

        return $this;
    }

    public function removeResize(ImageResize $resize): self
    {
        if ($this->resizes->removeElement($resize)) {
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
