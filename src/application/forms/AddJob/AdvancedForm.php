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

namespace forms\AddJob;

use library\App\Form;
use Zend\Form\Element;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Advanced form for adding a cron job.
 *
 * @author      Bogdan Ghervan <bogdan.ghervan@gmail.com>
 * @copyright   2014 Bogdan Ghervan
 * @link        http://github.com/cronkeep/cronkeep
 * @license     http://opensource.org/licenses/Apache-2.0 Apache License 2.0
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
