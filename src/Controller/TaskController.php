<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Task;
use App\Entity\TaskHistory;
use App\Entity\TaskDiscuss;
use App\Entity\TaskFile;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Tools\Pagination\CountWalker;
use Doctrine\ORM\Tools\Pagination\CountOutputWalker;
use Doctrine\ORM\Query\ResultSetMapping;
use App\Tools\CustomOutputWalker;

class TaskController extends Controller
{
    /**
     * @Route("/", name="tasks")
     */
    public function index(Request $request)
    {
        $page = intval($request->query->get('page', 1));
        if(!$page || $page < 1) {
            $page = 1;
        }
        $tasks   = array();
        $users   = array();
        $nbtasks = 0;
        $nbTasksPerPage = 50;

        // Filters
        $title        = trim($request->query->get('title', ''));
        $status       = $request->query->get('status', null);
        $assignment   = $request->query->get('assignment', null);
        $sort         = $request->query->get('sort', null);
        $sort_desc    = $request->query->has('sort_desc');

        $filtersQuery = '';
        $filters      = array(
            'title'      => '',
            'status'     => '',
            'assignment' => ''
        );

        if(!isset(Task::STATUS[$status])) {
            $status = null;
        }

        // Get list of tasks for current project
        if($projectId = $this->getCurrentProject($request)) {
            $user              = $this->get('security.token_storage')->getToken()->getUser();
            $showInternalTasks = ($user->getStatus() != 0);

            $qb = $this->getDoctrine()->getRepository(Task::class)->getTasksOfProjectQb(
                $projectId,
                $showInternalTasks
            );

            // Order by
            $ASC = ($sort_desc ? 'DESC' : 'ASC');
            switch($sort) {
                case 'id':           $qb->orderBy('t.id', $ASC); break;
                case 'urgency':      $qb->orderBy('t.urgency', $ASC); break;
                case 'type':         $qb->orderBy('t.type', $ASC); break;
                case 'date':         $qb->orderBy('t.date_creation', $ASC); break;
                case 'title':        $qb->orderBy('t.title', $ASC); break;
                case 'assigned_to':  $qb->addSelect('(CASE WHEN u.id IS NULL THEN 0 ELSE 1 END) AS HIDDEN user');
                                     $qb->orderBy('user', $ASC)->addOrderBy('u.firstName', $ASC)->addOrderBy('u.lastName', $ASC);
                                     break;
                case 'status':       $qb->orderBy('t.status', $ASC); break;
                default:
                    $sort = 'id';
                    $sort_desc = true;
                    $qb->orderBy('t.id', 'DESC');
            }

            // List of users for filters
            $usersQb = clone $qb;
            $users = $usersQb
            ->select('u.id, u.firstName, u.lastName')
            ->setFirstResult(null)
            ->setMaxResults(null)
            ->groupBy('u.id')
            ->orderBy('u.firstName, u.lastName')
            ->getQuery()->getScalarResult();

            // Add filters
            if($title) {
                $filters['title']  = $title;
                $filtersQuery      = '&title=' . urlencode($title);

                $qb->andWhere($qb->expr()->like('t.title', ':title'))
                   ->setParameter('title', '%'. addcslashes($title, "%_").'%');
            }
            if($status !== null) {
                $filters['status'] = intval($status);
                $filtersQuery      = '&status=' . urlencode($status);

                $qb->andWhere('t.status = :status')
                   ->setParameter('status', $status);
            } else {
                $qb->andWhere('t.status != :status')
                   ->setParameter('status', Task::ARCHIVE);
            }
            if($assignment) {
                $filters['assignment'] = $assignment;
                $filtersQuery          = '&assignment=' . urlencode($assignment);

                $qb->andWhere('t.assigned_to = :assigned_to')
                   ->setParameter('assigned_to', intval($assignment));
            }

            // Add pagination
            $qb->setFirstResult(($page-1)*$nbTasksPerPage);
            $qb->setMaxResults($nbTasksPerPage);

            // Get results
            $query = $qb->getQuery();
            $query->setHint(Query::HINT_FORCE_PARTIAL_LOAD, 1);
            $tasks = $query->getResult(Query::HYDRATE_ARRAY);

            if($tasks || $page > 1) {
                $paginator = new Paginator($query);
                $nbtasks   = $paginator->count();
            }
        }
        if(!sizeof($tasks) && $nbtasks) {
            return $this->redirectToRoute('tasks', $filters);
        }
        return $this->render('task/index.html.twig', array(
            'projectId'    => $projectId,
            'tasks'        => $tasks,

            // Pagination
            'nbtasks'      => $nbtasks,
            'page'         => $page,
            'nbpages'      => ceil($nbtasks / $nbTasksPerPage),

            // Filters
            'filtersQuery' => $filtersQuery,
            'filters'      => $filters,
            'users'        => $users,

            // Sort
            'sort'         => $sort,
            'sort_desc'    => $sort_desc,

            // Smallint to text
            'TYPE'         => Task::TYPE,
            'URGENCY'      => Task::URGENCY,
            'STATUS'       => Task::STATUS
        ));
    }

