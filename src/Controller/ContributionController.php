<?php

namespace App\Controller;

use App\Entity\Contribution;
use App\Form\ContributionType;
use App\Repository\ContributionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\MemberUser;

/**
 * @Route("/contribution")
 * @Security("is_granted('ROLE_ADMIN')")
 */
//
class ContributionController extends AbstractController
{
    /**
     * @Route("/", name="contribution_index", methods={"GET"})
     */
    public function index(ContributionRepository $contributionRepository): Response
    {
        return $this->render('contribution/index.html.twig', [
            // 'contributions' => $contributionRepository->findAll(),
            'contributions' => $contributionRepository->findBy([], ['paymentDate' => 'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="contribution_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $contribution = new Contribution();
        $contribution->setPaymentDate(new \DateTime('now'));
        $form = $this->createForm(ContributionType::class, $contribution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contribution);
            $entityManager->flush();

            return $this->redirectToRoute('contribution_index');
        }

        return $this->render('contribution/new.html.twig', [
            'contribution' => $contribution,
            'form' => $form->createView(),
        ]);
    }

    

    /**
     * @Route("/{id}", name="contribution_show", methods={"GET"})
     */
    public function show(Contribution $contribution): Response
    {
        return $this->render('contribution/show.html.twig', [
            'contribution' => $contribution,
        ]);
    }

    /**
     * @Route("/{id}/new", name="contribution_new_forUser", methods={"GET","POST"})
     */
    public function newForUser(Request $request, MemberUser $memberUser): Response
    {
        $contribution = new Contribution();
        $contribution->setMyUser($memberUser);
        $contribution->setPaymentDate(new \DateTime('now'));
        $form = $this->createForm(ContributionType::class, $contribution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contribution);
            $entityManager->flush();

            return $this->redirectToRoute('member_user_show', ['id' => $memberUser->getId()]);
        }

        return $this->render('contribution/new.html.twig', [
            'contribution' => $contribution,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/print", name="contribution_print", methods={"GET","POST"})
     */
    public function PrintConfirmation(Contribution $contribution): Response
    {
        $template = '';
        //można wydrukować tylko jeden raz

        if($contribution->getPrinted() != null){
            $template = 'contribution/confirmationPrinted.html.twig';
        }
        else {
            $contribution->setPrinted(new \DateTime('now'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contribution);
            $entityManager->flush();
            $template = 'contribution/confirmation.html.twig';
        }
        return $this->render($template, [
            'contribution' => $contribution,
        ]);
    }
    /**
     * @Route("/{id}/edit", name="contribution_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Contribution $contribution): Response
    {
        $form = $this->createForm(ContributionType::class, $contribution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('contribution_index', [
                'id' => $contribution->getId(),
            ]);
        }

        return $this->render('contribution/edit.html.twig', [
            'contribution' => $contribution,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="contribution_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Contribution $contribution): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contribution->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($contribution);
            $entityManager->flush();
        }

        return $this->redirectToRoute('contribution_index');
    }
}
