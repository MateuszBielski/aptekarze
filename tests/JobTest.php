<?php

namespace App\Tests;

use App\Entity\Job;
use App\Entity\MemberUser;
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
   
}
