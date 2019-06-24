<?php

namespace App\Service;

use App\Repository\MemberUserRepository;
use App\Repository\MemberHistoryRepository;
use App\Repository\JobRepository;
use App\Repository\ContributionRepository;
// use Symfony\Component\Serializer\Encoder\CsvEncoder;
// use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
// use Symfony\Component\Serializer\Serializer;
// use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;

class MemberUserOptimizer
{
    private $memUsRep;
    private $memHistRep;
    private $jobRep;
    private $contrRep;
    private $usersList;
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
        $this->historyList = $this->memHistRep->findAllIndexedById();
        $this->contrList = $this->contrRep->findAllIndexedById();
        $this->jobList = $this->jobRep->findAllIndexedById();
        $this->usersList = $this->memUsRep->findAllIndexedById();

        $this->setJobHistoryContribution();
        
        
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

        $this->setJobHistoryContribution();
    }
    private function setJobHistoryContribution()
    {
        
        foreach($this->usersList as $us)
        {
            $jobId = $us->getJob()->getId();
            $us->setMyJobRateCached($this->jobList[$jobId]->getRate());
        }
        foreach($this->historyList as $h)
        {
            $jobId = $h->getJob()->getId();
            $h->setMyJobRateCached($this->jobList[$jobId]->getRate());
            $this->usersList[$h->getMyUser()->getId()]->addMyHistoryDirectly($h);
        }
        foreach($this->contrList as $contr)
        {
            $usId = $contr->getMyUser()->getId();
            $this->usersList[$usId]->addContributionCached($contr);
        }
    }

    public function ReadUsersIndexed()
    {
        $this->usersList = $this->memUsRep->findAllIndexedById();//
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
