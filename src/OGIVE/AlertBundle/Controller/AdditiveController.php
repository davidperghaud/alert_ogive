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
        $form = $this->createForm('OGIVE\AlertBundle\Form\AdditiveType', $additive);
        $additives = $em->getRepository('OGIVEAlertBundle:Additive')->getAll();
        return $this->render('OGIVEAlertBundle:additive:index.html.twig', array(
                    'additives' => $additives,
                    'form' => $form->createView()
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
        $view = View::create(["code" => 200, 'additive' => $additive, 'additive_details' => $additive_details]);
        $view->setFormat('json');
        return $view;
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
        $form = $this->createForm('OGIVE\AlertBundle\Form\AdditiveType', $additive);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($repositoryAdditive->findOneBy(array('reference' => $additive->getReference(), 'status' => 1))) {
                return new JsonResponse(["success" => false, 'message' => "Un additif avec cette référence existe dejà !"], Response::HTTP_BAD_REQUEST);
            }
            if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                $additive->setState(1);
            }
            $additive->setAbstract($this->getAbstractOfAdditive($additive));
            if($additive->getCallOffer()){
                $additive->setDomain($additive->getCallOffer()->getDomain());
                $additive->setSubDomain($additive->getCallOffer()->getSubDomain());
            }elseif($additive->getExpressionInterest()){
                $additive->setDomain($additive->getExpressionInterest()->getDomain());
                $additive->setSubDomain($additive->getExpressionInterest()->getSubDomain());
            }
            $additive = $repositoryAdditive->saveAdditive($additive);
            $additive_content_grid = $this->renderView('OGIVEAlertBundle:additive:additive-grid.html.twig', array('additive' => $additive));
            $additive_content_list = $this->renderView('OGIVEAlertBundle:additive:additive-list.html.twig', array('additive' => $additive));
            $view = View::create(["code" => 200, 'additive' => $additive, 'additive_content_grid' => $additive_content_grid, 'additive_content_list' => $additive_content_list]);
            $view->setFormat('json');
            return $view;
        } else {
            $view = View::create($form);
            $view->setFormat('json');
            return $view;
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
            $view = View::create(['additive' => $additive, "message" => "Additif supprimé avec succès !"]);
            $view->setFormat('json');
            return $view;
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

        if (empty($additive)) {
            return new JsonResponse(['message' => "Additif introuvable"], Response::HTTP_NOT_FOUND);
        }
        
        if($request->get('action')== 'enable'){
            $additive->setState(1);
            $additive = $repositoryAdditive->updateAdditive($additive);
            return new JsonResponse(['message' => "Additif activé avec succcès !"], Response::HTTP_OK
                    );
        }
        
        if($request->get('action')== 'disable'){
            $additive->setState(0);
            $additive = $repositoryAdditive->updateAdditive($additive);
            return new JsonResponse(['message' => "Additif désactivé avec succcès !"], Response::HTTP_OK
                    );
        }
        $form = $this->createForm('OGIVE\AlertBundle\Form\AdditiveType', $additive, array('method' => 'PUT'));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $additiveUnique = $repositoryAdditive->findOneBy(array('reference' => $additive->getReference(), 'status' => 1));
            if ($additiveUnique && $additiveUnique->getId() != $additive->getId()) {
                return new JsonResponse(["success" => false, 'message' => "Un additif avec cette référence existe dejà"], Response::HTTP_NOT_FOUND);
            }
            $additive->setAbstract($this->getAbstractOfAdditive($additive));
            if($additive->getCallOffer()){
                $additive->setDomain($additive->getCallOffer()->getDomain());
                $additive->setSubDomain($additive->getCallOffer()->getSubDomain());
            }elseif($additive->getExpressionInterest()){
                $additive->setDomain($additive->getExpressionInterest()->getDomain());
                $additive->setSubDomain($additive->getExpressionInterest()->getSubDomain());
            }
            $additive = $repositoryAdditive->updateAdditive($additive);
            $additive_content_grid = $this->renderView('OGIVEAlertBundle:additive:additive-grid-edit.html.twig', array('additive' => $additive));
            $additive_content_list = $this->renderView('OGIVEAlertBundle:additive:additive-list-edit.html.twig', array('additive' => $additive));
            $view = View::create(["code" => 200, 'additive' => $additive, 'additive_content_grid' => $additive_content_grid, 'additive_content_list' => $additive_content_list]);
            $view->setFormat('json');
            return $view;
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            return $form;
        } else {
            $edit_additive_form = $this->renderView('OGIVEAlertBundle:additive:edit.html.twig', array('form' => $form->createView(), 'additive' => $additive));
            $view = View::create(["code" => 200, 'additive' => $additive, 'edit_additive_form' => $edit_additive_form]);
            $view->setFormat('json');
            return $view;
        }
    }
    
    public function getAbstractOfAdditive(Additive $additive){
        if($additive && $additive->getCallOffer()){
            return  "Ref : ".$additive->getType()." : "."N°".$additive->getReference()."/".date_format($additive->getPublicationDate(), "Y")." du ".date_format($additive->getPublicationDate(), "d/m/Y")." relatif à ".$additive->getCallOffer()->getType()." N°".$additive->getCallOffer()->getReference()."/".$additive->getCallOffer()->getType()."/".$additive->getCallOffer()->getOwner()."/".date_format($additive->getCallOffer()->getPublicationDate(), "Y")." du ".date_format($additive->getCallOffer()->getPublicationDate(), "d/m/Y"); 
        }elseif ($additive && $additive->getExpressionInterest()) {
            return  "Ref : ".$additive->getType()." : "."N°".$additive->getReference()."/".date_format($additive->getPublicationDate(), "Y")." du ".date_format($additive->getPublicationDate(), "d/m/Y")." relatif à ".$additive->getExpressionInterest()->getType()." N°".$additive->getExpressionInterest()->getReference()."/".$additive->getExpressionInterest()->getType()."/".$additive->getExpressionInterest()->getOwner()."/".date_format($additive->getExpressionInterest()->getPublicationDate(), "Y")." du ".date_format($additive->getExpressionInterest()->getPublicationDate(), "d/m/Y"); 
        }else{
            return "";
        }
    }

}
