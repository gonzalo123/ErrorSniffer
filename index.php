<?php
//error_reporting(0);
include('ErrorSniffer.php');
ErrorSniffer::factory();

$a = 1/0;

throw new Exception("myException");
echo "hi";