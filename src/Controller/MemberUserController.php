<?php

namespace App\Controller;

use App\Entity\MemberUser;
use App\Form\MemberUserType;
use App\Repository\MemberUserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/member/user")
 */
class MemberUserController extends AbstractController
{
    /**
     * @Route("/", name="member_user_index", methods={"GET"})
     */
    public function index(MemberUserRepository $memberUserRepository): Response
    {
        return $this->render('member_user/index.html.twig', [
            'member_users' => $memberUserRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="member_user_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $memberUser = new MemberUser();
        $form = $this->createForm(MemberUserType::class, $memberUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**********create temporary traits******* */
            $memberUser->createTempUsername();
            $memberUser->setPassword('87654321');


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($memberUser);
            $entityManager->flush();

            return $this->redirectToRoute('member_user_index');
        }

        return $this->render('member_user/new.html.twig', [
            'member_user' => $memberUser,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="member_user_show", methods={"GET"})
     */
    public function show(MemberUser $memberUser): Response
    {
        return $this->render('member_user/show.html.twig', [
            'member_user' => $memberUser,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="member_user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, MemberUser $memberUser): Response
    {
        $form = $this->createForm(MemberUserType::class, $memberUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('member_user_index', [
                'id' => $memberUser->getId(),
            ]);
        }

        return $this->render('member_user/edit.html.twig', [
            'member_user' => $memberUser,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="member_user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, MemberUser $memberUser): Response
    {
        if ($this->isCsrfTokenValid('delete'.$memberUser->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($memberUser);
            $entityManager->flush();
        }

        return $this->redirectToRoute('member_user_index');
    }
}
