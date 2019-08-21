<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\MemberHistory;
use App\Entity\MemberUser;
use App\Entity\Job;
use App\Entity\ArchiveJob;
use DateTime;
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
        $muRegistration = $this->GenerateFilledHistory('2013-04-05',1.0,$mu);
        $mu->addMyHistory($muRegistration);
        
        
        
        $h2 = $this->GenerateFilledHistory('2012-03-07',2.0,$mu);
        $h3 = $this->GenerateFilledHistory('2011-03-07',3.0,$mu);

        
        
        
        //testowana funkcja dokonuje podmiany danych z sąsiadującym wpisem lub nie dokonuje podmiany, należy właśnie to sprawdzić.
        $mu->addMyJobHistory($h2);
        $this->assertEquals(2,$muRegistration->getJob()->getRate());
        $this->assertEquals(2,$mu->getJob()->getRate());
        $this->assertEquals(1,$h2->getJob()->getRate());

        $mu->addMyJobHistory($h3);
        $this->assertEquals(2,$muRegistration->getJob()->getRate());
        $this->assertEquals(2,$mu->getJob()->getRate());
        $this->assertEquals(3,$h2->getJob()->getRate());
        $this->assertEquals(1,$h3->getJob()->getRate());
        // $this->assertEquals('021',$this->GenerateStringFromRatesOfArray($mu->getMyHistory()));
        $h4 = $this->GenerateFilledHistory('2013-06-07',4.0,$mu);
        $mu->addMyJobHistory($h4);
        $this->assertEquals(2,$h4->getJob()->getRate());
        $this->assertEquals(4,$mu->getJob()->getRate());
    }

    public function testRemoveMyJobHistory()
    {
        $mu = new MemberUser();
        $mu->CreateDummyData();

        $job1 = new Job();
        $job1->setRate(1);

        $mu->setJob($job1);
        $muRegistration = $this->GenerateFilledHistory('2013-04-05',2.0,$mu);
        $mu->addMyHistory($muRegistration);

        $mu->getJob()->setRate(4);
        
        $h2 = $this->GenerateFilledHistory('2012-03-07',3.0,$mu);
        $h3 = $this->GenerateFilledHistory('2011-03-07',1.0,$mu);
        $h4 = $this->GenerateFilledHistory('2013-06-07',2.0,$mu);

        //wprowadzamy bezpośrednio, bez użycia addMyJobHistory
        $mu->addMyHistory($h2);
        $mu->addMyHistory($h3);
        $mu->addMyHistory($h4);

        $this->assertEquals(2,$muRegistration->getJob()->getRate());
        $this->assertEquals(4,$mu->getJob()->getRate());
        $this->assertEquals(3,$h2->getJob()->getRate());
        $this->assertEquals(1,$h3->getJob()->getRate());
        $this->assertEquals(4,count($mu->getMyHistoryCached()));

        $result = $mu->removeMyJobHistory($h2);
        $this->assertEquals(3,count($mu->getMyHistoryCached()));
        $this->assertEquals('021',$result);
        $this->assertEquals('021',$this->GenerateStringFromRatesOfArray($mu->getMyHistory()));
        $this->assertEquals(3,$h3->getJob()->getRate());
        $this->assertEquals(3,$h4->getJob()->getRate());
        
    }
    public function testIsRegisterDate()
    {
        $mu = new MemberUser();
        $mu->CreateDummyData();

        $job1 = new Job();
        $job1->setRate(2);

        $mu->setJob($job1);
        $muRegistration = $this->GenerateFilledHistory('2013-04-05',2.0,$mu);
        $mu->addMyHistory($muRegistration);
        $mu->KindOfHistoryChanges();
        $this->assertTrue($muRegistration->IsRegisterDate());
    }



    private function GenerateStringFromRatesOfArray(Collection $history)
    {
        $result = '0';
        foreach($history as $h)
        {
            $result .= ' ';
            $result .= $h->getJob()->getRate();
            $result .= $h->getDate()->format('Y.m.d');
        }

        return $result;
    }

    private function GenerateFilledHistory(string $date, float $rate, MemberUser $mu)
    {
        
        $mh = new MemberHistory($mu);
        $job = new Job();
        $job->setRate($rate);
        $mh->setDate(new \DateTime($date));
        $mh->setJob($job);
        return $mh;
    }


}
