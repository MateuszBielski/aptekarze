<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JobControllerTest extends WebTestCase
{
    public function testShowJob()
    {
        $client = static::createClient();
        $jobId = $this->getFirstJobFromDataBase()->getId();
        $client->request('GET', '/job/'.$jobId);
        $this->assertResponseIsSuccessful();
    }
    public function testShowFormUpdateRate()
    {
        // $crawler = $client->request('GET', '/');
        
        // $this->assertResponseIsSuccessful();
        // $this->assertSelectorTextContains('h1', 'Hello World');
        $client = static::createClient([],
        [
            'PHP_AUTH_USER' => 'użytkRegister',
            'PHP_AUTH_PW'   => '87654321',
        ]
        );
       
        $jobId = $this->getFirstJobFromDataBase()->getId();


        $crawler = $client->request('GET', '/job/'.$jobId.'/updateRate');
        
        $this->assertResponseIsSuccessful();
        
        $form = $crawler->selectButton('submit-form')->form();
        $crawler = $client->submit($form);
        $this->assertResponseRedirects('/job/'.$jobId, 302);
        
    }

    private function getFirstJobFromDataBase()
    {
        $container = self::$container;
        $jobRep = $container->get('App\Repository\JobRepository');
        $jobs = $jobRep->findAll();
        return $jobs[0];
    }
}
