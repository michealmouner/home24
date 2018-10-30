<?php

namespace AppBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccountControllerTest extends WebTestCase
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    private $container;
    private $passwordEncoder;
    private $token;
    private static $staticClient;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {

        $kernel = self::bootKernel();
        $this->container = $kernel->getContainer();
        $this->entityManager = $this->container
                ->get('doctrine')
                ->getManager();
        $this->passwordEncoder = $this->container->get("security.password_encoder");
        self::$staticClient = static::createClient([
                    'base_uri'    => 'http://localhost:8000',
                    'http_errors' => false,
                        ], [
                    'HTTP_X_API_KEY' => '123',
        ]);

        $user = new User();
        $user->setHash(rand(0, 1000) + time());
        $user->setPassword($this->passwordEncoder->encodePassword(
                        $user, 'test1234'
        ));
        $user->setEmail("test@home24.com");
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function testWrongAPIKeyExcute()
    {
        /* @var $client \Symfony\Bundle\FrameworkBundle\Client */
        self::$staticClient->request('POST', '/api/login', [
            'email'    => 'test@home24.com',
            'password' => 'test1234',
                ], [], [
            'HTTP_X_API_KEY' => '123999',
        ]);

        $response = self::$staticClient->getResponse();
        $responseData = json_decode($response->getContent(), true);
        $this->assertSame($responseData['message'], "apikey.error");
    }

    public function testLoginFailedExcute()
    {
        /* @var $client \Symfony\Bundle\FrameworkBundle\Client */
        self::$staticClient->request('POST', '/api/login', [
            'email'    => 'test@home24.com',
            'password' => 'test1234555',
        ]);

        $response = self::$staticClient->getResponse();
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testLoginSuccessExcute()
    {
        /* @var $client \Symfony\Bundle\FrameworkBundle\Client */
        self::$staticClient->request('POST', '/api/login', [
            'email'    => 'test@home24.com',
            'password' => 'test1234',
        ]);

        $response = self::$staticClient->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('token', $responseData['data']);
        $this->token = $responseData['data']['token'];
    }

    public function testgetProfileExcute()
    {
        /* @var $client \Symfony\Bundle\FrameworkBundle\Client */
        self::$staticClient->request('GET', '/api/profile', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
        ]);

        $response = self::$staticClient->getResponse();
        $responseData = json_decode($response->getContent(), true);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('data', $responseData);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
        $urls = $this->entityManager->getRepository("AppBundle:User")->findAll();
        foreach($urls as $url)
        {
            $this->entityManager->remove($url);
        }
        $this->entityManager->flush();

        $this->entityManager->close();
        $this->entityManager = null; // avoid memory leaks
    }

}
