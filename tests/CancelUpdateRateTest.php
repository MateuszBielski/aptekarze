<?php

namespace App\Tests;

use App\Entity\Job;
use App\Entity\MemberUser;
use App\Entity\MemberUserToTest;
use App\Entity\MemberHistory;
use App\Service\RetrieveOldNewRateJunctions;
use App\Repository\MemberUserRepository;
use App\Repository\JobRepository;
use App\Repository\MemberHistoryRepository;
use App\Service\CancelUpdateRate as AppCancelUpdateRate;
use PHPUnit\Framework\TestCase;

class CancelUpdateRateTest extends TestCase
{
    public function setUp()
    {
        $this->cancelUpR = new AppCancelUpdateRate();
    }
    public function testMakeCancel()
    {
        $this->cancelUpR->MakeCancel();
        $this->assertTrue($this->cancelUpR->MakeCancel());
    }

    public function testRestoreReplacedJob()
    {
        $allJobs = array();
        for($i = 0 ; $i < 10 ; $i++)
        {
            $job = new Job();
            $job->setRate(rand(12,34));
            $allJobs[] = $job;
        }
        $replacedJob = new Job();
        $newJob = new Job();
        $replacedJob->setRate(9);
        $newJob->setRate(40);

        $replacedJob->setReplacedBy($newJob);

        $allJobs[] = $replacedJob;
        $allJobs[] = $newJob;

        $memberUser = new MemberUser();
        $memberUser->setJob($newJob);

        $jobRep = $this->createMock(JobRepository::class);
        $jobRep->expects($this->any())
            ->method('findAll')
            ->willReturn([$replacedJob, $newJob]);

        $memUserRep = $this->createMock(MemberUserRepository::class);
        $memUserRep->expects($this->any())  
            ->method('findByJob')
            ->willReturn($memberUser);

        $this->cancelUpR->setJobRep($jobRep);
        $this->cancelUpR->setMemUserRep($memUserRep);

        $this->cancelUpR->RestoreJobReplacedBy($newJob);
        $this->assertEquals(9,$memberUser->getJob()->getRate());
    }
}
