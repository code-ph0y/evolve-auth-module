<?php
namespace AuthModule\Controller;

use PPI\Framework\Module\Controller as BaseController;

class Shared extends BaseController
{
    public function loggedInCheck()
    {
        if ($this->getService('auth.security')->isLoggedIn()) {
            return $this->redirectToRoute($this->getService('auth.security')->getRedirectRoute());
        }
    }
}
