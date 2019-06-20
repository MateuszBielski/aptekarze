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
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\JobRepository;

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

    
    public function deserialize(JobRepository $jobRepository): Response
    {
        $jobs = array();
        foreach ($jobRepository->findAll() as $job) {
            $jobs[$job->getRate()] = $job;
        }
        $encoders = [new CsvEncoder()];//new XmlEncoder(), new JsonEncoder(), 
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $file = '/home/mateusz/symfonyProjekt/aptekarze/var/dataDeserialize.csv';
        $data = file_get_contents($file);
        $mus = array();
        $mus = $serializer->deserialize($data, UserMemberToSerialize::class, 'csv');
        $entityManager = $this->getDoctrine()->getManager();
        // foreach ($mus as $mu) {
            $memberUser = $mus->createMemberUser($jobs);
            $entityManager->persist($memberUser);
        // }
        // $content = 'tablica $mus zawiera '.count($mus).' obiektów';
        $entityManager->flush();
        $response = new Response();
        $response->setContent(gettype($mus));
        $newHistory = new MemberHistory($mus);
        return $response;
        //return $this->redirectToRoute('member_user_index');
        /*
        1,87654321,a@b,Jan,Kowalski5,20,
1,98765432,a@b,imię,nazwisko6,20,
1,888031726,mateo.bass@interia.pl,Mateusz,Bielski23,20,
1,888031726,mateo.bass@interia.pl,Mateusz3,Bielski34,20,
22,888031726,mateo.bass@interia.pl,Mateusz4,Bielski45,20,
       */
    }

   
    public function serialize(MemberUserRepository $memberUserRepository): Response
    {
        $users = $memberUserRepository->findAll();
        $encoders = [new XmlEncoder(), new JsonEncoder(), new CsvEncoder()];//XmlEncoder do usunięcia
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $memberUserToSerialize = array();
        foreach($users as $u)
        {
            $mts = new UserMemberToSerialize();
            $mts->setPropertiesFrom($u);
            $memberUserToSerialize[]=$mts;
        }
        $content = $serializer->serialize($memberUserToSerialize, 'csv',[
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        $response = new Response();
        file_put_contents(
            '/home/mateusz/symfonyProjekt/aptekarze/var/data.csv',
            $content
        );
        $response->setContent($content);
        return $response;
    }
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
