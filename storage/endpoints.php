<?php return array (
  'routes' => 
  array (
    'GET' => 
    array (
      '/' => 
      array (
        0 => 'App\\Controllers\\WelcomeController',
        1 => 'index',
        2 => 
        array (
        ),
      ),
      '/blacklist' => 
      array (
        0 => 'App\\Controllers\\BlacklistController',
        1 => 'index',
        2 => 
        array (
        ),
      ),
      '/whitelist' => 
      array (
        0 => 'App\\Controllers\\WhitelistController',
        1 => 'index',
        2 => 
        array (
        ),
      ),
    ),
    'POST' => 
    array (
      '/blacklist' => 
      array (
        0 => 'App\\Controllers\\BlacklistController',
        1 => 'create',
        2 => 
        array (
        ),
      ),
      '/whitelist' => 
      array (
        0 => 'App\\Controllers\\WhitelistController',
        1 => 'store',
        2 => 
        array (
        ),
      ),
    ),
    'PUT' => 
    array (
      '/blacklist' => 
      array (
        0 => 'App\\Controllers\\BlacklistController',
        1 => 'update',
        2 => 
        array (
          0 => 'int',
        ),
      ),
      '/whitelist' => 
      array (
        0 => 'App\\Controllers\\WhitelistController',
        1 => 'update',
        2 => 
        array (
          0 => 'int',
        ),
      ),
    ),
    'DELETE' => 
    array (
      '/blacklist' => 
      array (
        0 => 'App\\Controllers\\BlacklistController',
        1 => 'delete',
        2 => 
        array (
          0 => 'int',
        ),
      ),
      '/whitelist' => 
      array (
        0 => 'App\\Controllers\\WhitelistController',
        1 => 'delete',
        2 => 
        array (
          0 => 'int',
        ),
      ),
    ),
  ),
);