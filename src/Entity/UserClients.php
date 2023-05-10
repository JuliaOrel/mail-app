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
    public function getUsersForMailingNewProfiles(object $customConnect, UserClientsRepository $userClientsRepository, array $clients = null): array
    {
        if ($clients) {
            $uss = $userClientsRepository->getUsersForMailingNewProfiles($customConnect, $clients);
        }
        else {
            $uss = $userClientsRepository->getUsersForMailingNewProfiles($customConnect);
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
    public function deliveryGetUnsubscribeUserHashNewMails(object $customConnect, UserClientsRepository $userClientsRepository, int $uid): string
    {
        $b = $userClientsRepository->getUnsubscribeUserHashNewMails($customConnect, $uid);
        if (!empty($b)) {
            return $b;
        }
        return null;
    }

    /**
     * Concatenating strings.
     */
    public function mailPromoGetUrlUnsub(string $hashunsub,string $prefix = "https://veronikalove.com/default/unsubscribe/index/user/"): string
    {
      return $prefix . $hashunsub;
    }

    /**
     * Formant format introductional msg strings.
     */
    public static function formatIntroductionalMsg(string $subject, string $fn, string $ln): string
    {         
        $str = stripslashes($subject);
        if(strpos($str, '{Customer.FirstName}') !== false){
            $str = str_replace('{Customer.FirstName}', $fn, $str);
        }
        if(strpos($str, '{Customer.LastName}') !== false){
            $str = str_replace('{Customer.LastName}', $ln, $str);
        }
        return $str;
    }

    /**
     * Get icon for mail by type.
     */
    public static function checkTypeAttach($attachment) {
      if (self::isGifAttach($attachment)) {
        return "https://veronikalove.com/images/frontend/gif_attach.png";
      }
      elseif (self::isVideoAttach($attachment)) {
        return "https://veronikalove.com/images/frontend/videoattach.png";
      }
      else {
        return "https://veronikalove.com/images/frontend/mail_attach.png";
      }
    }
    public static function isGifAttach($attachment){
        $exts = 'gif, GIF';
        $gif_ext_array = explode(',',$exts);
        $attachmentExp = explode(".", $attachment);
        $attachmentExt = array_pop($attachmentExp);
        if (!in_array( strtolower($attachmentExt), $gif_ext_array)){
                return false;
        }	
        return true;
    }
    public static function isVideoAttach($attachment){
        $types = 'application/octet-stream,video/x-msvideo,video/flv,video/avi,video/mpeg,'
                . 'video/mpg,video/wmv,video/x-ms-wmv,video/mp4,video/3gpp,video/3gpp2,'
                . 'video/x-ms-asf,video/vnd.avi,';
        $exts = '3gp,avi,asf,asx,divx,flv,mov,qt,ogv,mp4,m4v,mkv,mpg,mpe,mpeg,wmv,wmx,wm,webm';
        $video_type_array = explode(',',$types);
        $video_ext_array = explode(',',$exts);
        $attachmentExp = explode(".", $attachment);
        $attachmentExt = array_pop($attachmentExp);
        if (!in_array( strtolower($attachmentExt), $video_ext_array)){
                return false;
        }	
        return true;
    }
}
