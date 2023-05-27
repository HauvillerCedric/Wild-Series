<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Actor;
use App\Entity\Program;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ActorFixtures extends Fixture implements DependentFixtureInterface

{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();


        for($i = 0; $i < 10; $i++)
        {
            $actor = new Actor();
            $actor->setFirstname($faker->firstName());
            $actor->setLastname($faker->lastName());
            $birthDate = $faker->dateTimeBetween('-60 years', '-18 years');
            $actor->setBirthDate($birthDate);
            $manager->persist($actor);

            $programs = $manager->getRepository(Program::class)->findAll();
            $randomPrograms = $faker->randomElements($programs, 3);

            foreach ($randomPrograms as $program) {
                $actor->addProgram($program);
            }

        }
        
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
           ProgramFixtures::class,
        ];
    }
}


