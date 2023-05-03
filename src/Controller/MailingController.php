<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Mailing;
use App\Entity\UserClients;
use App\Form\Handler\MailingFormHandler;
use App\Form\MailingFormType;
use App\Form\MailingTestFormType;
use App\Repository\MailingRepository;
use App\Utils\Manager\MailingManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class MailingController extends AbstractController
{
    const TYPE_MARKER_TEST_MAIL = "test-mail-aws";
    /**
     * @Route("/mailing/list", name="app_mailing_list")
     */
    public function index(MailingRepository $mailingRepository): Response
    {
        $ms = $mailingRepository->findBy([], ["id" => "DESC"]);
        
        return $this->render('mailing/index.html.twig', [
            'products' => [],
            'mailings' => $ms,
        ]);
    }

    /**
     * @Route("/mailing/edit/{id}", methods="GET|POST|PUT", name="app_mailing_edit", requirements={"id"="\d+"})
     */
    public function edit(Request $request, MailingFormHandler $mailingFormHandler, Mailing $mailing): Response
    {
        $form = $this->createForm(MailingFormType::class, $mailing);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form);
            $mailing = $mailingFormHandler->processEditForm($mailing, $form);
            return $this->redirectToRoute("app_mailing_edit", ["id" => $mailing->getId()]);
        }
        $unsubscribeBlocks = [
            [
                "name" => "veronikalove.com",
                "value" => $this->renderView('_embed/_email/_unsubscribe.html.twig', [
                    'marker' => '{{ marker }}',
                    'pixel' => '{{ pixel }}',
                    'hash_unsub' => '{{ hash_unsub }}',
                ]),
            ],
        ];
        
        return $this->render('mailing/edit.html.twig', [
            "form" => $form->createView(),
            "mailing" => $mailing,
            "unsubscribeBlocks" => $unsubscribeBlocks,
        ]);
    }

    /**
     * @Route("/mailing/test/{id}", methods="GET|POST|PUT", name="app_mailing_test", requirements={"id"="\d+"})
     */
    public function test(Request $request, MailingFormHandler $mailingFormHandler, Mailing $mailing, MailingManager $mailingManager): Response
    {
        $form = $this->createForm(MailingTestFormType::class);        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userId = $form->get("field_user_id")->getData(); 
            $fname = $form->get("field_name")->getData(); 
            $lname = $form->get("field_last_name")->getData(); 
            $email = $form->get("field_email")->getData();
            $us = new UserClients();
            $hashUnsub = "demohashunsubscribe";
            $marker = $mailingFormHandler->createMarkerEmailUTM(self::TYPE_MARKER_TEST_MAIL);
            $data = [
                "user_id" => $userId,
                "user_name" => $fname,
                "user_surname" => $lname,
                "user_email" => $email,
                "unsubsribe_url" => $us->mailPromoGetUrlUnsub($hashUnsub),
                "hash_unsub" => $hashUnsub,
                "pixel" => $mailingManager->createUrlPixel($userId, $mailing->getId()),
                "marker" => $marker
            ];          
            $mailing = $mailingFormHandler->processTestMailing($mailing, $form, $data);
            return $this->redirectToRoute("app_mailing_test", ["id" => $mailing->getId()]);
        }
        
        return $this->render('mailing/test.html.twig', [
            "form" => $form->createView(),
            "mailing" => $mailing
        ]);
    }

    /**
     * @Route("/mailing/add", methods="GET|POST|PUT", name="app_mailing_add")
     */
    public function add(EntityManagerInterface $entityManager, Request $request): Response
    {
        $ml = new Mailing();
        $user = $this->getUser();
        $form = $this->createForm(MailingFormType::class, $ml);        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ml->setAuthor(0);
            $entityManager->persist($ml);
            $entityManager->flush();

            return $this->redirectToRoute("app_mailing_edit", ["id" => $ml->getId()]);
        }
        $unsubscribeBlocks = [
            [
                "name" => "veronikalove.com",
                "value" => $this->renderView('_embed/_email/_unsubscribe.html.twig', [
                    'marker' => '{{ marker }}',
                    'pixel' => '{{ pixel }}',
                    'hash_unsub' => '{{ hash_unsub }}',
                ]),
            ],
        ];
        return $this->render('mailing/add.html.twig', [
            "form" => $form->createView(),
            "unsubscribeBlocks" => $unsubscribeBlocks,
        ]);
    }

    /**
     * @Route("/mailing/delete", name="app_mailing_delete")
     */
    public function delete(Request $request): Response
    {
        return $this->render('mailing/index.html.twig', [
            'controller_name' => 'MailingController',
        ]);
    }
    /**
     * @Route("/pixel/pixel.gif", methods="GET", name="app_mailing_pixel")
     */
    public function pixel(Request $request,MailingManager $mailingManager): Response
    {
        $image = "1px.gif";
        $file = $mailingManager->getPixelFileContent();
        $uid = $request->query->get("uid");
        $mid = $request->query->get("mid");
        $sign = $request->query->get("sign");
        $isOk = $mailingManager->checkPixelUserSign($uid, $mid, $sign);
        if ($isOk) {
            $mailingManager->saveMailingItemOpen($uid, $mid);
        }
        $headers = array(
            'Content-Type'     => 'image/gif',
            'Content-Disposition' => 'inline; filename="'.$file.'"');
        return new Response($image, 200, $headers);
    }
    
    /**
     * @Route("/mailing-click", methods="GET", name="app_mailing_click")
     */
    public function click(Request $request,MailingManager $mailingManager): Response
    {
        $sign = $request->query->get("sign");
        $url = $request->query->get("url");
        
        try {
            $mailingManager->saveMailingRead($sign);
        } catch (Exception $ex) {
            return new Response("error: somnthing went wrong", Response::HTTP_NOT_FOUND);
        }
        if (empty($url)) {
            return new Response("error: url is not define", Response::HTTP_NOT_FOUND);
        }
        
        return $this->redirect($url, Response::HTTP_PERMANENTLY_REDIRECT);
    }
}
