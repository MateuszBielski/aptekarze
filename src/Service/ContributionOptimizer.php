<?php

namespace App\Service;

use App\Repository\ContributionRepository;
use App\Repository\MemberUserRepository;
use Symfony\Component\HttpFoundation\ParameterBag;

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

    public function readRepositoryAndSetCollectionByDate(ParameterBag $date_a) //
    {
       
        $day = $date_a->get('day');
        $toLastDay = false;
        $temporayDate = new \DateTime('now');
        if( $day == null or $day == '-'){
            $day = '01';
            $toLastDay = true;
        }
        $month = $date_a->get('month');
        if($month == null){
            $month = $temporayDate->format('m');
        }
        $year = $date_a->get('year');
        if($year == null){
            $year = $temporayDate->format('Y');
        }
        $date = new \DateTime("$year-$month-$day");
        if($toLastDay)$date->modify('last day of this month');
        $this->contributionList = $this->contrRep->findByDateIndexedById($date);//dodać order by: cokolwiek
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