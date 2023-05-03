<?php

namespace App\Entity;

use App\Repository\MailingImageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MailingImageRepository::class)
 */
class MailingImage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $mailingId;

    /**
     * @ORM\ManyToOne(targetEntity=Mailing::class, inversedBy="mailingImages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mailing;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $filenameBig;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $filenameMiddle;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $filenameSmall;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMailingId(): ?int
    {
        return $this->mailingId;
    }

    public function setMailingId(int $mailingId): self
    {
        $this->mailingId = $mailingId;

        return $this;
    }

    public function getMailing(): ?Mailing
    {
        return $this->mailing;
    }

    public function setMailing(?Mailing $mailing): self
    {
        $this->mailing = $mailing;

        return $this;
    }

    public function getFilenameBig(): ?string
    {
        return $this->filenameBig;
    }

    public function setFilenameBig(string $filenameBig): self
    {
        $this->filenameBig = $filenameBig;

        return $this;
    }

    public function getFilenameMiddle(): ?string
    {
        return $this->filenameMiddle;
    }

    public function setFilenameMiddle(string $filenameMiddle): self
    {
        $this->filenameMiddle = $filenameMiddle;

        return $this;
    }

    public function getFilenameSmall(): ?string
    {
        return $this->filenameSmall;
    }

    public function setFilenameSmall(string $filenameSmall): self
    {
        $this->filenameSmall = $filenameSmall;

        return $this;
    }
}
