<?php

namespace models\Crontab;

/**
 * Job model.
 * 
 * @author Bogdan Ghervan <bogdan.ghervan@gmail.com>
 */
class Job
{
	/**
	 * @var string
	 */
	protected $_raw;
	
	/**
	 * @var string|Expression
	 */
	protected $_expression;
	
	/**
	 * @var string
	 */
	protected $_command;
	
	/**
	 * @var string
	 */
	protected $_commandLine;
	
	/**
	 * @var string
	 */
	protected $_comment;
	
	/**
	 * @var string
	 */
	protected $_hash;
	
	/**
	 * Tells whether the job schedule is paused or not.
	 * 
	 * @return boolean
	 */
	public function isPaused()
	{
		return $this->_commandLine[0] == '#';
	}
	
	/**
	 * Pauses job schedule by commenting it out.
	 * 
	 * @return \models\Crontab\Job
	 */
	public function pause()
	{
		if (!$this->isPaused()) {
			$newCommandLine = '# ' . $this->getCommandLine();
			$this->setRaw(str_replace($this->getCommandLine(), $newCommandLine, $this->getRaw()));
			$this->setCommandLine($newCommandLine);
		}
		
		return $this;
	}
	
	/**
	 * Resumes job schedule by un-commenting the entry.
	 * 
	 * @return \models\Crontab\Job
	 */
	public function resume()
	{
		if ($this->isPaused()) {
			$newCommandLine = preg_replace('/\#\s/', '', $this->getCommandLine());
			$this->setRaw(str_replace($this->getCommandLine(), $newCommandLine, $this->getRaw()));
			$this->setCommandLine($newCommandLine);
		}
		
		return $this;
	}
	
	/**
	 * Sets raw entry.
	 * 
	 * @param string $raw
	 * @return Job
	 */
	public function setRaw($raw)
	{
		$this->_raw = $raw;
		$this->_generateHash();
		
		return $this;
	}
	
	/**
	 * Sets expression part.
	 * 
	 * @param string|Expression $expression
	 * @return Job
	 */
	public function setExpression($expression)
	{
		$this->_expression = $expression;
		$this->_updateDependentFields();
		
		return $this;
	}
	
	/**
	 * Sets command part.
	 * 
	 * @param string $command
	 * @return Job
	 */
	public function setCommand($command)
	{
		$this->_command = $command;
		$this->_updateDependentFields();
		
		return $this;
	}
	
	/**
	 * Sets the entire command line (expression and command).
	 *  
	 * @param string $commandLine
	 * @return \models\Crontab\Job
	 */
	public function setCommandLine($commandLine)
	{
		$this->_commandLine = $commandLine;
		return $this;
	}
	
	/**
	 * Sets comment.
	 * 
	 * @param string $comment
	 * @return Job
	 */
	public function setComment($comment)
	{
		$this->_comment = $comment;
		$this->_updateDependentFields();
		
		return $this;
	}
	
	/**
	 * Retrieves raw version of the entry.
	 * 
	 * @return string
	 * @throws \BadMethodCallException
	 */
	public function getRaw()
	{
		if (empty($this->_raw)) {
			throw new \BadMethodCallException('Raw job definition could not be generated for '
				. 'incomplete object (either expression or command fields are missing)');
		}
		
		return $this->_raw;
	}
	
	/**
	 * Retrieves expression part.
	 * 
	 * @return string
	 */
	public function getExpression()
	{
		return $this->_expression;
	}
	
	/**
	 * Retrieves command part.
	 * 
	 * @return string
	 */
	public function getCommand()
	{
		return $this->_command;
	}
	
	/**
	 * Retrieves command line (expression and command).
	 * 
	 * @return string
	 */
	public function getCommandLine()
	{
		return $this->_commandLine;
	}
	
	/**
	 * Retrieves comment.
	 * 
	 * @return string
	 */
	public function getComment()
	{
		return $this->_comment;
	}
	
	/**
	 * Retrieves crc32 generated hash unique to this job.
	 * 
	 * @return string
	 */
	public function getHash()
	{
		return $this->_hash;
	}
	
	/**
	 * Updates dependents fields (raw), if one of the component fields
	 * (command, expression, comment) changes.
	 * 
	 * @return Job
	 */
	protected function _updateDependentFields()
	{
		if ($this->_command && $this->_expression) {			
			$newRaw = sprintf("%s%s",
				$this->_comment ?  '# ' . ltrim(ltrim($this->_comment, '#')) . PHP_EOL : '',
				$this->_commandLine . PHP_EOL);
			
			// Update raw job representation (this in turn refreshes the hash)
			$this->setRaw($newRaw);
		}
		
		return $this;
	}
	
	/**
	 * Generates unique hash for this job using crc32.
	 * 
	 * @return Job
	 */
	protected function _generateHash()
	{
		$this->_hash = hash('crc32', $this->_raw);
		return $this;
	}
}