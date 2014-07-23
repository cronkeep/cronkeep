<?php
namespace library\Extra;

/**
 * Implementation of a layout system similar to what other framworks have.
 * 
 * @author Bogdan Ghervan <bogdan.ghervan@gmail.com>
 */
class Layout extends \Slim\View
{
	/**
	 * Layout file relative to the templates path.
	 * 
	 * @var string
	 */
	protected $_layoutFile = 'layout.phtml';
	
	/**
	 * Renders template and injects it to the layout file.
	 * 
	 * @param string $template
	 * @param array $data
	 * @return string
	 */
	public function render($template, $data = null)
	{
		$viewContent = parent::render($template, $data);
		
		return parent::render($this->_layoutFile, array(
			'content' => $viewContent
		));
	}
}