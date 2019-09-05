<?php

namespace App\Tests;

use App\Entity\Job;
use App\Entity\MemberUser;
use App\Entity\MemberUserToTest;
use App\Entity\MemberHistory;
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

   public function testCompleteHistoryFor()
   {
        $memNum = 324;
        $members = $this->CreateMembersToTest($memNum);
        $numberHistory = 563;
        $historyToDistribute = $this->CreateRandomHistoryFor($members,$numberHistory,"Jan 01 2012","Nov 01 2016");
        $mhRep = $this->createMock(MemberHistoryRepository::class);
        $mhRep->expects($this->any())
        ->method('findByUserIdIn')
        ->willReturn($historyToDistribute);
        
        $muRep = $this->createMock(MemberUserRepository::class);
        $jobRep = $this->createMock(JobRepository::class);
        
        $retrieveRateJunctions = new RetrieveOldNewRateJunctions($muRep,$mhRep,$jobRep);
        
        $memberIndex = rand(0,$memNum - 1);
        $randomMember = $members[$memberIndex];
        $memberHistory = $randomMember->getMyHistoryCached();
        $this->assertEquals(0,count($memberHistory));

        $retrieveRateJunctions->CompleteHistoryFor($members,range(0,$memNum - 1));
        
        $historyAmount = 0;
        while(!$historyAmount)
        {
            $randomMember = $members[$memberIndex];
            $memberHistory = $randomMember->getMyHistoryCached();
            $historyAmount = count($memberHistory);
            $memberIndex = ($memberIndex < $memNum - 1) ? $memberIndex + 1 : $memberIndex - 1;
        }
        
        $randomHistory = $memberHistory[rand(0,count($memberHistory) - 1)];

        $this->assertEquals($randomMember->getId(),$randomHistory->getMyUser()->getId());
        $this->assertEquals($randomMember,$randomHistory->getMyUser());
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
        // $numHistory = 10;
        // $newJob = new Job();
        // $newJob->setRate(32.1);
        // $oldJob = new Job();
        // $oldJob->setRate(13.5);
        // $oldJob->setReplacedBy($newJob);

        // $user_1 = 
        // for($i = 0 ; $i < $numHistory ; $i++)
        // {
        //     $memberHistory = new MemberHistory()
        //     $historyWithReplacedJob[] = $memberHistory;
        // }

        // //MemberHistoryRepositoryMock
        // $mhRep = $this->createMock(MemberHistoryRepository::class);
        // $mhRep->expects($this->any()
        //     ->method('findWithThisJob')
        //     ->willReturn($historyWithReplacedJob);

        // //  = $this->memHistRep->findWithThisJob($replacedJob);
        // $muRep = $this->createMock(MemberUserRepository::class);
        // $jobRep = $this->createMock(JobRepository::class);
        // $retrieveRateJunctions = new RetrieveOldNewRateJunctions($muRep,$mhRep,$jobRep);    
        // $retrieveRateJunctions->RetrieveJunctions(Job $oldJob,Job $newJob);
        // $historyRecords = $retrieveRateJunctions->getHistoryRecordsWithJobAsNextAndLast();
        // $result = $historyRecords[rand(0,$num)]->getJob()->getRate();
        // $this->assertEquals();
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
   protected function CreateMembersToTest(int $number)
   {
       $members = array();
       for($i = 0 ; $i < $number ; $i++)
       {
           $mu = new MemberUserToTest();
           $mu->CreateDummyData();
           $mu->setId($i);
        //    $mu->setJob($job);
           $members[$i] = $mu;
       }
       return $members;
   }
   protected function CreateRandomHistoryFor(array $members,int $numberHistory, string $firstDate, string $lastDate)
   {
        $histories = array();
        $numberMembers = count($members);
        for($i = 0 ; $i < $numberHistory ; $i++){
            $memberRandIndex = rand(0,$numberMembers - 1);
            $mu = $members[$memberRandIndex];
            $h = new MemberHistory($mu);
            
            $timestamp = rand( strtotime($firstDate), strtotime($lastDate) );
            // $h->setDate(strtotime($firstDate));
            $h->setDate(new \DateTime(date("d.m.Y", $timestamp )));
            $histories[] = $h;
        }
        return $histories;
   }

   protected function AmountNotEmptyHistories(array $members)
   {
       $result = 0;
       foreach ($members as $mu) {
           if( count( $mu->getMyHistoryCached() ) ) $result++;
       }
       return $result;
   }
   protected function createJobSetAfterUpdate(Job $updatedJob)
   {
       
       
       return $jobs;
   }
}