    /**
     * @Route("/task/goto", name="goto_task", methods={"POST"})
     */
    public function gotoTask(Request $request)
    {
        if($taskId = intval($request->request->get('task', 0))) {
            return $this->redirectToRoute('edit_task', array('taskId' => $taskId));
        } else {
            return $this->redirectToRoute('tasks');
        }
    }

    /**
     * Get current project id (first project of list if undefined)
     */
    protected function getCurrentProject(Request $request)
    {
        $projectId = $request->getSession()->get('project', 0);

        if(!$projectId) {
            $ctl = $this->get('ProjectController');
            $ctl->setContainer($this->container);

            if($projects = $ctl->getProjects($request)) {
                $projectId = $projects[0]['id'];

                $request->getSession()->set('project', $projectId);
            }
        }
        return $projectId;
    }

    /**
     * @Route("/task/new", name="new_task")
     */
    public function newTask(Request $request)
    {
        return $this->renderForm($request, new Task, true);
    }

    /**
     * @Route("/task/edit/{taskId}", name="edit_task", requirements={"taskId"="\d+"})
     */
    public function editProject(Request $request, $taskId)
    {
        if(!$task = $this->getDoctrine()->getRepository(Task::class)->findOneById($taskId)) {
            return $this->render404();
        }

        return $this->renderForm($request, $task);
    }

    protected function render404()
    {
        return $this->render('task/form.html.twig');
    }

