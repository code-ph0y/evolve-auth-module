<?php
$config = array();

# ---- Redirects ----
# Route name for not logged in
$config['Guest'] = 'Homepage';

# Route names for user level
$config['Member']        = 'Homepage';
$config['Administrator'] = 'Homepage';
$config['Developer']     = 'Homepage';

# ---- Auth ----
# Application Salt Value
$config['authSalt'] = 'AuThS41t';

return $config;
