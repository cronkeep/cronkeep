<?php

namespace forms\AddJob;

use library\App\Form;
use Zend\Form\Element;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Advanced form for adding a cron job.
 * 
 * @author Bogdan Ghervan <bogdan.ghervan@gmail.com>
 */
class AdvancedForm extends Form implements InputFilterProviderInterface
{
	/**
	 * Form initialization.
	 * 
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		
		// Job Name
		$name = new Element\Text('name');
		$name->setLabel('Job Name');
		$name->setAttributes(array(
			'autocomplete' => 'off',
			'class' => 'form-control'
		));
		$this->add($name);
		
		// Expression
		$expression = new Element\Text('expression');
		$expression->setLabel('Time');
		$expression->setValue('* * * * *');
		$expression->setAttributes(array(
			'autocomplete' => 'off',
			'class' => 'form-control'
		));
		$this->add($expression);
		
		// Command
		$command = new Element\Textarea('command');
		$command->setLabel('Command');
		$command->setAttributes(array(
			'autocomplete' => 'off',
			'class' => 'form-control command'
		));
		$this->add($command);
	}
	
	/**
	 * Returns an array specification compatible with
	 * {@link Zend\InputFilter\Factory::createInputFilter()}.
	 * 
	 * @return array
	 */
	public function getInputFilterSpecification()
    {
		return array(
			'name' => array(
				'required' => false,
				'filters' => array(
					array('name' => 'Zend\Filter\StringTrim')
				)
			),
			'expression' => array(
				'required' => true,
				'filters' => array(
					array('name' => 'Zend\Filter\StringTrim')
				)				
			),
			'command' => array(
				'required' => true,
				'filters' => array(
					array('name' => 'Zend\Filter\StringTrim')
				)
			)
		);
	}
}