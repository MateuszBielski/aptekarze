<?php

namespace App\Controller;

use App\Entity\MemberUser;
use App\Entity\MemberHistory;
use App\Entity\UserMemberToSerialize;
use App\Form\MemberUserType;
use App\Form\MemberUserExtendType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use App\Repository\JobRepository;
use App\Service\MemberUserOptimizer;
use App\Form\MemberHistoryJobAndDateType;


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
        $memUsOptim->CalculateAndSetCurrentAccounts();
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
        $memUsOptim->CalculateAndSetCurrentAccounts();
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
        $memberUser->GenerateMonthsReckoning();
        //tu warto posortować w odwrotnej kolejności historię;
        return $this->render('member_user/show.html.twig', [
            'member_user' => $memberUser,
        ]);
    }

    private function edit(Request $request, MemberUser $memberUser, MemberHistory $previousUserData = null): Response
    {
        $form = $this->createForm(MemberUserType::class, $memberUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $memberUser->ArchiveChanges($previousUserData);
            $em->persist($memberUser);
            $em->flush();

            return $this->redirectToRoute('member_user_show', [
                'id' => $memberUser->getId(),
            ]);
        }
        $memberUser->GenerateMonthsReckoning();

        return $this->render('member_user/edit.html.twig', [
            'member_user' => $memberUser,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/setRight", name="member_user_setRight", methods={"GET","POST"})
     */
    public function setRight(Request $request, MemberUser $memberUser)
    {
        //poprawia dane (również w historii, ale nie dodaje historii)
        return $this->edit($request,$memberUser);
    }

    /**
    * @Route("/{id}/change", name="member_user_change", methods={"GET","POST"})
    */
    public function Change(Request $request, MemberUser $memberUser)
    {
        //nie zmienia historii, lecz dodaje nową
        $previousUserData = new MemberHistory($memberUser);
        $previousUserData->setWhoMadeChange($this->getUser());
        return $this->edit($request,$memberUser,$previousUserData);
    }
    /**
    *@Route("/{id}/table_months_ajax", name="table_months_ajax", methods={"GET","POST"})
    */
    public function tableMonthsAjax(Request $request, MemberUser $memberUser,JobRepository $jobRepository): Response
    {
        $memberUser->setInitialAccount($request->query->get("initialAccount"));
        $Y = $request->query->get('year');
        $m = $request->query->get('month');
        $d = $request->query->get('day');
        $date = new \DateTime("$Y-$m-$d");
        $job = $jobRepository->find($request->query->get('job'));
        $memberUser->setBeginDate($date);
        $memberUser->setJob($job);
        $memberUser->ArchiveChanges();//tu jest symulacja zmiany pierwszego wpisu historii
        $memberUser->GenerateMonthsReckoning();
        return $this->render('member_user/_monthsReckoning.html.twig', [
            'member_user' => $memberUser,
        ]);
    }

    /**
     * @Route("/{id}/addJobHistory", name="member_user_addJobHistory", methods={"GET","POST"})
     */
    public function addJobHistory(Request $request, MemberUser $memberUser): Response
    {
        # code...
        //nowy MemberHistory 
        //ustawić wstępnie datę na dziś
        //wyswietlić formularz member history okrojony tylko do joba i daty
        //sprawdzić co z pierwszym wpisem (,żeby nie był poźniejszy niż ta zmiana)
        //ten history ma mieć joba wcześniejszego niż aktualny i tak też należy zmienić pierwszy wpis
        //najlepiej pierwszy ustawić na nowo z datą równą dacie początkowej
        //id  w template edit delete warunkowo, bo przecież na początku nie ma id
        //
        $changeJob = new MemberHistory($memberUser);
        
        $form = $this->createForm(MemberHistoryJobAndDateType::class, $changeJob);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $memberUser->ArchiveChanges($changeJob);//<- tu musi być inna funkcja
            $em->persist($memberUser);
            $em->flush();

            return $this->redirectToRoute('member_user_show', [
                'id' => $memberUser->getId(),
            ]);
        }
        return $this->render('member_history/edit.html.twig', [
            'member_history' => $changeJob,
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
