<?php

namespace App\Entity;

use App\Repository\MemberHistoryRepository;
use App\Repository\MemberUserRepository;
use App\Entity\Job;

class RetrieveOldNewRateJunctions
{
    private $memHistRep;
    private $memUsRep;
    private $oldJob;
    private $newJob;
    private $memberHistoryWithOldJob;

    public function __construct(MemberUserRepository $mur, MemberHistoryRepository $mhr)
    {
        $this->memHistRep = $mhr;
        $this->memUsRep = $mur;
    }

    public function TakeOldNewJobAndInit(Job $oldJob, Job $newJob)
    {
        $this->memberHistoryWithOldJob = $this->memHistRep->findWithThisJob($oldJob);
        
    }
}