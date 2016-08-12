<?php

/**
 * Creates file "lock" file with 
 * process id where method self::lock is called 
 * 
 */
final class ProcessLock {
    /**
     * Lock file location
     * @var string
     */
    private $file;
    
    /**
     * Process id
     * @var int
     */
    private $pid;
    
    //constructor
    private function __construct() { }
    
    /**
     * Creates lock file 
     * 
     * @param string $file - lock file desired location
     * @return \self
     * @throws Exception     if unable to create
     */
    public static function lock($file) { 
        $obj = new self;
        
        if (!file_exists($file)) {
            if(!touch($file)) {
                throw new Exception("Unable to create lock file");
            }
        }
        
        $obj->file = $file;
        $obj->pid = posix_getpid();
        
        file_put_contents($obj->file = $file, strval($obj->pid));
        
        return $obj;
    }
    
    /**
     * Removes lock file 
     * @return int - process id
     * @throws Exception     if file does not exists or it's not readable or writeable
     */
    public function unlock() {        
        if (!file_exists($this->file)) {
            throw new Exception("Lock file does not exists ");
        }
        
        if (!is_readable($this->file) || !is_writable($this->file)) {
            throw new Exception("Lock file is not readable or writable");
        }
        
        $pid = intval($this->getLockFileContent());
        
        unlink($this->file);
        
        return $pid;
    }
    
    /**
     * kills process
     */
    public function kill() {
        $pid = $this->unlock();
        
        if (!empty($pid) && self::processExists($pid)) {
            exec("kill {$pid}");
        }
    }
    
    /**
     * Check if process is running
     * @param int $pid
     * @return boolean     /
     */
    public static function processExists($pid) {
        if (is_numeric($pid)) {
            return file_exists("/proc/{$pid}");
        }
        return false;
    }
    
    /**
     * Lock file exists and process is running
     * @param string $file - file location
     * @return boolean     /
     */
    public static function lockExists($file) {
        if (file_exists($file)) {
            if (self::processExists(file_get_contents($file))) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Lock file content (pid)
     * @return string
     */
    private function getLockFileContent() {
        return file_get_contents($this->file);
    }
}
