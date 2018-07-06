<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Project;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ProjectController extends Controller
{
    /**
     * @Route("/admin/project", name="projects")
     */
    public function index(Request $request) {
        $archive = $request->query->has('archive');

        return $this->render('project/index.html.twig', array(
            'projects' => $this->getDoctrine()->getRepository(Project::class)->getProjects($archive),
            'archive'  => $archive
        ));
    }

    /**
     * @Route("/admin/project/new", name="new_project")
     */
    public function createProject(Request $request)
    {
        return $this->renderForm($request, new Project(), true);
    }

    /**
     * @Route("/admin/project/{projectId}", name="edit_project", requirements={"projectId"="\d+"})
     * @ParamConverter("project", class="App:Project", options={"id" = "projectId"})
     */
    public function editProject(Request $request, Project $project)
    {
        return $this->renderForm($request, $project);
    }

    /**
     * Common response to newProject and editProject
     */
    protected function renderForm(Request $request, Project $project, $isNew = false)
    {
        // Create form
        $formBuilder = $this->createFormBuilder($project)
            ->add('name', Type\TextType::class)
            ->add('users', EntityType::class, array(
                'class' => 'App\Entity\User',
                'multiple' => true,
                'required' => false,
                'expanded' => false
              ));
        if(!$isNew) {
            $formBuilder->add('archive', Type\CheckboxType::class, array('required' => false));
        }
        $formBuilder->add('save', Type\SubmitType::class, array(
            'label' => $isNew ? 'Create Project' : 'Update Project',
            'attr' => array('class' => 'btn')
        ));

        $form = $formBuilder->getForm();

        // Handle submission
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($project);
            $em->flush();

            $this->addFlash('success', 'Your project has been ' . ($isNew ? 'created' : 'updated') . '!');
            return $this->redirectToRoute('edit_project', array('projectId' => $project->getId()));
        }

        // Display form
        return $this->render('project/form.html.twig', array(
            'form'  => $form->createView(),
            'title' => $isNew ? 'Create Project' : 'Update Project #' . $project->getId()
        ));
    }

    /**
     * Get projects of user (using session if exists)
     */
    public function getProjects(Request $request) {
        $session = $request->getSession();

        if($session->has('projects')) {
            $projects = $session->get('projects');
        } else {
            $user     = $this->get('security.token_storage')->getToken()->getUser();
            $projects = $this->getDoctrine()->getRepository(Project::class)->getProjectsOfUser($user);
            $session->set('projects', $projects);
        }
        return $projects;
    }

    /**
     * Toolbar
     */
    public function selectProject(Request $request) {
        return $this->render('project/selectProject.html.twig', array(
            'projects' => $this->getProjects($request),
            'active'   => $request->getSession()->get('project', 0)
        ));
    }

    /**
     * Toolbar
     * @Route("/switch_project", name="switch_project", methods={"POST"})
     */
    public function switchProject(Request $request) {
        $exists = false;

        if($projectId = intval($request->request->get('projectId', 0))) {
            $projects  = $this->getProjects($request);

            foreach($projects as $project) {
                if($project['id'] == $projectId) {
                    $exists = true;
                    break;
                }
            }
        }
        if($exists) {
            $request->getSession()->set('project', $projectId);
        }
        return $this->redirectToRoute('tasks');
    }
}