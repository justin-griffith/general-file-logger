<?php
/**
 * Basic file logger
 * @author  Justin Griffith <capeprogrammer@gmail.com>
 * @date 	2016
 */

class Logger {

	protected $logDir;
	protected $logExtension	= "txt";
    protected $newLine		= "\n";
    protected $delimeter	= "|";
	
	/**
	 * The desired log size in KB
	 * This log size can and will be exceeded by the size of one log.
	 * If you expect to add log items which are 10 KB set logSize under desired log file size by 10KB
	 */
    protected $logSize		= 10000;
   

    public function __construct( $logDir="", $logExtension="", $logSize="", $delimeter="", $newLine="" ) { 
        
		if(!empty($logDir)) {
			
			$this->logDir = $logDir;
			$this->generate_path($this->logDir);
		
		}
        
		if(!empty($logExtension))
			$this->logExtension = $logExtension;
 
		if(!empty($logSize))
			$this->logSize = $logSize;
       
		if(!empty($delimeter))
			$this->delimeter = $delimeter;
		
		if(!empty($newLine))
			$this->newLine = $newLine;
        
		if(!empty($writeFlag))
			$this->writeFlag = $writeFlag;
		
    }
    
	/**
	 * Adds new line to log file
	 */
    public function addLog( $type="general", $content="" ) {
        
		if(!empty($type) && !empty($content)) {
			
			// Get file name
			$fileName = $this->getFilename( $type );
				
			// Prepare content
			$content = $this->prepareContent($content);	
			
			// Write content
			$result = $this->commit( $fileName, $content );
			
			// Check file
			
			return $result;
		
		}

	return false;	
    }
    
	
	/**
	 * Commits content to file
	 * Exclusive lock
	 * If filesize exceeds max, copy contents to new file.
	 * Copy occurs while file is locked. It should be safe to copy to new file and clear working log file.
	 */
    protected function commit( $fileName, $content ) {
		
		$filePath = $this->logDir . $fileName;
		
		$file = fopen($filePath, "a+");
		
		// Lock file
		if(flock($file, LOCK_EX)) {
			
			// Check current file size
			$fileStats = fstat($file);
			
			// If filesize is greater then max size copy it
			if($fileStats['size'] >= ($this->logSize*1000)) {

				$newFileName = $this->newFileName($this->logDir, $fileName);

				$filePut = file_put_contents($this->logDir . $newFileName, stream_get_contents($file, $fileStats['size'], 0));

				// Clear working log file
				ftruncate($file, 0);
				rewind($file);			
				
			}
			
			fwrite($file, $content . $this->newLine);

			// Unlock file
			flock($file, LOCK_UN);

			return true;
		
		} else {
		  
			// If file locking queue becomes a problem we can switch to use 
			// if(flock($fp, LOCK_EX | LOCK_NB)) {
			// and queue log files in an overflow directory. (perhaps in individual timestamped files so we have no additional issues with locked file.)
			// Then on next successfull file lock, check this directory and write all overflow log files to main working log file.
			// Remove written overflow log files
		
		}
		
		fclose($file);			
			
	return false;
    }
	
	/**
	 * Generates the file name
	 */
	protected function getFilename( $type ) {
	
		// File Name
		if(!empty($type))
			return $type . "." . $this->logExtension;
		else
			return false;
		
	}
	
	/**
	 * Prepare content
	 */
	protected function prepareContent( $content ) {
		
		// If content is array, implode
		if(is_array($content)) {
			
			$newContent = implode($this->delimeter, $content);
			unset($content);
			$content = $newContent;
				
		}
	return $content;
	}
	
	/**
	 * Creates directory structure if it does not exist.
	 */
	protected function generate_path($dir) {
        
		$parts = explode('/', $dir);
        $file = array_pop($parts);
        $dir = '';
        
		foreach($parts as $part)
            if(!is_dir($dir .= "/$part")) mkdir($dir);
		
    }
	
	/**
	 * Get the next available filename
	 */
	function newFileName($path, $filename){
		
		if ($pos = strrpos($filename, '.')) {
			   
			   $name = substr($filename, 0, $pos);
			   $ext = substr($filename, $pos);
		
		} else {
			   
			   $name = $filename;
		
		}
	
		$newpath = $path.'/'.$filename;
		$newname = $filename;
		$counter = 0;
		
		while(file_exists($newpath)) {
			   $newname = $name .'_'. $counter . $ext;
			   $newpath = $path.'/'.$newname;
			   $counter++;
		 }

    return $newname;
	}
    
}

?>