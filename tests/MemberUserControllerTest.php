<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\MemberUserRepository;
use App\Entity\MemberUser;

class MemberUserControllerTest extends WebTestCase
{
    private $memUsId;
    private $em;
    
    // public function testRouteIndex()
    // {
    //     $client = static::createClient();
    //     $crawler = $client->request('GET', '/member/user/');
    //     $this->assertResponseIsSuccessful();
    //     // $this->assertSelectorTextContains('h1', 'Hello World');
    // }

    // public function testRouteNew()
    // {
    //     $client = static::createClient();
    //     $crawler = $client->request('GET', '/member/user/new');
    //     $this->assertResponseIsSuccessful();
    // }
    // public function __construct(MemberUserRepository $memberUserRepository, EntityManagerInterface $entityManager)
    // {
    //     $this->memRep = $memberUserRepository;
    //     $this->em = $entityManager;
    // }
  
    
    public function testAddAndDeleteUsers()
    {   
        
        $client = static::createClient();
        $userAdmin = $this->NewMemberWith('7655');
        $otherUser = $this->NewMemberWith('5325');
        $this->RegisterUserAsAdmin($userAdmin,$client);
        $this->AddNewUser($otherUser,$client);
        $this->LoginAs($userAdmin,$client);

        $this->FindUserAndDelete($otherUser,$client);
        $this->FindUserAndDelete($userAdmin,$client);
    }

    private function NewMemberWith($num)
    {
        $mu= new MemberUser();
        $mu->setSurname('test'.$num.'Nazwisko');
        $mu->setFirstName('test'.$num.'Imie');
        $mu->setPassword('password'.$num.'Test');
        $mu->setUserName('testUN'.$num);
        $mu->setTelephone($num);
        return $mu;
    }

    private function RegisterUserAsAdmin(MemberUser $user,$client)
    {
        $crawler = $client->request('GET', '/register');
        
        
        $form = $crawler->selectButton('submit-form')->form();
        $form['registration_form[firstname]'] = $user->getFirstName();
        $form['registration_form[surname]'] = $user->getSurname();
        $form['registration_form[username]'] = $user->getUsername();
        $form['registration_form[plainPassword]'] = $user->getPassword();
        $crawler = $client->submit($form);
        
        $container = self::$container;
        $memRep = $container->get('App\Repository\MemberUserRepository');
        
        $mu = $memRep->findOneBy(['username' => $user->getUsername(),]);
        
        $mu->setRoles(["ROLE_ADMIN"]);
        
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $entityManager->persist($mu);
        $entityManager->flush();
        
        $this->assertResponseRedirects('/member/user/', 302);
    }

    private function AddNewUser(MemberUser $user,$client)
    {
        $crawler = $client->request('GET', '/member/user/new');
        $form = $crawler->selectButton('submit-form')->form();


        
        $form['member_user[firstName]'] = $user->getFirstName();;
        $form['member_user[surname]'] = $user->getSurname();
        $form['member_user[telephone]'] = $user->getTelephone();
        $form['member_user[job]']->select(2);//nie ma znaczenia

        //powinno przekierować do mem us Index
        $crawler = $client->submit($form);
        // $this->assertResponseIsSuccessful();<-**********odblokować gdy zadziała usuwanie

        $user->createTempUsername();
        //poniższe działa dopiero po wywołaniu $client = static::createClient();
        $container = self::$container;
        $memRep = $container->get('App\Repository\MemberUserRepository');
        $mu = $memRep->findOneBy(['username' => $user->getUsername(),]);

        $this->memUsId = $mu->getId();
        
        $this->assertGreaterThan(0,$this->memUsId);
    }

    private function LoginAs(MemberUser $admin,$client)
    {
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('submit_login')->form();//przycisk musi mieć np: name="submit_login
        $form['username'] = $admin->getUsername();
        $form['password'] = $admin->getPassword();
        $crawler = $client->submit($form);
    }

    private function FindUserAndDelete($userToDelete,$client)
    {
        $crawler = $client->request('GET', '/member/user/');
        $link = $crawler->filter('a:contains("'.$userToDelete->getSurname().'")')
        ->eq(0)
        ->link();
        
        $crawler = $client->click($link);
        
        $this->assertResponseIsSuccessful();

        $filtered = $crawler->filter('a:contains("zmiana od dziś na przyszłość")');
        $this->assertGreaterThan(0,$filtered->count());
        $link = $filtered->eq(0)->link();

        $crawler = $client->click($link);
        
        $this->assertResponseIsSuccessful();//jesteśmy na stronie /member/user/*/change

        //klikniemy na usuń użytkownika
        $form = $crawler->selectButton('submit-delete')->form();
        $crawler = $client->submit($form);

        $this->assertResponseRedirects('/member/user/', 302);
    }

    
    /*zrobić index i indexAjax podobnie ze składkami */
}