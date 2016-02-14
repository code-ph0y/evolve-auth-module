<?php
namespace AuthModule\Controller;

use AuthModule\Controller\Shared as SharedController;
use AuthModule\Entity\User as UserEntity;
use PPI\Framework\Http\Request as Request;

class Auth extends SharedController
{
    protected $userStorage;

    public function signupAction()
    {
        // Check to see if user is logged in
        $this->loggedInCheck();

        return $this->render('AuthModule:auth:signup.html.php');
    }

    public function loginAction()
    {
        // Check to see if user is logged in
        $this->loggedInCheck();

        return $this->render('AuthModule:auth:login.html.php');
    }

    public function forgotpwAction()
    {
        // Check to see if user is logged in
        $this->loggedInCheck();

        return $this->render('AuthModule:auth:signup.html.php');
    }

    public function forgotpwenterAction()
    {
        // Check to see if user is logged in
        $this->loggedInCheck();

        return $this->render('AuthModule:auth:forgotpwenter.html.php');
    }

    public function logoutAction()
    {
        $this->getService('user.security')->logout();
        return $this->redirectToRoute('Homepage');
    }

    public function logincheckAction(Request $request, $userEmail, $userPassword)
    {
        // Check to see if user is logged in
        $this->loggedInCheck();

        // Get security helper
        $security = $this->getService('auth.security');

        $errors   = array();

        if (trim($userEmail) == '' || trim($userPassword) == '') {
            $errors[] = 'Email and Password are required fields';
            return $this->render('AuthModule:auth:login.html.php', compact('errors'));
        }

        // Lets try to authenticate the user
        if (!$security->checkAuth($userEmail, $userPassword)) {
            $errors[] = 'Login Invalid';
            return $this->render('AuthModule:auth:login.html.php', compact('errors'));
        }

        // Get user record
        $userEntity = $this->getService('auth.user.storage')->getByEmail($userEmail);

        // Check if user is activated
        if (!$this->getService('auth.user.activation.storage')->isActivated($userEntity->getId())) {
            $errors[] = 'Account not activated. Please check your email for activation instructions';
            return $this->render('AuthModule:auth:login.html.php', compact('errors'));
        }

        // Lets populate the session with the user's auth information
        $security->login(new UserEntity($userEntity));

        // Login Successful.
        $this->setFlash('success', 'Login Successful');
        return $this->redirectToRoute($this->getService('auth.security')->getRedirectRoute());
    }

    protected function renderJsonResponse($response)
    {
        $this->getRequest()->headers->set('Content-Type', 'application/json');

        return json_encode($response);
    }

    public function forgotpwsendAction()
    {
        // Check to see if user is logged in
        $this->loggedInCheck();

        $response = array('status' => 'E_UNKNOWN');
        $email    = $this->post('email');
        $us       = $this->getUserStorage();

        // Check for missing field
        if (empty($email)) {
            $response['status'] = 'E_MISSING_FIELD';
            $response['error_value'] = 'email';
            $this->renderJsonResponse($response);
        }

        // Check if user record does not exist
        if (!$us->existsByEmail($email)) {
            $response['status'] = 'E_MISSING_RECORD';
            $this->renderJsonResponse($response);
        }

        $forgotUser  = $us->getByEmail($email);
        $forgotToken = sha1(openssl_random_pseudo_bytes(16));

        // Insert a forgot token for this user
        $this->getUserForgotStorage()->create(array(
            'user_id' => $forgotUser->getID(),
            'token'   => $forgotToken
        ));

        // Lets send the user forgotpw email
        $this->sendForgotPWEmail($forgotUser, $forgotToken);

        // Successful response
        $response['status'] = 'success';
        $this->renderJsonResponse($response);

    }

    public function forgotpwcheckAction()
    {
        // Check to see if user is logged in
        $this->loggedInCheck();


        $token = $this->getRouteParam('token');
        $fs = $this->getUserForgotStorage();

        // If the user has not activated their token before, activate it!
        if (!$fs->isUserActivatedByToken($token)) {

            $fs->useToken($token);

            // Lets generate a CSRF token for the update password page.
            $csrf = sha1(openssl_random_pseudo_bytes(16));
            $this->getSession()->set('forgotpw_csrf', $csrf);
            $this->getSession()->set('forgotpw_token', $token);

            // Render the 'enter your new password' view
            return $this->render('AuthModule:auth:forgotpwenter.html.php', compact('csrf'));
        }

        // redirect the user to the login page
        $this->redirectToRoute('User_Signup');
    }

