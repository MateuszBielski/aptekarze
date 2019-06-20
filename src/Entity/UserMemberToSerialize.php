<?php

namespace App\Entity;

use App\Entity\MemberHistory;
use PHPUnit\Framework\Exception;

class UserMemberToSerialize
{
    private $telephone;
    private $email;
    private $firstName;
    private $surname;
    private $job;
    private $paymentDayOfMonth = 20;
    private $jobRate;
    //przydadzą się w przyszłości:
    //initial account
    //jakaś forma daty rejestracji wcześniejszej niż bieżąca

    public function setPropertiesFrom(MemberUser $mu)
    {
         //$this->id = $mu->getId();
        $this->telephone = $mu->getTelephone();
        $this->email = $mu->getEmail();
        $this->firstName = $mu->getFirstName();
        $this->surname = $mu->getSurname();
        $this->paymentDayOfMonth = $mu->getPaymentDayOfMonth();
    }

    /**
     * Get the value of paymentDayOfMonth
     */ 
    public function getPaymentDayOfMonth()
    {
        return $this->paymentDayOfMonth;
    }

    /**
     * Set the value of paymentDayOfMonth
     *
     * @return  self
     */ 
    public function setPaymentDayOfMonth($paymentDayOfMonth)
    {
        $this->paymentDayOfMonth = $paymentDayOfMonth;

        return $this;
    }

    /**
     * Get the value of telephone
     */ 
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * Set the value of telephone
     *
     * @return  self
     */ 
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of firstName
     */ 
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set the value of firstName
     *
     * @return  self
     */ 
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get the value of surname
     */ 
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set the value of surname
     *
     * @return  self
     */ 
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }
    /**
     * Get the value of jobRate
     */ 
    public function getJob()
    {
        return $this->jobRate;
    }

    /**
     * Set the value of jobRate
     *
     * @return  self
     */ 
    public function setJob(Job $job)
    {
        $this->jobRate = $job->getRate();
        $this->job = $job;
        return $this;
    }

    public function setJobRate(int $rate)
    {
        $this->jobRate = $rate;
    }

    public function createMemberUser(array& $jobs)
    {
        $newMemberUser = new MemberUser();
        $newMemberUser->setTelephone($this->getTelephone());
        $newMemberUser->setEmail($this->getEmail());
        $newMemberUser->setFirstName($this->getFirstName());
        $newMemberUser->setSurname($this->getSurname());
        $newMemberUser->setPaymentDayOfMonth($this->getPaymentDayOfMonth());

        try{
            //może nie być odpowiedniego obiektu w tablicy jobs
        }catch(Exception $e){

        }
        $newMemberUser->setJob($jobs[$this->jobRate]);
        $newMemberUser->createTempUsername();
        $newMemberUser->setPassword('87654321');

        $memberStartHistory = new MemberHistory($newMemberUser);
        $newMemberUser->addMyHistory($memberStartHistory);
        return $newMemberUser;
    }

    
}