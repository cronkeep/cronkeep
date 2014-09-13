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
}