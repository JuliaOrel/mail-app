<?php

namespace App\Form\Handler;

use App\Entity\Mailing;
use App\Utils\File\FileSaver;
use App\Utils\Manager\MailingEmailSender;
use App\Utils\Manager\MailingManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Twig\Environment;

class MailingFormHandler
{
    const HREF_REDIRECT = "mailing-click";

    /**
     * @var MailingManager
     */
    private $_mailingManager;

    /**
     * @var FileSaver
     */
    private $_fileSaver;

    /**
     * @var MailingEmailSender
     */
    private $mailingEmailSender;
    /**
     * @var Environment
     */
    private $twig;

    public function __construct(MailingManager $mailingManager, FileSaver $fileSaver, MailingEmailSender $mailingEmailSender, Environment $twig)
    {
        $this->setMailingManager($mailingManager);
        $this->setFileSaver($fileSaver);
        $this->setMailingEmailSender($mailingEmailSender);
        $this->twig = $twig;
    }

    public function processEditForm(Mailing $mailing, Form $form)
    {
        $imgs = $form->get("mailingImages")->getData();
        foreach ($imgs as $key => $img) {
            $tmpImg = $img ? $this->_fileSaver->saveUploadedFileIntoTemp($img) : null;
            $this->_mailingManager->uploadMailingImages($mailing, $tmpImg);
        }
        $this->_mailingManager->save($mailing);
        
        return $mailing;
    }

    public function processTestMailing(Mailing $mailing, Form $form, array $data)
    {
        $emailSubject = $mailing->getTitle();
        $emailBody = $mailing->getBody();
        $template = $this->twig->createTemplate($emailBody);
        $emailBody = $template->render($data);
        $emailText = strip_tags($emailBody);
        $sign = $this->_mailingManager->createPixelUserSign($data["user_id"], $mailing->getId());
        $this->editHrefForTrackingFromStr($emailBody, $sign, self::HREF_REDIRECT);
        $this->mailingEmailSender->senEmail($data["user_email"], $data["user_name"], $data["user_surname"], $emailSubject, $emailBody, $data["unsubsribe_url"]);

        return $mailing;
    }
    public function userUnsubscribeMailing(Mailing $mailing, array $data)
    {
        $this->_mailingManager->createMailingItemIsUnsubscribed($data["user_id"], $data["user_email"], $mailing->getId());
    }
    public function processSendMailing(Mailing $mailing, array $data)
    {
        $emailSubject = $mailing->getTitle();
        $emailBody = $mailing->getBody();
        $template = $this->twig->createTemplate($emailBody);
        $emailBody = $template->render($data);
        $emailText = strip_tags($emailBody);
        $fname = (!empty($data["user_name"]))?$data["user_name"]:"";
        $lname = (!empty($data["user_surname"]))?$data["user_surname"]:"";
        $sign = $this->_mailingManager->createPixelUserSign($data["user_id"], $mailing->getId());
        $this->editHrefForTrackingFromStr($emailBody, $sign, self::HREF_REDIRECT);
        $r = $this->mailingEmailSender->senEmail($data["user_email"], $fname, $lname, $emailSubject, $emailBody, $data["unsubsribe_url"]);
        $this->_mailingManager->createMailingItem($data["user_id"], $data["user_email"], $mailing->getId());
        if ($r["error"]) {
            $this->_mailingManager->saveMailingItemSendError($data["user_id"], $mailing->getId(), $r["error_text"]);
            return $mailing;
        }
        $this->_mailingManager->saveMailingItemSend($data["user_id"], $mailing->getId());
        return $mailing;
    }
    public function createMarkerEmailUTM(string $type = "news-aws"): string
    {
        $mediumUtm = $type . date('l-Y');
        $compaignUtm = $type . date('Y');
        $marker = "utm_source=" . $type . date('jMY') . "&utm_medium={$mediumUtm}&utm_campaign={$compaignUtm}";
        $marker = strtolower($marker);

        return $marker;
    }    
    public function editHrefForTrackingFromStr(string &$htmlText, string $sign, string $redirectHref): void
    {
        $replace = 'href="' . $_ENV["APP_SITE_URL"] . "/" . $redirectHref . "?sign=" . $sign . "&url=";
        $htmlText = str_ireplace('href="', $replace, $htmlText);
    }

    /**
     * Get the value of entityManager
     */ 
    public function getMailingManager()
    {
        return $this->_mailingManager;
    }

    /**
     * Set the value of entityManager
     *
     * @return  self
     */ 
    public function setMailingManager($mailingManager)
    {
        $this->_mailingManager = $mailingManager;

        return $this;
    }

    /**
     * Get the value of fileSaver
     */ 
    public function getFileSaver()
    {
        return $this->_fileSaver;
    }

    /**
     * Set the value of fileSaver
     *
     * @return  self
     */ 
    public function setFileSaver($fileSaver)
    {
        $this->_fileSaver = $fileSaver;

        return $this;
    }

    /**
     * Get the value of mailingEmailSender
     */ 
    public function getMailingEmailSender()
    {
        return $this->mailingEmailSender;
    }

    /**
     * Set the value of mailingEmailSender
     *
     * @return  self
     */ 
    public function setMailingEmailSender($mailingEmailSender)
    {
        $this->mailingEmailSender = $mailingEmailSender;

        return $this;
    }
}