<?php

namespace AuthModule\Entity;

class UserForgotToken
{
    protected $id = null;
    protected $used = null;
    protected $user_id = null;
    protected $date_used = null;
    protected $token = null;

    public function __construct($data = array())
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Get the value of Id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of Used
     *
     * @return mixed
     */
    public function getUsed()
    {
        return $this->used;
    }

    /**
     * Get the value of User Id
     *
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Get the value of Date Used
     *
     * @return mixed
     */
    public function getDateUsed()
    {
        return $this->date_used;
    }

    /**
     * Get the value of Token
     *
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }
}
