<?php
class ErrorSniffer
{
    public function __construct($restringToIp)
    {
        if ($this->getip() == $restringToIp) {
            self::register_exceptionHandler($this);
            self::set_error_handler($this);
            self::register_shutdown_function($this);
        }
    }

    public static function factory($restringToIp)
    {
        return new self($restringToIp);
    }
    
    private $errors = array();

    private function getip() {
        if ($_SERVER) {
            if ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] ) {
                $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } elseif ( isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER["HTTP_CLIENT_IP"] ) {
                $realip = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $realip = $_SERVER["REMOTE_ADDR"];
            }
        } else {
            if ( getenv('HTTP_X_FORWARDED_FOR') ) {
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            } elseif ( getenv('HTTP_CLIENT_IP') ) {
                $realip = getenv('HTTP_CLIENT_IP');
            } else {
                $realip = getenv('REMOTE_ADDR');
            }
        }
        return $realip;
    }
    
    public static function getErrorName($err)
    {
        $errors = array(
            E_ERROR             => 'ERROR',
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_WARNING           => 'WARNING',
            E_PARSE             => 'PARSE',
            E_NOTICE            => 'NOTICE',
            E_STRICT            => 'STRICT',
            E_DEPRECATED        => 'DEPRECATED',
            E_CORE_ERROR        => 'CORE_ERROR',
            E_CORE_WARNING      => 'CORE_WARNING',
            E_COMPILE_ERROR     => 'COMPILE_ERROR',
            E_COMPILE_WARNING   => 'COMPILE_WARNING',
            E_USER_ERROR        => 'USER_ERROR',
            E_USER_WARNING      => 'USER_WARNING',
            E_USER_NOTICE       => 'USER_NOTICE',
            E_USER_DEPRECATED   => 'USER_DEPRECATED',
        );
        return $errors[$err];
    }

    private static function set_error_handler(ErrorSniffer &$that)
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) use (&$that) {
                $type = ErrorSniffer::getErrorName($errno);
                $that->registerError(array('type' => $type, 'message' => $errstr, 'file' => $errfile, 'line' => $errline));
                return false;
        });
    }

    private static function register_exceptionHandler(ErrorSniffer &$that)
    {
        set_exception_handler(function($exception) use (&$that) {
                $exceptionName = get_class($exception);
                $message = $exception->getMessage();
                $file  = $exception->getFile();
                $line  = $exception->getLine();
                $trace = $exception->getTrace();
    
                $that->registerError(array('type' => 'EXCEPTION', 'exception' => $exceptionName, 'message' => $message, 'file' => $file, 'line' => $line, 'trace' => $trace));
                return false;
        });
    }

    private static function register_shutdown_function(ErrorSniffer &$that)
    {
        register_shutdown_function(function() use (&$that) {
                $error = error_get_last();

                if ($error['type'] == E_ERROR) {
                    $type = ErrorSniffer::getErrorName($error['type']);
                    $that->registerError(array('type' => $type, 'message' => $error['message'], 'file' => $error['file'], 'line' => $error['line']));
                }

                $that->printErrors();
        });
    }

    public function registerError($err)
    {
        $this->errors[] = $err;
    }
    
    public function printErrors()
    {
        print_r($this->errors);
    }
}