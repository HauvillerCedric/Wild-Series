<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Program;
use App\DataFixtures\CategoryFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProgramFixtures extends Fixture implements DependentFixtureInterface
{
    private SluggerInterface $slugger;
    
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $admin = $this->getReference('admin');
        $contributor = $this->getReference('contributor');
        for ($i = 0; $i < 50; $i++) {
            $program = new Program();
            $program->setTitle($faker->sentence());
            $program->setSlug($this->slugger->slug($program->getTitle()));
            $program->setSynopsis($faker->paragraphs(3, true));
            $program->setCountry($faker->country());
            $program->setYear($faker->year());
            $randomCategoryKey = array_rand(CategoryFixtures::CATEGORIES);
            $categoryName = CategoryFixtures::CATEGORIES[$randomCategoryKey];
            $program->setCategory($this->getReference('categorie_' . $categoryName));
            $this->addReference('program_' . $i, $program);
            $user = $i % 2 === 0 ? $contributor : $admin;
            $program->setOwner($user);

           
            
            $manager->persist($program);
        }
        $manager->flush();
    }
    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
        ];
    }
}