<?php class ErrorSniffer {
    public $errors = array();

    public function __construct()
    {
        self::register_exceptionHandler($this);
        self::set_error_handler($this);
        self::register_shutdown_function($this);
    }
    
    public static function factory()
    {
        return new self();
    }
    
    public static function get_error_name($err)
    {
        $errors = array(
            E_ERROR => 'ERROR',
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE',
            E_NOTICE => 'NOTICE',
            E_STRICT => 'STRICT',
            E_DEPRECATED => 'DEPRECATED',
            E_CORE_ERROR => 'CORE_ERROR',
            E_CORE_WARNING => 'CORE_WARNING',
            E_COMPILE_ERROR => 'COMPILE_ERROR',
            E_COMPILE_WARNING => 'COMPILE_WARNING',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE',
            E_USER_DEPRECATED => 'USER_DEPRECATED'
        );
        return $errors[$err];
    }
    
    private static function set_error_handler(ErrorSniffer &$that)
    {
        set_error_handler(array($that, '_error'));
    }
    
    private static function register_exceptionHandler(ErrorSniffer &$that)
    {
        set_exception_handler(array($that, '_exception'));
    }
    
    private static function register_shutdown_function(ErrorSniffer &$that)
    {
        register_shutdown_function(array($that, '_shutdown'));
    }
    
    public function register_error($err)
    {
        $this->errors[] = $err;
    }
    
    public function print_errors()
    {
        echo '<pre>'.print_r($this->errors, TRUE).'</pre>';
    }

    public function _error($errno, $errstr, $errfile, $errline)
    {
        $type = ErrorSniffer::get_error_name($errno);
        $this->register_error(array(
            'type' => $type,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ));
        return false;
    }
        
    public function _exception($exception)
    {
        $exceptionName = get_class($exception);
        $message       = $exception->getMessage();
        $file          = $exception->getFile();
        $line          = $exception->getLine();
        $trace         = $exception->getTrace();
        
        $this->register_error(array(
            'type' => 'EXCEPTION',
            'exception' => $exceptionName,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'trace' => $trace
        ));
        return false;
    }

    public function _shutdown()
    {
        $error = error_get_last();
        
        if ($error['type'] == E_ERROR)
        {
            $type = ErrorSniffer::get_error_name($error['type']);
            $this->register_error(array(
                'type' => $type,
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line']
            ));
        }
        
        self::print_errors();
    }
}