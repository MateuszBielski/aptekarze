<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Job;
use App\Entity\MemberUser;
use App\Service\JobOptimizer;
use App\Repository\MemberUserRepository;


class JobOptimizerTest extends WebTestCase
{
    public function testReplaceOldByNewInAdequateUsers()
     {
        $members = array();
        $jobToChange = new Job();
        $jobToChange->setRate(11.2);
        $min = 0;
        $max = 10;
        for($i = $min ; $i <= $max ; $i++)
        {
            $mu = new MemberUser();
            $mu->CreateDummyData();
            $mu->setJob($jobToChange);
            $members[$i] = $mu;
        }
        $randomIndex = rand($min,$max);
        $this->assertEquals(11.2,$members[$randomIndex]->getJob()->getRate());

        $jobWithNewRate = new Job();
        $jobWithNewRate->setRate(37.3);

        $memUsRepMock = $this->createMock(MemberUserRepository::class);
        $memUsRepMock->expects($this->any())
            ->method('findBy')
            ->willReturn($members);

        $jobOptimizer = new JobOptimizer($memUsRepMock);
        $jobOptimizer->ReplaceOldByNewInAdequateUsers($jobToChange,$jobWithNewRate);

        $randomMember = $members[rand($min,$max)];
        $this->assertEquals(37.3,$randomMember->getJob()->getRate());
     }
}
