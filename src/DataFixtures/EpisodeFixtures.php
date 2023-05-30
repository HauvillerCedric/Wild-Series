<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Episode;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\String\Slugger\SluggerInterface;


class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    private $slugger;
    
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }
   
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for($i = 0; $i < 50; $i++) {

            $episode = new Episode();
            $episode->setTitle($faker->sentence());
            $episode->setSlug($this->slugger->slug($episode->getTitle()));
            $episode->setNumber($faker->numberBetween(1, 10));
            $episode->setSynopsis($faker->paragraphs(3, true));
            $randomSeasonNumber = $faker->numberBetween(1, 10);
            $durationEpisode = $faker->numberBetween(45, 60);
            $episode->setDuration($durationEpisode);
            $episode->setSeason($this->getReference('season_' . $randomSeasonNumber));

            $slug = $this->slugger->slug($episode->getTitle());
            $episode->setSlug($slug);

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