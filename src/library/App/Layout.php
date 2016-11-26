<?php
/**
 * Copyright 2014 Bogdan Ghervan
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace library\App;

/**
 * Layout system implementation.
 *
 * @author      Bogdan Ghervan <bogdan.ghervan@gmail.com>
 * @copyright   2014 Bogdan Ghervan
 * @link        http://github.com/cronkeep/cronkeep
 * @license     http://opensource.org/licenses/Apache-2.0 Apache License 2.0
 */
class Layout extends View
{
    /**
     * Layout file relative to the templates path.
     * 
     * @var string
     */
    protected $_layoutFile = 'layout.phtml';
    
    /**
     * JavaScript variables registry.
     * 
     * @var array
     */
    protected $_vars = array();
    
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
            'content' => $viewContent,
            'vars'    => $this->_getVarsForOutput()
        ));
    }
    
    /**
     * Push variable $name to JavaScript.
     * 
     * @param string $name
     * @param mixed $value
     * @return \library\App\Layout
     */
    public function appendVar($name, $value)
    {
        $this->_vars[$name] = $value;
        return $this;
    }
    
    /**
     * Returns aggregated variables to send to JavaScript, with values encoded in JSON.
     * 
     * @return string
     */
    protected function _getVarsForOutput()
    {
        $output = array();
        foreach ($this->_vars as $name => $value) {
            $output[] = sprintf('%s = %s', $name, json_encode($value));
        }
        
        return $output ? sprintf('var %s;', implode(', ', $output)) : null;
    }
}
