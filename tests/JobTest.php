<?php

namespace App\Tests;

use App\Entity\Job;
use App\Entity\MemberUser;
use App\Service\RetrieveOldNewRateJunctions;
use App\Repository\MemberUserRepository;
use App\Repository\JobRepository;
use App\Repository\MemberHistoryRepository;
use PHPUnit\Framework\TestCase;

class JobTest extends TestCase
{
    public function testInsertJobAsChange()
    {
        $mu = new MemberUser();
        $mu->CreateDummyData();
        $job = new Job();
        $mu->setJob($job);
        $mu->getJob()->setRate(12.0);
        $newJob = new Job();
        $newJob->setRate(23);
        $mu->InsertJobAsChange($newJob);
        // $this->assertEquals(1,count($mu->getMyHistory()));
        $this->assertEquals(12,$mu->getMyHistory()->last()->getJob()->getRate());
    }
   public function testGetUniqueReplacedOldJobFor()
   {
       
        $jobs = array();
        for($i = 0 ; $i < 5 ; $i++){
            $jobs[$i] = new Job();
        }
        $replacedJob = $jobs[2];
        $updatedJob = new Job();
        $replacedJob->setReplacedBy($updatedJob);
        $jobs[] = $updatedJob;
        $muRep = $this->createMock(MemberUserRepository::class);
        $mhRep = $this->createMock(MemberHistoryRepository::class);
        $jobRep = $this->createMock(JobRepository::class);
        $jobRep->expects($this->any())
            ->method('findAll')
            ->willReturn($jobs);
        $retrieveRateJunctions = new RetrieveOldNewRateJunctions($muRep,$mhRep,$jobRep);
        $this->assertEquals($replacedJob,$retrieveRateJunctions->GetUniqueReplacedOldJobFor($updatedJob));
   }

   public function testIsAvaliableCancelUpdateRate()
   {
        //musi być jedno stanowisko, które ma moje id w kolumnie replacedBy
        //wśród uzytkowników wyszukać styki historii stare job_id/nowe_id muszą to być ostatnie styki, tzn później nie może być już zmiany
        //lub wszystkie historyczne z nową stawką Job muszą mieć jednkową datę, nie ma sensu cofać zmiany stawki, jeśli ktoś już ma celowo wybraną później,
        //zapewne omyłkowa zmiana stawki cofana jest tego samego dnia


        //testowanie:
        //przygotować job na którym następnie dokonać update, a także użytkowników, którzy mają ten Job    
    // $this->assertTrue($retrieveRateJunctions->IsAvaliableCancelUpdateRateFor($updatedJob));
   }
   public function testRetrieveJunctions()
   {
       
   }
   

   protected function createMembersWithJob(Job $job, int $number)
   {
       $members = array();
       for($i = 0 ; $i < $number ; $i++)
       {
           $mu = new MemberUser();
           $mu->CreateDummyData();
           $mu->setJob($job);
           $members[$i] = $mu;
       }
       return $members;
   }
   protected function createJobSetAfterUpdate(Job $updatedJob)
   {
       
       
       return $jobs;
   }
}
