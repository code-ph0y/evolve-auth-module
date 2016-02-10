<?php

namespace AuthModule\Storage;

class Base
{
    protected $ds;

    public function __construct($ds)
    {
        $this->ds = $ds;
    }
}
