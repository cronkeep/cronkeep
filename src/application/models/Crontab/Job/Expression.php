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

namespace models\Crontab\Job;

/**
 * Expression model.
 * 
 * @author      Bogdan Ghervan <bogdan.ghervan@gmail.com>
 * @copyright   2014 Bogdan Ghervan
 * @link        http://github.com/cronkeep/cronkeep
 * @license     http://opensource.org/licenses/Apache-2.0 Apache License 2.0
 * 
 * @method Expression setMinute(int|string|array $minute)
 * @method Expression addMinute(int|string|array $minute)
 * @method array getMinute()
 * @method Expression setHour(int|string|array $hour)
 * @method Expression addHour(int|string|array $hour)
 * @method array getHour()
 * @method Expression setDayOfMonth(int|string|array $dayOfMonth)
 * @method Expression addDayOfMonth(int|string|array $dayOfMonth)
 * @method array getDayOfMonth()
 * @method Expression setMonth(int|string|array $month)
 * @method Expression addMonth(int|string|array $month)
 * @method array getMonth()
 * @method Expression setDayOfWeek(int|string|array $dayOfWeek)
 * @method Expression addDayOfWeek(int|string|array $dayOfWeek)
 * @method array getDayOfWeek()
 */
class Expression
{
    const MINUTE       = 'minute';
    const HOUR         = 'hour';
    const DAY_OF_MONTH = 'dayOfMonth';
    const MONTH        = 'month';
    const DAY_OF_WEEK  = 'dayOfWeek';
    
    /**
     * The component parts of a cron expression.
     * 
     * @var array
     */
    protected $_parts = array(
        self::MINUTE       => array(),
        self::HOUR         => array(),
        self::DAY_OF_MONTH => array(),
        self::MONTH        => array(),
        self::DAY_OF_WEEK  => array()
    );
    
    /**
     * Minimum and maximum allowed value for every part of a cron expression.
     * 
     * @var array
     */
    protected $_bounds = array(
        self::MINUTE       => array('min' => 0, 'max' => 59),
        self::HOUR         => array('min' => 0, 'max' => 23),
        self::DAY_OF_MONTH => array('min' => 0, 'max' => 31),
        self::MONTH        => array('min' => 1, 'max' => 12),
        self::DAY_OF_WEEK  => array('min' => 0, 'max' => 7)
    );
    
    /**
     * Special time specification "nicknames".
     * @todo Support "@reboot".
     * 
     * @var array
     */
    protected static $_shorthands = array(
        '@yearly'   => '0 0 1 1 *',
        '@annually' => '0 0 1 1 *',
        '@monthly'  => '0 0 1 * *',
        '@weekly'   => '0 0 * * 0',
        '@daily'    => '0 0 * * *',
        '@hourly'   => '0 * * * *'
    );
    
    /**
     * Supported synonyms used by normalization and expression parsing.
     * 
     * @var array
     */
    protected static $_synonyms = array(
        self::MONTH => array(
            'jan' => 1,
            'feb' => 2,
            'mar' => 3,
            'apr' => 4,
            'may' => 5,
            'jun' => 6,
            'jul' => 7,
            'aug' => 8,
            'sep' => 9,
            'oct' => 10,
            'nov' => 11,
            'dec' => 12
        ),
        self::DAY_OF_WEEK => array(
            7 => 0,
            'sun' => 0,
            'mon' => 1,
            'tue' => 2,
            'wed' => 3,
            'thu' => 4,
            'fri' => 5,
            'sat' => 6
        )
    );
    
    /**
     * Parses expression given in literal form, builds an Expression object and returns it.
     * 
     * @param string $string
     * @return Expression
     * @throws \InvalidArgumentException
     */
    public static function create($string)
    {
        $months = implode('|', array_keys(self::$_synonyms[self::MONTH]));
        $daysOfWeek = implode('|', array_keys(self::$_synonyms[self::DAY_OF_WEEK]));
        $shorthands = implode('|', array_keys(self::$_shorthands));
        
        $pattern = "/
            (?:^
                (?P<minute>[\d\*\/\,\-\%]+)
                \s
                (?P<hour>[\d\*\/\,\-\%]+)
                \s
                (?P<dayOfMonth>[\d\*\/\,\-\%]+)
                \s
                (?P<month>[\d\*\/\,\-\%]+|$months)
                \s
                (?P<dayOfWeek>[\d\*\/\,\-\%]+|$daysOfWeek)
            $) | (?:^
                (?P<shorthand>$shorthands)
            $)
        $/ix";
        
        if (preg_match($pattern, $string, $matches)) {
            if (isset($matches['shorthand']) && $matches['shorthand']) {
                return self::create(self::$_shorthands[$matches['shorthand']]);
            }
            
            $expression = new Expression();
            foreach (array('minute', 'hour', 'dayOfMonth', 'month', 'dayOfWeek') as $part) {
                // Expand lists
                $matches[$part] = explode(',', $matches[$part]);
                
                // Separate range and step
                foreach ($matches[$part] as $timeUnit) {
                    $shards = explode('/', $timeUnit);
                    
                    // Do we have a range (or asterisk) *and* a step?
                    if (count($shards) > 1) {
                        // Do we have an asterisk?
                        if ($shards[0] == '*') {
                            $expression->addPart($part, array(
                                'scalar' => '*',
                                'step'   => $shards[1]
                            ));
                            
                        // OK, clearly we have a range
                        } else {
                            $rangeLimits = explode('-', $shards[0]);
                            $expression->addPart($part, array(
                                'min'  => $rangeLimits[0],
                                'max'  => $rangeLimits[1],
                                'step' => $shards[1]
                            ));
                        }
                    
                    // OK, we have just a range
                    } else {
                        $rangeLimits = explode('-', $shards[0]);
                        
                        // Do we have a range *or* a number?
                        if (count($rangeLimits) > 1) {
                            $expression->addPart($part, array(
                                'min'  => $rangeLimits[0],
                                'max'  => $rangeLimits[1]
                            ));
                        
                        // OK, we have just a number
                        } else {
                            $expression->addPart($part, $rangeLimits[0]);
                        }
                    }
                }
            }
            
            return $expression;
        }
        
        throw new \InvalidArgumentException(sprintf('Expression "%s" is not supported', $string));
    }
    
