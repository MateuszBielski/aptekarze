<?php

namespace App\Service;

use App\Repository\MemberHistoryRepository;
use App\Repository\MemberUserRepository;
use App\Repository\JobRepository;
use App\Entity\Job;

class RetrieveOldNewRateJunctions
{
    private $memHistRep;
    private $memUsRep;
    private $jobRep;
    private $oldJob;
    private $newJob;
    private $memberHistoryWithOldJob;

    public function __construct(MemberUserRepository $mur, MemberHistoryRepository $mhr, JobRepository $jobRep)
    {
        $this->memHistRep = $mhr;
        $this->memUsRep = $mur;
        $this->jobRep = $jobRep;
    }

    public function IsAvaliableCancelUpdateRateFor(Job $jobToCancel)
    {
        
        //musi być jedno stanowisko, które ma moje id w kolumnie replacedBy
        //wśród uzytkowników wyszukać styki historii stare job_id/nowe_id muszą to być ostatnie styki, tzn później nie może być już zmiany
        //lub wszystkie historyczne z nową stawką Job muszą mieć jednkową datę, nie ma sensu cofać zmiany stawki, jeśli ktoś już ma celowo wybraną później,
        //zapewne omyłkowa zmiana stawki cofana jest tego samego dnia

        
        //propozycja klasy RetrieveOldNewRateJunctions 
        $result = true;
        $oldJob = $this->GetUniqueReplacedOldJobFor($jobToCancel);
        if($oldJob == 0)return false;

        return $result;
    }
    public function GetUniqueReplacedOldJobFor(Job $jobToCancel)
    {
        $jobsActiveAndUnActive = $this->jobRep->findAll();
        $num = 0;
        
        foreach($jobsActiveAndUnActive as $job)
        {
            if ($job->getReplacedBy() == null) continue;
            if ($job->getReplacedBy() == $jobToCancel) {
                $num++;
                $uniqueOld = $job;
            }
        }
        if($num != 1)return 0;
        return $uniqueOld; 
    }

    public function getHistoryRecordsWithJobAsNext(Job $replacedJob,Job $canceledJob)
    {
        //MemHisRep::metoda, która zwróci wpisy tylko z tym jobem
        //MemUsRep::metoda zwróci użtkowników z zakresu wynikającego z poprzedniej linii
        $historyWithReplacedJob = $this->memHistRep->findWithThisJob($replacedJob);
        $memberIds_OfHistoryReplacedJob = array();
        foreach($historyWithReplacedJob as $rec)
        {
            $memberIds_OfHistoryReplacedJob[] = $rec->getMyUser()->getId();
        }
        $member_OfHistoryCanceledJob = $this->memUsRep->findWithJobInRange($canceledJob, $memberIds_OfHistoryReplacedJob);
        return $member_OfHistoryCanceledJob;
    }
}