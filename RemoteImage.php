<?php 

class RemoteImage {
    
    private $url;
    
    private $base; 
    
    private $extension;
    
    private $currentDirectory;
    
    private function __construct($url) {
        $this->url = $url;
        $this->proccessUrl();
        $this->currentDirectory = __DIR__;
    }
    
    public static function load($url) {
        return new self($url);
    }
    
    public function setDirectory($dir) {
        if (is_dir($dir) && !is_file($dir)) {
            $this->currentDirectory = $dir;
        } else {
            throw new Exception("Directory not found");
        }
        return $this;
    }
    
    public function save() {
        
        $content = file_get_contents($this->url);
        
        if (empty($content)) {
            throw new Exception("Unable to fetch image {$this->url}");
        }
        
        $name = $this->currentDirectory . DIRECTORY_SEPARATOR . $this->base;
        
        if (file_put_contents($name, $content)) {
            return $name;
        }
        
        throw new Exception("Unable to save image in {$this->currentDirectory} image {$this->url}");
    }
    
    
    private function proccessUrl() {
        
        $url = $this->url;
        
        $len = $startlen = strlen($url)-1;
        
        $extension = '';
        
        while($url[$len] != '.' && $len > 0) {
            $extension = $url[$len] . $extension;
            $len--;
        }
        
        if (($index = strpos($extension, '?')) !== false) {
            $parts = explode('?', $extension);
            $extension = $parts[0];
        }
       
        $len=$startlen;
        
        $basename = basename($url);
        $filterBase = '';
        for ($i =0; $i<strlen($basename); $i ++) {
            if ($basename[$i] == '?') {
                break;
            }
            
            $filterBase .= $basename[$i];
        }
        
        
        $this->base = $filterBase;
        $this->extension = $extension;
    }
    
}
