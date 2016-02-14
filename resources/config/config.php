<?php
$config = array();

# ---- Redirects ----
# UserLevel => Route
$config['redirectRoutes'] = array(
    'Member'        => 'Homepage',
    'Administrator' => 'Homepage',
    'Developer'     => 'Homepage'
);

# ---- Security ----
# Application Salt Value
$config['authSalt']   = 'AuThS41t';
$config['encryption'] = 'md5';

# ---- Application Details ----
$config['registeringAt'] = 'My Application';
$config['teamName']      = 'My Team';
$config['emailFrom']     = 'noreply@myapplication.com';

return $config;
