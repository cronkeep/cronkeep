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
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\InputFilter\InputFilter;

/**
 * Simple form for adding a cron job.
 *
 * @author      Bogdan Ghervan <bogdan.ghervan@gmail.com>
 * @copyright   2014 Bogdan Ghervan
 * @link        http://github.com/cronkeep/cronkeep
 * @license     http://opensource.org/licenses/Apache-2.0 Apache License 2.0
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
     * Time radio set options.
     * 
     * @var array
     */
    protected $_timeOptions = array(self::SPECIFIC_TIME, self::EVERY_HOUR, self::EVERY_MINUTE);
    
    /**
     * Repeat select options.
     * 
     * @var array
     */
    protected $_repeatOptions = array(
        self::DAILY   => 'Daily',
        self::WEEKLY  => 'Weekly',
        self::MONTHLY => 'Monthly',
        self::YEARLY  => 'Yearly'
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
        $picker->setValueOptions($this->_timeOptions);
        $picker->setValue(self::SPECIFIC_TIME);
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
        $picker->setValueOptions($this->_repeatOptions);
        $picker->setValue(self::DAILY);
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
        
        // Note that this field is rendered on the frontend without the view helper,
        // for more flexibility.
        $dayOfWeek = new Element\MultiCheckbox('dayOfWeek');
        $dayOfWeek->setLabel('Pick day of week');
        $dayOfWeek->setValueOptions($this->_dayOfWeekOptions);
        
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
        
        // Note that this field is rendered on the frontend without the view helper,
        // for more flexibility.
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
        $repeatYearlyFieldset->setLabel('Pick date');
        
        $month = new Element\Select('month');
        $month->setAttributes(array(
            'class' => 'form-control month pull-left',
            'autocomplete' => 'off'
        ));
        $month->setValueOptions($this->_monthOptions);
        $repeatYearlyFieldset->add($month);
        
        $day = new Element\Number('dayOfMonth');
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
                'type' => 'Zend\InputFilter\InputFilter',
                'picker' => array(
                    'required' => true,
                    'validators' => array(
                        // Pushing what is otherwise an implied validator for selects, to break
                        // validation chain on failure to prevent reaching the callback
                        array(
                            'name' => 'Zend\Validator\InArray',
                            'break_chain_on_failure' => true,
                            'options' => array(
                                'haystack' => $this->_timeOptions
                            )
                        ),
                        // Finds a fieldset called $timeOption inside fieldset "time",
                        // and marks all those inputs as required
                        array(
                            'name' => 'Zend\Validator\Callback',
                            'options' => array(
                                'callback' => array($this, 'requireDependentTimeInputs')
                            )
                        ),
                    )
                ),
                self::SPECIFIC_TIME => array(
                    'type' => 'Zend\InputFilter\InputFilter',
                    'hour' => array(
                        'required' => false,
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
                        'required' => false,
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
                    'type' => 'Zend\InputFilter\InputFilter',
                    'step' => array(
                        'required' => false,
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
                        'required' => false,
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
                    'type' => 'Zend\InputFilter\InputFilter',
                    'step' => array(
                        'required' => false,
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
                'type' => 'Zend\InputFilter\InputFilter',
                'picker' => array(
                    'required' => true,
                    'validators' => array(
                        // Pushing what is otherwise an implied validator for selects, to break
                        // validation chain on failure to prevent reaching the callback
                        array(
                            'name' => 'Zend\Validator\InArray',
                            'break_chain_on_failure' => true,
                            'options' => array(
                                'haystack' => array_keys($this->_repeatOptions)
                            )
                        ),
                        // Finds a fieldset called $repeatOption inside fieldset "repeat",
                        // and marks all those inputs as required
                        array(
                            'name' => 'Zend\Validator\Callback',
                            'options' => array(
                                'callback' => array($this, 'requireDependentRepeatInputs')
                            )
                        ),
                    )
                ),
                self::WEEKLY => array(
                    'type' => 'Zend\InputFilter\InputFilter',
                    'dayOfWeek' => array(
                        'required' => false
                    )
                ),
                self::MONTHLY => array(
                    'type' => 'Zend\InputFilter\InputFilter',
                    'dayOfMonth' => array(
                        'required' => false
                    )
                ),
                self::YEARLY => array(
                    'type' => 'Zend\InputFilter\InputFilter',
                    'dayOfMonth' => array(
                        'required' => false
                    )
                )
            )
        );
    }

    /**
     * Marks inputs related to chosen time option as required.
     * This can be used as a callback validator for the time picker.
     * 
     * @param string $timeOption
     * @return boolean
     */
    public function requireDependentTimeInputs($timeOption)
    {
        $timeInputFilter = $this->getInputFilter()->get('time');
        $this->_markInputsAsRequired($timeInputFilter, $timeOption);
        
        return true;
    }

    /**
     * Marks inputs related to chosen repeat option as required.
     * This can be used as a callback validator for the repeat picker.
     * 
     * @param string $repeatOption
     * @return bool
     */
    public function requireDependentRepeatInputs($repeatOption)
    {
        $repeatInputFilter = $this->getInputFilter()->get('repeat');
        $this->_markInputsAsRequired($repeatInputFilter, $repeatOption);

        return true;
    }
    
    /**
     * Checks that a fieldset called $chosenOption exists under fieldset $pickerInputFilter,
     * and marks all the inputs in that fieldset as required.
     * 
     * Example:
     * Given this structure:
     * - name
     * - command
     * - repeat
     *   \ picker (daily, weekly, monthly, yearly)
     *   \ weekly
     *     \ dayOfWeek
     *   \ monthly
     *     \ dayOfMonth
     *   [...]
     * [...]
     * 
     * For the given input filter associated to fieldset "repeat" and "weekly" as the chosen
     * picker option, we only want repeat/weekly/dayOfWeek to be required, but not
     * repeat/monthly/dayOfMonth.
     * 
     * @param InputFilter $inputFilter
     * @param string $chosenOption
     * @return SimpleForm
     */
    protected function _markInputsAsRequired(InputFilter $pickerInputFilter, $chosenOption)
    {
        if ($pickerInputFilter->has($chosenOption)) {
            $dependentFieldsetInputFilter = $pickerInputFilter->get($chosenOption);
            
            foreach ($dependentFieldsetInputFilter->getInputs() as $input) {
                /* @var Zend\InputFilter\Input $input */
                $input->setRequired(true);
            }
        }
        
        return $this;
    }
}
