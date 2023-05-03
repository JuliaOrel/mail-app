<?php

namespace App\Entity;

use App\Repository\MailingItemRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MailingItemRepository::class)
 */
class MailingItem
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $userId;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $sentAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $readAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $openAt;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isRead;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isUnscribed;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="integer")
     */
    private $MailingId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sign;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hasError;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $errorText;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTimeImmutable $sentAt): self
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    public function setReadAt(?\DateTimeImmutable $readAt): self
    {
        $this->readAt = $readAt;

        return $this;
    }

    public function getOpenAt(): ?\DateTimeImmutable
    {
        return $this->openAt;
    }

    public function setOpenAt(?\DateTimeImmutable $openAt): self
    {
        $this->openAt = $openAt;

        return $this;
    }

    public function isIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(?bool $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function isIsUnscribed(): ?bool
    {
        return $this->isUnscribed;
    }

    public function setIsUnscribed(?bool $isUnscribed): self
    {
        $this->isUnscribed = $isUnscribed;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getMailingId(): ?int
    {
        return $this->MailingId;
    }

    public function setMailingId(int $MailingId): self
    {
        $this->MailingId = $MailingId;

        return $this;
    }

    public function getSign(): ?string
    {
        return $this->sign;
    }

    public function setSign(string $sign): self
    {
        $this->sign = $sign;

        return $this;
    }

    public function isHasError(): ?bool
    {
        return $this->hasError;
    }

    public function setHasError(?bool $hasError): self
    {
        $this->hasError = $hasError;

        return $this;
    }

    public function getErrorText(): ?string
    {
        return $this->errorText;
    }

    public function setErrorText(string $errorText): self
    {
        $this->errorText = $errorText;

        return $this;
    }
}
