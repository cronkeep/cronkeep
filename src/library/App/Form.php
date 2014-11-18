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