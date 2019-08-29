<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Job;
use App\Entity\MemberUser;
use App\Service\JobOptimizer;
use App\Repository\MemberUserRepository;


class JobOptimizerTest extends WebTestCase
{
    private $members;
    private $jobToChange;
    private $min = 0;
    private $max = 10;
    
    protected function setUp()
    {
        $this->members = array();
        $this->jobToChange = new Job();
        $this->jobToChange->setRate(11.2);
        for($i = $this->min ; $i <= $this->max ; $i++)
        {
            $mu = new MemberUser();
            $mu->CreateDummyData();
            $mu->setJob($this->jobToChange);
            $this->members[$i] = $mu;
        }
    }
    public function testReplaceOldByNewInAdequateUsers()
    {
        
        $randomIndex = rand($this->min,$this->max);
        $this->assertEquals(11.2,$this->members[$randomIndex]->getJob()->getRate());

        $jobWithNewRate = new Job();
        $jobWithNewRate->setRate(37.3);

        $memUsRepMock = $this->createMock(MemberUserRepository::class);
        $memUsRepMock->expects($this->any())
            ->method('findBy')
            ->willReturn($this->members);

        $jobOptimizer = new JobOptimizer($memUsRepMock);
        $jobOptimizer->ReplaceOldByNewInAdequateUsers($this->jobToChange,$jobWithNewRate);

        $randomMember = $this->members[rand($this->min,$this->max)];
        $this->assertEquals(37.3,$randomMember->getJob()->getRate());
    }
}
