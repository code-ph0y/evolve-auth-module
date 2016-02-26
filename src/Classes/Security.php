<?php
namespace AuthModule\Classes;

class Security
{
    protected $userStorage;
    protected $session;
    protected $user;
    protected $config;

    public function __construct()
    {
        // constructor stub
    }

    public function setUserStorage($storage)
    {
        $this->userStorage = $storage;
    }

    public function setSession($session)
    {
        $this->session = $session;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function isLoggedIn()
    {
        return $this->getUser() !== null;
    }

    public function logout()
    {
        $this->session->clear();
    }

    public function login($user)
    {
        $this->session->set('ppiAuthUser', $user);
    }

    public function getUser()
    {
        if ($this->session->has('ppiAuthUser')) {
            $this->user = $this->session->get('ppiAuthUser');
        }

        return $this->user;
    }

    public function getRedirectRoute()
    {
        $redirectRoute = 'Guest';
        $config = $this->getConfig();
        $user = $this->getUser();
        $level_name = $user->getLevelName();

        if (isset($config['redirectRoutes'][$level_name])) {
            $redirectRoute = $config['redirectRoutes'][$level_name];
        } else {
            throw new \Exception('Redirect route not found');
        }

        return $redirectRoute;
    }

    public function checkAuth($email, $password, $userStorage)
    {
        $user = $this->userStorage->findByEmail($email);
        $encPass = $this->saltPass($user['salt'], $this->config['authSalt'], $password);
        return $this->userStorage->checkAuth($email, $encPass);
    }

    /**
    * Salt the password
    *
    * @param  string $userSalt
    * @param  string $configSalt
    * @param  string $pass
    * @return string
    */
    public function saltPass($userSalt, $configSalt, $pass)
    {
        return sha1($userSalt . $configSalt . $pass);
    }

    public function generateSalt()
    {
        return base64_encode(openssl_random_pseudo_bytes(16));
    }

    public function generateStrongPassword($length = 10, $add_dashes = false, $available_sets = 'luds')
    {
        $sets = array();
        if (strpos($available_sets, 'l') !== false) {
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        }
        if (strpos($available_sets, 'u') !== false) {
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        }
        if (strpos($available_sets, 'd') !== false) {
            $sets[] = '23456789';
        }
        if (strpos($available_sets, 's') !== false) {
            $sets[] = '!@#$%&*?';
        }

        $all = '';
        $password = '';

        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }

        $all = str_split($all);
        for ($i = 0; $i < $length - count($sets); $i++) {
            $password .= $all[array_rand($all)];
        }

        $password = str_shuffle($password);
        if (!$add_dashes) {
            return $password;
        }

        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while (strlen($password) > $dash_len) {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }

        $dash_str .= $password;
        return $dash_str;
    }
}
