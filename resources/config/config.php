<?php
$config = array();

# ---- Redirects ----
# UserLevel => Route
$config['redirectRoutes'] = array(
    'Member'        => 'Homepage',
    'Administrator' => 'Homepage',
    'Developer'     => 'Homepage'
);

# ---- Auth ----
# Application Salt Value
$config['authSalt'] = 'AuThS41t';

return $config;
