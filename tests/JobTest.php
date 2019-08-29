<?php

namespace App\Tests;

use App\Entity\Job;
use App\Entity\MemberUser;
use App\Repository\JobRepository;
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
   public function testIsAvaliableCancelUpdateRate()
   {
       //musi być jedno stanowisko, które ma moje id w kolumnie replacedBy
        //wśród uzytkowników wyszukać styki historii stare job_id/nowe_id muszą to być ostatnie styki, tzn później nie może być już zmiany
        //lub wszystkie historyczne z nową stawką Job muszą mieć jednkową datę, nie ma sensu cofać zmiany stawki, jeśli ktoś już ma celowo wybraną później,
        //zapewne omyłkowa zmiana stawki cofana jest tego samego dnia


        //testowanie:
        //przygotować job na którym następnie dokonać update, a także użytkowników, którzy mają ten Job
        $jobsActiveAndReplaced = $this->createJobSetAfterUpdate();
        $this->assertEquals(3,$jobsActiveAndReplaced[3]->getId());
        $this->assertEquals(24.5,$jobsActiveAndReplaced[3]->getRate());
        $this->assertTrue($jobsActiveAndReplaced[3]->IsAvaliableCancelUpdateRate($jobsActiveAndReplaced));
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
   protected function createJobSetAfterUpdate()
   {
       $jobs = array();
       for($i = 1 ; $i < 5 ; $i++){
            $job = $this->createMock(Job::class);
            $job->expects($this->any())
                ->method('getId')
                ->willReturn($i);
            $job->expects($this->any())
            ->method('getRate')
            ->willReturn(24.5);
            $jobs[$i] = $job;
       }
       return $jobs;
   }
}
