<?php

namespace forms\AddJob;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Simple form for adding a cron job.
 * 
 * @author Bogdan Ghervan <bogdan.ghervan@gmail.com>
 */
class SimpleForm extends Form implements InputFilterProviderInterface
{
	/**
	 * Constants designating possible values of the time and repeat picker fields,
	 * which also match the name of corresponding fieldsets with additional options
	 * to set for the respective chosen value.
	 */
	const SPECIFIC_TIME = 'specificTime';
	const EVERY_HOUR = 'everyHour';
	const EVERY_MINUTE = 'everyMinute';
	const DAILY = 'daily';
	const WEEKLY = 'weekly';
	const MONTHLY = 'monthly';
	const YEARLY = 'yearly';
	
	/**
	 * Options of the dayOfWeek multi-checkbox field.
	 * 
	 * @var array
	 */
	protected $_dayOfWeekOptions = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
	
	/**
	 * Options of the month select.
	 * 
	 * @var array
	 */
	protected $_monthOptions = array(
		1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May',
		6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October',
		11 => 'November', 12 => 'December'
	);
	
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
		
		// Command
		$command = new Element\Textarea('command');
		$command->setLabel('Command');
		$command->setAttributes(array(
			'autocomplete' => 'off',
			'class' => 'form-control command'
		));
		$this->add($command);
		
		// Add fieldset wrapping all time options
		$this->add($this->getTimeFieldset());
		
