<?php

namespace AppBundle\Tests\Command;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

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

    const USER = [
        'email'    => 'test@home24.com',
        'phone'    => '12345',
        'password' => 'test1234',
    ];

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
                    'HTTP_X_API_KEY' => $this->container->getParameter('api_key'),
        ]);

        $user = new User();
        $user->setHash(rand(0, 1000) + time());
        $user->setPassword($this->passwordEncoder->encodePassword(
                        $user, self::USER['password']
        ));
        $user->setEmail(self::USER['email']);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function testWrongAPIKeyExcute()
    {
        /* @var $client \Symfony\Bundle\FrameworkBundle\Client */
        self::$staticClient->request(Request::METHOD_POST, '/api/login', [
            'email'    => self::USER['email'],
            'password' => self::USER['password'],
                ], [], [
            'HTTP_X_API_KEY' => $this->container->getParameter('api_key') . "88",
        ]);

        $response = self::$staticClient->getResponse();
        $responseData = json_decode($response->getContent(), true);
        $this->assertSame($responseData['message'], "apikey.error");
    }

    public function testLoginFailedExcute()
    {
        /* @var $client \Symfony\Bundle\FrameworkBundle\Client */
        self::$staticClient->request('POST', '/api/login', [
            'email'    => self::USER['email'],
            'password' => self::USER['password'],
        ]);

        $response = self::$staticClient->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testLoginSuccessExcute()
    {
        /* @var $client \Symfony\Bundle\FrameworkBundle\Client */
        self::$staticClient->request(Request::METHOD_POST, '/api/login', [
            'email'    => self::USER['email'],
            'password' => self::USER['password'],
        ]);

        $response = self::$staticClient->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('token', $responseData['data']);
        $this->token = $responseData['data']['token'];
    }

    public function testgetProfileExcute()
    {
        /* @var $client \Symfony\Bundle\FrameworkBundle\Client */
        self::$staticClient->request(Request::METHOD_GET, '/api/profile', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
        ]);

        $response = self::$staticClient->getResponse();
        $responseData = json_decode($response->getContent(), true);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('data', $responseData);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
        $users = $this->entityManager->getRepository("AppBundle:User")->findAll();
        foreach($users as $user)
        {
            $this->entityManager->remove($user);
        }
        $this->entityManager->flush();

        $this->entityManager->close();
        $this->entityManager = null; // avoid memory leaks
    }

}
