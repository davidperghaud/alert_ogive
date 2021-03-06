<?php

namespace OGIVE\AlertBundle\Controller;

use OGIVE\AlertBundle\Entity\Additive;
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
 * Additive controller.
 *
 */
class AdditiveController extends Controller {

    /**
     * @Rest\View()
     * @Rest\Get("/additives" , name="additive_index", options={ "method_prefix" = false, "expose" = true })
     * @param Request $request
     */
    public function getAdditivesAction(Request $request) {
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $em = $this->getDoctrine()->getManager();
        $additive = new Additive();
        $page = 1;
        $maxResults = 6;
        $route_param_page = array();
        $route_param_search_query = array();
        $search_query = null;
        $placeholder = "Rechercher un additif...";
        $start_date = null;
        $end_date = null;
        $owner = null;
        $domain = null;
        if ($request->get('page')) {
            $page = intval(htmlspecialchars(trim($request->get('page'))));
            $route_param_page['page'] = $page;
        }
        if ($request->get('search_query')) {
            $search_query = htmlspecialchars(trim($request->get('search_query')));
            $route_param_search_query['search_query'] = $search_query;
        }
        if ($request->get('start-date')) {
            $start_date = htmlspecialchars(trim($request->get('start-date')));
            $route_param_search_query['start-date'] = $start_date;
        }
        if ($request->get('end-date')) {
            $end_date = htmlspecialchars(trim($request->get('end-date')));
            $route_param_search_query['end-date'] = $end_date;
        }
        if ($request->get('owner')) {
            $owner = htmlspecialchars(trim($request->get('owner')));
            $route_param_search_query['owner'] = $owner;
        }
        if ($request->get('domain')) {
            $domain = htmlspecialchars(trim($request->get('domain')));
            $route_param_search_query['domain'] = $domain;
        }
        $start_from = ($page - 1) * $maxResults >= 0 ? ($page - 1) * $maxResults : 0;
        $total_procedures = count($em->getRepository('OGIVEAlertBundle:Additive')->getAllByQueriedParameters($search_query, $start_date, $end_date, $owner, $domain));
        $total_pages = ceil($total_procedures / $maxResults);
        $form = $this->createForm('OGIVE\AlertBundle\Form\AdditiveType', $additive);
        $additives = $em->getRepository('OGIVEAlertBundle:Additive')->getAll($start_from, $maxResults, $search_query, $start_date, $end_date, $owner, $domain);
        $owners = $em->getRepository('OGIVEAlertBundle:Owner')->findBy(array("state"=>1, "status"=>1));
        $domains = $em->getRepository('OGIVEAlertBundle:Domain')->findBy(array("state"=>1, "status"=>1));
        return $this->render('OGIVEAlertBundle:additive:index.html.twig', array(
                    'additives' => $additives,
                    'total_procedures'=> $total_procedures,
                    'total_pages' => $total_pages,
                    'page' => $page,
                    'form' => $form->createView(),
                    'route_param_page' => $route_param_page,
                    'route_param_search_query' => $route_param_search_query,
                    'search_query' => $search_query,
                    'placeholder' => $placeholder,
                    'owners' => $owners,
                    'domains'=> $domains,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'queried_owner'=> $owner,
                    'queried_domain'=> $domain
        ));
    }

