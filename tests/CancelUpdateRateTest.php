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
use Doctrine\Common\Collections\ArrayCollection;
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
        
        $jobs = $this->createJobsWithRates([9,40]);
        $replacedJob = $jobs[0];
        $newJob = $jobs[1];

        $replacedJob->setReplacedBy($newJob);


        $memberUser = new MemberUser();
        $memberUser->setJob($newJob);

        $memberUser2 = new MemberUser();
        $memberUser2->setJob($newJob);

        $this->FillMocks(['findAll','findByJob'],
            [[$replacedJob, $newJob],
            [$memberUser,$memberUser2]
            ]);    

        $this->cancelUpR->RestoreJobReplacedBy($newJob);
        $this->assertEquals(9,$memberUser->getJob()->getRate());
        $this->assertEquals(9,$memberUser2->getJob()->getRate());
    }
    public function testRemoveReplacedJobHistory()
    {
        $jobs = $this->createJobsWithRates([9,40]);
        $replacedJob = $jobs[0];
        $newJob = $jobs[1];

        $replacedJob->setReplacedBy($newJob);

        $memberUser = $this->CreateMemberWithDummyDataAndJob($newJob);

        $this->AddHistoryWithJobAndDateTo([$replacedJob,],
                                        ['2012-05-06',]
                                        ,$memberUser );

        $this->FillMocks(['findAll','findByJob'],
                        [[$replacedJob, $newJob],
                        [$memberUser]
                        ]);
        
        $this->assertEquals(1,count($memberUser->getMyHistoryCached()));
        $this->cancelUpR->RestoreJobReplacedBy($newJob);
        $this->assertEquals(0,count($memberUser->getMyHistoryCached()));

    }

    public function testNotRemoveOtherHistory()
    {
        $jobs = $this->createJobsWithRates([9,51,40]);
        $otherJob1 = $jobs[0];
        $otherJob2 = $jobs[1];
        $newJob = $jobs[2];

        $memberUser = $this->CreateMemberWithDummyDataAndJob($otherJob2);

        $this->AddHistoryWithJobAndDateTo([$otherJob1,],
                                        ['2012-05-06',]
                                        ,$memberUser );

        $this->FillMocks(['findAll','findByJob'],
                        [[$otherJob1, $otherJob2],
                        [$memberUser]
                        ]);
        
        $this->cancelUpR->RestoreJobReplacedBy($newJob);
        $this->assertEquals(1,count($memberUser->getMyHistoryCached()));
    }

    public function t_RestoreNotAffectIfFurtherChangesJobExist()
    {
        $jobs = $this->createJobsWithRates([9,40,51]);
        $replacedJob = $jobs[0];
        $newJob = $jobs[1];
        $replacedJob->setReplacedBy($newJob);
        
        $otherJob = $jobs[2];

        $memberUser = $this->CreateMemberWithDummyDataAndJob($newJob);
        $memberUser2 = $this->CreateMemberWithDummyDataAndJob($newJob);

        $this->AddHistoryWithJobAndDateTo($jobs,
                                        ['2012-05-06','2014-03-07']
                                        ,$memberUser );
                                        $this->AddHistoryWithJobAndDateTo([$replacedJob],
                                        ['2012-05-06']
                                        ,$memberUser2 );
                                        
        $this->FillMocks(['findAll','findByJob'],
                                        [$jobs,
                                        [$memberUser,$memberUser2]
                                        ]);

        $this->assertEquals(2,count($memberUser->getMyHistoryCached()));                            
        $this->cancelUpR->RestoreJobReplacedBy($newJob);
        $this->assertEquals(40,$memberUser->getJob()->getRate());
        $this->assertEquals(40,$memberUser2->getJob()->getRate());

    }
    public function testRestoreJobAffectOnlyUpdatedUsers()
    {
        $jobs = $this->createJobsWithRates([9,40,32]);
        $replacedJob = $jobs[0];
        $newJob = $jobs[1];
        $otherJob = $jobs[2];

        $replacedJob->setReplacedBy($newJob);


        $memberUser = $this->CreateMemberWithDummyDataAndJob($newJob);
        $memberUser2 = $this->CreateMemberWithDummyDataAndJob($otherJob);

        $this->FillMocks(['findAll','findByJob'],
            [$jobs,
            [$memberUser]
            ]);    

        $this->cancelUpR->RestoreJobReplacedBy($newJob);
        $this->assertEquals($replacedJob,$memberUser->getJob());
        $this->assertEquals($otherJob,$memberUser2->getJob());
    }
    public function testRestoreReplacedJobInPast()
    {
        $jobs = $this->createJobsWithRates([9,40,32]);
        $replacedJob = $jobs[0];
        $newJob = $jobs[1];
        $otherJob = $jobs[2];

        $memberUser = $this->CreateMemberWithDummyDataAndJob($otherJob);

        $this->AddHistoryWithJobAndDateTo([$replacedJob,$newJob],
                                        ['2012-05-06','2014-04-23']
                                        ,$memberUser );

        $this->FillMocks(['findAll','findByJob'],
                            [$jobs,
                            [$memberUser]
                            ]);

        $this->cancelUpR->RestoreJobReplacedBy($newJob);
        $this->assertEquals(1,count($memberUser->getMyHistoryCached()));

    }
    public function testNotRestoreJobInPastIfSeparatedWithCanceled()
    {
        //w historii ma przywrócić te przypadki, gdzie aktualizowany job był bieżącym, czyli musi być styk replaced/new 
        $jobs = $this->createJobsWithRates([9,40,32,25]);
        $replacedJob = $jobs[0];
        $newJob = $jobs[1];
        $otherJob1 = $jobs[2];
        $otherJob2 = $jobs[3];

        $memberUser = $this->CreateMemberWithDummyDataAndJob($otherJob1);

        $this->AddHistoryWithJobAndDateTo([$replacedJob,$otherJob2,$replacedJob,$newJob],
                                        ['2012-05-06','2013-04-23','2017-02-12','2018-05-12']
                                        ,$memberUser );

        $this->FillMocks(['findAll','findByJob'],
                            [$jobs,
                            [$memberUser]
                            ]);

        // $this->assertEquals(1,count($memberUser->getMyHistoryCached()));                                
        $this->cancelUpR->RestoreJobReplacedBy($newJob);
        $this->assertEquals(3,count($memberUser->getMyHistoryCached()));
    }
    public function testThrowErrorIfCanceledFollowsOtherJob()
    {
        //w historii ma przywrócić te przypadki, gdzie aktualizowany job był bieżącym, czyli musi być styk replaced/new 
        $jobs = $this->createJobsWithRates([9,40,32,25]);
        $replacedJob = $jobs[0];
        $newJob = $jobs[1];
        $otherJob1 = $jobs[2];
        $otherJob2 = $jobs[3];

        $memberUser = $this->CreateMemberWithDummyDataAndJob($otherJob1);

        $this->AddHistoryWithJobAndDateTo([$replacedJob,$otherJob2,$newJob],
                                        ['2012-05-06','2013-04-23','2017-02-12','2018-05-12']
                                        ,$memberUser );

        $this->FillMocks(['findAll','findByJob'],
                            [$jobs,
                            [$memberUser]
                            ]);

        // $this->assertEquals(1,count($memberUser->getMyHistoryCached()));                                
        $this->cancelUpR->RestoreJobReplacedBy($newJob);
        $this->expectException("Exception");
        // $this->assertEquals(3,count($memberUser->getMyHistoryCached()));
    }
    public function t_RestoreReplacedJobInPastIfHistoryNotSorted()
    {
        # code...
    }
    public function t_RestoreIfHistoryLoadSeparately()
    {
        
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

    private function createJobsWithRates(array $rates)
    {
        foreach($rates as $rate)
        {
            $job = new Job();
            $job->setRate($rate);
            $jobs[] = $job;
        }
        return $jobs;
        
    }

    private function AddHistoryWithJobAndDateTo(array $jobs, array $dates, MemberUser $mu)
    {
        $number = count($jobs);
        for($i = 0 ; $i < $number ; $i++)
        {
            $memberHistory = new MemberHistory($mu);
            $memberHistory->setJob($jobs[$i]);
            $memberHistory->setDate(new \DateTime($dates[$i]));
            $mu->addMyHistoryDirectly($memberHistory);

        }
        $mu->setOptimizedTrue();
    }
    private function CreateMemberWithDummyDataAndJob(Job $otherJob2)
    {
        $memberUser = new MemberUser();
        $memberUser->CreateDummyData();
        $memberUser->setJob($otherJob2);
        return $memberUser;
    }
    private function FillMocks(array $methods, array $returns)
    {
        $repositories = [$this->jobRep,$this->memUserRep];
        $i = 0;
        foreach($repositories as $rep)
        {
            $rep->expects($this->any())
            ->method($methods[$i])
            ->willReturn($returns[$i]);
            $i++;
        }
    }
}
