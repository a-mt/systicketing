<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;

class ProfileController extends Controller
{
    /**
     * @Route("/profile", name="profile", methods={"GET"})
     */
    public function profile(Request $request)
    {
        $session = $request->getSession();
        $errors  = false;

        if($session->has('errors')) {
            $errors = $session->get('errors');
            $session->remove('errors');
        }
        return $this->render('profile/index.html.twig', array('errors' => $errors));
    }

    /**
     * @Route("/profile", name="profile_submit", methods={"POST"})
     */
    public function profileSubmit(Request $request)
    {
        $encoder   = $this->get('security.password_encoder');
        $validator = $this->get('validator');
        $user      = $this->get('security.token_storage')->getToken()->getUser();
        $POST      = $request->request;
        $errors    = [];

        // Check CSRF token
        if(!$this->isCsrfTokenValid('udpate_password', $POST->get('_csrf_token'))) {
            $errors = array("_csrf_token" => ["Invalid CSRF token."]);

        // Check current password
        } else if($user->getPassword() != $encoder->encodePassword($user, $POST->get('current_password'))) {
            $errors = array("currentPassword" => ["Bad Credentials."]);

        // Check password == confirm password
        } else if($POST->get('password') != $POST->get('password_confirm')) {
            $errors = array("plainPassword" => ["Passwords don't match."]);

        // Check new password is valid
        } else {
            $user->setPlainPassword($POST->get('password'));
            $invalid = $validator->validate($user);

            for($i = 0; $i < $invalid->count(); $i++) {
                $error = $invalid->get($i);
                var_dump($error);
                $prop  = $error->getPropertyPath();

                if(isset($errors[$prop])) {
                    $errors[$prop][] =  $error->getMessage();
                } else {
                    $errors[$prop]   = [$error->getMessage()];
                }
            }
        }

        // Save new password
        if(!$errors) {
            $password = $encoder->encodePassword($user, $user->getPlainPassword());

            if($password != $user->getPassword()) {
                $user->setPassword($password);

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Your changes were saved!');
                return $this->redirectToRoute('profile');
            }
        }

        // Redirect to form with errors
        $session = $request->getSession();
        $session->set('errors', $errors);

        return $this->redirectToRoute('profile');
    }
}