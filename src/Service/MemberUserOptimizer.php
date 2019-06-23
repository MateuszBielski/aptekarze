<?php

namespace App\Service;

use App\Repository\MemberUserRepository;
use App\Repository\MemberHistoryRepository;
use App\Repository\JobRepository;

class MemberUserOptimizer
{
    private $memUsRep;
    private $memHistRep;
    private $jobRep;
    private $usersList;
    private $historyList;
    private $jobList;

    
    public function __construct(MemberUserRepository $mur, MemberHistoryRepository $mhr, JobRepository $jr)
    {
        $this->memUsRep = $mur;
        $this->memHistRep = $mhr;
        $this->jobRep = $jr;
    }

    public function ReadRepositoriesAndCompleteCollections()
    {
        $this->usersList = array();
        $this->historyList = $this->memHistRep->findAll();
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
