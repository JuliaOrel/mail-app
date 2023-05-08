<?php

namespace App\Utils\Manager;

use App\Entity\Mailing;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class MailingEmailSender
{

    /**
     * @var PHPMailer
     */
    private $_phpMailer;

    private $_host;
    private $_user;
    private $_passw;
    private $_fromNM;
    private $_fromNameNM;
    private $_replytoNM;
    private $_replytoNameNM;
    private $_from;
    private $_fromName;
    private $_replyto;
    private $_replytoName;
    private $_xmailer;
    private $_dkim;
    private $_smtpSecure;
    private $_charSet;
    private $_port;

    public function __construct()
    {
        $this->_phpMailer = new PHPMailer();
        $this->_host = $_ENV["APP_SES_SMTP_HOST"];
        $this->_user = $_ENV["APP_SES_SMTP_USER"];
        $this->_passw = $_ENV["APP_SES_SMTP_PASSW"];

        $this->_fromNM = $_ENV["APP_SES_FROM_NM"];
        $this->_fromNameNM = $_ENV["APP_SES_FROM_NAME_NM"];
        $this->_replytoNM = $_ENV["APP_SES_REPLYTO_NM"];
        $this->_replytoNameNM = $_ENV["APP_SES_REPLYTO_NAME_NM"];

        $this->_from = $_ENV["APP_SES_FROM"];
        $this->_fromName = $_ENV["APP_SES_FROM_NAME"];
        $this->_replyto = $_ENV["APP_SES_REPLYTO"];
        $this->_replytoName = $_ENV["APP_SES_REPLYTO_NAME"];
        $this->_xmailer = $_ENV["APP_SES_XMAILER"];
        $this->_dkim = $_ENV["APP_SES_DKIM_SELECTOR"];
        $this->_smtpSecure = $_ENV["APP_SES_SMTP_SECURE"];
        $this->_charSet = $_ENV["APP_SES_SMTP_CHARSET"];
        $this->_port = $_ENV["APP_SES_SMTP_PORT"];
    }

    /**
     * @return array
     */
    public function senEmail(string $emailTo, string $fname, string $lname, string $emailSubject, string $emailBody, string $unsubsribeUrl): array
    {
        $result = [
            "error" => false,
            "error_is" => "",
            "error_text" => "",
        ];
        $mail = $this->_phpMailer;
        $mail->CharSet = $this->_charSet;
        $mail->XMailer = $this->_xmailer;
        $mail->setFrom($this->_from, $this->_fromName);
        $mail->addReplyTo($this->_replyto, $this->_replytoName);
        $mail->addAddress($emailTo, $fname);
        $mail->Subject = $emailSubject;
        $mail->DKIM_selector = $this->_dkim;
        $msgHtml = $emailBody;
        $mail->AddCustomHeader('List-Unsubscribe', '<' . $unsubsribeUrl . '>, <admin@' . $this->_xmailer . '>');
        $mail->msgHTML($msgHtml);
        $mail->isSMTP();
        $mail->Host = $this->_host;
        $mail->SMTPAuth = true;
        $mail->Username = $this->_user;
        $mail->Password = $this->_passw;
        $mail->SMTPSecure = $this->_smtpSecure;
        $mail->Port = $this->_port; 
        // return true;
        try {
            $mail->send();
        } catch (phpmailerException $e) {
            $result["error"] = true;
            $result["error_is"] = get_class($e);
            $result["error_text"] = "An error occurred. {$e->errorMessage()}";
        } catch(Exception $ex) {
            $result["error"] = true;
            $result["error_is"] = get_class($ex);
            $result["error_text"] = "Email not sent. {$mail->ErrorInfo}";
        }
        $mail->clearAddresses();
        $mail->clearAttachments();
        $mail->clearCustomHeaders();
        
        return $result;
    }

    /**
     * @return array
     */
    public function senEmailNewMails(string $emailTo, string $fname, string $lname, string $emailSubject, string $emailBody, string $unsubsribeUrl): array
    {
        $result = [
            "error" => false,
            "error_is" => "",
            "error_text" => "",
        ];
        $mail = $this->_phpMailer;
        $mail->CharSet = $this->_charSet;
        $mail->XMailer = $this->_xmailer;
        $mail->setFrom($this->_fromNM, $this->_fromNameNM);
        $mail->addReplyTo($this->_replytoNM, $this->_replytoNameNM);
        $mail->addAddress($emailTo, $fname);
        $mail->Subject = $emailSubject;
        $mail->DKIM_selector = $this->_dkim;
        $msgHtml = $emailBody;
        $mail->AddCustomHeader('List-Unsubscribe', '<' . $unsubsribeUrl . '>, <admin@' . $this->_xmailer . '>');
        $mail->msgHTML($msgHtml);
        $mail->isSMTP();
        $mail->Host = $this->_host;
        $mail->SMTPAuth = true;
        $mail->Username = $this->_user;
        $mail->Password = $this->_passw;
        $mail->SMTPSecure = $this->_smtpSecure;
        $mail->Port = $this->_port; 
        // return true;
        try {
            $mail->send();
        } catch (phpmailerException $e) {
            $result["error"] = true;
            $result["error_is"] = get_class($e);
            $result["error_text"] = "An error occurred. {$e->errorMessage()}";
        } catch(Exception $ex) {
            $result["error"] = true;
            $result["error_is"] = get_class($ex);
            $result["error_text"] = "Email not sent. {$mail->ErrorInfo}";
        }
        $mail->clearAddresses();
        $mail->clearAttachments();
        $mail->clearCustomHeaders();
        
        return $result;
    }
}