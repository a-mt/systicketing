<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/admin/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="users")
     */
    public function index() {
        return $this->render('user/index.html.twig', array(
            'users' => $this->getDoctrine()->getRepository(User::class)->getUsers()
        ));
    }

    /**
     * @Route("/new", name="new_user")
     */
    public function createuser(Request $request)
    {
        return $this->renderForm($request, new User(), true);
    }

    /**
     * @Route("/{userId}", name="edit_user", requirements={"userId"="\d+"})
     * @ParamConverter("user", class="App:User", options={"id" = "userId"})
     */
    public function edituser(Request $request, User $user)
    {
        return $this->renderForm($request, $user);
    }

    /**
     * Common response to newUser and editUser
     */
    protected function renderForm(Request $request, User $user, $isNew = false)
    {
        // Create form
        $formBuilder = $this->createFormBuilder($user, array('validation_groups' => array($isNew ? 'Default' : 'update')))
            ->add('email', Type\EmailType::class)
            ->add('firstName', Type\TextType::class)
            ->add('lastName', Type\TextType::class);

        // Super admin's password cannot be changed nor be deactivated
        if($isNew || $user->getId() != 1) {
            $formBuilder->add('plainPassword', Type\RepeatedType::class, array(
                'type' => Type\PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ($isNew ? array() : array('help' => 'Leave empty to keep current password.')),
                'required' => $isNew,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ))
            ->add('isActive', Type\CheckboxType::class, array('label' => 'Active'))
            ->add('status', Type\ChoiceType::class, array(
                'choices'  => array_flip(User::STATUS),
                'expanded' => true,
                'multiple' => false
            ));
        }
        $formBuilder->add('projects', EntityType::class, array(
                'class' => 'App\Entity\Project',
                'by_reference' => false,
                'multiple' => true,
                'required' => false,
                'expanded' => false
              ))
            ->add('save', Type\SubmitType::class, array(
                'label' => $isNew ? 'Create User' : 'Update User',
                'attr' => array('class' => 'btn')
            ));
        $form = $formBuilder->getForm();

        // Handle submission
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em  = $this->getDoctrine()->getManager();

            // Update password
            if($user->getPlainPassword()) {
                $encoder  = $this->get('security.password_encoder');

                if($isNew) {
                    $salt = md5(uniqid(mt_rand(0, 99999)) . $user->getEmail());
                    $user->setSalt($salt);
                }

                $password = $encoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password);
            }

            // Update user
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'The user has been ' . ($isNew ? 'created' : 'updated') . '!');
            return $this->redirectToRoute('edit_user', array('userId' => $user->getId()));
        }

        // Display form
        return $this->render('user/form.html.twig', array(
            'form'  => $form->createView(),
            'title' => $isNew ? 'Create User' : 'Update User #' . $user->getId()
        ));
    }
}