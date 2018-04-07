<?php
require('../phpBack/lib/api.php');
print '1 should have random string is ' . API::randomString(1) . '<br>';
print '10 should have random string is ' . API::randomString(10) . '<br>';
print '100 should have random string is ' . API::randomString(100) . '<br>';
print '1000 should have random string is ' . API::randomString(1000) . '<br>';
?>