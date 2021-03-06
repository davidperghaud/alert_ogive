<?php

namespace OGIVE\UserBundle\Controller;

use OGIVE\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @Route("/users", name="users_index")
     * @Method({"GET"})
     * @param Request $request
     */
    public function getUsersAction(Request $request) {

        if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $userManager = $this->container->get('fos_user.user_manager');

        $page = 1;
        //$maxResults = 8;
        $route_param_page = array();
        $route_param_search_query = array();
        $search_query = null;
        $placeholder = "Rechercher un utilisateur...";
        if ($request->get('page')) {
            $page = intval(htmlspecialchars(trim($request->get('page'))));
            $route_param_page['page'] = $page;
        }
        if ($request->get('search_query')) {
            $search_query = htmlspecialchars(trim($request->get('search_query')));
            $route_param_search_query['search_query'] = $search_query;
        }
        //$start_from = 0;
        $total_pages = 1;
        try {
            $users = $userManager->findUsers();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
        return $this->render('OGIVEUserBundle:users:index.html.twig', array(
                    'users' => $users,
                    'total_pages' => $total_pages,
                    'page' => $page,
                    'route_param_page' => $route_param_page,
                    'route_param_search_query' => $route_param_search_query,
                    'search_query' => $search_query,
                    'placeholder' => $placeholder
        ));
    }

    /**
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @Route("/users/{id}", name="user_update")
      @Method({"GET", "POST"})
     */
    public function updateUserAction(Request $request, User $user) {
        if (!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $form = $this->createForm('OGIVE\UserBundle\Form\RegistrationType', $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager = $this->container->get('fos_user.user_manager');
            if ($request->get('role')) {
                $user->setRoles(array($request->get('role')));
            }
            $userManager->updateUser($user);
            return $this->redirectToRoute('users_index');
        }
        $user_role = $user->getRoles()[0];
        return $this->render('OGIVEUserBundle:users:update.html.twig', array(
                    'user' => $user,
                    'user_role' => $user_role,
                    'form' => $form->createView()
        ));
    }

}
