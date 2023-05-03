<?php

namespace App\Controller;

use App\Entity\MailingImage;
use App\Utils\Manager\MailingImageManager;
use App\Utils\Manager\MailingManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mailing/image", name="app_mailing_image_")
 */
class MailingImageController extends AbstractController
{
    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(MailingImage $mailingImage, MailingManager $mailingManager, MailingImageManager $mailingImageManager): Response
    {
        if (!$mailingImage) {
            return $this->redirectToRoute("mailing_list");
        }

        $mailing = $mailingImage->getMailing();
        $mailingImageDir = $mailingManager->getMailingImagesDir($mailing);
        $mailingImageManager->removeImageFromMailing($mailingImage, $mailingImageDir);
        return $this->redirectToRoute("app_mailing_edit", [
            "id" =>  $mailing->getId(),
        ]);
    }
}