    /**
     * Appends $value to given $part.
     * 
     * Example 1:
     * <code>
     * $expression = new Expression();
     * $expression->addPart(Expression::MINUTE, array('min' =>  0, 'max' => 29, 'step' => 5));
     * $expression->addPart(Expression::MINUTE, array('min' => 30, 'max' => 59, 'step' => 10));
     * $expression->addPart(Expression::MINUTE, 7);
     * $expression->addPart(Expression::MINUTE, array(0, 15, 30, 45));
     * </code>
     * Resulting expression: 0-29/5,30-59/10,7,0,15,30,45 * * * *
     * 
     * Example 2:
     * <code>
     * $expression->addPart(Expression::MONTH, array('scalar' => 3));
     * </code>
     * This is, however, the equivalent of:
     * <code>
     * $expression->addPart(Expression::MONTH, 3);
     * </code>
     * It's recommended that the latter form be used for improved readibility.
     * See Example 3 for a case where scalar is essential.
     * 
     * Example 3:
     * <code>
     * $expression->addPart(Expression::HOUR, array('scalar' => '*', 'step' => 2));
     * </code>
     * Here a step is used in conjuction with an asterisk to say "every 2 hours".
     * 
     * @param string $part
     * @param int|string|array $value
     * @return \models\Crontab\Job\Expression
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     */
    public function addPart($part, $value)
    {
        if (!array_key_exists($part, $this->_parts)) {
            throw new \OutOfBoundsException(__METHOD__ . ' called with an invalid part: ' . $part);
        }
        
        // Value is a number
        if (is_numeric($value)) {
            $value = $this->_normalize($part, $value);
            if ($value < $this->_bounds[$part]['min'] || $value > $this->_bounds[$part]['max']) {
                throw new \InvalidArgumentException('Value is outside the valid range for ' . $part);
            }
            
            $this->_parts[$part][] = (int) $value;
        
        // Value is literal
        } elseif (is_string($value)) {
            if ($value == '*') {
                $this->_parts[$part] = array();
            } else {
                $value = $this->_normalize($part, $value);
                $this->_parts[$part][] = $value;
            }
        
        // Value is a list or a range
        } elseif (is_array($value)) {
            // Value is a range
            if (isset($value['min']) && isset($value['max'])) {
                if (!is_numeric($value['min'])) {
                    throw new \InvalidArgumentException(__METHOD__ . ' called with an invalid value for min');
                }
                if ($value['min'] < $this->_bounds[$part]['min']
                        || $value['min'] > $this->_bounds[$part]['max']) {
                    throw new \InvalidArgumentException('Value is outside the valid range for ' . $part);
                }
                if (!is_numeric($value['max'])) {
                    throw new \InvalidArgumentException(__METHOD__ . ' called with an invalid value for max');
                }
                if ($value['max'] < $this->_bounds[$part]['min']
                        || $value['max'] > $this->_bounds[$part]['max']) {
                    throw new \InvalidArgumentException('Value is outside the valid range for ' . $part);
                }
                if (isset($value['step']) && !is_numeric($value['step'])) {
                    throw new \InvalidArgumentException(__METHOD__ . ' called with an invalid value for step');
                }
                
                $this->_parts[$part][] = array(
                    'min'  => (int) $value['min'],
                    'max'  => (int) $value['max'],
                    'step' => isset($value['step']) ? (int) $value['step'] : 1
                );
            
            // Scalar value "*" (possibly accompanied by a step) *or* a plain number?
            } elseif (isset($value['scalar'])) {
                if (isset($value['step'])) {
                    if (is_numeric($value['scalar'])) {
                        throw new \InvalidArgumentException(
                            'Illegal use of a step in conjunction with a number');
                    }
                    if (!is_numeric($value['step'])) {
                        throw new \InvalidArgumentException(
                            __METHOD__ . ' called with an invalid value for step');
                    }
                }
                $step = isset($value['step']) ? (int) $value['step'] : 1;
                
                if ($value['scalar'] !== '*' || $step <= 1) {
                    // We'll rather store it as a plain number / asterisk instead
                    // Also, steps don't make sense for plain numbers 
                    return $this->addPart($part, $value['scalar']);
                }
                
                $this->_parts[$part][] = array(
                    'scalar' => '*',
                    'step'   => $step
                );
                
            // Value is a list
            } else {
                foreach ($value as $item) {
                    $this->addPart($part, $item);
                }
            }
        } else {
            throw new \InvalidArgumentException(__METHOD__ . ' called with an invalid value');
        }
        
        return $this;
    }
    
