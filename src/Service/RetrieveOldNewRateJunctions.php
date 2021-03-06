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
    private $junctionsOldNew;
    private $oldJob;
    private $newJob;
    private $memberHistoryWithOldJob;

    public function __construct(MemberUserRepository $mur, MemberHistoryRepository $mhr, JobRepository $jobRep)
    {
        $this->memHistRep = $mhr;
        $this->memUsRep = $mur;
        $this->jobRep = $jobRep;
    }

    public function AfterProcess_IsAvaliableCancelUpdateRateFor(Job $jobToCancel)
    {
        
        //musi być jedno stanowisko, które ma moje id w kolumnie replacedBy
        //wśród uzytkowników wyszukać styki historii stare job_id/nowe_id muszą to być ostatnie styki, tzn później nie może być już zmiany
        //lub wszystkie historyczne z nową stawką Job muszą mieć jednkową datę, nie ma sensu cofać zmiany stawki, jeśli ktoś już ma celowo wybraną później,
        //zapewne omyłkowa zmiana stawki cofana jest tego samego dnia

        
        //propozycja klasy RetrieveOldNewRateJunctions 
        $result = true;
        $oldJob = $this->GetUniqueReplacedOldJobFor($jobToCancel);
        if($oldJob == 0)return false;

        $this->junctionsOldNew = $this->RetrieveJunctions($oldJob,$jobToCancel);

        if(count($this->junctionsOldNew['not last']))return false;
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

    public function RetrieveJunctions(Job $replacedJob,Job $canceledJob)
    {
        $historyWithReplacedJob = $this->memHistRep->findWithThisJob($replacedJob);
        $memberIds_OfHistoryReplacedJob = array();
        foreach($historyWithReplacedJob as $rec)
        {
            $memberIds_OfHistoryReplacedJob[] = $rec->getMyUser()->getId();
        }
        $memberIds_OfHistoryReplacedJob = array_unique($memberIds_OfHistoryReplacedJob);
        $memberWithReplacedInHistoryAndCanceledAsLast = $this->memUsRep->findWithJobInRangeIndexedById($canceledJob, $memberIds_OfHistoryReplacedJob);
        $this->CompleteHistoryFor($memberWithReplacedInHistoryAndCanceledAsLast, $memberIds_OfHistoryReplacedJob);
        
        foreach($memberWithReplacedInHistoryAndCanceledAsLast as $mem){
            $history = $mem->getMyHistoryCached();
            $historyCount = count($history);
            for($i = 0 ; $i < $historyCount-1 ; $i++){
                if($history[$i]->getJob() == $replacedJob && 
                $history[$i + 1]->getJob() == $canceledJob)
                $this->junctionsOldNew['not last'][] = $history[$i];
            }

            $lastHistory = $history[$historyCount - 1];
            if($lastHistory->getJob() == $replacedJob && $mem->getJob() == $canceledJob){
                $this->junctionsOldNew['last'][] = $lastHistory;
            }
        }
       
        // return $memberWithReplacedInHistoryAndCanceledAsLast;
    }
    public function getHistoryRecordsWithJobAsNextAndLast()
    {
        return $this->junctionsOldNew['last'];
    }
    public function CompleteHistoryFor(array $members, array $member_ids )
    {
        $historyToDistribute = $this->memHistRep->findByUserIdIn($member_ids);
        foreach($historyToDistribute as $h)
        {
            $memberId = $h->getMyUser()->getId();
            $members[$memberId]->addMyHistoryDirectly($h);
        }
        foreach ($members as $member) {
            $member->setOptimizedTrue();//zapobiega czytaniu oddzielnie historii z bazy
            $member->SortMyHistoryCached();
        }
        // ;
        //KindOfHistoryChanges lub inaczej poszukać styków
    }
}