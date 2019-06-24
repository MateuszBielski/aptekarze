<?php

namespace App\Service;

use App\Repository\MemberUserRepository;
use App\Repository\MemberHistoryRepository;
use App\Repository\JobRepository;
use App\Repository\ContributionRepository;

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
        $this->usersList = array();
        $this->historyList = $this->memHistRep->findAll();
        $this->contrList = $this->contrRep->findAll();
        $jobsCollection = $this->jobRep->findAll();
        $usersCollection = $this->memUsRep->findAll();

        
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
}
