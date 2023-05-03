<?php

namespace App\Controller;

use App\Entity\MailingItem;
use App\Form\MailingItemFormType;
use App\Repository\MailingItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MailingItemController extends AbstractController
{
    /**
     * @Route("/mailing/item/{id}", methods="GET", name="app_mailing_item", requirements={"id"="\d+"})
     */
    public function index(EntityManagerInterface $entityManager,Request $request, int $id): Response
    {
        return $this->render('mailing_item/index.html.twig', [
            'controller_name' => 'MailingItemController',
        ]);
    }
    /**
     * @Route("/mailing/items", methods="GET", name="app_mailing_items", requirements={"id"="\d+"})
     */
    public function items(MailingItemRepository $mailingItemRepository): Response
    {
        $mis = $mailingItemRepository->findBy([], ["id" => "DESC"]);
        
        return $this->render('mailing_item/items.html.twig', [
            'mailingItems' => $mis,
        ]);
    }

    /**
     * @Route("/mailing-item/edit/{id}", methods="GET|POST|PUT", name="app_mailing_item_edit", requirements={"id"="\d+"})
     */
    public function edit(EntityManagerInterface $entityManager,Request $request, int $id): Response
    {
        $ml = new MailingItem();
        if ($id) {
            $ml = $entityManager->getRepository(Mailing::class)->find($id);
        }
        $form = $this->createForm(MailingFormType::class, $ml);        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $ml->setAuthor(0);
            $entityManager->persist($ml);
            $entityManager->flush();

            return $this->redirectToRoute("app_mailing_edit", ["id" => $ml->getId()]);
        }
        
        return $this->render('mailing/edit.html.twig', [
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/mailing-item/add", methods="GET|POST|PUT", name="app_mailing_item_add")
     */
    public function add(EntityManagerInterface $entityManager, Request $request): Response
    {
        $mi = new MailingItem();
        $form = $this->createForm(MailingItemFormType::class, $mi);        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($mi);
            $entityManager->flush();

            return $this->redirectToRoute("app_mailing_edit", ["id" => $mi->getId()]);
        }
        
        return $this->render('mailing/add.html.twig', [
            "form" => $form->createView()
        ]);
    }
}
