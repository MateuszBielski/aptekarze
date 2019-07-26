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
    public function testDeleteMemberUser()
    {
        $client = static::createClient();
        $route = "/member/user/6004";
        // $crawler = $client->request('DELETE',$route );
        // $this->assertResponseIsSuccessful();
    }
    
    public function testRegisterNewUser()
    {   
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        // $container = self::$container;
        // $entityManager = $container->get('doctrine.orm.entity_manager');
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->getConnection()->setAutoCommit(false);//ważne
        $entityManager->getConnection()->beginTransaction();
        $form = $crawler->selectButton('submit-form')->form();
        $form['registration_form[firstname]'] = 'test4AdminImie';
        $form['registration_form[surname]'] = 'testAdminNazwisko';
        $form['registration_form[username]'] = 'testAdmin7764';
        $form['registration_form[plainPassword]'] = 'passwordTest';
        $crawler = $client->submit($form);
        $this->assertResponseRedirects('/member/user/', 302);
        
        // $memRep = $container->get('App\Repository\MemberUserRepository');
        // $mu = $memRep->findOneBy(['username' => "testAdmin7768",]);
        // $mu->setRoles(["ROLE_ADMIN"]);
        
        // $entityManager->persist($mu);
        // $entityManager->flush();
        $entityManager->getConnection()->rollback();
    }
    /*zrobić index i indexAjax podobnie ze składkami */
}