    /**
     * Common response to newTask and editTask
     */
    protected function renderForm(Request $request, Task $task, $isNew = false)
    {
        if(!$projectId = $this->getCurrentProject($request)) {
            return $this->redirectToRoute('tasks');
        }
        $user  = $this->get('security.token_storage')->getToken()->getUser();

        // Create form
        $formBuilder = $this->createFormBuilder($task);

        if(!$isNew) {
            $formBuilder
            ->add('id', Type\TextType::class, array(
                'disabled'    => true
            ))
            ->add('date_creation', Type\DateTimeType::class, array(
                'disabled'    => true,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text'
            ))
            ->add('created_by', Type\TextType::class, array(
                'disabled' => true
            ));
        };
        if($user->getStatus() != 0) {
            $formBuilder->add('internal', Type\CheckboxType::class, array(
                'required' => false
            ));
        }
        $formBuilder
        ->add('title', Type\TextType::class)
        ->add('description', Type\TextareaType::class, array(
            'required' => false,
            'attr'     => array('maxlength' => 65535),
            'row_attr' => array('class' => 'description')
        ))
        ->add('type', Type\ChoiceType::class, array(
            'choices'  => array_flip(Task::TYPE),
            'expanded' => true,
            'multiple' => false
        ))
        ->add('urgency', Type\ChoiceType::class, array(
            'choices'  => array_flip(Task::URGENCY),
            'expanded' => true,
            'multiple' => false
        ));

        $formBuilder->add('status', Type\ChoiceType::class, array(
            'choices'  => array_flip(Task::STATUS),
            'expanded' => false,
            'multiple' => false
        ))
        ->add('assigned_to', EntityType::class, array(
            'class'         => 'App\Entity\User',
            'query_builder' => function (EntityRepository $er) use ($projectId) {
                return $er->getUsersOfProjectQb($projectId);
            },
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'attr' => array('autocomplete' => 'off')
        ))
        ->add('save', Type\SubmitType::class, array(
            'label'    => $isNew ? 'Create Task' : 'Update Task',
            'attr'     => array('class' => 'btn', 'data-counter' => false)
        ));

        $form = $formBuilder->getForm();

        // Handle submission
        $checkHistory = [
            "Status"     => $task->getStatus(), 
            "Type"       => $task->getType(), 
            "Urgency"    => $task->getUrgency(), 
            "AssignedTo" => $task->getAssignedTo()
        ];
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $currentUser = $this->get('security.token_storage')->getToken()->getUser();

            if($isNew) {
                $project = $em->getPartialReference('App\Entity\Project', $projectId);
                $task->setProject($project);
                $task->setDateCreation(new \DateTime());
                $task->setCreatedBy($currentUser);
            }
            if(!$task->getDescription()) {
                $task->setDescription('');
            }
            if(!$task->getStatus()) {
                $task->setStatus(0);
            }
            $em->persist($task);

            // Add discuss
            if($text = trim($request->request->get("discuss", ""))) {
                $discuss = new TaskDiscuss();
                $discuss->setCreatedBy($currentUser);
                $discuss->setDate(new \DateTime());
                $discuss->setInternal($request->request->get("discuss_internal", 0));
                $discuss->setText($text);

                $task->addDiscuss($discuss);
                $em->persist($discuss);
            }

            // Keep history of status changes
            $addHistory = false;
            foreach($checkHistory as $prop => $value) {
                if($task->{"get" . $prop}() != $value) {
                    $addHistory = true;
                    break;
                }
            }
            if($addHistory) {
                $history = new TaskHistory();
                $history->setStatus($task->getStatus());
                $history->setType($task->getType());
                $history->setUrgency($task->getUrgency());
                $history->setAssignedTo($task->getAssignedTo());
                $history->setUpdatedBy($currentUser);
                $history->setDate(new \DateTime());

                $task->addHistory($history);
                $em->persist($history);
            }

            // Upload files
            if(isset($_FILES['attachment'])) {
                $uploadedFiles = $this->uploadFiles(
                    $task->getId(),
                    $request->files->get('attachment'),
                    $_FILES['attachment']['tmp_name']
                );
                foreach($uploadedFiles as $uploadedFile) {
                    $file = new TaskFile();
                    $file->setAddedBy($currentUser);
                    $file->setDate(new \DateTime());
                    $file->setName($uploadedFile['name']);
                    $file->setDriveId($uploadedFile['id']);
                    $file->setFilesize($uploadedFile['size']);

                    $task->addFile($file);
                    $em->persist($file);
                }
            }

            // Delete file
            if($driveId = $request->request->get('delete_file')) {
                $this->deleteFile($driveId);

                foreach($task->getFiles() as $file) {
                    if($file->getDriveId() != $driveId) {
                        continue;
                    }
                    $task->removeFile($file);
                    $em->remove($file);
                    break;
                }
            }

            $em->flush();

            $this->addFlash('success', 'The task has been ' . ($isNew ? 'created' : 'updated') . '!');
            $args = array('taskId' => $task->getId());

            if($tab = $request->query->get('tab')) {
                $args["tab"] = $tab;
            }
            return $this->redirectToRoute('edit_task', $args);
        }