    /**
     * Sets $value for $part. Any previous value of $part is lost.
     * 
     * Example:
     * <code>
     * $expression = new Expression();
     * $expression->addPart(Expression::HOUR, array(9, 12, 15));
     * </code>
     * Resulting expression thus far: * 9,12,15 * * *
     * 
     * <code>
     * $expression->setPart(Expression::HOUR, array(20, 22));
     * </code>
     * Resulting expression thus far: * 20,22 * * *
     * 
     * @param string $part
     * @param int|string|array $value
     * @return Expression
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     */
    public function setPart($part, $value)
    {
        // Reset part
        $this->_parts[$part] = array();
        
        // Add part as usual
        $this->addPart($part, $value);
        
        return $this;
    }
    
    /**
     * Renders expression.
     * 
     * @return string
     */
    public function render()
    {
        $expr = array();
        foreach (array_keys($this->_parts) as $part) {
            $expr[] = $this->_renderPart($part);
        }
        
        return implode(' ', $expr);
    }
    
    /**
     * Overloads method access.
     * 
     * Allows the following method calls:
     * - setMinute($minute)
     * - addMinute($minute)
     * - getMinute()
     * - setHour($hour)
     * - addHour($hour)
     * - getHour()
     * - setDayOfMonth($dayOfMonth)
     * - addDayOfMonth($dayOfMonth)
     * - getDayOfMonth()
     * - setMonth($month)
     * - addMonth($month)
     * - getMonth()
     * - setDayOfWeek($dayOfWeek)
     * - addDayOfWeek($dayOfWeek)
     * - getDayOfWeek()
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call($method, $args)
    {
        $partNames = implode('|', array_keys($this->_parts));
        if (preg_match("/^(?P<action>set|get|add)(?P<part>$partNames)$/i", $method, $matches)) {
            extract($matches);
            $part = lcfirst($part);
            
            switch ($action) {
                case 'get': {
                    return $this->_parts[$part];
                }
                case 'set': {
                    if (!$args) {
                        throw new \BadMethodCallException('Method ' . $method . ' called without a value');
                    }
                    return $this->setPart($part, $args[0]);
                }
                case 'add': {
                    if (!$args) {
                        throw new \BadMethodCallException('Method ' . $method . ' called without a value');
                    }
                    return $this->addPart($part, $args[0]);
                }
            }
        } else {
            throw new \BadMethodCallException('Call to undefined method ' . $method);
        }
    }
    
    /**
     * Returns string representation of this object.
     * This is an alias for @see Expression::render.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
    
    /**
     * Normalizes given part value based on a dictionary of alternate values
     * for that part.
     * 
     * @param string $part
     * @param int|string $value
     * @return int|string
     */
    protected function _normalize($part, $value)
    {
        // Working only with int and string values
        if (is_scalar($value)) {
            if (is_string($value)) {
                $value = strtolower($value);
            }

            if (isset(self::$_synonyms[$part][$value])) {
                return self::$_synonyms[$part][$value];
            }
        }
        
        return $value;
    }
    
    /**
     * Renders given part.
     * It basically iterates over a list of numbers, or ranges, or both,
     * that have been stored for this part.
     * 
     * Ranges are stored as arrays, but so are scalar constructions
     * (always an asterisk followed by a step - asterisks without a step
     * are not stored as an array).
     * 
     * Here's how a range looks like:
     * <code>
     * array('min' => ..., 'max' => ...[, 'step' => ...])
     * </code>
     * Here's how an asterisk with a step looks like:
     * <code>
     * array('scalar' => '*', 'step' => ...)
     * </code>
     * 
     * @param string $part
     * @return string
     */
    protected function _renderPart($part)
    {
        $expr = array();
        
        // We're a iterating over a list of numbers, or ranges, or both
        foreach ($this->_parts[$part] as $value) {
            if (is_array($value)) {
                $step = $value['step'] > 1 ? '/' . $value['step'] : '';
                
                // Is it a range?
                if (isset($value['min'])) {
                    $expr[] = sprintf('%s-%s%s', $value['min'], $value['max'], $step);
                
                // OK, it's an asterisk with a step
                } else {
                    $expr[] = '*' . $step;
                }
            } else {
                // A regular number or a regular asterisk (no step)
                $expr[] = $value;
            }
        }
        
        return $expr ? implode(',', $expr) : '*';
    }
}
