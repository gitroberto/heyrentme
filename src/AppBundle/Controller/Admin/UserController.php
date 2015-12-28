<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends BaseAdminController {
     /**
     * 
     * @Route("/admin/users", name="admin_users_list")
     */
    public function indexAction() {
        return $this->render('admin/user/index.html.twig');
    }
    
    /**
     * 
     * @Route("/admin/users/details/{id}", name="admin_users_details")
     */
    public function detailsAction(Request $request, $id) {
        $user = $this->getDoctrineRepo('AppBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('No user found for id '.$id);
        }        
        
        $form = $this->createFormBuilder($user)
                
                ->add('Enabled', 'checkbox', array('required' => false))
                ->getForm();

       
        //when the form is posted this method prefills entity with data from form
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_users_list' ));
                    
        }
        
        
        return $this->render('admin/user/details.html.twig', array(
            'form' => $form->createView(),
            'user' => $user
        ));
    }
    
    
    /**
     * @Route("/admin/users/jsondata", name="admin_users_jsondata")
     */
    public function JsonData(Request $request)
    {  
        $sortColumn = $request->get('sidx');
        $sortDirection = $request->get('sord');
        $pageSize = $request->get('rows');
        $page = $request->get('page');
        $callback = $request->get('callback');
        
        $email = $request->get('u_email');
        $name = $request->get('u_name');
        $surname = $request->get('u_surname');
        $enabled = $request->get('u_enabled');        
        $createdAt = $request->get('u_createdAt');
        $modifiedAt = $request->get('u_modifiedAt');
        
        $repo = $this->getDoctrineRepo('AppBundle:User');
        $dataRows = $repo->getGridOverview($sortColumn, $sortDirection, $pageSize, $page, 
                $email, $name, $surname, $enabled, $createdAt, $createdAt, $modifiedAt);
        $rowsCount = $repo->countAll();
        $pagesCount = ceil($rowsCount / $pageSize);
        
        $rows = array(); // rows as json result
        
        foreach ($dataRows as $dataRow) { // build single row
            $row = array();
            $row['id'] = $dataRow->getId();
            $cell = array();
            $i = 0;
            $cell[$i++] = "";
            $cell[$i++] = $dataRow->getId();
            $cell[$i++] = $dataRow->getUsername();
            $cell[$i++] = $dataRow->getName();
            $cell[$i++] = $dataRow->getSurname();
            $cell[$i++] = $dataRow->isEnabled();
            $cell[$i++] = $dataRow->getCreatedAt()->format('Y-m-d H:i');
            $cell[$i++] = $dataRow->getModifiedAt()->format('Y-m-d H:i');            
            $row['cell'] = $cell;
            array_push($rows, $row);
        }
        
        $result = array( // main result object as json
            'records' => $rowsCount,
            'page' => $page,
            'total' => $pagesCount,
            'rows' => $rows
        );        
        
        $resp = new JsonResponse($result, JsonResponse::HTTP_OK);
        $resp->setCallback($callback);
        return $resp;
    }
}
