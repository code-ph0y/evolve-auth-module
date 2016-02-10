<?php
namespace AuthModule\Classes;

use Symfony\Component\Templating\Helper\Helper as BaseHelper;

class UserSecurityTemplatingHelper extends BaseHelper
{

    protected $security;

    public function __construct($security)
    {
        $this->security = $security;
    }

    public function isLoggedIn()
    {
        return $this->userSecurity->isLoggedIn();
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
