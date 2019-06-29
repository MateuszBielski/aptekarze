<?php

namespace App\Service;

use App\Repository\ContributionRepository;
use App\Repository\MemberUserRepository;

class ContributionOptimizer
{
    private $contrRep;
    private $userRep;
    private $contributionList;
    private $usersList;

    public function __construct(ContributionRepository $cr, MemberUserRepository $mur)
    {
        $this->contrRep = $cr;
        $this->userRep = $mur;
    }
    public function readRepositoryAndSetCollection()
    {
        // $this->contributionList = $this->contrRep->findAllIndexedById();
        // $this->contributionList = $this->contrRep->findAllIndexedByIdOrderBy('o.paymentDate','DESC');
        $this->contributionList = $this->contrRep->findAllIndexedByIdOrderBy('o.value','DESC');
        $this->usersList = $this->userRep->findAllIndexedById();

        $this->setCollections();
    }
    public function getContributionList()
    {
        return $this->contributionList;
    }
    private function setCollections()
    {
        foreach($this->contributionList as $c)
        {
            $userId = $c->getMyUser()->getId();
            $user = $this->usersList[$userId];
            $c->setMyUserCached($user);
            $c->setOptimized();
        }
    }
}