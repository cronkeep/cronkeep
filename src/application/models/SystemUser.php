<?php

namespace models;

class SystemUser
{
	protected $_username;
	
	public function getUsername()
	{
		if (!$this->_username) {
			$this->_username = trim(`whoami`);
		}
		
		return $this->_username;
	}
}