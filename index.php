<?php
//error_reporting(0);
include('ErrorSniffer.php');
ErrorSniffer::factory('127.0.0.1');

$a = 1/0;

throw new Exception("myException");
echo "hi";