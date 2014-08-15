<?php

namespace models;
use \Symfony\Component\Process\Process;

/**
 * SystemUser model.
 * 
 * @author Bogdan Ghervan <bogdan.ghervan@gmail.com>
 */
class SystemUser
{
	/**
	 * User the web server is running as.
	 * 
	 * @var string
	 */
	protected $_username;
	
	/**
	 * Retrieves the system user the web server is running as.
	 * 
	 * @return string
	 * @throws \RuntimeException
	 */
	public function getUsername()
	{
		if (!$this->_username) {
			$process = new Process('whoami');
			$process->run();
			if (!$process->isSuccessful()) {
				throw new \RuntimeException(sprintf('Unable to detect current user: %s',
					trim($process->getErrorOutput())));
			}
			
			$this->_username = trim($process->getOutput());
		}
		
		return $this->_username;
	}
}