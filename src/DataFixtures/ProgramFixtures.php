<?php

namespace App\DataFixtures;

use App\Entity\Program;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProgramFixtures extends Fixture implements DependentFixtureInterface
{

    public const PROGRAMS =[
        [
            'title' => 'How I Met Your Mother',
            'synopsis' => 'Mais qui est la maman?',
            'reference' => 'category_Comedie',
        ],

        [
            'title' => 'The Last of US',
            'synopsis' => 'Des monstres et des méchants',
            'reference' => 'category_Action',
        ],

        [
            'title' => 'Dragon Ball Z',
            'synopsis' => 'Les boules de cristal',
            'reference' => 'category_Animation',
        ],

        [
            'title' => 'The Expanse',
            'synopsis' => 'Dans les étoiles...mais sans budget',
            'reference' => 'category_Aventure',
        ],

        [
            'title' => 'Chambers',
            'synopsis' => 'La frousse sous la couette',
            'reference' => 'category_Horreur',
        ]

    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::PROGRAMS as $key => $programValue) {
            $program = new Program();
            $program->setTitle($programValue['title']);
            $program->setSynopsis($programValue['synopsis']);
            $program->setCategory($this->getReference($programValue['reference']));
            $manager->persist($program);
        }
       
        $manager->flush();
    }


    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont ProgramFixtures dépend
        return [
          CategoryFixtures::class,
        ];
    }
}
