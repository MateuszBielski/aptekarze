<?php

namespace App\Controller;

use App\Entity\Job;
use App\Service\RetrieveOldNewRateJunctions;
use App\Form\JobType;
use App\Repository\JobRepository;
use App\Repository\ActiveJobRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Serializer;
use App\Form\JobChangeRateType;
use App\Service\JobOptimizer;

/**
 * @Route("/job")
 */
class JobController extends AbstractController
{
    /**
     * @Route("/", name="job_index", methods={"GET"})
     */
    public function index(ActiveJobRepository $jobRepository): Response
    {
        return $this->render('job/index.html.twig', [
            'jobs' => $jobRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="job_new", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function new(Request $request): Response
    {
        $job = new Job();
        $form = $this->createForm(JobType::class, $job);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($job);
            $entityManager->flush();

            return $this->redirectToRoute('job_index');
        }

        return $this->render('job/new.html.twig', [
            'job' => $job,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/zpliku", name="job_zpliku", methods={"GET", "POST"})
     */
    public function deserialize(): Response //JobRepository $jobRepository
    {
        $encoders = [new CsvEncoder()];//XmlEncoder do usunięcia
        $normalizers = [new ObjectNormalizer(),new ArrayDenormalizer()];// 
        $serializer = new Serializer($normalizers, $encoders);
        $file = '/home/mateusz/symfonyProjekt/aptekarze/var/dataJobDeserialize.csv';
        $data = file_get_contents($file);
        $jobs = $serializer->deserialize($data,'App\Entity\Job[]', 'csv');
        $entityManager = $this->getDoctrine()->getManager();
        foreach ($jobs as $job) {
            $entityManager->persist($job);
        }
        $entityManager->flush();
        return $this->redirectToRoute('job_index');
    }

    /**
     * @Route("/serialize", name="job_serialize", methods={"GET", "POST"})
     */
    public function serialize(JobRepository $jobRepository): Response
    {
        $jobs = $jobRepository->findAll();
        $encoders = [new CsvEncoder()]; 
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $content = $serializer->serialize($jobs, 'csv',[
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        $response = new Response();
        file_put_contents(
            '/home/mateusz/symfonyProjekt/aptekarze/var/dataJobSerialize.csv',
            $content
        );
        $response->setContent($content);
        return $response;
    }

    /**
     * @Route("/{id}/serialize", name="job_id_serialize", methods={"GET", "POST"})
     */
    public function serializeId(Job $job): Response
    {
        $encoders = [new CsvEncoder()];//new XmlEncoder(), new JsonEncoder(), 
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $content = $serializer->serialize($job, 'csv',[
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        $response = new Response();
        file_put_contents(
            '/home/mateusz/symfonyProjekt/aptekarze/var/dataJobSerializeId.csv',
            $content
        );
        $response->setContent($content);
        return $response;
    }

    /**
     * @Route("/{id}", name="job_show", methods={"GET"})
     */
    public function show(Job $job): Response
    {
        return $this->render('job/show.html.twig', [
            'job' => $job,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="job_edit", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function edit(Request $request, Job $job): Response
    {
        // $archiveJob = new ArchiveJob($job);
        // $job->addArchiveJob($archiveJob);
        $form = $this->createForm(JobType::class, $job);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('job_index', [
                'id' => $job->getId(),
            ]);
        }

        return $this->render('job/edit.html.twig', [
            'job' => $job,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/updateRate", name="job_update_rate", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function UpdateRate(Request $request, Job $oldJob, JobOptimizer $jo)// 
    {
        $newJob = new Job($oldJob);
        $form = $this->createForm(JobChangeRateType::class,$newJob);
        $form->handleRequest($request);
        

        if ($form->isSubmitted() && $form->isValid()) {
            if($newJob->getRate() == $oldJob->getRate()){
                return $this->redirectToRoute('job_show', [
                    'id' => $oldJob->getId(),
                ]);
            }
            $jo->ReplaceOldByNewInAdequateUsers($oldJob,$newJob);
            
            $entityManager = $this->getDoctrine()->getManager();
            //dziwne, że nie trzeba robić persist
            $entityManager->flush();

            return $this->redirectToRoute('job_index');
        }
        return $this->render('job/new.html.twig', [
            'title' => "zmiana wysokości stawki",
            'job' => $newJob,
            'form' => $form->createView(),
        ]);
        //w nazwie dodać nieaktualn od (data)
    }

    /**
     * @Route("/{id}/cancelUpdateRate", name="job_cancel_update_rate", methods={"GET", "POST"})
     */
    public function CancelUpdateRate(Job $jobCanceled, RetrieveOldNewRateJunctions $retrJun)
    {
        // if(!$retrJun->AfterProcess_IsAvaliableCancelUpdateRateFor($job)) return $this->redirectToRoute('job_index');
        $jobToRestore = $retrJun->GetUniqueReplacedOldJobFor($jobCanceled);
        $retrJun->RetrieveJunctions($jobToRestore, $jobCanceled);
        $memberHistoriesToDelete = $retrJun->getHistoryRecordsWithJobAsNextAndLast();
        // $restoredJob = $retrJun->getJobReplacedByCanceled();
        // $restoredJob->setReplacedBy(null);
        //znaleźć wszystkie wpisy historyczne, gdzie jako następny występuje wycofywany job
        //zrobić usunięcie ich, powinna już być odpowiednia funkcja
        //znaleźć stary job
        //usunąć interesujący job,
        //a w starym usunąć replacedBy
        //----------ponowna definicja--------------
        //przywrócić job tym, którzy mieli go jako bieżący w czasie zmiany

        //może przenieść IsAvaliableCancelUpdateRate do klasy jobOptimizer lub podobnej
        // return $this->render('showTemporaryVariables.html.twig', [
        //     'variables' => $memberHistoriesToDelete
        // ]);
        // return $this->render('member_user/index.html.twig', [
        //     'member_users' => $memberHistoriesToDelete,
        // ]);
        return $this->render('member_history/index.html.twig', [
            'member_histories' => $memberHistoriesToDelete,
        ]);
    }
    
    /**
     * @Route("/{id}", name="job_delete", methods={"DELETE"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function delete(Request $request, Job $job): Response
    {
        if ($this->isCsrfTokenValid('delete'.$job->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($job);
            $entityManager->flush();
        }

        return $this->redirectToRoute('job_index');
    }
}
