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
        // Get blank User Entity
        $user = $this->getService('auth.user.storage')->getBlankEntity();
        return $this->render('AuthModule:auth:signup.html.php', compact('user'));
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

        return $this->render('AuthModule:auth:forgotpw.html.php');
    }

    public function forgotpwenterAction()
    {
        // Check to see if user is logged in
        $this->loggedInCheck();

        return $this->render('AuthModule:auth:forgotpwenter.html.php');
    }

    public function logoutAction()
    {
        $this->getService('auth.security')->logout();
        $this->setFlash('info', 'You are now logged out.');
        return $this->redirectToRoute('Homepage');
    }

    public function logincheckAction(Request $request)
    {
        // Check to see if user is logged in
        $this->loggedInCheck();

        // Get Login Post values
        $userEmail    = $request->get('userEmail');
        $userPassword = $request->get('userPassword');

        // Get Application config
        $config = $this->getConfig();

        // Get security helper
        $security = $this->getService('auth.security');

        // Validate required fields
        if (trim($userEmail) == '' || trim($userPassword) == '') {
            $this->setFlash('danger', 'Email and Password are required fields');
            return $this->render('AuthModule:auth:login.html.php');
        }

        // Lets try to authenticate the user
        if (!$security->checkAuth($userEmail, $userPassword, $security, $config)) {
            $this->setFlash('danger', 'Login Invalid');
            return $this->render('AuthModule:auth:login.html.php');
        }

        // Get user record
        $userEntity = $this->getService('auth.user.storage')->getByEmail($userEmail);

        // Check if user is activated
        if (!$this->getService('auth.user.activation.storage')->isActivated($userEntity->getId())) {
            $this->setFlash('danger', 'Account not activated. Please check your email for activation instructions');
            return $this->render('AuthModule:auth:login.html.php');
        }

        // Lets populate the session with the user's auth information
        $security->login($userEntity);

        // Login Successful
        $this->setFlash('success', 'Login Successful');
        return $this->redirectToRoute($this->getService('auth.security')->getRedirectRoute($userEntity));
    }

    public function signupsaveAction(Request $request)
    {
        $post = $request->request->all();

        $missingFields = array();
        $requiredKeys  = array(
            'userFirstName',
            'userLastName',
            'userEmail',
            'userPassword',
            'userConfirmPassword'
        );

        // Prepare user
        $user_array = array(
            'email'         => $post['userEmail'],
            'first_name'    => $post['userFirstName'],
            'last_name'     => $post['userLastName'],
            'user_level_id' => 1
        );

        $config        = $this->getConfig();
        $userStorage   = $this->getService('auth.user.storage');

        // Check for missing fields, or fields being empty.
        foreach ($requiredKeys as $field) {
            if (!isset($post[$field]) || trim($post[$field]) == '') {
                $missingFields[] = $field;
            }
        }

        // If any fields were missing, inform the client
        if (!empty($missingFields)) {
            $user = $this->getService('auth.user.storage')->makeEntity($user_array);
            $this->setFlash('danger', 'Some required fields were blank. Please re-evaluate your input and try again.');
            return $this->render('AuthModule:auth:signup.html.php', compact('user'));
        }

        // Check if the user's passwords do not match
        if ($post['userPassword'] !== $post['userConfirmPassword']) {
            $user = $userStorage->makeEntity($user_array);
            $this->setFlash('danger', 'Passwords do not match. Please try again.');
            return $this->render('AuthModule:auth:signup.html.php', compact('user'));
        }

        // Check if the user's email address already exists
        if ($userStorage->existsByEmail($post['userEmail'])) {
            $user = $userStorage->makeEntity($user_array);
            $this->setFlash('danger', 'Email address already exists.');
            return $this->render('AuthModule:auth:signup.html.php', compact('user'));
        }

        $user_array['salt'] = $this->getService('auth.security')->generateSalt();

        $user_array['password'] = $this->getService('auth.security')->saltPass(
            $user_array['salt'],
            $config['authSalt'],
            $post['userPassword']
        );

        $user_array['blocked'] = 0;

        // Create the user
        $newUserID = $userStorage->create($user_array);

        // Generate sha1() based activation code
        $activationCode = sha1(openssl_random_pseudo_bytes(16));

        $activation = array(
            'user_id'   => $newUserID,
            'token'     => $activationCode,
            'used'      => '0',
            'date_used' => date('Y-m-d H:i:s', strtotime('now'))
        );

        // Insert an activation token for this user
        $this->getService('auth.user.activation.storage')->create($activation);

        // Send the users activation email
        // @todo : Get email system working
        //$this->sendActivationEmail($user, $activationCode);

        // Successful registration
        $fromEmail = $user_array['email'];
        return $this->render('AuthModule:auth:signupsuccess.html.php', compact('fromEmail'));
    }

    protected function renderJsonResponse($response)
    {
        $this->getRequest()->headers->set('Content-Type', 'application/json');

        return email_encode($response);
    }

    public function forgotpwsendAction(Request $request)
    {
        // Check to $this->getService('auth.email.helper') if user is logged in
        $this->loggedInCheck();

        $email       = $request->get('userEmail');
        $userStorage = $this->getService('auth.user.storage');

        // Check for missing field
        if (empty($email)) {
            $this->setFlash('danger', 'Email field was blank. Please enter email and try again.');
            return $this->render('AuthModule:auth:forgotpw.html.php');
        }

        // Check if user record does not exist
        if (!$userStorage->existsByEmail($email)) {
            $this->setFlash('danger', 'Email does not exist. Please enter valid email and try again.');
            return $this->render('AuthModule:auth:forgotpw.html.php');
        }

        $forgotUser  = $userStorage->getByEmail($email);
        $forgotToken = sha1(openssl_random_pseudo_bytes(16));

        // Insert a forgot token for this user
        $this->getService('auth.user.forgot.storage')->create(array(
            'user_id'   => $forgotUser->getId(),
            'token'     => $forgotToken,
            'used'      => 0,
            'date_used' => date('Y-m-d H:i:s', strtotime('now'))
        ));

        // Lets send the user forgotpw email
        //@todo - Add email helper
        //$this->sendForgotPWEmail($forgotUser, $forgotToken);

        // Successful response
        $this->setFlash(
            'success',
            'Your request has been received. You will receive an e-mail with instructions to change your password in the a few minutes.'
        );

        return $this->redirectToRoute('Homepage');
    }

    public function forgotpwcheckAction(Request $request)
    {
        // Check to see if user is logged in
        $this->loggedInCheck();

        $token             = $request->get('token');
        $userForgotStorage = $this->getService('auth.user.forgot.storage');

        // If the user has not activated their token before, activate it!
        if (!$userForgotStorage->isUserActivatedByToken($token)) {

            $userForgotStorage->useToken($token);

            // Lets generate a CSRF token for the update password page.
            $csrf = sha1(openssl_random_pseudo_bytes(16));
            $this->getSession()->set('forgotpw_csrf', $csrf);
            $this->getSession()->set('forgotpw_token', $token);

            // Render the 'enter your new password' view
            return $this->render('AuthModule:auth:forgotpwenter.html.php', compact('csrf'));
        }

        // Redirect the user to the login page
        return $this->redirectToRoute('User_Signup');
    }

    public function forgotpwsaveAction(Request $request)
    {
        // Check to see if user is logged in
        $this->loggedInCheck();

        // Get post variables
        $post = $request->request->all();

        // Get auth config
        $config = $this->getConfig();

        // Get user storage
        $userStorage       = $this->getService('auth.user.storage');
        // Get user forgot storage
        $userForgotStorage = $this->getService('auth.user.forgot.storage');

        // Required fields
        $requiredKeys  = array(
            'userPassword',
            'userConfirmPassword',
            'csrf'
        );

        // Check for missing fields, or fields being empty.
        foreach ($requiredKeys as $field) {
            if (!isset($post[$field]) || empty($post[$field])) {
                $missingFields[] = $field;
            }
        }

        // If any fields were missing, inform the client
        if (!empty($missingFields)) {
            $csrf = $post['csrf'];
            $this->setFlash('danger', 'Required fields were blank. Please re-evaluate your input and try again.');
            return $this->render('AuthModule:auth:forgotpwenter.html.php', compact('csrf'));
        }

        // Check if both passwords match
        if ($post['userPassword'] !== $post['userConfirmPassword']) {
            $csrf = $post['csrf'];
            $this->setFlash('danger', 'Passwords did not match. Please re-evaluate your input and try again.');
            return $this->render('AuthModule:auth:forgotpwenter.html.php', compact('csrf'));
        }

        // Check for csrf protection
        $csrf = $this->getSession()->get('forgotpw_csrf');
        if (empty($csrf) || $csrf !== $post['csrf']) {
            $this->setFlash('danger', 'CSRF value did not checkout!');
            $this->redirectToRoute('Homepage');
        }

        // Get the user record out of the session token
        $token = $this->getSession()->get('forgotpw_token');

        if (empty($token)) {
            $this->setFlash('danger', 'Forgot password token did not match.');
            $this->redirectToRoute('Homepage');
        }

        $userEntity = $userStorage->getByID($userForgotStorage->getByToken($token)->getUserId());

        // Get new encrypted password

        $encPassword = $this->getService('auth.security')->saltPass(
            $userEntity->getSalt(),
            $config['authSalt'],
            $post['userPassword']
        );

        // Update user password
        $userStorage->updatePassword(
            $userEntity->getId(),
            $encPassword
        );

        // Wipe session values clean
        $session = $this->getSession();
        $session->remove('fogotpw_csrf');
        $session->remove('fogotpw_token');

        // Return successful response \o/
        $this->setFlash('success', 'Password changed successfully');
        return $this->redirectToRoute('AuthModule_Login');
    }

    /**
      * Activation action. Active the user's account
      */
    public function activateAction(Request $request)
    {
        // Check to see if user is logged in
        $this->loggedInCheck();

        // Get token from post variable
        $token = $request->get('token');

        // Get config
        $config = $this->getConfig();

        // Get user activation storage
        $userActivationStorage = $this->getService('auth.user.activation.storage');

        // If the user has not activated their token before, activate it!
        if (!$userActivationStorage->isUserActivatedByToken($token)) {
            $userActivationStorage->activateUserByToken($token);
        }
        $registeringAt = $config['registeringAt'];
        return $this->render('AuthModule:auth:activate.html.php', compact('registeringAt'));
    }

    /**
    * Send the user's activation email to them.
    *
    * @param array $toUser
    * @param string $activationCode
    */
    protected function sendActivationEmail($toUser, $activationCode)
    {
        // Check to see if user is logged in
        $this->loggedInCheck();

        $fromUser = new UserEntity($this->getEmailConfig());
        $toUser   = new UserEntity($toUser);
        $config   = $this->getConfig();

        // Generate the activation link from the route key
        $activationLink = $this->generateUrl('User_Activate', array('token' => $activationCode), true);

        // Get the activation email content, it's in a view file.
        $emailContent = $this->render('AuthModule:email:signupemail.html.php', compact('toUser', 'activationLink'));

        // Send activation email
        $this->getService('auth.email.helper')->sendEmail(
            $fromUser,
            $toUser,
            $config['signupEmail']['subject'],
            $emailContent
        );
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
