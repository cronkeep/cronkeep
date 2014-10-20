<?php

namespace library\App;

/**
 * Form class.
 * 
 * @author Bogdan Ghervan <bogdan.ghervan@gmail.com>
 */
class Form extends \Zend\Form\Form
{
	/**
	 * Retrieves validation error messages in an one-dimensional array format.
	 * 
	 * @return array
	 */
	public function getFormattedMessages()
	{
		$messages = array();
		foreach ($this->getMessages() as $elementName => $messageSet) {
			$messages[] = sprintf('Field "%s": %s', $elementName, implode(' ', $messageSet));
		}
		
		return $messages;
	}
}