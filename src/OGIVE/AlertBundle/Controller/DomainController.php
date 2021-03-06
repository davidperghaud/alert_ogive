<?php

namespace OGIVE\AlertBundle\Controller;

use OGIVE\AlertBundle\Entity\Domain;
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
 * Domain controller.
 *
 */
class DomainController extends Controller {

    /**
     * @Rest\View()
     * @Rest\Get("/domains" , name="domain_index", options={ "method_prefix" = false, "expose" = true })
     * @param Request $request
     */
    public function getDomainsAction(Request $request) {

        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $em = $this->getDoctrine()->getManager();
        $domain = new Domain();
        $page=1;
        $maxResults = 8;
        $route_param_page= array();
        $route_param_search_query= array();
        $search_query =null;
        $placeholder = "Rechercher un domaine...";
        if ($request->get('page')) {
            $page = intval(htmlspecialchars(trim($request->get('page'))));
            $route_param_page['page'] = $page;
        }
        if ($request->get('search_query')) {
            $search_query = htmlspecialchars(trim($request->get('search_query')));
            $route_param_search_query['search_query'] = $search_query;
        }
        $start_from = ($page-1)*$maxResults>=0 ? ($page-1)*$maxResults: 0;
        $total_pages = ceil(count($em->getRepository('OGIVEAlertBundle:Domain')->getAllByString($search_query))/$maxResults);
        $form = $this->createForm('OGIVE\AlertBundle\Form\DomainType', $domain);
        $domains = $em->getRepository('OGIVEAlertBundle:Domain')->getAll($start_from, $maxResults, $search_query);
        return $this->render('OGIVEAlertBundle:domain:index.html.twig', array(
                    'domains' => $domains,
                    'total_pages' => $total_pages,
                    'page' => $page,
                    'form' => $form->createView(),
                    'route_param_page'=> $route_param_page, 
                    'route_param_search_query' => $route_param_search_query,
                    'search_query' => $search_query,
                    'placeholder' => $placeholder
        ));
    }

    /**
     * @Rest\View()
     * @Rest\Get("/domains/{id}" , name="domain_get_one", options={ "method_prefix" = false, "expose" = true })
     */
    public function getDomainByIdAction(Domain $domain) {
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $serializer = $this->container->get('jms_serializer');
        if (empty($domain)) {
            return new JsonResponse(['message' => 'Domaine introuvable'], Response::HTTP_NOT_FOUND);
        }
        $form = $this->createForm('OGIVE\AlertBundle\Form\DomainType', $domain, array('method' => 'PUT'));
        $domain_details = $this->renderView('OGIVEAlertBundle:domain:show.html.twig', array(
            'domain' => $domain,
            'form' => $form->createView()
        ));
        $view = View::create(['domain_details' => $domain_details]);
        $view->setFormat('json');
        return $view;
//         return new JsonResponse(["code" => 200,'domain_details' => $domain_details], Response::HTTP_OK);
    }

