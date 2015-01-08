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

namespace services;

use models\Crontab\Job\Expression;
use forms\AddJob\SimpleForm;

/**
 * Time expression service.
 * 
 * Provides capabilities for converting a cron time expression
 * to and from various formats required by the application.
 *
 * @author      Bogdan Ghervan <bogdan.ghervan@gmail.com>
 * @copyright   2014 Bogdan Ghervan
 * @link        http://github.com/cronkeep/cronkeep
 * @license     http://opensource.org/licenses/Apache-2.0 Apache License 2.0
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
                $expression->setDayOfMonth($options['dayOfMonth']);
                break;
            }
        }
        
        return $expression;
    }
    
    /**
     * Tells whether the given expression is simple enough for
     * the simple add job form to render it.
     * 
     * @param Expression|string $expression
     * @return boolean
     */
    public function isSimpleExpression($expression)
    {        
        if (!$expression instanceof Expression) {
            $expression = Expression::create($expression);
        }
        
        $minute     = $expression->getMinute();
        $hour       = $expression->getHour();
        $dayOfMonth = $expression->getDayOfMonth();
        $month      = $expression->getMonth();
        $dayOfWeek  = $expression->getDayOfWeek();
        
        // Simple expressions feature a specific time (one hour, one minute)
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
            
            // Step is not supported for day of month intervals
            foreach ($dayOfMonth as $value) {
                if (is_array($value) && isset($value['step']) && $value['step'] > 1) {
                    return false;
                }
            }
        }
        
        // "Yearly" repeat option (only one month and one day should be set)
        if ($month) {
            if (count($dayOfMonth) != 1 || is_array($dayOfMonth[0])) {
                return false;
            }
            if (count($month) != 1 || is_array($month[0])) {
                return false;
            }
            if ($dayOfWeek) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Hydrates simple form with expression data.
     * 
     * @param Expression|string $expression
     * @param SimpleForm $form
     * @return ExpressionService
     */
    public function hydrateSimpleForm($expression, SimpleForm $form)
    {
        if (!$expression instanceof Expression) {
            $expression = Expression::create($expression);
        }
        if (!$this->isSimpleExpression($expression)) {
            throw new \InvalidArgumentException(
                'Expression is too complex to be rendered by the simple form');
        }
        
        $hour        = $expression->getHour();
        $minute        = $expression->getMinute();
        $dayOfMonth = $expression->getDayOfMonth();
        $month        = $expression->getMonth();
        $dayOfWeek  = $expression->getDayOfWeek();
        
        $timeFieldset = $form->get('time');
        if ($hour && $minute) {
            if (is_array($hour[0])) {
                // Populate "Every Hour" fieldset
                $timeFieldset->get('picker')->setValue(SimpleForm::EVERY_HOUR);
                $timeFieldset->get(SimpleForm::EVERY_HOUR)
                             ->get('step')->setValue($hour[0]['step']);
                $timeFieldset->get(SimpleForm::EVERY_HOUR)
                             ->get('minute')->setValue($minute[0]);
            } else {
                // Populate "Specific Time" fieldset
                $timeFieldset->get('picker')->setValue(SimpleForm::SPECIFIC_TIME);
                $timeFieldset->get(SimpleForm::SPECIFIC_TIME)
                             ->get('hour')->setValue($hour[0]);
                $timeFieldset->get(SimpleForm::SPECIFIC_TIME)
                             ->get('minute')->setValue($minute[0]);
            }
        } elseif ($minute) {
            if (is_array($minute[0])) {
                // Populate "Every Minute" fieldset
                $timeFieldset->get('picker')->setValue(SimpleForm::EVERY_MINUTE);
                $timeFieldset->get(SimpleForm::EVERY_MINUTE)
                             ->get('step')->setValue($minute[0]['step']);
            } else {
                // Populate "Every Hour" fieldset
                $timeFieldset->get('picker')->setValue(SimpleForm::EVERY_HOUR);
                $timeFieldset->get(SimpleForm::EVERY_HOUR)->get('step')->setValue(1);
                $timeFieldset->get(SimpleForm::EVERY_HOUR)
                             ->get('minute')->setValue($minute[0]);
            }
        } else {
            // Populate "Every Minute" fieldset
            $timeFieldset->get('picker')->setValue(SimpleForm::EVERY_MINUTE);
            $timeFieldset->get(SimpleForm::EVERY_MINUTE)->get('step')->setValue(1);
        }
        
        $repeatFieldset = $form->get('repeat');
        if ($dayOfMonth) {            
            // Populate "Yearly" fieldset
            if ($month) {                
                $repeatFieldset->get('picker')->setValue(SimpleForm::YEARLY);
                $repeatFieldset->get(SimpleForm::YEARLY)
                               ->get('dayOfMonth')->setValue($dayOfMonth[0]);
                $repeatFieldset->get(SimpleForm::YEARLY)
                               ->get('month')->setValue($month[0]);
            
            // Populate "Monthly" fieldset
            } else {
                $dayOfMonth = $this->_expandRanges($dayOfMonth);
                
                $repeatFieldset->get('picker')->setValue(SimpleForm::MONTHLY);
                $repeatFieldset->get(SimpleForm::MONTHLY)
                               ->get('dayOfMonth')->setValue($dayOfMonth);
            }
        }
        
        // Populate "Weekly" fieldset
        if ($dayOfWeek) {
            $dayOfWeek = $this->_expandRanges($dayOfWeek);
            
            $repeatFieldset->get('picker')->setValue(SimpleForm::WEEKLY);
            $repeatFieldset->get(SimpleForm::WEEKLY)->get('dayOfWeek')->setValue($dayOfWeek);
        }
        
        return $this;
    }
    
    /**
     * Goes through the values of an expression part and expands any ranges.
     * Basically, from something like "1-4,7" we'll get "1,2,3,4,7".
     * 
     * @param array $partValues
     * @return array
     */
    protected function _expandRanges(array $partValues)
    {
        $finalValues = array();
        foreach ($partValues as $partValue) {
            // It's a range
            if (is_array($partValue)) {
                /**
                 * $partValue looks like:
                 * array(
                 *   'min'  => (int),
                 *   'max'  => (int),
                 *   'step' => (int)
                 * )
                 * 
                 * @var int $min
                 * @var int $max
                 * @var int $step
                 */
                extract($partValue);
                while ($min <= $max) {
                    $finalValues[] = $min;
                    $min += $step;
                }
            } else {
                $finalValues[] = $partValue;
            }
        }
        
        return $finalValues;
    }
}
