<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Episode;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for($i = 0; $i < 50; $i++) {

            $episode = new Episode();
            $episode->setTitle($faker->sentence());
            $episode->setNumber($faker->numberBetween(1, 10));
            $episode->setSynopsis($faker->paragraphs(3, true));
            $randomSeasonNumber = $faker->numberBetween(1, 10);
            $episode->setSeason($this->getReference('season_' . $randomSeasonNumber));
            $manager->persist($episode) ;
        }
    $manager->flush();
    }
public function getDependencies(): array
    {
        return [
            SeasonFixtures::class,
        ];
    }
}