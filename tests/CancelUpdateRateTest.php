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
    //nie wiadomo dlaczego nie ma potrzeby deklaracji poniższych pól:
    // private $jobRep;
    // private $memUserRep;
    // private $cancelUpR;
    
    public function setUp()
    {
        //nie wiadomo dlaczego nie ma potrzeby deklaracji poniższych pól
        $this->jobRep = $this->createMock(JobRepository::class);
        $this->memUserRep = $this->createMock(MemberUserRepository::class);
        $this->cancelUpR = new AppCancelUpdateRate($this->jobRep,$this->memUserRep);
    }
    public function testMakeCancel()
    {
        $this->cancelUpR->MakeCancel();
        $this->assertTrue($this->cancelUpR->MakeCancel());
    }

    public function testRestoreReplacedJob()
    {
        
        $replacedJob = new Job();
        $newJob = new Job();
        $replacedJob->setRate(9);
        $newJob->setRate(40);

        $replacedJob->setReplacedBy($newJob);


        $memberUser = new MemberUser();
        $memberUser->setJob($newJob);

        $memberUser2 = new MemberUser();
        $memberUser2->setJob($newJob);

        
        $this->jobRep->expects($this->any())
            ->method('findAll')
            ->willReturn([$replacedJob, $newJob]);

        
        $this->memUserRep->expects($this->any())  
            ->method('findByJob')
            ->willReturn([$memberUser,$memberUser2]);

        $this->cancelUpR->RestoreJobReplacedBy($newJob);
        $this->assertEquals(9,$memberUser->getJob()->getRate());
        $this->assertEquals(9,$memberUser2->getJob()->getRate());
    }
    public function testRemoveReplacedJobHistory()
    {
        $replacedJob = new Job();
        $newJob = new Job();
        $replacedJob->setRate(9);
        $newJob->setRate(40);

        $replacedJob->setReplacedBy($newJob);

        $memberUser = new MemberUser();
        $memberUser->CreateDummyData();
        $memberUser->setJob($newJob);

        $memberHistory = new MemberHistory($memberUser);
        $memberHistory->setJob($replacedJob);

        $memberUser->addMyHistoryDirectly($memberHistory);
        $memberUser->setOptimizedTrue();

        $this->jobRep->expects($this->any())
            ->method('findAll')
            ->willReturn([$replacedJob, $newJob]);

        $this->memUserRep->expects($this->any())  
            ->method('findByJob')
            ->willReturn([$memberUser]);
        
        $this->assertEquals(1,count($memberUser->getMyHistoryCached()));
        $this->cancelUpR->RestoreJobReplacedBy($newJob);
        $this->assertEquals(0,count($memberUser->getMyHistoryCached()));

    }

    public function testNotRemoveOtherHistory()
    {
        $otherJob1 = new Job();
        $otherJob2 = new Job();
        $newJob = new Job();
        $otherJob1->setRate(9);
        $otherJob2->setRate(51);
        $newJob->setRate(40);

        $memberUser = new MemberUser();
        $memberUser->CreateDummyData();
        $memberUser->setJob($otherJob2);

        $memberHistory = new MemberHistory($memberUser);
        $memberHistory->setJob($otherJob1);

        $memberUser->addMyHistoryDirectly($memberHistory);
        $memberUser->setOptimizedTrue();

        $this->jobRep->expects($this->any())
            ->method('findAll')
            ->willReturn([$otherJob1, $otherJob2]);

        $this->memUserRep->expects($this->any())  
            ->method('findByJob')
            ->willReturn([$memberUser]);
        
        $this->cancelUpR->RestoreJobReplacedBy($newJob);
        $this->assertEquals(1,count($memberUser->getMyHistoryCached()));
    }

    public function t_notCancelIfFurtherChangesExist(Type $var = null)
    {
        # code...
    }
    public function notUsed()
    {
        $allJobs = array();
        for($i = 0 ; $i < 10 ; $i++)
        {
            $job = new Job();
            $job->setRate(rand(12,34));
            $allJobs[] = $job;
        }

        // $allJobs[] = $replacedJob;
        // $allJobs[] = $newJob;

    }
}
