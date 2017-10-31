<?php

namespace OGIVE\UserBundle\Controller;

use OGIVE\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\ViewHandler;
use FOS\RestBundle\View\View;

/**
 * Users controller.
 *
 */
class UsersController extends Controller {

    /**
     * @Rest\View()
     * @Rest\Get("/users" , name="users_index", options={ "method_prefix" = false, "expose" = true })
     * @param Request $request
     */
    public function getUsersAction(Request $request) {

        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $userManager = $this->container->get('fos_user.user_manager');

        try {
            $users = $userManager->findUsers();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
        return $this->render('OGIVEUserBundle:users:index.html.twig', array(
                    'users' => $users,
        ));
    }

    /**
     * @Rest\View()
     * @Rest\Get("/users/{id}" , name="user_update_get", options={ "method_prefix" = false, "expose" = true })
     */
    public function getUserByIdAction(User $user) {
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $form = $this->createForm('OGIVE\UserBundle\Form\RegistrationType', $user);
        return $this->renderView('OGIVEUserBundle:users:update.html.twig', array(
            'user' => $user,
            'form' => $form->createView()
        ));
    }

    /**
     * @Rest\View()
     * @Rest\Post("/users/{id}", name="user_update_post", options={ "method_prefix" = false, "expose" = true  })
     * @param Request $request
     */
    public function updateUserAction(Request $request, User $user) {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
    }

}
