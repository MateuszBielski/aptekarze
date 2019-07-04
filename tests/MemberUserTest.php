<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\MemberUser;
use App\Entity\MemberHistory;
use App\Entity\Job;

class MemberUserTest extends TestCase
{
    //private $testMemberUser;
    
    public function testAddChangeRateTo()
    {
        $testMemberUser = $this->UserWithOne_JobRate_Date(10,new \DateTime('2000-01-01'));
        $this->AddChangeRateTo($testMemberUser,20,new \DateTime('2000-10-02'));
        $this->AddChangeRateTo($testMemberUser,51,new \DateTime('2000-10-02'));
        $this->assertEquals(51, $testMemberUser->getJob()->getRate());
    }

    public function testAfterPaymentDay()
    {
        $testMemberUser = $this->UserWithOne_JobRate_Date(10,new \DateTime('2000-01-01'));
        $testMemberUser->setPaymentDayOfMonth(10);
        $this->assertTrue($testMemberUser->AfterPaymentDay(new \DateTime('2000-02-11')));
    }
    public function testBeforePaymentDay()
    {
        $testMemberUser = $this->UserWithOne_JobRate_Date(10,new \DateTime('2000-01-01'));
        $testMemberUser->setPaymentDayOfMonth(10);
        $this->assertFalse($testMemberUser->AfterPaymentDay(new \DateTime('2000-02-09')));
    }

    public function testIntervalToMonths()
    {
        $start = new \DateTime('2012-07-01');
        $stop = new \DateTime('2012-10-01');
        //sprawdzono, że ta funkcja działa nieprawidłowo dla niektórych kombinacji
        $result = MemberUser::IntervalToMonths($stop->diff($start));
        $this->assertEquals(3, $result);
    }

    public function  testDatesDiffToMonth()
    {
        $start = new \DateTime('2012-08-01');
        $stop = new \DateTime('2012-10-01');
        $result = MemberUser::DatesDiffToMonth($start, $stop);
        $this->assertEquals(2, $result);
    }

    
    public function  testDatesDiffToMonth_2()
    {
        $start = new \DateTime('1999-11-01');
        $stop = new \DateTime('2001-02-01');
        $result = MemberUser::DatesDiffToMonth($start, $stop);
        $this->assertEquals(15, $result);
    }

    public function testUserWithoutHistory()
    {
        $testMemberUser = new MemberUser();
        $testMemberUser->setInitialAccount(264);

        $result = $testMemberUser->StringCurrentAccount();//(new \DateTime('now'))
        $this->assertEquals('+264 zł', $result);
    }

    public function test_1_CalculateAllDueContributionOn()
    {
        $testMemberUser = $this->UserWithOne_JobRate_Date(10,new \DateTime('2000-01-03'));
        $result = $testMemberUser->CalculateAllDueContributionOn(new \DateTime('2000-08-02'));
        // 
        $this->assertEquals(-70, $result);
    }
    
    public function test_2_CalculateAllDueContributionOn()
    {
        $testMemberUser = $this->UserWithOne_JobRate_Date(10,new \DateTime('2000-01-03'));
        $this->AddChangeRateTo($testMemberUser,20,new \DateTime('2000-08-02'));
        $result = $testMemberUser->CalculateAllDueContributionOn(new \DateTime('2000-10-02'));
       
        $this->assertEquals(-(70+40), $result);
    }

    public function test_3_CalculateAllDueContributionOn()
    {
        $testMemberUser = $this->UserWithOne_JobRate_Date(10,new \DateTime('2000-02-28'));
        $this->AddChangeRateTo($testMemberUser,20,new \DateTime('2000-08-02'));
        //$testMemberUser->setPaymentDayOfMonth(10);
        $result = $testMemberUser->CalculateAllDueContributionOn(new \DateTime('2000-10-21'));
       
        $this->assertEquals(-(50 + 60), $result);
    }

    public function test_4_CalculateAllDueContributionOn()
    {
        $testMemberUser = $this->UserWithOne_JobRate_Date(10,new \DateTime('2000-02-28'));
        
        $h = new MemberHistory($testMemberUser);
        $h->setDate(new \DateTime('2000-02-27'));
        $testMemberUser->addMyHistory($h);
        $testMemberUser->setFirstName('nameTest4');
        
        $this->AddChangeRateTo($testMemberUser,20,new \DateTime('2000-08-02'));
        $result = $testMemberUser->CalculateAllDueContributionOn(new \DateTime('2000-10-02'));
       
        $this->assertEquals(-(50+40), $result);
    }

    public function test_5_CalculateAllDueContributionOn()
    {
        $testMemberUser = $this->UserWithOne_JobRate_Date(10,new \DateTime('2000-07-15'));
        $result = $testMemberUser->CalculateAllDueContributionOn(new \DateTime('2000-08-02'));
        // 
        $this->assertEquals(-10, $result);
    }
    public function test_6_CalculateAllDueContributionOn()
    {
        // ***********nie tak jak wygląda bo poniższe nie działa 
        $testMemberUser = $this->UserWithOne_JobRate_Date(10,new \DateTime('2000-07-16'));
        $testMemberUser->setPaymentDayOfMonth(3);
        $result = $testMemberUser->CalculateAllDueContributionOn(new \DateTime('2000-09-03'));
        // 
        $this->assertEquals(-10, $result);
    }
    
    public function test_7_CalculateAllDueContributionOn()
    {
        $testMemberUser = $this->UserWithOne_JobRate_Date(10,new \DateTime('2012-07-02'));
        $testMemberUser->setPaymentDayOfMonth(3);
        $result = $testMemberUser->CalculateAllDueContributionOn(new \DateTime('2012-9-4'));
        // 
        $this->assertEquals(-30, $result);
    }

    public function test_8_CalculateAllDueContributionOn()
    {
        $testMemberUser = $this->UserWithOne_JobRate_Date(10,new \DateTime('2019-06-20'));
        $result = $testMemberUser->CalculateAllDueContributionOn(new \DateTime('2019-06-20'));
        $this->assertEquals(0, $result);
    }

    public function test_9_CalculateAllDueContributionOn()
    {
        $testMemberUser = $this->UserWithOne_JobRate_Date(10,new \DateTime('2019-06-20'));
        $result = $testMemberUser->CalculateAllDueContributionOn(new \DateTime('2019-07-20'));
        $this->assertEquals(0, $result);
    }


    private function UserWithOne_JobRate_Date(int $rate,\DateTimeInterface $date)
    {

        $testMemberUser = new MemberUser();
        $testMemberUser->CreateDummyData();
        $job = new Job();
        $job->setRate($rate);

        $testMemberUser->setJob($job);
        
        $h1 = new MemberHistory($testMemberUser);//jako rejestracja
        $h1->setDate($date);

        $testMemberUser->addMyHistory($h1);
        return $testMemberUser;
    }

    private function AddChangeRateTo(MemberUser& $mu, int $rate,\DateTimeInterface $date)
    {
        $h = new MemberHistory($mu);
        $h->setDate($date);
        $mu->addMyHistory($h);
        $job = new Job();
        $job->setRate($rate);
        $mu->setJob($job);
    }

    //zrobić GenerateInfoChangeComparingToNext (historia)
}
