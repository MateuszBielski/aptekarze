<?php

namespace App\Controller;

use App\Entity\MemberHistory;
use App\Form\MemberHistoryType;
use App\Repository\MemberHistoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/member/history")
 *  @Security("is_granted('ROLE_ADMIN')")
 */
class MemberHistoryController extends AbstractController
{
    /**
     * @Route("/", name="member_history_index", methods={"GET"})
     */
    public function index(MemberHistoryRepository $memberHistoryRepository): Response
    {
        return $this->render('member_history/index.html.twig', [
            'member_histories' => $memberHistoryRepository->findAll(),
        ]);
    }

    

    /**
     * @Route("/{id}", name="member_history_show", methods={"GET"})
     */
    public function show(MemberHistory $memberHistory): Response
    {
        return $this->render('member_history/show.html.twig', [
            'member_history' => $memberHistory,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="member_history_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, MemberHistory $memberHistory): Response
    {
        $form = $this->createForm(MemberHistoryType::class, $memberHistory);
        $form->handleRequest($request);
        $memberUserId = $memberHistory->getMyUser()->getId();

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('member_user_show', [
                'id' => $memberUserId,
            ]);
        }

        return $this->render('member_history/edit.html.twig', [
            'member_history' => $memberHistory,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="member_history_delete", methods={"DELETE"})
     */
    public function delete(Request $request, MemberHistory $memberHistory): Response
    {
        // $memberUserId = $memberHistory->getMyUser()->getId();
        // if ($this->isCsrfTokenValid('delete'.$memberHistory->getId(), $request->request->get('_token'))) {
        //     $entityManager = $this->getDoctrine()->getManager();
        //     $entityManager->remove($memberHistory);
        //     $entityManager->flush();
        // }
        // return $this->redirectToRoute('member_user_show', [
        //     'id' => $memberUserId,
        // ]);
        $memberUser = $memberHistory->getMyUser();
        $memberUserId = $memberUser->getId();
        if ($this->isCsrfTokenValid('delete'.$memberHistory->getId(), $request->request->get('_token'))) {
            $content = $memberUser->removeMyJobHistory($memberHistory);
            //return new Response($content);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($memberUser);
            $entityManager->remove($memberHistory);
            $entityManager->flush();
        }
        return $this->redirectToRoute('member_user_show', [
            'id' => $memberUserId,
        ]);
    }
}
