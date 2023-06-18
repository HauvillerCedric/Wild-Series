<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Entity\Season;
use App\Entity\Episode;
use App\Entity\Program;
use App\Form\ProgramType;
use App\Form\SearchProgramType;
use App\Service\ProgramDuration;
use App\Repository\SeasonRepository;
use App\Repository\EpisodeRepository;
use App\Repository\ProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


#[Route('/program', name: 'program_')]
class ProgramController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index( RequestStack $requestStack, ProgramRepository $programRepository, Request $request): Response
    {   
        $session = $requestStack->getSession();
        if (!$session->has('total')) {
            $session->set('total', 0); // if total doesn’t exist in session, it is initialized.
        }
    
        $total = $session->get('total'); // get actual value in session with ‘total' key.
        $programs = $programRepository->findAll();

        $form = $this->createForm(SearchProgramType::class);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
           
            $search = $form->getData()['search'];
            $programs = $programRepository->findLikeName($search);
        } else {
            $programs = $programRepository->findAll();
        }
    

        return $this->render('program/index.html.twig', [
           'programs' => $programs,
           'form' => $form,
        ]);
    }


    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request,ProgramRepository $programRepository, SluggerInterface $slugger, MailerInterface $mailer) : Response
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
            $program->setOwner($this->getUser());
            $programRepository->save($program, true);

            $email = (new Email())
            ->from($this->getParameter('mailer_from'))
            ->to('your_email@example.com')
            ->subject('Une nouvelle série vient d\'être publiée !')
            ->html('<p>Une nouvelle série vient d\'être publiée sur Wild Séries !</p>');

    $mailer->send($email);

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

    #[Route("/{slug}/edit", name: "edit", methods: ['GET', 'POST'])]
    #[Route("/", name: 'app_home')]
    #[ParamConverter('program', options: ['mapping' => ['slug' => 'slug']])]
    public function edit(Request $request, Program $program, ProgramRepository $programRepository, SluggerInterface $slugger): Response
    {
        if ($this->getUser() !== $program->getOwner()) {
            throw $this->createAccessDeniedException('You are not allowed to access this page');
         }
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugger->slug($program->getTitle());
            $program->setSlug($slug);
            $programRepository->save($program, true);
            $this->addFlash('success', 'Program updated successfully!');
            return $this->redirectToRoute('program_index');
        }
        return $this->render('program/edit.html.twig', [
            'program' => $program,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Program $program, ProgramRepository $programRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $program->getId(), $request->request->get('_token'))) {
            $programRepository->remove($program, true);
            $this->addFlash('danger', 'Program deleted successfully!');
        }
        return $this->redirectToRoute('program_index', [], Response::HTTP_SEE_OTHER);
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
