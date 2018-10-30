<?php

namespace AppBundle\DataFixtures;

use AppBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{

    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        // create 20 products! Bam!
        for($i = 1; $i <= 3; $i++)
        {
            $user = new User();
            $user->setHash(rand(0, 1000) + time());
            $user->setPassword($this->passwordEncoder->encodePassword(
                            $user, 'test1234'
            ));
            $user->setEmail("user_$i@home24.com");
            $manager->persist($user);
        }

        $manager->flush();
    }

}
