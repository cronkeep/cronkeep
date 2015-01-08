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
 * Form class.
 *
 * @author      Bogdan Ghervan <bogdan.ghervan@gmail.com>
 * @copyright   2014 Bogdan Ghervan
 * @link        http://github.com/cronkeep/cronkeep
 * @license     http://opensource.org/licenses/Apache-2.0 Apache License 2.0
 */
class Form extends \Zend\Form\Form
{
    /**
     * Retrieves validation error messages in a one-dimensional array format.
     * 
     * @return array
     */
    public function getFormattedMessages()
    {
        $messages = $this->getMessages();
        $preparedMessages = array();
        
        // Prepend every element with the name of the form?
        $formName = $this->wrapElements() ? $this->getName() : '';
        
        // Prepare an one-dimensional array of error messages
        $this->_prepareMessages($messages, $preparedMessages, $formName);
        
        return $preparedMessages;
    }
    
    /**
     * Recursively iterates through the validation error messages in the format returned
     * by {@link Zend\Form\Form::getMessages()} and builds up a one-dimensional
     * representation in $output.
     * 
     * @param array $messages Validation error messages
     * @param array $output Array where output is built
     * @param string $elementName Name to prepend (optional)
     * @param string $separator Separator for multiple messages on one element (optional)
     * @return Form
     */
    protected function _prepareMessages(array $messages, array &$output, $elementName = '',
        $separator = '. ')
    {
        if (is_string(current($messages))) {
            $output[$elementName] = implode($separator, $messages);
        } else {
            foreach ($messages as $currentName => $messageSet) {
                $currentName = $elementName ?
                    ($elementName . '[' . $currentName . ']') :
                    $currentName;
                
                $this->_prepareMessages($messageSet, $output, $currentName);
            }
        }
        
        return $this;
    }
}