    public function forgotpwsaveAction()
    {
        // Check to see if user is logged in
        $this->loggedInCheck();

        $post          = $this->post();
        $requiredKeys  = array('password', 'confirm_password', 'csrf');

        // Check for missing fields, or fields being empty.
        foreach ($requiredKeys as $field) {
            if (!isset($post[$field]) || empty($post[$field])) {
                $missingFields[] = $field;
            }
        }

        // If any fields were missing, inform the client
        if (!empty($missingFields)) {
            $response['status']       = 'E_MISSING_FIELD';
            $response['error_value']  = implode(',', $missingFields);
            $this->renderJsonResponse($response);
        }

        // Check if both passwords match
        if ($post['password'] !== $post['confirm_password']) {
            $response['status'] = 'E_PASSWORD_MISMATCH';
            $this->renderJsonResponse($response);
        }

        // Check for csrf protection
        $csrf = $this->session('forgotpw_csrf');
        if (empty($csrf) || $csrf !== $post['csrf']) {
            $response['status'] = 'E_INVALID_CSRF';
            $this->renderJsonResponse($response);
        }

        // Get the user record out of the session token
        $token = $this->session('forgotpw_token');
        if (empty($token)) {
            $response['status'] = 'E_MISSING_TOKEN';
            $this->renderJsonResponse($response);
        }

        // Get user entity from the userID on the token row
        $us = $this->getUserStorage();
        $userEntity = $us->getByID($this->getUserForgotStorage()->getByToken($token)->getUserID());

        // Update the user's password
        $this->getUserStorage()->updatePassword(
            $userEntity->getID(),
            $userEntity->getSalt(),
            $this->getConfigSalt(),
            $post['password']
        );

        // Wipe session values clean
        $session = $this->getSession();
        $session->remove('fogotpw_csrf');
        $session->remove('fogotpw_token');

        // Return successful response \o/
        $response['status'] = 'success';
        $this->renderJsonResponse($response);

    }

    /**
      * Activation action. Active the user's account
      */
    public function activateAction()
    {
        // Check to see if user is logged in
        $this->loggedInCheck();

        $token = $this->getRouteParam('token');
        $uas = $this->getUserActivationStorage();

        // If the user has not activated their token before, activate it!
        if (!$uas->isUserActivatedByToken($token)) {
            $uas->activateUser($token);
        }

        return $this->render('AuthModule:auth:activate.html.php', compact('csrf'));
    }

    /**
    * Send the user's activation email to them.
    *
    * @param \AuthModule\Entity\User $toUser
    * @param string $activationCode
    */
    protected function sendActivationEmail($toUser, $activationCode)
    {
        // Check to see if user is logged in
        $this->loggedInCheck();

        $fromUser = new UserEntity($this->getEmailConfig());
        $toUser   = new UserEntity($toUser);

        // Generate the activation link from the route key
        $activationLink = $this->generateUrl('User_Activate', array('token' => $activationCode), true);

        // Get the activation email content, it's in a view file.
        $emailContent = $this->render('AuthModule:auth:signupemail.html.php', compact('toUser', 'activationLink'));

        // Send the activation email to the user
        $helper = new \AuthModule\Classes\Email();
        $config = $this->getConfig();
        $helper->sendEmail($fromUser, $toUser, $config['signupEmail']['subject'], $emailContent);

    }

    /**
    * Send the user's forgotpw email to them.
    *
    * @param \AuthModule\Entity\User|array $toUser
    * @param string $activationCode
    * @return void
    */
    protected function sendForgotPWEmail($toUser, $forgotToken)
    {
        // Check to see if user is logged in
        $this->loggedInCheck();

        // User entity preparation
        $fromUser = new UserEntity($this->getEmailConfig());
        if (is_array($toUser)) {
            $toUser = new UserEntity($toUser);
        }

        // Generate the activation link from the route key
        $forgotLink = $this->generateUrl('User_Forgot_Password_Check', array('token' => $forgotToken), true);

        // Get the activation email content, it's in a view file.
        $emailContent = $this->render('AuthModule:auth:forgotpwemail.html.php', compact('toUser', 'forgotLink'));

        // Send the activation email to the user
        $helper = new \AuthModule\Classes\Email();
        $config = $this->getConfig();
        $helper->sendEmail($fromUser, $toUser, $config['forgotEmail']['subject'], $emailContent);
    }
}
