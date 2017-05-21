<?php

namespace OGIVE\AlertBundle\Controller;

use OGIVE\AlertBundle\Entity\ProcedureResult;
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
 * ProcedureResult controller.
 *
 */
class ProcedureResultController extends Controller {

    /**
     * @Rest\View()
     * @Rest\Get("/attributions" , name="procedureResult_index", options={ "method_prefix" = false, "expose" = true })
     * @param Request $request
     */
    public function getProcedureResultsAction(Request $request) {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED') || $this->get('security.authorization_checker')->isGranted('FACTURIER')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $em = $this->getDoctrine()->getManager();
        $procedureResult = new ProcedureResult();
        $page = 1;
        $maxResults = 4;
        $route_param_page = array();
        $route_param_search_query = array();
        $search_query = null;
        $placeholder = "Rechercher une attribution...";
        if ($request->get('page')) {
            $page = intval(htmlspecialchars(trim($request->get('page'))));
            $route_param_page['page'] = $page;
        }
        if ($request->get('search_query')) {
            $search_query = htmlspecialchars(trim($request->get('search_query')));
            $route_param_search_query['search_query'] = $search_query;
        }
        $start_from = ($page - 1) * $maxResults >= 0 ? ($page - 1) * $maxResults : 0;
        $total_pages = ceil(count($em->getRepository('OGIVEAlertBundle:ProcedureResult')->getAllByString($search_query)) / $maxResults);
        $form = $this->createForm('OGIVE\AlertBundle\Form\ProcedureResultType', $procedureResult);
        $procedureResults = $em->getRepository('OGIVEAlertBundle:ProcedureResult')->getAll($start_from, $maxResults, $search_query);
        return $this->render('OGIVEAlertBundle:procedureResult:index.html.twig', array(
                    'procedureResults' => $procedureResults,
                    'total_pages' => $total_pages,
                    'page' => $page,
                    'form' => $form->createView(),
                    'route_param_page' => $route_param_page,
                    'route_param_search_query' => $route_param_search_query,
                    'search_query' => $search_query,
                    'placeholder' => $placeholder
        ));
    }

    /**
     * @Rest\View()
     * @Rest\Get("/attributions/{id}" , name="procedureResult_get_one", options={ "method_prefix" = false, "expose" = true })
     */
    public function getProcedureResultByIdAction(ProcedureResult $procedureResult) {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED') || $this->get('security.authorization_checker')->isGranted('FACTURIER')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        if (empty($procedureResult)) {
            return new JsonResponse(['message' => "Attribution introuvable"], Response::HTTP_NOT_FOUND);
        }
        $form = $this->createForm('OGIVE\AlertBundle\Form\ProcedureResultType', $procedureResult, array('method' => 'PUT'));
        $procedureResult_details = $this->renderView('OGIVEAlertBundle:procedureResult:show.html.twig', array(
            'procedureResult' => $procedureResult,
            'form' => $form->createView()
        ));
        $view = View::create(['procedureResult_details' => $procedureResult_details]);
        $view->setFormat('json');
        return $view;
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/attributions", name="procedureResult_add", options={ "method_prefix" = false, "expose" = true })
     */
    public function postProcedureResultsAction(Request $request) {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED') || $this->get('security.authorization_checker')->isGranted('FACTURIER')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $procedureResult = new ProcedureResult();
        $repositoryProcedureResult = $this->getDoctrine()->getManager()->getRepository('OGIVEAlertBundle:ProcedureResult');

        if ($request->get('testunicity') == 'yes' && $request->get('reference')) {
            $reference = $request->get('reference');
            if ($repositoryProcedureResult->findOneBy(array('reference' => $reference, 'status' => 1))) {
                return new JsonResponse(["success" => false, 'message' => "Une attribution avec ce numéro existe dejà !"], Response::HTTP_BAD_REQUEST);
            } else {
                return new JsonResponse(['message' => "Add Procedure result is possible !"], Response::HTTP_OK);
            }
        }

        $form = $this->createForm('OGIVE\AlertBundle\Form\ProcedureResultType', $procedureResult);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                $sendActivate = $request->get('send_activate');
                if ($sendActivate && $sendActivate === 'on') {
                    $procedureResult->setState(1);
                }
            }
            $procedureResult->setType($request->get('attribution_type'));

