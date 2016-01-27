<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\User;
use Swift_Message;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserController extends BaseAdminController {
     /**
     * 
     * @Route("/admin/users", name="admin_users_list")
     */
    public function indexAction() {
        return $this->render('admin/user/index.html.twig');
    }
    
    public function sendUserBlockedMessage(Request $request, User $user)
    {      
                        
        $template = 'Emails/User/mail_user_blocked.html.twig';       
        
        $emailHtml = $this->renderView($template, array(                                    
            'user' => $user,
            'mailer_app_url_prefix' => $this->getParameter('mailer_app_url_prefix')            
        ));
        
        $subject = "User bloked.";
        
        $from = array($this->getParameter('mailer_fromemail') => $this->getParameter('mailer_fromname'));
        $message = Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($user->getEmail())
            ->setBody($emailHtml, 'text/html');
        $this->get('mailer')->send($message);
        
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
        
        $form = $this->createFormBuilder($user)->add('status', 'choice', array(
                    'choices' => array(                        
                        'Ok' => User::STATUS_OK,
                        'Blocked' => User::STATUS_BLOCKED,
                        'Deleted' => User::STATUS_DELETED
                    ),
                    'choices_as_values' => true,
                    'required' => true,
                    'constraints' => array(
                        new NotBlank()
                    )
                ))
                ->getForm();

       
        //when the form is posted this method prefills entity with data from form
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            
            if ($user->getStatus() == User::STATUS_BLOCKED){
                $this->sendUserBlockedMessage($request, $user);
            }

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
        $status = $request->get('u_status');        
        $createdAt = $request->get('u_createdAt');
        $modifiedAt = $request->get('u_modifiedAt');
        
        $repo = $this->getDoctrineRepo('AppBundle:User');
        $dataRows = $repo->getGridOverview($sortColumn, $sortDirection, $pageSize, $page, 
                $email, $name, $surname, $status, $createdAt, $createdAt, $modifiedAt);
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
            $cell[$i++] = $dataRow->getStatusStr();
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
