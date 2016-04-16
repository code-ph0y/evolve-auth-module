<?php

namespace AuthModule\Entity;

class User
{
    protected $id            = null;
    protected $user_level_id = null;
    protected $first_name    = null;
    protected $last_name     = null;
    protected $email         = null;
    protected $salt          = null;
    protected $blocked       = null;

    // Virtual
    protected $level_name = null;

    public function __construct($data = array())
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function toArray()
    {
        return get_object_vars($this);
    }

    public function toInsertArray()
    {
        $vars = get_object_vars($this);
        unset($vars['id']);
        return $vars;
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
     * Get the value of User Level Id
     *
     * @return mixed
     */
    public function getUserLevelId()
    {
        return $this->user_level_id;
    }

    /**
     * Get the value of Username
     *
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get the value of First Name
     *
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Get the value of Last Name
     *
     * @return mixed
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Get the value of Email
     *
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get the value of Level Name
     *
     * @return mixed
     */
    public function getLevelName()
    {
        return $this->level_name;
    }

    /**
     * Get the value of first_name and last_name
     *
     * @return mixed
     */
    public function getFullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get the value of Salt
     *
     * @return mixed
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Get the value of Blocked
     *
     * @return mixed
     */
    public function getBlocked()
    {
        return $this->blocked;
    }
}
