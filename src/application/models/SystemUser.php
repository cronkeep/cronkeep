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
 * SystemUser model.
 * 
 * @author      Bogdan Ghervan <bogdan.ghervan@gmail.com>
 * @copyright   2014 Bogdan Ghervan
 * @link        http://github.com/cronkeep/cronkeep
 * @license     http://opensource.org/licenses/Apache-2.0 Apache License 2.0
 */
class SystemUser
{
    /**
     * User the web server is running as.
     * 
     * @var string
     */
    protected $_username;
    
    /**
     * Retrieves the system user the web server is running as.
     * 
     * @return string
     * @throws \RuntimeException
     */
    public function getUsername()
    {
        if (!$this->_username) {
            $process = new Process('whoami');
            $process->run();
            if (!$process->isSuccessful()) {
                throw new \RuntimeException(sprintf('Unable to detect current user: %s',
                    trim($process->getErrorOutput())));
            }
            
            $this->_username = trim($process->getOutput());
        }
        
        return $this->_username;
    }
}
