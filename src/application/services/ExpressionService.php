<?php
namespace services;

use models\Crontab\Job\Expression;
use forms\AddJob\SimpleForm;

/**
 * Time expression service.
 * 
 * Provides capabilities for converting a cron time expression
 * to and from various formats required by the application.
 * 
 * @author Bogdan Ghervan <bogdan.ghervan@gmail.com>
 */
class ExpressionService
{
	/**
	 * Builds expression from passed form data and returns the Expression object.
	 * 
	 * @param array $formData
	 * @return Expression
	 */
	public static function createExpression(array $formData)
	{
		$expression = new Expression();
		
		$timePicker = $formData['time']['picker'];
		$options = $formData['time'][$timePicker];
		
		switch ($timePicker) {
			case SimpleForm::SPECIFIC_TIME: {
				$expression->setHour($options['hour']);
				$expression->setMinute($options['minute']);
				break;
			}
			case SimpleForm::EVERY_HOUR: {
				$expression->setHour(array('scalar' => '*', 'step' => $options['step']));
				$expression->setMinute($options['minute']);
				break;
			}
			case SimpleForm::EVERY_MINUTE: {
				$expression->setMinute(array('scalar' => '*', 'step' => $options['step']));
				break;
			}
		}
		
		$repeatPicker = $formData['repeat']['picker'];
		switch ($repeatPicker) {
			case SimpleForm::WEEKLY: {
				$options = $formData['repeat'][$repeatPicker];
				foreach ($options['dayOfWeek'] as $dayOfWeek) {
					$expression->addDayOfWeek($dayOfWeek);
				}
				break;
			}
			case SimpleForm::MONTHLY: {
				$options = $formData['repeat'][$repeatPicker];
				foreach ($options['dayOfMonth'] as $dayOfMonth) {
					$expression->addDayOfMonth($dayOfMonth);
				}
				break;
			}
			case SimpleForm::YEARLY: {
				$options = $formData['repeat'][$repeatPicker];
				$expression->setMonth($options['month']);
				$expression->setDayOfMonth($options['day']);
				break;
			}
		}
		
		return $expression;
	}
	
	/**
	 * Tells whether the given expression is simple enough so that
	 * the simple add job form is able to render it.
	 * 
	 * @param Expression $expression
	 * @return boolean
	 */
	public static function isSimpleExpression(Expression $expression)
	{
		$minute		= $expression->getMinute();
		$hour		= $expression->getHour();
		$dayOfMonth = $expression->getDayOfMonth();
		$month		= $expression->getMonth();
		$dayOfWeek  = $expression->getDayOfWeek();
		
		// Simple expressions support a specific time (one hour, one minute)
		if (count($minute) > 1 || count($hour) > 1) {
			return false;
		}
		
		// "Every minute" time option (minute part can have a step)
		if ($minute && is_array($minute[0])) {
			// Only * followed by a step is supported (stored as scalar)
			if (!isset($minute[0]['scalar'])) {
				return false;
			}
			
			// A specific hour can't be set in this case
			if ($hour) {
				return false;
			}
		}
		
		// "Every hour" time option (may have a step and the minute to run at)
		if ($hour && is_array($hour[0])) {
			// Only * followed by a step is supported (stored as scalar)
			if (!isset($hour[0]['scalar'])) {
				return false;
			}
			
			// Something else than a simple int, the expression is not simple
			if (!is_numeric($minute[0])) {
				return false;
			}
		}
		
		// "Weekly" repeat option (only day of week should be set)
		if ($dayOfWeek) {
			if ($month) {
				return false;
			}
			if ($dayOfMonth) {
				return false;
			}
			
			// Step in not supported for day of week intervals
			foreach ($dayOfWeek as $value) {
				if (is_array($value) && isset($value['step']) && $value['step'] > 1) {
					return false;
				}
			}
		}
		
		// "Monthly" repeat option (only day of month should be set)
		if ($dayOfMonth) {
			if ($dayOfWeek) {
				return false;
			}
			if ($month) {
				return false;
			}
			
			// Step is not supported for day of month intervals
			foreach ($dayOfMonth as $value) {
				if (is_array($value) && isset($value['step']) && $value['step'] > 1) {
					return false;
				}
			}
		}
		
		// "Yearly" repeat option (only a month and a day should be set)
		if ($month) {
			if (count($dayOfMonth) != 1) {
				return false;
			}
			if ($dayOfWeek) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Converts an expression back into form data.
	 * 
	 * @param Expression $expression
	 * @return array
	 */
	public function getFormData(Expression $expression)
	{
		if (!self::isSimpleExpression($expression)) {
			throw new \InvalidArgumentException(
				'Expression is too complex to be rendered by the simple form');
		}
		
		$formData = array();
		
		// Build form data here
		
		return $formData;
	}
}