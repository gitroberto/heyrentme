<?php
#whole class added by Seba
namespace AppBundle\Security\Core\User;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
/**
 * Class OAuthUserProvider
 * @package AppBundle\Security\Core\User
 */
class OAuthUserProvider extends BaseClass
{       
    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $socialID = $response->getUsername();
        $user = $this->userManager->findUserBy(array($this->getProperty($response)=>$socialID));
        $email = $response->getEmail();
        
        if (null === $email) {
            throw new UnsupportedUserException("Unsupported user: no email address");
        }
        
        $data = $response->getResponse();
        
        //check if the user already has the corresponding social account
        if (null === $user) {
            //check if the user has a normal account
            $user = $this->userManager->findUserByEmail($email);
            $isNewUser = false;
            if (null === $user || !$user instanceof UserInterface) {
                //if the user does not have a normal account, set it up:
                $user = $this->userManager->createUser();
                $user->setEmail($email);
                $user->setPlainPassword(md5(uniqid()));
                #facebook don't provide username param, email used;
                $user->setUsername($email);
                $user->setEnabled(true);
                $user->setName($data['first_name']);
                $user->setSurname($data['last_name']);
                $isNewUser = true;                

            }
            //then set its corresponding social id
            $service = $response->getResourceOwner()->getName();
            switch ($service) {
                case 'google':
                    $user->setGoogleID($socialID);
                    break;
                case 'facebook':
                    $user->setFacebookID($socialID);
                    break;
            }
            $this->userManager->updateUser($user);
            if ($isNewUser){
                global $kernel;
                $kernel->getContainer()->get('app.general_mailer')->SendWelcomeEmail($user, true);
            }
        } else {
            //and then login the user
            $checker = new UserChecker();
            $checker->checkPreAuth($user);
            // update name
            $user->setName($data['first_name']);
            $user->setSurname($data['last_name']);
        }

        return $user;
    }
}