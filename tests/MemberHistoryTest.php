<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\MemberHistory;
use App\Entity\MemberUser;

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
}
