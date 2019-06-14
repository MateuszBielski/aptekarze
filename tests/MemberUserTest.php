<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\MemberUser;
use App\Entity\MemberHistory;
use App\Entity\Job;

class MemberUserTest extends TestCase
{
    private $testMemberUser;
    
    public function testAddChangeRateTo()
    {
        $testMemberUser = $this->UserWithOne_JobRate_Date(10,new \DateTime('2000-01-01'));
        $this->AddChangeRateTo($testMemberUser,20,new \DateTime('2000-10-02'));
        $this->AddChangeRateTo($testMemberUser,51,new \DateTime('2000-10-02'));
        $this->assertEquals(51, $testMemberUser->getJob()->getRate());
    }
    
    
    public function test_1_CalculateAllDueContributionOn()
    {
        $testMemberUser = $this->UserWithOne_JobRate_Date(10,new \DateTime('2000-01-01'));
        $result = $testMemberUser->CalculateAllDueContributionOn(new \DateTime('2000-08-02'));
        // 
        $this->assertEquals(80, $result);
    }
    
    public function test_2_CalculateAllDueContributionOn()
    {
        $testMemberUser = $this->UserWithOne_JobRate_Date(10,new \DateTime('2000-01-01'));
        $this->AddChangeRateTo($testMemberUser,20,new \DateTime('2000-08-02'));
        $result = $testMemberUser->CalculateAllDueContributionOn(new \DateTime('2000-10-02'));
       
        $this->assertEquals(120, $result);
    }

    public function test_3_CalculateAllDueContributionOn()
    {
        $testMemberUser = $this->UserWithOne_JobRate_Date(10,new \DateTime('2000-01-31'));
        $this->AddChangeRateTo($testMemberUser,20,new \DateTime('2000-08-02'));
        $result = $testMemberUser->CalculateAllDueContributionOn(new \DateTime('2000-10-02'));
       
        $this->assertEquals(120, $result);
    }

    public function test_4_CalculateAllDueContributionOn()
    {
        $testMemberUser = $this->UserWithOne_JobRate_Date(10,new \DateTime('2000-01-31'));
        
        $h = new MemberHistory($testMemberUser);
        $h->setDate(new \DateTime('2000-02-27'));
        $testMemberUser->addMyHistory($h);
        $testMemberUser->setFirstName('nameTest4');
        
        $this->AddChangeRateTo($testMemberUser,20,new \DateTime('2000-08-02'));
        $result = $testMemberUser->CalculateAllDueContributionOn(new \DateTime('2000-10-02'));
       
        $this->assertEquals(120, $result);
    }

    public function test_5_CalculateAllDueContributionOn()
    {
        $testMemberUser = $this->UserWithOne_JobRate_Date(10,new \DateTime('2000-01-02'));
        $result = $testMemberUser->CalculateAllDueContributionOn(new \DateTime('2000-08-02'));
        // 
        $this->assertEquals(80, $result);
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
