<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\MemberHistory;
use App\Entity\MemberUser;
use App\Entity\Job;
use App\Entity\ArchiveJob;
use Doctrine\Common\Collections\Collection;

class MemberHistoryTest extends TestCase
{
    public function test_1_DateRoundToMonthAccordingToDayOfChange()
    {
        $mu = new MemberUser();
        $mu->CreateDummyData();
        $mh = new MemberHistory($mu);
        $mh->setDate(new \DateTime('2003-05-15'));
       
        $this->assertEquals(new \DateTime('2003-05-01'), $mh->getDateRoundToMonthAccordingToDayOfChange());
    }

    public function test_2_DateRoundToMonthAccordingToDayOfChange()
    {
        $mu = new MemberUser();
        $mu->CreateDummyData();
        $mh = new MemberHistory($mu);
        $mh->setDate(new \DateTime('2003-05-16'));
       
        $this->assertEquals(new \DateTime('2003-06-01'), $mh->getDateRoundToMonthAccordingToDayOfChange());
    }

    public function testAddMyArchievedRatesToCollection()
    {
        $mu = new MemberUser();
        $mu->CreateDummyData();

        $newJob0 = new Job();
        $newJob0->setRate(24);
        $mu->setJob($newJob0);

        $mh = new MemberHistory($mu);
        $mh->setDate(new \DateTime('2000-01-03'));
        $mu->addMyHistory($mh);//pierwszy rekord jako data rejestracji
        
        //zmiana stanowiska z zapisem historii
        $mh1 = new MemberHistory($mu);
        $mh1->setDate(new \DateTime('2000-05-04'));
        $newJob1 = new Job();
        $newJob1->setRate(12);
        $mu->addMyHistory($mh1);
        $mu->setJob($newJob1);

        $newArchiveJob = new ArchiveJob($newJob1);

        //zmiana stanowiska z zapisem historii
        $mh2 = new MemberHistory($mu);
        $mh2->setDate(new \DateTime('2000-08-06'));
        $newJob2 = new Job();
        $newJob2->setRate(21);
        $mu->addMyHistory($mh2);
        $mu->setJob($newJob2);

        $numberOfHistoryRecords = count($mu->getMyHistory());
        $this->assertEquals(3,$numberOfHistoryRecords);
    }

    public function testGetRegistrationDate_NoInitialHistory()
    {
        $mu = new MemberUser();
        $mu->CreateDummyData();

        $newJob0 = new Job();
        $newJob0->setRate(24);
        $mu->setJob($newJob0);
        $result = new \DateTime('now');
        $this->assertEquals($result->format('d.m.Y'),$mu->getRegistrationDate()->format('d.m.Y'));
    }

    public function testAddMyJobHistory()
    {
        $mu = new MemberUser();
        $mu->CreateDummyData();

        $job1 = new Job();
        $job1->setRate(1);

        $mu->setJob($job1);
        $muRegistration = new MemberHistory($mu);
        $muRegistration->setDate(new \DateTime('2013-04-05'));

        $mu->addMyHistory($muRegistration);
        $this->assertEquals('01',$this->GenerateStrigFromRatesOfArray($mu->getMyHistory()));

        $job2 = new Job();
        $job3 = new Job();

        $job2->setRate(2);
        $job3->setRate(3);

        $h2 = new MemberHistory($mu);
        $h2->setDate(new \DateTime('2012-03-07'));
        $h2->setJob($job2);

        $mu->addMyJobHistory($h2);
        $this->assertEquals('021',$this->GenerateStrigFromRatesOfArray($mu->getMyHistory()));
    }

    private function GenerateStrigFromRatesOfArray(Collection $history)
    {
        $result = '0';
        foreach($history as $h)
        {
            $result .= $h->getJob()->getRate();
        }

        return $result;
    }
}
