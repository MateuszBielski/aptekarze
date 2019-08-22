<?php

namespace App\Service;

use App\Repository\MemberUserRepository;
use App\Repository\MemberHistoryRepository;
use App\Repository\ActiveJobRepository;
use App\Repository\ContributionRepository;
// use Symfony\Component\Serializer\Encoder\CsvEncoder;
// use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
// use Symfony\Component\Serializer\Serializer;
// use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;

class MemberUserOptimizer
{
    private $memUsRep;
    private $memHistRep;
    private $jobRep;
    private $contrRep;
    private $usersList;
    private $historyList;
    private $jobList;
    private $contrList;

    
    public function __construct(MemberUserRepository $mur, MemberHistoryRepository $mhr, ActiveJobRepository $jr, ContributionRepository $cr)
    {
        $this->memUsRep = $mur;
        $this->memHistRep = $mhr;
        $this->jobRep = $jr;
        $this->contrRep = $cr;

    }

    public function ReadRepositoriesAndCompleteCollections()
    {
        $this->historyList = $this->memHistRep->findAllIndexedById();
        $this->contrList = $this->contrRep->findAllIndexedById();
        $this->jobList = $this->jobRep->findAllIndexedById();
        $this->usersList = $this->memUsRep->findAllIndexedById();

        $this->setJobHistoryContribution();
    }
    public function ReadRepositoriesAndCompleteCollectionsNarrow(string $str)
    {
        $this->usersList = $this->memUsRep->findByNamePortion($str);
        $usersIdList = array_keys($this->usersList);
        
        $this->historyList = $this->memHistRep->findByUserIdIn($usersIdList);
        $this->contrList = $this->contrRep->findByUserIdIn($usersIdList);
        $this->jobList = $this->jobRep->findAllIndexedById();//wszystkie stanowiska odczytać

        $this->setJobHistoryContribution();
    }
    private function setJobHistoryContribution()
    {
        
        foreach($this->usersList as $us)
        {
            $jobId = $us->getJob()->getId();
            $us->setMyJobRateCached($this->jobList[$jobId]->getRate());
            // $us->setMyJobRateCached(21);
            $us->setOptimizedTrue();
        }
        foreach($this->historyList as $h)
        {
            $jobId = $h->getJob()->getId();
            $h->setMyJobRateCached($this->jobList[$jobId]->getRate());
            $this->usersList[$h->getMyUser()->getId()]->addMyHistoryDirectly($h);
            $h->setOptimizedTrue();
        }
        foreach($this->contrList as $contr)
        {
            $usId = $contr->getMyUser()->getId();
            $this->usersList[$usId]->addContributionCached($contr);
        }
    }
    public function sortUsersList()
    {

    }
    public function CalculateAndSetCurrentAccounts()
    {       
        foreach ($this->usersList as  $user) {
            $user->setStringCurrentAccount($user->StringCurrentAccount());
        }
    }
    public function ReadUsersIndexed()
    {
        $this->usersList = $this->memUsRep->findAllIndexedById();//
    }

    public function getUsersList()
    {
        return $this->usersList;
    }

    public function getfUserIdFromHistoryList()
    {
        $result = '';
        foreach ($this->historyList as $h) {
            $result .= ' '.$h->getMyUser()->getId();
        }
        return $result;
    }

    public function getUserListJson()
    {
        
    }
}
