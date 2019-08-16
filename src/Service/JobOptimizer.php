<?php

namespace App\Service;

use App\Repository\MemberUserRepository;
use App\Entity\Job;
use App\Repository\MemberHistoryRepository;
use App\Repository\JobRepository;
use App\Repository\ContributionRepository;

class JobOptimizer
{
    private $memUsRep;
    private $memHistRep;
    private $jobRep;
    private $contrRep;
    private $usersList;
    private $historyList;
    private $jobList;
    private $contrList;

    
    public function __construct(MemberUserRepository $mur)//, MemberHistoryRepository $mhr, JobRepository $jr, ContributionRepository $cr
    {
        $this->memUsRep = $mur;
        // $this->memHistRep = $mhr;
        // $this->jobRep = $jr;
        // $this->contrRep = $cr;

    }

    public function getMembersNowOn(Job $job)
    {
        return $this->memUsRep->findBy(['job'=> $job->getId(),]);
    }

    public function ReplaceOldByNewInAdequateUsers(Job $old, Job $new)
    {
        $usersToUpdate = $this->getMembersNowOn($old);
        $old->setReplacedBy($new);
        foreach($usersToUpdate as $user){
            $user->InsertJobAsChange($new);
        }
    }
}