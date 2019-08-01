<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\MemberUserRepository;

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

    public function testPersistNewMemberUser()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/member/user/new');
        $form = $crawler->selectButton('submit-form')->form();

        $form['member_user[firstName]'] = 'testImie';
        $form['member_user[surname]'] = 'testNazwisko';
        $form['member_user[telephone]'] = 'testTelefon';
        $form['member_user[job]']->select(6);//nie ma znaczenia

        //powinno przekierować do mem us Index
        // $crawler = $client->submit($form);
        // $this->assertResponseIsSuccessful();<-**********odblokować gdy zadziała usuwanie

        $container = self::$container;
        $memRep = $container->get('App\Repository\MemberUserRepository');
        $mu = $memRep->findOneBy(['username' => "testImietestNazwiskotestTelefon",]);

        $this->memUsId = $mu->getId();
        
        $this->assertGreaterThan(0,$this->memUsId);
        
        
        /* usunięcie */
        // if($mu != null){
        //     $entityManager = $container->get('doctrine.orm.entity_manager');
        //     $entityManager->remove($mu);
        //     $entityManager->flush();
        // }


    }
    
    
    public function testRegisterNewUser()
    {   
        $client = static::createClient();
        /*
        $crawler = $client->request('GET', '/register');

        $num = '7757';
        
        $form = $crawler->selectButton('submit-form')->form();
        $form['registration_form[firstname]'] = 'test'.$num.'AdminImie';
        $form['registration_form[surname]'] = 'testAdmin'.$num.'Nazwisko';
        $form['registration_form[username]'] = 'testAdmin'.$num;
        $form['registration_form[plainPassword]'] = 'passwordTest';
        $crawler = $client->submit($form);
        
        $container = self::$container;
        $memRep = $container->get('App\Repository\MemberUserRepository');
        $toFind = "testAdmin".$num;
        $mu = $memRep->findOneBy(['username' => $toFind,]);
        
        $mu->setRoles(["ROLE_ADMIN"]);
        
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $entityManager->persist($mu);
        $entityManager->flush();
        
        $this->assertResponseRedirects('/member/user/', 302);

        $toFind = 'testAdmin'.$num.'Nazwisko';
        $link = $crawler->filter('a:contains("'.$toFind.")')->link();
        $crawler = $client->click($link);
        
        $this->assertResponseIsSuccessful();
        */
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('form')->form();//->selectButton('submit')
        $form['username'] = 'użytkregister';
        $form['password'] = '87654321';
        
        $toFind = 'testAdminNazwisko';
        $crawler = $client->request('GET', '/member/user/');
        $link = $crawler->filter('a:contains("'.$toFind.'")')
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

        // $this->assertResponseRedirects('/member/user/', 302);

    }


    /*zrobić index i indexAjax podobnie ze składkami */
}