		// Add fieldset wrapping all repeat options
		$this->add($this->getRepeatFieldset());
	}
	
	/**
	 * Builds time fieldset and returns it.
	 * 
	 * @return Fieldset
	 */
	public function getTimeFieldset()
	{
		$timeFieldset = new Fieldset('time');
		
		// Picker field
		$picker = new Element\Radio('picker');
		$picker->setLabel('Pick Time');
		$picker->setValueOptions(array(self::SPECIFIC_TIME, self::EVERY_HOUR, self::EVERY_MINUTE));
		$timeFieldset->add($picker);
		
		// Attach "child" fieldsets
		$timeFieldset->add($this->getSpecificTimeFieldset());
		$timeFieldset->add($this->getEveryHourFieldset());
		$timeFieldset->add($this->getEveryMinuteFieldset());
		
		return $timeFieldset;
	}
	
	/**
	 * Builds repeat fieldset and returns it.
	 * 
	 * @return Fieldset
	 */
	public function getRepeatFieldset()
	{
		$repeatFieldset = new Fieldset('repeat');
		
		// Picker field
		$picker = new Element\Select('picker');
		$picker->setLabel('Repeat');
		$picker->setAttributes(array(
			'class' => 'form-control repeat',
			// @todo make "autocomplete" attribute work for select
			'autocomplete' => 'off'
		));
		$picker->setValueOptions(array(
			self::DAILY   => 'Daily',
			self::WEEKLY  => 'Weekly',
			self::MONTHLY => 'Monthly',
			self::YEARLY  => 'Yearly'
		));
		$repeatFieldset->add($picker);
		
		// Attach "child" fieldsets
		$repeatFieldset->add($this->getRepeatWeeklyFieldset());
		$repeatFieldset->add($this->getRepeatMonthlyFieldset());
		$repeatFieldset->add($this->getRepeatYearlyFieldset());
		
		return $repeatFieldset;
	}
	
	/**
	 * Builds fieldset with additional properties for the "specificTime" time picker value.
	 * It returns the built fieldset (used as a child of the "time" fieldset).
	 * 
	 * @return Fieldset
	 */
	public function getSpecificTimeFieldset()
	{
		$specificTimeFieldset = new Fieldset(self::SPECIFIC_TIME);
		
		$hour = new Element\Number('hour');
		$hour->setAttributes(array(
			'placeholder' => 'h',
			'autocomplete' => 'off',
			'class' => 'form-control input-hour',
			'min' => 0,
			'max' => 23,
			'maxlength' => 2
		));
		$specificTimeFieldset->add($hour);
		
		$minute = new Element\Number('minute');
		$minute->setAttributes(array(
			'placeholder' => 'm',
			'autocomplete' => 'off',
			'class' => 'form-control input-minute',
			'min' => 0,
			'max' => 59,
			'maxlength' => 2
		));
		$specificTimeFieldset->add($minute);
		
		return $specificTimeFieldset;
	}

	/**
	 * Builds fieldset with additional properties for the "everyHour" time picker value.
	 * It returns the built fieldset (used as a child of the "time" fieldset).
	 * 
	 * @return Fieldset
	 */
	public function getEveryHourFieldset()
	{
		$everyHourFieldset = new Fieldset(self::EVERY_HOUR);
		
		$step = new Element\Number('step');
		$step->setValue(1);
		$step->setAttributes(array(
			'class' => 'form-control input-step',
			'min' => 1,
			'max' => 23,
			'autocomplete' => 'off',
			'maxlength' => 2,
			'disabled' => 'disabled'
		));
		$everyHourFieldset->add($step);
		
		$minute = new Element\Number('minute');
		$minute->setValue(0);
		$minute->setAttributes(array(
			'autocomplete' => 'off',
			'class' => 'form-control input-minute',
			'min' => 0,
			'max' => 59,
			'maxlength' => 2,
			'disabled' => 'disabled'
		));
		$everyHourFieldset->add($minute);
		
		return $everyHourFieldset;
	}

	/**
	 * Builds fieldset with additional properties for the "everyMinute" time picker value.
	 * It returns the built fieldset (used as a child of the "time" fieldset).
	 * 
	 * @return Fieldset
	 */
	public function getEveryMinuteFieldset()
	{
		$everyMinuteFieldset = new Fieldset(self::EVERY_MINUTE);
		
		$step = new Element\Number('step');
		$step->setValue(1);
		$step->setAttributes(array(
			'class' => 'form-control input-step',
			'min' => 1,
			'max' => 59,
			'autocomplete' => 'off',
			'maxlength' => 2,
			'disabled' => 'disabled'
		));
		$everyMinuteFieldset->add($step);
		
		return $everyMinuteFieldset;
	}
	
	/**
	 * Builds fieldset with additional properties for the "weekly" repeat picker value.
	 * It returns the built fieldset (used as a child of the "repeat" fieldset).
	 * 
	 * @return Fieldset
	 */
	public function getRepeatWeeklyFieldset()
	{
		$repeatWeeklyFieldset = new Fieldset(self::WEEKLY);
		
		$dayOfWeek = new Element\MultiCheckbox('dayOfWeek');
		$dayOfWeek->setLabel('Pick day of week');
		
		$valueOptions = array();
		foreach ($this->_dayOfWeekOptions as $key => $dayOption) {
			$valueOptions[] = array(
				'label' => $dayOption,
				'value' => $key,
				'attributes' => array(
					'autocomplete' => 'off'
				),
				'label_attributes' => array(
					'class' => 'btn btn-default'
				)
			);
		}
		$dayOfWeek->setValueOptions($valueOptions);
		
		$repeatWeeklyFieldset->add($dayOfWeek);		
		return $repeatWeeklyFieldset;
	}

	/**
	 * Builds fieldset with additional properties for the "monthly" repeat picker value.
	 * It returns the built fieldset (used as a child of the "repeat" fieldset).
	 * 
	 * @return Fieldset
	 */
	public function getRepeatMonthlyFieldset()
	{
		$repeatMonthlyFieldset = new Fieldset(self::MONTHLY);
		
		$dayOfMonth = new Element\MultiCheckbox('dayOfMonth');
		$dayOfMonth->setLabel('Pick days');
		$dayOfMonth->setValueOptions(array_combine(range(1, 31), range(1, 31)));
		$repeatMonthlyFieldset->add($dayOfMonth);
		
		return $repeatMonthlyFieldset;
	}
	

	/**
	 * Builds fieldset with additional properties for the "yearly" repeat picker value.
	 * It returns the built fieldset (used as a child of the "repeat" fieldset).
	 * 
	 * @return Fieldset
	 */
	public function getRepeatYearlyFieldset()
	{
		$repeatYearlyFieldset = new Fieldset(self::YEARLY);
		$repeatYearlyFieldset->setLabel('Pick dates');
		
		$month = new Element\Select('month');
		$month->setAttributes(array(
			'class' => 'form-control month pull-left',
			'autocomplete' => 'off'
		));
		$month->setValueOptions($this->_monthOptions);
		$repeatYearlyFieldset->add($month);
		
		$day = new Element\Number('day');
		$day->setValue(1);
		$day->setAttributes(array(
			'class' => 'form-control input-day pull-left',
			'autocomplete' => 'off',
			/* @todo Make maxlength work for numbers */
			'maxlength' => 2,
			'min' => 1,
			'max' => 31
		));
		$repeatYearlyFieldset->add($day);
		
		return $repeatYearlyFieldset;
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
			'command' => array(
				'required' => true,
				'filters' => array(
					array('name' => 'Zend\Filter\StringTrim')
				)
			),
			'time' => array(
				'picker' => array(
					'required' => true
				),
				self::SPECIFIC_TIME => array(
					'hour' => array(
						'filters' => array(
							array('name' => 'Zend\Filter\StringTrim')
						),
						'validators' => array(
							array('name' => 'Zend\Validator\Digits'),
							array(
								'name' => 'Zend\Validator\Between',
								'options' => array('min' => 0, 'max' => 23)
							)
						)
					),
					'minute' => array(
						'filters' => array(
							array('name' => 'Zend\Filter\StringTrim')
						),
						'validators' => array(
							array('name' => 'Zend\Validator\Digits'),
							array(
								'name' => 'Zend\Validator\Between',
								'options' => array('min' => 0, 'max' => 59)
							)
						)
					)
				),
				self::EVERY_HOUR => array(
					'step' => array(
						'required' => true,
						'filters' => array(
							array('name' => 'Zend\Filter\StringTrim')
						),
						'validators' => array(
							array('name' => 'Zend\Validator\Digits'),
							array(
								'name' => 'Zend\Validator\Between',
								'options' => array('min' => 1, 'max' => 23)
							)
						)
					),
					'minute' => array(
						'required' => true,
						'filters' => array(
							array('name' => 'Zend\Filter\StringTrim')
						),
						'validators' => array(
							array('name' => 'Zend\Validator\Digits'),
							array(
								'name' => 'Zend\Validator\Between',
								'options' => array('min' => 0, 'max' => 59)
							)
						)
					)
				),
				self::EVERY_MINUTE => array(
					'step' => array(
						'required' => true,
						'filters' => array(
							array('name' => 'Zend\Filter\StringTrim')
						),
						'validators' => array(
							array('name' => 'Zend\Validator\Digits'),
							array(
								'name' => 'Zend\Validator\Between',
								'options' => array('min' => 1, 'max' => 59)
							)
						)
					)
				)
			),
			'repeat' => array(
				self::YEARLY => array(
					'day' => array(
						'filters' => array(
							array('name' => 'Zend\Filter\StringTrim')
						),
						'validators' => array(
							array('name' => 'Zend\Validator\Digits'),
							array(
								'name' => 'Zend\Validator\Between',
								'options' => array('min' => 1, 'max' => 31)
							)
						)
					)
				)
			)
         );
     }	
}