        // Display form
        return $this->render('task/form.html.twig', array(
            'form'   => $form->createView(),
            'taskId' => $isNew ? false : $task->getId(),
            'title'  => $isNew ? 'Create Task' : 'Update Task #' . $task->getId(),
            'tab'    => $request->query->get('tab', 'discuss')
        ));
    }

    /**
     * Upload files to Google Drive
     * @param string $task
     * @param array $files
     * @return array
     */
    protected function uploadFiles($taskId, $files, $filepath)
    {
        $toSave    = [];

        $folderId  = false;
        $client    = $this->getGoogleDriveClient();
        $service   = new \Google_Service_Drive($client);
        $shareable = new \Google_Service_Drive_Permission(array(
            'type' => 'anyone',
            'role' => 'reader'
        ));

        foreach($files as $i => $file) {
            if($file->getError() || !$file->getSize()) {
                continue;
            }

            // Retrieve folder id
            if(!$folderId) {

                // Query Google Drive to get folder
                $response = $service->files->listFiles(array(
                    'q'        => "name='task-" . $taskId . "' and mimeType='application/vnd.google-apps.folder' and trashed = false",
                    'fields'   => 'nextPageToken, files(id, name)',
                    'pageSize' => 1
                ));
                if($response->files){
                    $folderId = $response->files[0]['id'];

                // Folder doesn't exist: create it
                } else {
                    $driveMeta = new \Google_Service_Drive_DriveFile(array(
                        'name'     => 'task-' . $taskId,
                        'mimeType' => 'application/vnd.google-apps.folder'
                    ));
                    $driveFile = $service->files->create($driveMeta, array(
                        'fields' => 'id'
                    ));
                    $folderId = $driveFile->id;
                }
            }

            // Add file to folder
            $name = $file->getClientOriginalName();
            $mime = $file->getClientMimeType();

            $driveMeta = new \Google_Service_Drive_DriveFile(array(
                'name'       => $name,
                'parents'    => array($folderId)
            ));
            $driveFile = $service->files->create($driveMeta, array(
                'data'       => file_get_contents($filepath[$i]),
                'mimeType'   => $mime,
                'uploadType' => 'multipart',
                'fields'     => 'id'
            ));

            // Anyone with the link can view this file
            $service->permissions->create(
                $driveFile->id,
                $shareable,
                array('fields' => 'id')
            );

            // Save to database
            $toSave[] = array(
                'id'   => $driveFile->id,
                'name' => $name,
                'size' => $this->human_filesize($file->getSize())
            );
        }
        return $toSave;
    }

    /**
     * Delete file from Google Drive
     * @param string $driveId
     */
    protected function deleteFile($driveId)
    {
        $client  = $this->getGoogleDriveClient();
        $service = new \Google_Service_Drive($client);
        $service->files->delete($driveId);
    }

    /**
     * @param integer $bytes
     * @return string
     */
    protected function human_filesize($bytes, $decimals = 2) {
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }

    /**
     * @Route("/task/history/{taskId}", name="history_task", requirements={"taskId"="\d+"})
     */
    public function history(Request $request, $taskId)
    {
        if(!$task = $this->getDoctrine()->getRepository(Task::class)->findOneById($taskId)) {
            if($request->isXmlHttpRequest()) {
                throw $this->createNotFoundException('The task does not exist');
            } else {
                return $this->render404();
            }
        }

        return $this->render('task/history.html.twig', array(
            'task'         => $task,

            // Smallint to text
            'TYPE'         => Task::TYPE,
            'URGENCY'      => Task::URGENCY,
            'STATUS'       => Task::STATUS
        ));
    }

    /**
     * @Route("/task/discuss/{taskId}", name="discuss_task", requirements={"taskId"="\d+"})
     */
    public function discuss(Request $request, $taskId, $internal = false)
    {
        if(!$task = $this->getDoctrine()->getRepository(Task::class)->findOneById($taskId)) {
            if($request->isXmlHttpRequest()) {
                throw $this->createNotFoundException('The task does not exist');
            } else {
                return $this->render404();
            }
        }
        if($task->getInternal()) {
            $this->denyAccessUnlessGranted('USER_SUPPORT');
        }

        return $this->render('task/discuss.html.twig', array(
            'task'     => $task,
            'internal' => $internal,
            'date'     => new \DateTime()
        ));
    }

    /**
     * @Route("/task/attachments/{taskId}", name="discuss_attachments", requirements={"taskId"="\d+"})
     */
    public function attachments(Request $request, $taskId)
    {
        if(!$task = $this->getDoctrine()->getRepository(Task::class)->findOneById($taskId)) {
            if($request->isXmlHttpRequest()) {
                throw $this->createNotFoundException('The task does not exist');
            } else {
                return $this->render404();
            }
        }
        return $this->render('task/attachments.html.twig', array(
            'files' => $task->getFiles()
        ));
    }

    /**
     * Retrieve Google Drive Client
     * @return \Google_Client
     */
    protected function getGoogleDriveClient()
    {
        $configPath = $this->get('kernel')->getRootDir() . '/../config/';

        // Create Google Drive Client
        $client = new \Google_Client();
        $client->setApplicationName('Google Drive API PHP Quickstart');
        $client->setScopes(\Google_Service_Drive::DRIVE_METADATA_READONLY);
        $client->setAuthConfig($configPath . 'google_client_secret.json');
        $client->setAccessType('offline');

        // Get user credentials
        $credentialsPath = $configPath . 'credentials.json';
        $accessToken = json_decode($_ENV['GOOGLE_CREDENTIALS'], true);
        $client->setAccessToken($accessToken);

        // Refresh token if expired
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }
}