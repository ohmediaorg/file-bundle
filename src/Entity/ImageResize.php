<?php

namespace OHMedia\FileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use OHMedia\FileBundle\Repository\ImageResizeRepository;
use OHMedia\SecurityBundle\Entity\Entity;

/**
 * @ORM\Entity(repositoryClass=ImageResizeRepository::class)
 * @ORM\Table(name="image_resizes")
 */
class ImageResize extends Entity
{
    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $width;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $height;

    /**
     * @ORM\ManyToOne(targetEntity=Image::class, inversedBy="resizes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $image;

    /**
     * @ORM\OneToOne(targetEntity=File::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $file;

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
