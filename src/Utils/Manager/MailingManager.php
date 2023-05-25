<?php

namespace App\Utils\Manager;

use App\Entity\Mailing;
use App\Entity\MailingItem;
use App\Repository\MailingItemRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class MailingManager
{
    const _hashSignSalt = "NWd8lrXu";
    const MAILING_CATEGORY_PROMO = 1;
    const MAILING_CATEGORY_MAIL = 2;
    const MAILING_CATEGORY_VISITORS = 3;
    const MAILING_CATEGORY_BIRTHDAY = 4; 
    const MAILING_CATEGORY_NEW_PROFILES = 5;

    const MAILING_STATUS_INACTIVE = 0;
    const MAILING_STATUS_ACTIVE = 1;
    const MAILING_STATUS_BUSY = 3;
    const MAILING_STATUS_FINISHED = 4;
    const MAILING_STATUS_FAILED = 5;

    /**
     * @var EntityManagerInterface
     */
    private $_entityManager;

    /**
     * @var string
     */
    private $_mailingImagesDir;

    /**
     * @var MailingImageManager
     */
    private $_mailingImageManager;

    public function __construct(EntityManagerInterface $entityManager, MailingImageManager $mailingImageManager, string $mailingImagesDir)
    {
        $this->_entityManager = $entityManager;
        $this->_mailingImagesDir = $mailingImagesDir;
        $this->_mailingImageManager = $mailingImageManager;
    }

    /**
     * @return ObjectRepository
     */
    public function getRepository(): ObjectRepository
    {
        return $this->_entityManager->getRepository(Mailing::class);
    }

    public function remove() {
        //
    }
    static public function categoryOptions(): array
    {
        return [
            "PROMO" => self::MAILING_CATEGORY_PROMO,
            "MAILS" => self::MAILING_CATEGORY_MAIL,
            "Birthday" => self::MAILING_CATEGORY_BIRTHDAY,
            "Visitors" => self::MAILING_CATEGORY_VISITORS,
            "New profiles" => self::MAILING_CATEGORY_NEW_PROFILES,
        ];
    }
    static public  function statusOptions(): array
    {
        return [
            "inactive" => self::MAILING_STATUS_INACTIVE,
            "active" => self::MAILING_STATUS_ACTIVE,
            "busy" => self::MAILING_STATUS_BUSY,
            "finished" => self::MAILING_STATUS_FINISHED,
            "failed" => self::MAILING_STATUS_FAILED,
        ];
    }

    public function getMailingImagesDir(Mailing $mailing)
    {
        return sprintf("%s/%s", $this->_mailingImagesDir, $mailing->getId());
    }
    public function uploadMailingImages(Mailing $mailing, string $tempImageFilename = null): Mailing
    {
        if (!$tempImageFilename) {
            return $mailing;
        }

        $mailingDir = $this->getMailingImagesDir($mailing);
        $mailingImage = $this->_mailingImageManager->saveImageForMailing($mailingDir, $tempImageFilename);
        $mailingImage->setMailing($mailing);
        $mailing->addMailingImage($mailingImage);
        return $mailing;
    }

    /**
     * @param Mailing
     */
    public function save(Mailing $mailing)
    {
        $this->_entityManager->persist($mailing);
        $this->_entityManager->flush();
    }

    /**
     * @return Mailing|null
     */
    public function getMailingNewProfilesCronTask(): ?Mailing
    {
        $mailingRepository = $this->_entityManager->getRepository(Mailing::class);
        $m = $mailingRepository->findOneBy([
            "status" => self::MAILING_STATUS_ACTIVE,
            "isDeleted" => null,
            "isPublished" => true,
            "category" => self::MAILING_CATEGORY_NEW_PROFILES
        ]);

        return $m;
    }

    /**
     * @return Mailing|null
     */
    public function getMailingPromoCronTask(): ?Mailing
    {
        $cDate = date('Y-m-d H:00:00');
        $date = new DateTimeImmutable($cDate);
        $mailingRepository = $this->_entityManager->getRepository(Mailing::class);
        $m = $mailingRepository->findOneBy([
            "scheduledAt" => $date, 
            "status" => self::MAILING_STATUS_ACTIVE,
            "isDeleted" => null,
            "isPublished" => true,
            "category" => self::MAILING_CATEGORY_PROMO
        ]);

        return $m;
    }

    /**
     * @return Mailing|null
     */
    public function getMailingNewMailsCronTask(): ?Mailing
    {
        $mailingRepository = $this->_entityManager->getRepository(Mailing::class);
        $m = $mailingRepository->findOneBy([
            "status" => self::MAILING_STATUS_ACTIVE,
            "isDeleted" => null,
            "isPublished" => true,
            "category" => self::MAILING_CATEGORY_MAIL
        ]);

        return $m;
    }

    /**
     * @return Mailing|null
     */
    public function getMailingMailCronTask(): ?Mailing
    {
        $cDate = date('Y-m-d H:00:00');
        $date = new DateTimeImmutable($cDate);
        $mailingRepository = $this->_entityManager->getRepository(Mailing::class);
        $m = $mailingRepository->findOneBy([
            "scheduledAt" => $date, 
            "status" => self::MAILING_STATUS_ACTIVE,
            "isDeleted" => null,
            "isPublished" => true,
            "category" => self::MAILING_CATEGORY_MAIL
        ]);

        return $m;
    }

    /**
     * @return false|int $file
     */
    public function getPixelFileContent()
    {
        $file =    readfile($this->_mailingImagesDir . "/../../../build/images/pixel.gif");
        return $file;
    }

    /**
     * @param int
     * @param int
     * @param string
     * @return bool
     */
    public function checkPixelUserSign(int $uid, int $mid, string $sign): bool
    {
        $createSign = $this->createPixelUserSign($uid, $mid);
        if ($sign == $createSign) {
            return true;
        }

        return false;
    }

    /**
     * @param int
     * @param int
     * @return string
     */
    public function createPixelUserSign(int $uid, int $mid): string
    {
        $prepareStr = $uid . self::_hashSignSalt . $mid;
        $sign = md5($prepareStr);
        return $sign;
    }

    /**
     * @param int
     * @param int
     * @return string
     */
    public function createUrlPixel(int $uid, int $mid): string
    {
        $sign = $this->createPixelUserSign($uid, $mid);
        $urlPixel = sprintf("%s/pixel/pixel.gif?mid=%d&sign=%s&uid=%d", $_ENV["APP_SITE_URL"], $mid, $sign, $uid);
        return $urlPixel;
    }

    /**
     * @param Mailing
     * @param int
     * @return void
     */
    public function saveMailingStatus(Mailing $mailing, int $status): void
    {
        $mailing->setStatus($status);
        $this->_entityManager->persist($mailing);
        $this->_entityManager->flush();
    }

    /**
     * @param Mailing
     * @param int
     * @return void
     */
    public function createMailingCloneStatus(Mailing $mailing, int $status): void
    {
        $m = clone $mailing;
        $m->setId(0);
        $m->setStatus($status);
        $this->_entityManager->persist($m);
        $this->_entityManager->flush();
    }

    /**
     * @param Mailing
     * @param int
     * @return void
     */
    public function createMailingNewMailsCloneStatus(Mailing $mailing, int $status): void
    {
        $newMail = new Mailing();
        $date = new DateTimeImmutable();
        $date->format('Y-m-d H:i:s');
        $nextDay = (date("N") == 1) ? "thursday" : "monday";
        $newMail->setScheduledAt($date->modify($nextDay));
        $newMail->setStatus($status);
        $newMail->setBody($mailing->getBody());
        $newMail->setTitle($mailing->getTitle());
        $newMail->setCategory($mailing->getCategory());
        $newMail->setIsPublished(true);
        $newMail->setAuthor($mailing->getAuthor());
        
        $this->_entityManager->persist($newMail);
        $this->_entityManager->flush();
    }

    /**
     * @param Mailing
     * @param int
     * @return void
     */
    public function createMailingNewProfilesCloneStatus(Mailing $mailing, int $status): void
    {
        $newMail = new Mailing();
        $date = new DateTimeImmutable();
        $date->format('Y-m-d H:i:s');
        $nextDay = '+7 day';
        
        $newMail->setScheduledAt($date->modify($nextDay));
        $newMail->setStatus($status);
        $newMail->setBody($mailing->getBody());
        $newMail->setTitle($mailing->getTitle());
        $newMail->setAuthor($mailing->getAuthor());
        $newMail->setCategory($mailing->getCategory());
        $newMail->setIsPublished(true);
        
        $this->_entityManager->persist($newMail);
        $this->_entityManager->flush();
    }

    /**
     * @param int
     * @param int
     * @return string
     */
    public function saveMailingItemOpen(int $uid, int $mid): string
    {
        $mailingItemRepository = $this->_entityManager->getRepository(MailingItem::class);
        $sign = $this->createPixelUserSign($uid, $mid);
        $miEntity = new MailingItem();
        $mis = $mailingItemRepository->findOneBy(["sign" => $sign]);
        if ($mis && $mis->getOpenAt() == null) {
            $miEntity = $mis;
        } else {
            return $sign;
        }
        
        $date = new DateTimeImmutable();
        $date->format('Y-m-d H:i:s');        
        $miEntity->setOpenAt($date);
        $this->_entityManager->persist($miEntity);
        $this->_entityManager->flush();

        return $sign;
    }

    /**
     * @param string
     * @return string
     */
    public function saveMailingRead(string $sign): string
    {
        $mailingItemRepository = $this->_entityManager->getRepository(MailingItem::class);        
        $miEntity = new MailingItem();

        $mis = $mailingItemRepository->findOneBy(["sign" => $sign]);
        if ($mis && $mis->getReadAt() == null) {
            $miEntity = $mis;
        } else {
            return $sign;
        }
        
        $date = new DateTimeImmutable();
        $date->format('Y-m-d H:i:s');        
        $miEntity->setReadAt($date);      
        $miEntity->setIsRead(true);
        $this->_entityManager->persist($miEntity);
        $this->_entityManager->flush();
        
        return $sign;
    }

    /**
     * @param int
     * @param int
     * @return bool
     */
    public function saveMailingItemSend(int $uid, int $mid): string
    {
        $mailingItemRepository = $this->_entityManager->getRepository(MailingItem::class);
        $sign = $this->createPixelUserSign($uid, $mid);
        $miEntity = new MailingItem();
        $mis = $mailingItemRepository->findOneBy(["sign" => $sign]);
        if ($mis && $mis->getSentAt() == null) {
            $miEntity = $mis;
        } else {
            return $sign;
        }
        
        $date = new DateTimeImmutable();
        $date->format('Y-m-d H:i:s');        
        $miEntity->setSentAt($date);
        $this->_entityManager->persist($miEntity);
        $this->_entityManager->flush();

        return $sign;
    }
    /**
     * @param int
     * @param int
     * @return string
     */
    public function saveMailingIsUnsubscribed(int $uid, int $mid): string
    {
        $sign = $this->createPixelUserSign($uid, $mid);
        $mailingItemRepository = $this->_entityManager->getRepository(MailingItem::class);
        $miEntity = new MailingItem();
        $mis = $mailingItemRepository->findOneBy(["sign" => $sign]);
        if ($mis) {
            $miEntity = $mis;
        } else {
            return $sign;
        }
        
        $miEntity->setIsUnscribed(true);
        $this->_entityManager->persist($miEntity);
        $this->_entityManager->flush();

        return $sign;
    }
    /**
     * @param int
     * @param int
     * @param string
     * @return string
     */
    public function saveMailingItemSendError(int $uid, int $mid, string $errorText): string
    {
        $sign = $this->createPixelUserSign($uid, $mid);
        $mailingItemRepository = $this->_entityManager->getRepository(MailingItem::class);
        $miEntity = new MailingItem();
        $mis = $mailingItemRepository->findOneBy(["sign" => $sign]);
        if ($mis) {
            $miEntity = $mis;
        } else {
            return $sign;
        }
        
        $miEntity->setHasError(true);
        $miEntity->setErrorText($errorText);
        $this->_entityManager->persist($miEntity);
        $this->_entityManager->flush();

        return $sign;
    }

    /**
     * @param int
     * @param int
     * @param string
     * @return bool
     */
    public function isMailingItemSent(int $uid, string $email, int $mid): bool
    {
        $mailingItemRepository = $this->_entityManager->getRepository(MailingItem::class);
        $sign = $this->createPixelUserSign($uid, $mid);
        $miEntity = new MailingItem();
        $mis = $mailingItemRepository->findOneBy(["sign" => $sign]);
        if (!$mis) {
            return false;
        }
        elseif($mis && $mis->getSentAt() == null) {
            return false;
        }
        return true;
    }

    /**
     * @param int
     * @param int
     * @param string
     * @return bool
     */
    public function createMailingItem(int $uid, string $email, int $mid): string
    {
        $mailingItemRepository = $this->_entityManager->getRepository(MailingItem::class);
        $sign = $this->createPixelUserSign($uid, $mid);
        $miEntity = new MailingItem();
        $mis = $mailingItemRepository->findOneBy(["sign" => $sign]);
        if ($mis && $mis->getCreatedAt() == null) {
            $miEntity = $mis;
        }
        elseif($mis && $mis->getCreatedAt() != null) {
            return $sign;
        }
        
        $date = new DateTimeImmutable();
        $date->format('Y-m-d H:i:s');     
        $miEntity->setUserId($uid);       
        $miEntity->setMailingId($mid); 
        $miEntity->setEmail($email);      
        $miEntity->setSign($sign);      
        $miEntity->setCreatedAt($date);
        $this->_entityManager->persist($miEntity);
        $this->_entityManager->flush();

        return $sign;
    }

    /**
     * @param int
     * @param int
     * @param string
     * @return bool
     */
    public function createMailingItemIsUnsubscribed(int $uid, string $email, int $mid): string
    {
        $mailingItemRepository = $this->_entityManager->getRepository(MailingItem::class);
        $sign = $this->createPixelUserSign($uid, $mid);
        $miEntity = new MailingItem();
        $mis = $mailingItemRepository->findOneBy(["sign" => $sign]);
        if ($mis && $mis->getCreatedAt() == null) {
            $miEntity = $mis;
        }
        elseif($mis && $mis->getCreatedAt() != null) {
            return $sign;
        }
        
        $date = new DateTimeImmutable();
        $date->format('Y-m-d H:i:s');     
        $miEntity->setUserId($uid);       
        $miEntity->setMailingId($mid); 
        $miEntity->setEmail($email);      
        $miEntity->setSign($sign);      
        $miEntity->setCreatedAt($date);
        $miEntity->setIsUnscribed(true);
        $this->_entityManager->persist($miEntity);
        $this->_entityManager->flush();

        return $sign;
    }
}