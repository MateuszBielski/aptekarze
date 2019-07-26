<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\MemberUserRepository;

class MemberUserControllerTest extends WebTestCase
{
    private $memRep;
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
        $form = $crawler->selectButton('submit-form')->form();//nie działa

        // set some values
        $form['member_user[firstName]'] = 'testImie';
        $form['member_user[surname]'] = 'testNazwisko';
        $form['member_user[telephone]'] = 'testTelefon';
        $form['member_user[job]']->select(6);

        // submit the form
        //powinno przekierować do mem us Index
        $crawler = $client->submit($form);

        // $this->assertTrue($client->getResponse()->isRedirect('/member/user/'));
        // $this->assertCount(1, $crawler->filter('td:contains("testNazwisko")'));


        // $this->assertResponseIsSuccessful();
    }
}