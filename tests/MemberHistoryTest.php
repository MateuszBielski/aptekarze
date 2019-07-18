<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\MemberHistory;
use App\Entity\MemberUser;
use App\Entity\Job;
use App\Entity\ArchiveJob;

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

    public function testSetRegistrationDate_No_InitialHistory()
    {
        $mu = new MemberUser();
        $mu->CreateDummyData();

        $newJob0 = new Job();
        $newJob0->setRate(24);
        $mu->setJob($newJob0);
        $mu->createIfneededAndSetRegistrationDate(new \DateTime('2003-12-04'));
        $this->assertEquals('04.12.2003',$mu->getRegistrationDate()->format('d.m.Y'));
    }

    public function testSetRegistrationDate_With_InitialHistory()
    {
        $mu = new MemberUser();
        $mu->CreateDummyData();

        $newJob0 = new Job();
        $newJob0->setRate(24);
        $mu->setJob($newJob0);

        $mh2 = new MemberHistory($mu);
        $mh2->setDate(new \DateTime('2000-08-06'));
        $mu->addMyHistory($mh2);

        $mu->createIfneededAndSetRegistrationDate(new \DateTime('2003-12-04'));
        $this->assertEquals('04.12.2003',$mu->getRegistrationDate()->format('d.m.Y'));
    
    }
}
