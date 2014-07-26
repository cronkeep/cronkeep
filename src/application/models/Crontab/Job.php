<?php

namespace models\Crontab;

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
	protected $_comment;
	
	/**
	 * @var string
	 */
	protected $_hash;
	
	public function setRaw($raw)
	{
		$this->_raw = $raw;
		$this->_computeHash();
		
		return $this;
	}
	
	public function setExpression($expression)
	{
		$this->_expression = $expression;
		return $this;
	}
	
	public function setCommand($command)
	{
		$this->_command = $command;
		return $this;
	}
	
	public function setComment($comment)
	{
		$this->_comment = $comment;
		return $this;
	}
	
	public function getExpression()
	{
		return $this->_expression;
	}
	
	public function getCommand()
	{
		return $this->_command;
	}
	
	public function getComment()
	{
		return $this->_comment;
	}
	
	public function getHash()
	{
		return $this->_hash;
	}
	
	protected function _computeHash()
	{
		$this->_hash = hash('crc32', $this->_raw);
		return $this;
	}
}