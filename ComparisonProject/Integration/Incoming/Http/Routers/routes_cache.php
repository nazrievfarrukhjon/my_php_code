<?php 
 return array (
  'GET' => 
  array (
    '/' => 
    array (
      '/' => 
      array (
        0 => 'App\\Integration\\Incoming\\Http\\Controllers\\HomeController',
        1 => 'index',
      ),
      '/@' => 
      array (
        0 => 'App\\Integration\\Incoming\\Http\\Controllers\\HomeController',
        1 => 'one',
      ),
      '/@/@' => 
      array (
        0 => 'App\\Integration\\Incoming\\Http\\Controllers\\HomeController',
        1 => 'two',
      ),
    ),
    '/blacklisted' => 
    array (
      '/' => 
      array (
        0 => 'App\\Integration\\Incoming\\Http\\Controllers\\BlacklistedController',
        1 => 'getAll',
      ),
    ),
  ),
  'POST' => 
  array (
    '/blacklisted' => 
    array (
      '/' => 
      array (
        0 => 'App\\Integration\\Incoming\\Http\\Controllers\\BlacklistedController',
        1 => 'save',
      ),
    ),
    '/whitelisted' => 
    array (
      '/' => 
      array (
        0 => 'App\\Comparison\\Integration\\Incoming\\Http\\Controllers\\WhitelistedController',
        1 => 'save',
      ),
    ),
  ),
);