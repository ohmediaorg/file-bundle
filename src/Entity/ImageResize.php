<?php

namespace OHMedia\FileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use OHMedia\FileBundle\Repository\ImageResizeRepository;
use OHMedia\SecurityBundle\Entity\Traits\Blameable;

#[ORM\Entity(repositoryClass: ImageResizeRepository::class)]
class ImageResize
{
    use Blameable;

    #[ORM\Id()]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 50)]
    private $name;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private $width;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private $height;

    #[ORM\ManyToOne(targetEntity: Image::class, inversedBy: 'resizes')]
    #[ORM\JoinColumn(nullable: false)]
    private $image;

    #[ORM\OneToOne(targetEntity: File::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $file;

    public function __clone()
    {
        $this->id = null;

        $this->file = clone $this->file;
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

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): self
    {
        $this->image = $image;

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
}
