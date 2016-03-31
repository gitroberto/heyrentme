<?php 

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use FOS\UserBundle\Controller\SecurityController as BaseSecurityController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\SecurityContextInterface;

class SecurityController extends BaseSecurityController
{
    public function loginAction(Request $request)
    {
        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $request->getSession();

        if (class_exists('\Symfony\Component\Security\Core\Security')) {
            $authErrorKey = Security::AUTHENTICATION_ERROR;
            $lastUsernameKey = Security::LAST_USERNAME;
        } else {
            // BC for SF < 2.6
            $authErrorKey = SecurityContextInterface::AUTHENTICATION_ERROR;
            $lastUsernameKey = SecurityContextInterface::LAST_USERNAME;
        }

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif (null !== $session && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }

        $message = null;
        
        if ($error instanceof UnsupportedUserException) {
            $message = "Ups, eine Registrierung mit deinem Facebook-Account ist derzeit nicht möglich. Bitte registriere dich mit einer Email";
        }
        
        if (!$error instanceof AuthenticationException) {
            $error = null; // The value does not come from the security component.
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get($lastUsernameKey);

        if ($this->has('security.csrf.token_manager')) {
            $csrfToken = $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue();
        } else {
            // BC for SF < 2.4
            $csrfToken = $this->has('form.csrf_provider')
                ? $this->get('form.csrf_provider')->generateCsrfToken('authenticate')
                : null;
        }
                
        $user = $this->get('security.token_storage')->getToken()->getUser();
        
        
        if ($user != "anon." ){           
            return $this->userIsLoggedAction();
        }
        
        if ($session->has('logged_out_message')) {
            $message = $session->get('logged_out_message');            
            $session->set('logged_out_message', null);            
        }
        
        return $this->renderLogin(array(
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token' => $csrfToken,
            'message' => $message
        ));
    }
    
     /**
     * @Route("/loggedInFace", name="loggedInFace")
     */
    public function loggedInFaceAction(Request $request){
        $user = $this->getUser();
        $session = $request->getSession();
        if ($session->has('logged_out_message')) {
            $session->set('logged_out_message', null);
        }
        
        $referer = $request->headers->get('referer');
        
        if ($user && $user->getStatus() != User::STATUS_OK){        
            $message = $this->getMessage($user);            
            $session->set('logged_out_message', $message);            
            $this->removeAuthenticationToken();
            $url = $this->generateUrl('rentme');
            return $this->redirect( $url.'?login');
        }                    
        
        if (empty($referer)) {
            return $this->redirectToRoute("profil");
        } else {
            return new RedirectResponse($referer);
        }
        
        
    }
    
    public function getMessage($user) {
        $message = "Ups, ein Fehler ist aufgetreten. Bitte versuche es noch einmal, oder wende dich an support@heysharing.com";
        if ($user->getStatus() == User::STATUS_BLOCKED){
            $message = "Ups, es gibt ein Problem mit deinem User Account. Bitte wende dich an support@heysharing.com";
        } else {
            $message = "Authentication failed.";
        }
        return $message;
    }
    
    
    public function removeAuthenticationToken() {
        $anonToken = new AnonymousToken('theTokensKey', 'anon.', array());            
        if (interface_exists('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')) {
            $tokenStorage = $this->get('security.token_storage');
        } else {
            $tokenStorage = $this->get('security.context');
        }            
        $tokenStorage->setToken($anonToken);
    }
        
     /**
     * @Route("/loggedIn", name="loggedin")
     */
    public function userIsLoggedAction(){
        $user = $this->getUser();
        if ($user && $user->getStatus() != User::STATUS_OK){
            $message = $this->getMessage($user);            
            $this->removeAuthenticationToken();
                        
            $response = new Response(json_encode("User_Is_Not_Logged;".$message));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        
        $targetUrl = $this->getTargetUrlFromSession();
        $response = new Response(json_encode("User_Is_Logged;".$targetUrl));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    private function getTargetUrlFromSession()
    {
        // Set the SecurityContext for Symfony <2.6
        if (interface_exists('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')) {
            $tokenStorage = $this->get('security.token_storage');
        } else {
            $tokenStorage = $this->get('security.context');
        }
        
        $token = $tokenStorage->getToken();
        
        if ($token){
            $key = sprintf('_security.%s.target_path', $token->getProviderKey());
            if ($this->get('session')->has($key)) {
                return $this->get('session')->get($key);
            }
        }
    }
}