    /**
     * @Rest\View()
     * @Rest\Get("/sub-domains-of-domain/{id}" , name="subDomains_of_domain", options={ "method_prefix" = false, "expose" = true })
     */
    public function getSubDomainsOfDomainAction(Domain $domain) {
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $repositorySubDomain = $this->getDoctrine()->getManager()->getRepository('OGIVEAlertBundle:SubDomain');
        $subDomains = $repositorySubDomain->findBy(array('domain' => $domain, 'status' => 1, 'state' => 1));
        return $this->render('OGIVEAlertBundle:domain:subDomains_of_domain.html.twig', array(
                    'subDomains' => $subDomains
        ));
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/domains", name="domain_add", options={ "method_prefix" = false, "expose" = true  })
     */
    public function postDomainsAction(Request $request) {
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $domain = new Domain();
        $repositoryDomain = $this->getDoctrine()->getManager()->getRepository('OGIVEAlertBundle:Domain');
        $repositorySubDomain = $this->getDoctrine()->getManager()->getRepository('OGIVEAlertBundle:SubDomain');
        $form = $this->createForm('OGIVE\AlertBundle\Form\DomainType', $domain);
        $serializer = $this->container->get('jms_serializer');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($repositoryDomain->findOneBy(array('name' => $domain->getName(), 'status' => 1))) {
                return new JsonResponse(["success" => false, 'message' => 'Un domaine avec ce nom existe dejà'], Response::HTTP_BAD_REQUEST);
            }

            if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                $sendActivate = $request->get('send_activate');
                if ($sendActivate && $sendActivate === 'on') {
                    $domain->setState(1);
                } else {
                    $domain->setState(0);
                }
            }
            $subdomains = $domain->getSubDomains();
            foreach ($subdomains as $subDomain) {
                $subDomainUnique = $repositorySubDomain->findOneBy(array('name' => $subDomain->getName(), 'status' => 1));
                if ($subDomainUnique && $subDomainUnique->getDomain()) {
                    $domain->removeSubDomain($subDomain);
                    $return_string = "Le sous domaine ".$subDomainUnique->getName()." appartient déjà au domaine ".$subDomainUnique->getDomain()->getName();
                    return new JsonResponse(["success" => false, 'message' => $return_string], Response::HTTP_BAD_REQUEST);
                } else {
                    if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                        if ($sendActivate && $sendActivate === 'on') {
                            $subDomain->setState(1);
                        }
                        $subDomain->setDomain($domain);
                    }
                    $subDomain->setDomain($domain);
                }
            }            
            $domain = $repositoryDomain->saveDomain($domain);
//            $domain_content_grid = $this->renderView('OGIVEAlertBundle:domain:domain-grid.html.twig', array('domain' => $domain));
//            $domain_content_list = $this->renderView('OGIVEAlertBundle:domain:domain-list.html.twig', array('domain' => $domain));
//            $view = View::create(["code" => 200, 'domain' => $domain, 'domain_content_grid' => $domain_content_grid, 'domain_content_list' => $domain_content_list]);
            $view = View::create(['domain' => $domain, "message" => 'Domaine Ajouté avec succès']);
            $view->setFormat('json');
            return $view;
//            return new JsonResponse(["code" => 200, 'domain_content_grid' => $domain_content_grid, 'domain_content_list' => $domain_content_list], Response::HTTP_CREATED);
        } else {
            $view = View::create($form);
            $view->setFormat('json');
            return $view;
//            return new JsonResponse($form, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_OK)
     * @Rest\Delete("/domains/{id}", name="domain_delete", options={ "method_prefix" = false, "expose" = true  })
     */
    public function removeDomainAction(Domain $domain) {

        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $serializer = $this->container->get('jms_serializer');
        $repositoryDomain = $this->getDoctrine()->getManager()->getRepository('OGIVEAlertBundle:Domain');
        if ($domain) {
            $repositoryDomain->deleteDomain($domain);
            $view = View::create(['domain' => $domain, "message" => 'Domaine supprimé avec succès']);
            $view->setFormat('json');
            return $view;
//            return new JsonResponse(["message" => 'Domaine supprimé avec succès'], Response::HTTP_OK);
        } else {
            return new JsonResponse(["message" => 'Domaine introuvable'], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @Rest\View()
     * @Rest\Put("/domains/{id}", name="domain_update", options={ "method_prefix" = false, "expose" = true  })
     * @param Request $request
     */
    public function putDomainAction(Request $request, Domain $domain) {
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        return $this->updateDomainAction($request, $domain);
    }

    public function updateDomainAction(Request $request, Domain $domain) {

        $repositoryDomain = $this->getDoctrine()->getManager()->getRepository('OGIVEAlertBundle:Domain');
        $repositorySubDomain = $this->getDoctrine()->getManager()->getRepository('OGIVEAlertBundle:SubDomain');
        $originalSubDomains = new \Doctrine\Common\Collections\ArrayCollection();
        $serializer = $this->container->get('jms_serializer');
        if (empty($domain)) {
            return new JsonResponse(['message' => 'Domaine introuvable'], Response::HTTP_NOT_FOUND);
        }

        if ($request->get('action') == 'enable') {
            $domain->setState(1);
            $subDomains = $domain->getSubDomains();
            foreach ($subDomains as $subDomain) {
                $subDomain->setState(1);
                $subDomain->setDomain($domain);
            }
            $domain = $repositoryDomain->updateDomain($domain);
            return new JsonResponse(['message' => 'Domaine activé avec succcès !'], Response::HTTP_OK
            );
        }

        if ($request->get('action') == 'disable') {
            $domain->setState(0);
            $domain = $repositoryDomain->updateDomain($domain);
            $subDomains = $domain->getSubDomains();
            foreach ($subDomains as $subDomain) {
                $subDomain->setState(0);
                $subDomain->setDomain($domain);
            }
            return new JsonResponse(['message' => 'Domaine désactivé avec succcès !'], Response::HTTP_OK
            );
        }
        foreach ($domain->getSubDomains() as $subDomain) {
            $originalSubDomains->add($subDomain);
        }
        $form = $this->createForm('OGIVE\AlertBundle\Form\DomainType', $domain, array('method' => 'PUT'));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $domainUnique = $repositoryDomain->findOneBy(array('name' => $domain->getName(), 'status' => 1));
            if ($domainUnique && $domainUnique->getId() != $domain->getId()) {
                return new JsonResponse(["success" => false, 'message' => 'Un domaine avec ce nom existe dejà'], Response::HTTP_BAD_REQUEST);
            }
            if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                $sendActivate = $request->get('send_activate');
                if ($sendActivate && $sendActivate === 'on') {
                    $domain->setState(1);
                } else {
                    $domain->setState(0);
                }
            }
            
            foreach ($originalSubDomains as $subDomain) {
                if (false === $domain->getSubDomains()->contains($subDomain)) {
                    // remove the panne from the piece
                    $domain->getSubDomains()->removeElement($subDomain);
                    // if it was a many-to-one relationship, remove the relationship like this
                    $subDomain->setDomain(null);
                    $subDomain->setStatus(0);
                    $repositorySubDomain->updateSubDomain($subDomain);
                    // if you wanted to delete the Piece entirely, you can also do that
                    // $em->remove($piece);
                }
            }
            $subDomains = $domain->getSubDomains();
            foreach ($subDomains as $subDomain) {
                $subDomainUnique = $repositorySubDomain->findOneBy(array('name' => $subDomain->getName(), 'status' => 1));
                if ($subDomainUnique && $subDomainUnique->getId() != $subDomain->getId()) {
                    $domain->removeSubDomain($subDomain);
                    $return_string = "Le sous domaine ".$subDomainUnique->getName()." appartient déjà au domaine ".$subDomainUnique->getDomain()->getName();
                    return new JsonResponse(["success" => false, 'message' => $return_string], Response::HTTP_BAD_REQUEST);
                } else {
                    if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                        if ($sendActivate && $sendActivate === 'on') {
                            $subDomain->setState(1);
                        } else {
                            $subDomain->setState(0);
                        }
                    }
                    $subDomain->setDomain($domain);
                }
            }
            $domain = $repositoryDomain->updateDomain($domain);
//            $domain_content_grid = $this->renderView('OGIVEAlertBundle:domain:domain-grid-edit.html.twig', array('domain' => $domain));
//            $domain_content_list = $this->renderView('OGIVEAlertBundle:domain:domain-list-edit.html.twig', array('domain' => $domain));
//            $view = View::create(["code" => 200, 'domain' => $domain, 'domain_content_grid' => $domain_content_grid, 'domain_content_list' => $domain_content_list]);
            $view = View::create(['domain' => $domain, "message" => 'Domaine modifié avec succès']);
            $view->setFormat('json');
            return $view;
//            return new JsonResponse(["code" => 200, 'domain_content_grid' => $domain_content_grid, 'domain_content_list' => $domain_content_list], Response::HTTP_OK);
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $view = View::create($form);
            $view->setFormat('json');
            return $view;
//            return new JsonResponse($form, Response::HTTP_BAD_REQUEST);
        } else {
            $edit_domain_form = $this->renderView('OGIVEAlertBundle:domain:edit.html.twig', array('form' => $form->createView(), 'domain' => $domain));
            $view = View::create(['edit_domain_form' => $edit_domain_form]);
            $view->setFormat('json');
            return $view;
//            return new JsonResponse(["code" => 200, 'edit_domain_form' => $edit_domain_form], Response::HTTP_OK);
        }
    }

}
