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
	 * @var string
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
	 * @return \models\Crontab\Job
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
	 * @param string $expression
	 * @return \models\Crontab\Job
	 */
	public function setExpression($expression)
	{
		$this->_expression = $expression;
		return $this;
	}
	
	/**
	 * Sets command part.
	 * 
	 * @param string $command
	 * @return \models\Crontab\Job
	 */
	public function setCommand($command)
	{
		$this->_command = $command;
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
	 * @return \models\Crontab\Job
	 */
	public function setComment($comment)
	{
		$this->_comment = $comment;
		return $this;
	}
	
	/**
	 * Retrieves raw version of the entry.
	 * 
	 * @return string
	 */
	public function getRaw()
	{
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
	 * Generates unique hash for this job using crc32.
	 * 
	 * @return \models\Crontab\Job
	 */
	protected function _generateHash()
	{
		$this->_hash = hash('crc32', $this->_raw);
		return $this;
	}
}