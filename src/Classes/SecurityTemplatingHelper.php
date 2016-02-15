<?php
namespace AuthModule\Classes;

use Symfony\Component\Templating\Helper\Helper as BaseHelper;

class SecurityTemplatingHelper extends BaseHelper
{
    protected $security;

    public function __construct($security)
    {
        $this->security = $security;
    }

    public function isLoggedIn()
    {
        return $this->security->isLoggedIn();
    }

    public function getUserLevel()
    {
        $user = $this->security->getUser();
        return $user->getUserLevelId();
    }

    public function getName()
    {
        return 'security';
    }

    public function __call($name, $args)
    {
        return call_user_func_array(array($this->userSecurity, $name), $args);
    }
}
