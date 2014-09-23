<?php

namespace forms\AddJob;

/**
 * Initializes the right add job form (simple or advanced) based on passed form data.
 * 
 * @author Bogdan Ghervan <bogdan.ghervan@gmail.com>
 */
class FormFactory
{
	const SIMPLE = 'simple';
	const ADVANCED = 'advanced';
	
	/**
	 * Initializes the right add job form (simple or advanced) based on passed form data,
	 * injects the data into the form and returns the form object.
	 * 
	 * @param array $formData
	 * @return \forms\AddJob\AdvancedForm
	 * @throws \RuntimeException
	 */
	public static function createForm($formData)
	{
		if (empty($formData['mode'])) {
			throw new \RuntimeException('Mode is missing');
		}
		
		if ($formData['mode'] == self::SIMPLE) {
			$form = new SimpleForm();
		} else {
			$form = new AdvancedForm();
		}
		
		$form->setData($formData);
		return $form;
	}
}