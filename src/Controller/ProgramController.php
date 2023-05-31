<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Entity\Season;
use App\Entity\Episode;
use App\Entity\Program;
use App\Form\ProgramType;
use App\Service\ProgramDuration;
use App\Repository\SeasonRepository;
use App\Repository\EpisodeRepository;
use App\Repository\ProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


#[Route('/program', name: 'program_')]
class ProgramController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index( RequestStack $requestStack, ProgramRepository $programRepository): Response
    {   
        $session = $requestStack->getSession();
        if (!$session->has('total')) {
            $session->set('total', 0); // if total doesn’t exist in session, it is initialized.
        }
    
        $total = $session->get('total'); // get actual value in session with ‘total' key.
        $programs = $programRepository->findAll();

        return $this->render('program/index.html.twig', [
           'programs' => $programs
        ]);
    }


    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request,ProgramRepository $programRepository, SluggerInterface $slugger) : Response
    {
        // Create a new Category Object
        $program = new Program();
        // Create the associated Form
        $form = $this->createForm(ProgramType::class, $program);
        // Get data from HTTP request
        $form->handleRequest($request);
        // Was the form submitted ?
        if ($form->isSubmitted() && $form->isValid()) {
            
            $slug = $slugger->slug($program->getTitle());
            $program->setSlug($slug);
            
            // Deal with the submitted data
            // For example : persiste & flush the entity
            // And redirect to a route that display the result
            $programRepository->save($program, true);

             // Once the form is submitted, valid and the data inserted in database, you can define the success flash message
       $this->addFlash('success', 'La série a été ajoutée!');
    
            // Redirect to categories list
            return $this->redirectToRoute('program_index');
        }
    
        // Render the form
        return $this->render('program/new.html.twig', [
            'form' => $form,
        ]);
    }


    #[Route('/{slug}',  methods: ['GET'], name: 'show')]
    public function show(Program $program, ProgramRepository $programRepository, ProgramDuration $programDuration): Response
    {
    
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with id : '.$id.' found in program\'s table.'
            );
        }
        $duration = $programDuration->calculate($program);
        return $this->render('program/show.html.twig', [
            'program' => $program,  
            'duration' => $duration, 
        ]);
    }
    
    


    
    #[Route('/{slug}/season/{season}', name: 'season_show')]
public function showSeason(Program $program, Season $season, EpisodeRepository $episodeRepository): Response
{
    $episodes = $episodeRepository->findBy(['season' => $season]);

    return $this->render('program/season_show.html.twig', [
        'program' => $program,
        'season' => $season,
        'episodes' => $episodes,
    ]); 
}

#[Route('/{slug}/season/{season}/episode/{episode}', name: 'episode_show')]
#[ParamConverter('episode', options: ['mapping' => ['episode' => 'slug']])]
public function showEpisode(Program $program, Season $season, Episode $episode): Response
{
    return $this->render('program/episode_show.html.twig', [
        'episode' => $episode,
        'program' => $program,
        'season' => $season,
    ]);
}

}