            if ($procedureResult->getCallOffer()) {
                if ($procedureResult->getCallOffer()->getDomain()) {
                    $procedureResult->setDomain($procedureResult->getCallOffer()->getDomain());
                }
                if ($procedureResult->getCallOffer()->getSubDomain()) {
                    $procedureResult->setSubDomain($procedureResult->getCallOffer()->getSubDomain());
                }
                $procedureResult->setOwner($procedureResult->getCallOffer()->getOwner());
            } elseif ($procedureResult->getExpressionInterest()) {
                if ($procedureResult->getExpressionInterest()->getDomain()) {
                    $procedureResult->setDomain($procedureResult->getExpressionInterest()->getDomain());
                }
                if ($procedureResult->getExpressionInterest()->getSubDomain()) {
                    $procedureResult->setSubDomain($procedureResult->getExpressionInterest()->getSubDomain());
                }
                $procedureResult->setOwner($procedureResult->getExpressionInterest()->getOwner());
            }
            $procedureResult->setAbstract($this->getAbstractOfProcedureResult($procedureResult));
            $procedureResult = $repositoryProcedureResult->saveProcedureResult($procedureResult);
            $curl_response = $this->get('curl_service')->sendProcedureResultToWebsite($procedureResult);
            $curl_response_array = json_decode($curl_response, true);
            if ($curl_response_array['data']['url'] && $curl_response_array['data']['url'] != "") {
                $procedureResult->setAbstract($this->getAbstractOfProcedureResult($procedureResult, $curl_response_array['data']['url']));
                $repositoryProcedureResult->updateProcedureResult($procedureResult);
            }
            $view = View::createRedirect($this->generateUrl('procedureResult_index'));
            $view->setFormat('html');
            return $view;
        } else {
            $view = View::create($form);
            $view->setFormat('json');
            return $view;
        }
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_OK)
     * @Rest\Delete("/attributions/{id}", name="procedureResult_delete", options={ "method_prefix" = false, "expose" = true })
     */
    public function removeProcedureResultAction(ProcedureResult $procedureResult) {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED') || $this->get('security.authorization_checker')->isGranted('FACTURIER')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $repositoryProcedureResult = $this->getDoctrine()->getManager()->getRepository('OGIVEAlertBundle:ProcedureResult');
        if ($procedureResult) {
            $repositoryProcedureResult->deleteProcedureResult($procedureResult);
            $view = View::create(["message" => "Attribution supprimée avec succès !"]);
            $view->setFormat('json');
            return $view;
        } else {
            return new JsonResponse(["message" => "Attribution introuvable"], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @Rest\View()
     * @Rest\Put("/attributions/{id}", name="procedureResult_update", options={ "method_prefix" = false, "expose" = true })
     * @param Request $request
     */
    public function putProcedureResultAction(Request $request, ProcedureResult $procedureResult) {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED') || $this->get('security.authorization_checker')->isGranted('FACTURIER')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        return $this->updateProcedureResultAction($request, $procedureResult);
    }

    public function updateProcedureResultAction(Request $request, ProcedureResult $procedureResult) {

        $repositoryProcedureResult = $this->getDoctrine()->getManager()->getRepository('OGIVEAlertBundle:ProcedureResult');

        if (empty($procedureResult)) {
            return new JsonResponse(['message' => "Attribution introuvable"], Response::HTTP_NOT_FOUND);
        }

        if ($request->get('testunicity') == 'yes' && $request->get('reference')) {
            $reference = $request->get('reference');
            $procedureResultUnique = $repositoryProcedureResult->findOneBy(array('reference' => $reference, 'status' => 1));
            if ($procedureResultUnique && $procedureResultUnique->getId() != $procedureResult->getId()) {
                return new JsonResponse(["success" => false, 'message' => "Une attribution avec ce numéro existe dejà"], Response::HTTP_BAD_REQUEST);
            } else {
                return new JsonResponse(['message' => "Update Procedure result is possible !"], Response::HTTP_OK);
            }
        }

        if ($request->get('action') == 'enable') {
            $procedureResult->setState(1);
            $procedureResult = $repositoryProcedureResult->updateProcedureResult($procedureResult);
            return new JsonResponse(['message' => "Attribution activée avec succcès !"], Response::HTTP_OK
            );
        }

        if ($request->get('action') == 'disable') {
            $procedureResult->setState(0);
            $procedureResult = $repositoryProcedureResult->updateProcedureResult($procedureResult);
            return new JsonResponse(['message' => "Attribution désactivée avec succcès !"], Response::HTTP_OK
            );
        }
        $form = $this->createForm('OGIVE\AlertBundle\Form\ProcedureResultType', $procedureResult, array('method' => 'PUT'));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $procedureResult->setType($request->get('attribution_type'));
            if ($procedureResult->getCallOffer()) {
                if ($procedureResult->getCallOffer()->getDomain()) {
                    $procedureResult->setDomain($procedureResult->getCallOffer()->getDomain());
                }
                if ($procedureResult->getCallOffer()->getSubDomain()) {
                    $procedureResult->setSubDomain($procedureResult->getCallOffer()->getSubDomain());
                }
                $procedureResult->setOwner($procedureResult->getCallOffer()->getOwner());
            } elseif ($procedureResult->getExpressionInterest()) {
                if ($procedureResult->getExpressionInterest()->getDomain()) {
                    $procedureResult->setDomain($procedureResult->getExpressionInterest()->getDomain());
                }
                if ($procedureResult->getExpressionInterest()->getSubDomain()) {
                    $procedureResult->setSubDomain($procedureResult->getExpressionInterest()->getSubDomain());
                }
                $procedureResult->setOwner($procedureResult->getExpressionInterest()->getOwner());
            }
            if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                $sendActivate = $request->get('send_activate');
                if ($sendActivate && $sendActivate === 'on') {
                    $procedureResult->setState(1);
                } else {
                    $procedureResult->setState(0);
                }
            }
            $procedureResult->setAbstract($this->getAbstractOfProcedureResult($procedureResult));
            $procedureResult = $repositoryProcedureResult->updateProcedureResult($procedureResult);
            $curl_response = $this->get('curl_service')->sendProcedureResultToWebsite($procedureResult);
            $curl_response_array = json_decode($curl_response, true);
            if ($curl_response_array['data']['url'] && $curl_response_array['data']['url'] != "") {
                $procedureResult->setAbstract($this->getAbstractOfProcedureResult($procedureResult, $curl_response_array['data']['url']));
                $repositoryProcedureResult->updateProcedureResult($procedureResult);
            }
            $view = View::createRedirect($this->generateUrl('procedureResult_index'));
            $view->setFormat('html');
            return $view;
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            return $form;
        } else {
            $edit_procedureResult_form = $this->renderView('OGIVEAlertBundle:procedureResult:edit.html.twig', array('form' => $form->createView(), 'procedureResult' => $procedureResult));
            $view = View::create(['edit_procedureResult_form' => $edit_procedureResult_form]);
            $view->setFormat('json');
            return $view;
        }
    }

    public function getAbstractOfProcedureResult(ProcedureResult $procedureResult, $detail_url = null) {
        $abstract = "";
        if ($procedureResult && $procedureResult->getCallOffer()) {
            $abstract = "Décision " . "N°" . $procedureResult->getReference() . "/D/" . $procedureResult->getCallOffer()->getOwner() . "/" . date("Y", strtotime($procedureResult->getPublicationDate())) . " portant sur " . $procedureResult->getObject() . " de l'" . $procedureResult->getCallOffer()->getType() . " N°" . $procedureResult->getCallOffer()->getReference() . "/" . $procedureResult->getCallOffer()->getType() . "/" . $procedureResult->getCallOffer()->getOwner() . "/" . date("Y", strtotime($procedureResult->getCallOffer()->getPublicationDate())) . " du " . date("d/m/Y", strtotime($procedureResult->getCallOffer()->getPublicationDate())) . ".";
        } elseif ($procedureResult && $procedureResult->getExpressionInterest()) {
            $abstract = "Décision " . $procedureResult->getReference() . " : " . "N°" . $procedureResult->getReference() . "/D/" . $procedureResult->getExpressionInterest()->getOwner() . "/" . date("Y", strtotime($procedureResult->getPublicationDate())) . " portant sur " . $procedureResult->getObject() . " de l'" . $procedureResult->getExpressionInterest()->getType() . " N°" . $procedureResult->getExpressionInterest()->getReference() . "/" . $procedureResult->getExpressionInterest()->getType() . "/" . $procedureResult->getExpressionInterest()->getOwner() . "/" . date("Y", strtotime($procedureResult->getExpressionInterest()->getPublicationDate())) . " du " . date("d/m/Y", $procedureResult->getExpressionInterest()->getPublicationDate()) . ".";
        } else {
            $abstract = $procedureResult->getObject();
        }
        if ($detail_url && $detail_url != "") {
            $abstract .= " Détail téléchargeable à l'adresse " . $detail_url;
        }
        return $abstract;
    }

}