    /**
     * @Rest\View()
     * @Rest\Get("/additives/{id}" , name="additive_get_one", options={ "method_prefix" = false, "expose" = true })
     */
    public function getAdditiveByIdAction(Additive $additive) {
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        if (empty($additive)) {
            return new JsonResponse(['message' => "Additif introuvable"], Response::HTTP_NOT_FOUND);
        }
        $form = $this->createForm('OGIVE\AlertBundle\Form\AdditiveType', $additive, array('method' => 'PUT'));
        $additive_details = $this->renderView('OGIVEAlertBundle:additive:show.html.twig', array(
            'additive' => $additive,
            'form' => $form->createView()
        ));
        $view = View::create(['additive_details' => $additive_details]);
        $view->setFormat('json');
        return $view;
        //return new JsonResponse(['additive_details' => $additive_details], Response::HTTP_OK);
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/additives", name="additive_add", options={ "method_prefix" = false, "expose" = true })
     */
    public function postAdditivesAction(Request $request) {
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $additive = new Additive();
        $repositoryAdditive = $this->getDoctrine()->getManager()->getRepository('OGIVEAlertBundle:Additive');
        $repositoryOwner = $this->getDoctrine()->getManager()->getRepository('OGIVEAlertBundle:Owner');

        if ($request->get('testunicity') == 'yes' && $request->get('reference')) {
            $reference = $request->get('reference');
            if ($repositoryAdditive->findOneBy(array('reference' => $reference, 'status' => 1))) {
                return new JsonResponse(["success" => false, 'message' => "Un additif avec cette référence existe dejà !"], Response::HTTP_BAD_REQUEST);
            } else {
                return new JsonResponse(['message' => "Add Additive is possible !"], Response::HTTP_OK);
            }
        }

        $form = $this->createForm('OGIVE\AlertBundle\Form\AdditiveType', $additive);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                $sendActivate = $request->get('send_activate');
                if ($sendActivate && $sendActivate === 'on') {
                    $additive->setState(1);
                } else {
                    $additive->setState(0);
                }
            }
            if ($additive->getCallOffer()) {
                if ($additive->getCallOffer()->getDomain()) {
                    $additive->setDomain($additive->getCallOffer()->getDomain());
                }
                if ($additive->getCallOffer()->getSubDomain()) {
                    $additive->setSubDomain($additive->getCallOffer()->getSubDomain());
                }
                $additive->setOwner($additive->getCallOffer()->getOwner());
                //Set Object just for prevent database violation constraints
                $additive->setObject($additive->getCallOffer()->getObject());
            } elseif ($additive->getExpressionInterest()) {
                if ($additive->getExpressionInterest()->getDomain()) {
                    $additive->setDomain($additive->getExpressionInterest()->getDomain());
                }
                if ($additive->getExpressionInterest()->getSubDomain()) {
                    $additive->setSubDomain($additive->getExpressionInterest()->getSubDomain());
                }
                $additive->setOwner($additive->getExpressionInterest()->getOwner());
                //Set Object just for prevent database violation constraints
                $additive->setObject($additive->getExpressionInterest()->getObject());
            }
            $additive->setAbstract($this->getAbstractOfAdditive($additive));
            $user = $this->getUser();
            $additive->setUser($user);
            $additive = $repositoryAdditive->saveAdditive($additive);
            $curl_response = $this->get('curl_service')->sendAdditiveToWebsite($additive);
            $curl_response_array = json_decode($curl_response, true);
            $additive->setUrlDetails($curl_response_array['data']['url']);
            $additive->setAbstract($this->getAbstractOfAdditive($additive, $additive->getUrlDetails()));
            $repositoryAdditive->updateAdditive($additive);
            $repositoryOwner->saveOwnerForProcedure($additive);
            $view = View::createRedirect($this->generateUrl('additive_index'));
            $view->setFormat('html');
            return $view;
        } else {
            $view = View::create($form);
            $view->setFormat('json');
            return $view;
            //return new JsonResponse($form, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_OK)
     * @Rest\Delete("/additives/{id}", name="additive_delete", options={ "method_prefix" = false, "expose" = true })
     */
    public function removeAdditiveAction(Additive $additive) {
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $repositoryAdditive = $this->getDoctrine()->getManager()->getRepository('OGIVEAlertBundle:Additive');
        if ($additive) {
            $repositoryAdditive->deleteAdditive($additive);
            $view = View::create(["message" => "Additif supprimé avec succès !"]);
            $view->setFormat('json');
            return $view;
            //return new JsonResponse(["message" => "Additif supprimé avec succès !"], Response::HTTP_OK);
        } else {
            return new JsonResponse(["message" => "Additif introuvable"], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @Rest\View()
     * @Rest\Put("/additives/{id}", name="additive_update", options={ "method_prefix" = false, "expose" = true })
     * @param Request $request
     */
    public function putAdditiveAction(Request $request, Additive $additive) {
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        return $this->updateAdditiveAction($request, $additive);
    }

    public function updateAdditiveAction(Request $request, Additive $additive) {

        $repositoryAdditive = $this->getDoctrine()->getManager()->getRepository('OGIVEAlertBundle:Additive');
        $repositoryOwner = $this->getDoctrine()->getManager()->getRepository('OGIVEAlertBundle:Owner');

        if (empty($additive)) {
            return new JsonResponse(['message' => "Additif introuvable"], Response::HTTP_NOT_FOUND);
        }

        if ($request->get('testunicity') == 'yes' && $request->get('reference')) {
            $reference = $request->get('reference');
            $additiveUnique = $repositoryAdditive->findOneBy(array('reference' => $reference, 'status' => 1));
            if ($additiveUnique && $additiveUnique->getId() != $additive->getId()) {
                return new JsonResponse(["success" => false, 'message' => "Un additif avec cette référence existe dejà"], Response::HTTP_BAD_REQUEST);
            } else {
                return new JsonResponse(['message' => "Update Additive is possible !"], Response::HTTP_OK);
            }
        }

        if ($request->get('action') == 'enable') {
            $additive->setState(1);
            $additive = $repositoryAdditive->updateAdditive($additive);
            return new JsonResponse(['message' => "Additif activé avec succcès !"], Response::HTTP_OK
            );
        }

        if ($request->get('action') == 'disable') {
            $additive->setState(0);
            $additive = $repositoryAdditive->updateAdditive($additive);
            return new JsonResponse(['message' => "Additif désactivé avec succcès !"], Response::HTTP_OK
            );
        }
        $form = $this->createForm('OGIVE\AlertBundle\Form\AdditiveType', $additive, array('method' => 'PUT'));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($additive->getCallOffer()) {
                if ($additive->getCallOffer()->getDomain()) {
                    $additive->setDomain($additive->getCallOffer()->getDomain());
                }
                if ($additive->getCallOffer()->getSubDomain()) {
                    $additive->setSubDomain($additive->getCallOffer()->getSubDomain());
                }
                $additive->setOwner($additive->getCallOffer()->getOwner());
                //Set Object just for prevent database violation constraints
                $additive->setObject($additive->getCallOffer()->getObject());
            } elseif ($additive->getExpressionInterest()) {
                if ($additive->getExpressionInterest()->getDomain()) {
                    $additive->setDomain($additive->getExpressionInterest()->getDomain());
                }
                if ($additive->getExpressionInterest()->getSubDomain()) {
                    $additive->setSubDomain($additive->getExpressionInterest()->getSubDomain());
                }
                $additive->setOwner($additive->getExpressionInterest()->getOwner());
                //Set Object just for prevent database violation constraints
                $additive->setObject($additive->getExpressionInterest()->getObject());
            }
            if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                $sendActivate = $request->get('send_activate');
                if ($sendActivate && $sendActivate === 'on') {
                    $additive->setState(1);
                } else {
                    $additive->setState(0);
                }
            }
            $additive->setAbstract($this->getAbstractOfAdditive($additive));
            $user = $this->getUser();
            $additive->setUpdatedUser($user);
            $additive = $repositoryAdditive->updateAdditive($additive);
            $curl_response = $this->get('curl_service')->sendAdditiveToWebsite($additive);
            $curl_response_array = json_decode($curl_response, true);
            $additive->setUrlDetails($curl_response_array['data']['url']);
            $additive->setAbstract($this->getAbstractOfAdditive($additive, $additive->getUrlDetails()));
            $repositoryAdditive->updateAdditive($additive);
            $repositoryOwner->saveOwnerForProcedure($additive);
            $view = View::createRedirect($this->generateUrl('additive_index'));
            $view->setFormat('html');
            return $view;
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            return new JsonResponse($form, Response::HTTP_BAD_REQUEST);
        } else {
            $edit_additive_form = $this->renderView('OGIVEAlertBundle:additive:edit.html.twig', array('form' => $form->createView(), 'additive' => $additive));
            $view = View::create(['edit_additive_form' => $edit_additive_form]);
            $view->setFormat('json');
            return $view;
            //return new JsonResponse(['edit_additive_form' => $edit_additive_form], Response::HTTP_OK);
        }
    }

    public function getAbstractOfAdditive(Additive $additive, $detail_url = null) {
        $abstract = "";
        if ($additive && $additive->getCallOffer()) {
            $abstract = "Réf : " . $additive->getType() . " " . "N°" . $additive->getReference() ." du " . date("d/m/Y", strtotime($additive->getPublicationDate())) . " relatif à l'" . $additive->getCallOffer()->getType() . " N°" . $additive->getCallOffer()->getReference(). " lancé par " . $additive->getCallOffer()->getOwner() ." le " . date("d/m/Y", strtotime($additive->getCallOffer()->getPublicationDate())) . ".";
        } elseif ($additive && $additive->getExpressionInterest()) {
            $abstract = "Réf : " . $additive->getType() . " " . "N°" . $additive->getReference() ." du " . date("d/m/Y", strtotime($additive->getPublicationDate())) . " relatif à l'" . $additive->getExpressionInterest()->getType() . " N°" . $additive->getExpressionInterest()->getReference()." lancé par " . $additive->getExpressionInterest()->getOwner() . " du " . date("d/m/Y", strtotime($additive->getExpressionInterest()->getPublicationDate())) . '.';
        } else {
            $abstract = $additive->getObject();
        }
        if ($detail_url && $detail_url != "") {
            $abstract .= " Détail téléchargeable à l'adresse " . $detail_url;
        }
        return $abstract;
    }

}
