<?php 
 return array (
  'GET' => 
  array (
    '/' => 
    array (
      '/' => 
      array (
        'controller' => 'App\\Integration\\Incoming\\Http\\Controllers\\HomeController',
        'method' => 'index',
      ),
    ),
    '/blacklisted' => 
    array (
      '/' => 
      array (
        'controller' => 'App\\Integration\\Incoming\\Http\\Controllers\\BlacklistedController',
        'method' => 'getAll',
      ),
    ),
  ),
  'POST' => 
  array (
    '/blacklisted' => 
    array (
      '/' => 
      array (
        'controller' => 'App\\Integration\\Incoming\\Http\\Controllers\\BlacklistedController',
        'method' => 'save',
      ),
    ),
    '/whitelisted' => 
    array (
      '/' => 
      array (
        'controller' => 'Comparison\\Integration\\Incoming\\Http\\Controllers\\WhitelistedController',
        'method' => 'save',
      ),
    ),
  ),
);