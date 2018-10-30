<?php

namespace AppBundle\DataFixtures;

use AppBundle\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class PostFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
        // create 20 products! Bam!
        for($i = 1; $i <= 50; $i++)
        {
            $user = new Post();
            $user->setTitle("Title {$i}");
            $user->setIsPublished(true);
            $user->setDetails("Details for post {$i}");

            $manager->persist($user);
        }

        for($i = 51; $i <= 100; $i++)
        {
            $user = new Post();
            $user->setTitle("Title {$i}");
            $user->setIsPublished(false);
            $user->setDetails("Details for post {$i}");

            $manager->persist($user);
        }

        $manager->flush();
    }

}
