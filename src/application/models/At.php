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

namespace models;
use \Symfony\Component\Process\Process;

/**
 * Models operations with command "at".
 * 
 * @author      Bogdan Ghervan <bogdan.ghervan@gmail.com>
 * @copyright   2014 Bogdan Ghervan
 * @link        http://github.com/cronkeep/cronkeep
 * @license     http://opensource.org/licenses/Apache-2.0 Apache License 2.0
 * @see         http://en.wikipedia.org/wiki/At_%28Unix%29
 */
class At
{
    /**
     * Possible errors printed by "at".
     */
    const ERROR_ACCESS_DENIED = 'You do not have permission to use at.';
    const ERROR_GARBLED_TIME  = 'Garbled time';
    
    /**
     * Command is available and can be used.
     * 
     * @var bool
     */
    protected static $_isAvailable;
    
    /**
     * Last error outputted from the command.
     * 
     * @var string
     */
    protected static $_errorOutput;
    
    /**
     * Tells whether the "at" command is available and ready to use.
     * 
     * A common error is "You do not have permission to use at." which means
     * the web server's user is not allowed access.
     * 
     * The command is available if it returns "Garbled time" when called
     * without arguments. That's actually a standard error triggered
     * if no switch or time setting is given.
     * 
     * @return bool
     */
    public static function isAvailable()
    {
        if (self::$_isAvailable === null) {
            $process = new Process('at');
            $process->run();
            
            self::$_errorOutput = trim($process->getErrorOutput());
            
            // Receiving "Garbled time" is a good sign - it means the command
            // is available (even though the input was not right)
            self::$_isAvailable = trim(self::$_errorOutput) == self::ERROR_GARBLED_TIME;
        }
        
        return self::$_isAvailable;
    }
    
    /**
     * Returns last error output.
     * 
     * @return string
     */
    public static function getErrorOutput()
    {
        return self::$_errorOutput;
    }
}
