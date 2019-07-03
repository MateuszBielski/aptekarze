<?php

namespace App\Controller;

use App\Entity\MemberUser;
use App\Entity\MemberHistory;
use App\Entity\UserMemberToSerialize;
use App\Form\MemberUserType;
use App\Repository\MemberUserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\JobRepository;
use App\Service\MemberUserOptimizer;

/**
 * @Route("/member/user")
 */
class MemberUserController extends AbstractController
{
    
    /**
     * @Route("/", name="member_user_index", methods={"GET"})
     */
    public function index(MemberUserOptimizer $memUsOptim)
    {
        $memUsOptim->ReadRepositoriesAndCompleteCollections();
        $memUsOptim->setCurrentAccounts();
        return $this->render('member_user/index.html.twig', [
            'member_users' => $memUsOptim->getUsersList(),
        ]);
        
    }

    // public function index(MemberUserRepository $memberUserRepository): Response
    // {
        //     return $this->render('member_user/index.html.twig', [
            //         'member_users' => $memberUserRepository->findAll(),
            //     ]);
            // }

    /**
     * @Route("/indexAjax", name="member_user_indexAjax", methods={"GET", "POST"})
     */
    public function indexAjax(Request $request,MemberUserOptimizer $memUsOptim): Response
    {
        $memUsOptim->ReadRepositoriesAndCompleteCollectionsNarrow($request->query->get("str"));
        $memUsOptim->setCurrentAccounts();
        return $this->render('member_user/indexAjax.html.twig', [
            'member_users' => $memUsOptim->getUsersList(),
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
            
            $memberStartHistory = new MemberHistory($memberUser);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($memberUser);
            $entityManager->persist($memberStartHistory);
            $entityManager->flush();

            return $this->redirectToRoute('member_user_index');
        }

        return $this->render('member_user/new.html.twig', [
            'member_user' => $memberUser,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/zpliku", name="member_users_zpliku", methods={"GET", "POST"})
     */
    public function deserialize(JobRepository $jobRepository): Response
    {
        $jobs = array();
        foreach ($jobRepository->findAll() as $job) {
            $jobs[$job->getRate()] = $job;
        }
        $encoders = [new CsvEncoder()];
        $normalizers = [new ObjectNormalizer(), new ArrayDenormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $file = '../nazwiskaApt.csv';
        $data = file_get_contents($file);
        $mus = $serializer->deserialize($data, 'App\Entity\UserMemberToSerialize[]', 'csv');
        $entityManager = $this->getDoctrine()->getManager();
        foreach ($mus as $mu) {
            $memberUser = $mu->createMemberUser($jobs);
            $entityManager->persist($memberUser);
        }
        $entityManager->flush(); //<-nazwiska zapisano do bazy
        // $response = new Response();
        // $response->setContent(count($mus));
        // return $response;
        return $this->redirectToRoute('member_user_index');
        
    }

   
    // public function serialize(MemberUserRepository $memberUserRepository): Response
    // {
    //     $users = $memberUserRepository->findAll();
    //     $encoders = [new XmlEncoder(), new JsonEncoder(), new CsvEncoder()];//XmlEncoder do usunięcia
    //     $normalizers = [new ObjectNormalizer()];
    //     $serializer = new Serializer($normalizers, $encoders);

    //     $memberUserToSerialize = array();
    //     foreach($users as $u)
    //     {
    //         $mts = new UserMemberToSerialize();
    //         $mts->setPropertiesFrom($u);
    //         $memberUserToSerialize[]=$mts;
    //     }
    //     $content = $serializer->serialize($memberUserToSerialize, 'csv',[
    //         'circular_reference_handler' => function ($object) {
    //             return $object->getId();
    //         }
    //     ]);
    //     $response = new Response();
    //     file_put_contents(
    //         '/home/mateusz/symfonyProjekt/aptekarze/var/data.csv',
    //         $content
    //     );
    //     $response->setContent($content);
    //     return $response;
    // }
    /**
     * @Route("/{id}", name="member_user_show", methods={"GET"})
     */
    public function show(MemberUser $memberUser): Response
    {
        $memberUser->KindOfHistoryChanges();
        //tu warto posortować w odwrotnej kolejności historię;
        return $this->render('member_user/show.html.twig', [
            'member_user' => $memberUser,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="member_user_edit", methods={"GET","POST"})
     * @Security("memberUser == user or is_granted('ROLE_ADMIN')", message="Brak uprawnień")
     */
    public function edit(Request $request, MemberUser $memberUser): Response
    {
        $form = $this->createForm(MemberUserType::class, $memberUser);
        $previousUserData = new MemberHistory($memberUser);
        $previousUserData->setWhoMadeChange($this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($previousUserData);
            $em->flush();

            return $this->redirectToRoute('member_user_show', [
                'id' => $memberUser->getId(),
            ]);
        }

        return $this->render('member_user/edit.html.twig', [
            'member_user' => $memberUser,
            'form' => $form->createView(),
        ]);
    }

     /**
     * @Route("/{id}/test", name="member_user_test", methods={"GET","POST"})
     */
    public function test(Request $request, MemberUser $memberUser): Response
    {
        $content = json_encode($memberUser);
        $response = new Response();
        $response->setContent($content);
        return $response;
    }

    /**
     * @Route("/{id}", name="member_user_delete", methods={"DELETE"})
     * @Security("is_granted('ROLE_ADMIN')")
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
