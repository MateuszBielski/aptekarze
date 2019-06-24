<?php

namespace App\Service;

use App\Repository\MemberUserRepository;
use App\Repository\MemberHistoryRepository;
use App\Repository\JobRepository;
use App\Repository\ContributionRepository;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;

class MemberUserOptimizer
{
    private $memUsRep;
    private $memHistRep;
    private $jobRep;
    private $contrRep;
    private $usersList = array();
    private $historyList;
    private $jobList;
    private $contrList;

    
    public function __construct(MemberUserRepository $mur, MemberHistoryRepository $mhr, JobRepository $jr, ContributionRepository $cr)
    {
        $this->memUsRep = $mur;
        $this->memHistRep = $mhr;
        $this->jobRep = $jr;
        $this->contrRep = $cr;

    }

    public function ReadRepositoriesAndCompleteCollections()
    {
        $this->historyList = $this->memHistRep->findAll();
        $this->contrList = $this->contrRep->findAll();
        $jobsCollection = $this->jobRep->findAll();
        $usersCollection = $this->memUsRep->findAll();

        $this->setJobHistoryContribution($usersCollection,$jobsCollection);
        
        
    }
    public function ReadRepositoriesAndCompleteCollectionsNarrow(string $str)
    {
        $usersCollection = $this->memUsRep->findByNamePortion($str);
        $usersIdList = array();
        foreach ($usersCollection as $us) {
            $usersIdList[] = $us->getId();
        }
        $this->historyList = $this->memHistRep->findByUserIdIn();
        $this->contrList = $this->contrRep->findByUserIdIn();
        $jobsCollection = $this->jobRep->findByUserIdIn();

        $this->setJobHistoryContribution($usersCollection,$jobsCollection);
    }
    private function setJobHistoryContribution(array $usersCollection,array $jobsCollection)
    {
        foreach($jobsCollection as $job)
        {
            $this->jobList[$job->getId()] = $job;
        }
        foreach($usersCollection as $us)
        {
            $id = $us->getId();
            $jobId = $us->getJob()->getId();
            $us->setMyJobRatedCached($this->jobList[$jobId]->getRate());
            $this->usersList[$id] = $us;
        }
        foreach($this->historyList as $h)
        {
            $jobId = $h->getJob()->getId();
            $h->setMyJobRatedCached($this->jobList[$jobId]->getRate());
            $this->usersList[$h->getMyUser()->getId()]->addMyHistoryDirectly($h);
        }
        foreach($this->contrList as $contr)
        {
            $usId = $contr->getMyUser()->getId();
            $this->usersList[$usId]->addContributionCached($contr);
        }
    }

    public function getUsersList()
    {
        return $this->usersList;
    }

    public function getfUserIdFromHistoryList()
    {
        $result = '';
        foreach ($this->historyList as $h) {
            $result .= ' '.$h->getMyUser()->getId();
        }
        return $result;
    }

    public function getUserListJson()
    {
        
    }
}
