<?php

namespace App\Entity;

use Doctrine\Persistence\ManagerRegistry;
use App\Repository\UserClientsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserClientsRepository::class)
 */
class UserClients
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Lastname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $UserEmail;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->Firstname;
    }

    public function setFirstname(?string $Firstname): self
    {
        $this->Firstname = $Firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->Lastname;
    }

    public function setLastname(?string $Lastname): self
    {
        $this->Lastname = $Lastname;

        return $this;
    }

    public function getUserEmail(): ?string
    {
        return $this->UserEmail;
    }

    public function setUserEmail(string $UserEmail): self
    {
        $this->UserEmail = $UserEmail;

        return $this;
    }
    public function getUsersForMailingPromo(object $customConnect, UserClientsRepository $userClientsRepository, array $clients = null): array
    {
        if ($clients) {
            $uss = $userClientsRepository->getUsersForDefUsersMailingPromo($customConnect, $clients);
        }
        else {
            $uss = $userClientsRepository->getUsersForMailingPromo($customConnect);
        }

        return $uss;
    }
    public function getUsersForMailingNewMails(object $customConnect, UserClientsRepository $userClientsRepository, array $clients = null): array
    {
        if ($clients) {
            $uss = $userClientsRepository->getUsersForMailingNewMails($customConnect, $clients);
        }
        else {
            $uss = $userClientsRepository->getUsersForMailingNewMails($customConnect);
        }

        return $uss;
    }
    public function userIsActive(object $customConnect, UserClientsRepository $userClientsRepository, int $uid): bool
    {
        $b = $userClientsRepository->getUserStatus($customConnect, $uid);
        if (is_numeric($b) && $b > 0) {
            return true;
        }
        return false;
    }
    public function deliveryCheckUnsubscribeNews(object $customConnect, UserClientsRepository $userClientsRepository, int $uid): bool
    {
        $b = $userClientsRepository->getUserUnsubscribeNews($customConnect, $uid);
        if (is_numeric($b) && $b > 0) {
            return true;
        }
        return false;
    }
    public function deliveryCheckUnsubscribeNewMails(object $customConnect, UserClientsRepository $userClientsRepository, int $uid): bool
    {
        $b = $userClientsRepository->getUserUnsubscribeNewMails($customConnect, $uid);
        if (is_numeric($b) && $b > 0) {
            return true;
        }
        return false;
    }
    public function getNewMailsLadies(object $customConnect, UserClientsRepository $userClientsRepository, int $uid, string $fn, string $ln): array
    {
        return $userClientsRepository->getNewMailsLadies($customConnect, $uid, $fn, $ln);
    }
    public function deliveryGetUnsubscribeUserHash(object $customConnect, UserClientsRepository $userClientsRepository, int $uid): string
    {
        $b = $userClientsRepository->getUserUnsubscribeHash($customConnect, $uid);
        if (!empty($b)) {
            return $b;
        }
        return null;
    }

    /**
     * Concatenating strings.
     */
    function mailPromoGetUrlUnsub(string $hashunsub,string $prefix = "https://veronikalove.com/default/unsubscribe/index/user/"): string
    {
      return $prefix . $hashunsub;
    }
}
