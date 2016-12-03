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
use models\Crontab\Job;
use RuntimeException;
use models\Crontab\Exception\UserNotAllowedException;
use models\Crontab\Exception\SpoolUnreachableException;
use models\Crontab\Exception\PamUnreadableException;

/**
 * Crontab model.
 * 
 * @author      Bogdan Ghervan <bogdan.ghervan@gmail.com>
 * @copyright   2014 Bogdan Ghervan
 * @link        http://github.com/cronkeep/cronkeep
 * @license     http://opensource.org/licenses/Apache-2.0 Apache License 2.0
 * @see         man 5 crontab for details
 */
class Crontab implements \IteratorAggregate, \Countable
{
    /**
     * Possible errors printed by "crontab".
     */
    const ERROR_EMPTY = "/no crontab for .+/";
    const ERROR_USER_NOT_ALLOWED = "/You \([\w_.-]+\) are not allowed to use this program \(crontab\)/";
    const ERROR_SPOOL_UNREACHABLE = "/'\/var\/spool\/cron' is not a directory, bailing out/";
    const ERROR_PAM_UNREADABLE = "/You \([\w_.-]+\) are not allowed to access to \(crontab\) because of pam configuration/";
    
    /**
     * Raw cron table.
     * 
     * @var string
     */
    protected $_rawTable;
    
    /**
     * Loaded list of cron jobs.
     * 
     * @var array
     */
    protected $_jobs = array();
    
    /**
     * Default line separator.
     * 
     * @var string
     */
    protected $_lineSeparator = PHP_EOL;
    
    /**
     * Class constructor.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->_load();
    }
    
    /**
     * Finds job by hash. Returns null if the job couldn't be found.
     *
     * @param string $hash 
     * @return Crontab\Job|null
     */
    public function findByHash($hash)
    {
        foreach ($this->_jobs as $job) {
            /* @var $job Crontab\Job */
            if ($job->getHash() == $hash) {
                return $job;
            }
        }
        
        return null;
    }
    
    /**
     * Runs the job in background.
     * @see http://symfony.com/doc/current/components/process.html
     * 
     * @param Job $job
     * @return Crontab
     */
    public function run(Job $job)
    {
        $command = $job->getCommand();
        if (At::isAvailable()) {
            $command = sprintf('sh -c "echo "%s" | at now"', $job->getCommand());
        }
        
        $process = new Process($command);
        $process->start();
        
        return $this;
    }
    
    /**
     * Adds given cron job to crontab.
     * Additionally, {@link Crontab::save} should be called to persist the change.
     * 
     * @param Job $job
     * @return Crontab
     */
    public function add(Job $job)
    {
        // Trim any trailing newlines at the end of the raw cron table
        $this->_rawTable = rtrim($this->_rawTable, "\r\n");
        
        $this->_rawTable .= $this->_lineSeparator . $job->getRaw();
        
        return $this;
    }
    
    /**
     * Updates original job definition in the raw crontab with changed data.
     * 
     * @param Job $job
     * @return Crontab
     */
    public function update(Job $job)
    {
        $originalJob = $job->getOriginalRaw();
        $newJob = $job->getRaw();
        
        // Replace the job definition in the raw crontab
        $this->_rawTable = str_replace($originalJob, $newJob, $this->_rawTable);
        
        return $this;
    }
    
    /**
     * Pauses cron schedule by commenting the job in crontab.
     * Additionally, {@link Crontab::save} should be called to persist the change.
     * 
     * @param Job $job
     * @return Crontab
     */
    public function pause(Job $job)
    {
        // Comment the cron job
        $job->setIsPaused(true);
        $this->update($job);
        
        return $this;
    }
    
    /**
     * Resumes cron schedule by un-commenting the job in crontab.
     * Additionally, {@link Crontab::save} should be called to persist the change.
     * 
     * @param Job $job
     * @return Crontab
     */
    public function resume(Job $job)
    {
        // Uncomment the cron job
        $job->setIsPaused(false);
        $this->update($job);
        
        return $this;
    }
    
    /**
     * Deletes given job from crontab.
     * Additionally, {@link Crontab::save} should be called to persist the change.
     * 
     * @param Job $job
     * @return Crontab
     */
    public function delete(Job $job)
    {
        $this->_rawTable = str_replace($job->getOriginalRaw(), '', $this->_rawTable);
        
        return $this;
    }
    
    /**
     * Saves the current user's crontab.
     * 
     * @return Crontab
     * @throws RuntimeException
     */
    public function save()
    {
        $process = new Process('crontab');
        $process->setInput($this->_rawTable);
        $process->run();
        
        if (!$process->isSuccessful()) {
            $errorOutput = $process->getErrorOutput();
            if ($errorOutput) {
                throw new RuntimeException(sprintf(
                    'There has been an error saving the crontab. Here\'s the output from the shell: %s',
                    trim($errorOutput)));
            }
            throw new RuntimeException('There has been an error saving the crontab.');
        }
        
        return $this;
    }
    
    /**
     * Loads system crontab.
     * 
     * @return Crontab
     */
    protected function _load()
    {
        $this->_readCrontab();
        $this->_parseCrontab();
        
        return $this;
    }
    
    /**
     * Reads crontab for current user.
     * 
     * @return Crontab
     * @throws SpoolUnreachableException
     * @throws
     */
    protected function _readCrontab()
    {
        $process = new Process('crontab -l');
        $process->run();
        
        if (!$process->isSuccessful()) {
            $errorOutput = $process->getErrorOutput();
            $this->_handleReadError($errorOutput);
        }
        
        $this->_rawTable = $process->getOutput();
        
        return $this;
    }
    
    /**
     * Parses a crontab with commentaries. It assumes the comment for a cron job
     * comes before the job definition.
     * 
     * @return Crontab
     */
    protected function _parseCrontab()
    {
        $pattern = "/
(?(DEFINE)

    # Subroutine matching the (optional) comment sign preceding a cron definition
    (?<_commentSign>(?:\#[ \t]*))
    
    # Subroutine matching the time expression
    (?<_expression>
        
        # either match expression
        (?:
            (?:
                [\d\*\/\,\-\%]+                 # minute part
            )
            [ \t]+                              # space
            (?:
                [\d\*\/\,\-\%]+                 # hour part
            )
            [ \t]+                              # space
            (?:
                [\d\*\/\,\-\%]+                 # day of month part
            )
            [ \t]+                              # space
            (?:
                [\d\*\/\,\-\%]+|jan|feb|        # month part
                mar|apr|may|jun|jul|aug|
                sep|oct|nov|dec
            )
            [ \t]+                              # space
            (?:
                [\d\*\/\,\-\%]+|mon|tue|        # day of week part
                wed|thu|fri|sat|sun
            )
        )
        
        # or match specials
        | (?:
            @hourly|@midnight|@daily|
            @weekly|@monthly|@yearly|
            @annually|@reboot
        )
    )
    
    # Subroutine matching the command part (everything except line ending)
    (?<_command>[^\r\n]*)

    # Subroutine matching full cron definition (time + command)
    (?<_cronDefinition>
        ^(?&_commentSign)?                      # comment sign (optional)
        (?&_expression)                         # time expression part
        \s+                                     # space
        (?&_command)                            # command part
    )
    
    # Subroutine matching comment (which is above cron, by convention)
    (?<_comment>[^\r\n]*)
)

# Here's where the actual matching happens.
# Subroutine calls are wrapped by named capture groups so we could
# easily reference the captured subpatterns later.

# A comment isn't allowed to look like a cron definition, or otherwise
# commented crons could pass as comments for neighboring crons
^(?(?!(?&_cronDefinition))                      # conditional: not a cron definition
    (?:
        (?&_commentSign)                        # comment sign preceding comment
        (?P<comment>(?&_comment))               # comment
        \s*                                     # trailing whitespace, if any
        [\r\n]{1,}                              # line endings
    )?                                          # comment is, however, optional
)
(?P<cronCommentSign>^(?&_commentSign)?)         # comment sign for 'paused' crons (optional)
(?P<expression>(?&_expression))                 # time expression
\s+                                             # space
(?P<command>(?&_command))                       # command to be run (everything, but line ending)
\s*[\r\n]{1,}                                   # trailing space and line ending

        /imx";

        $this->_jobs = array();
        if (preg_match_all($pattern, $this->_rawTable, $lines, PREG_SET_ORDER)) {
            foreach ($lines as $lineParts) {
                $job = new Job();
                $job->setRaw($lineParts[0]);
                $job->setComment(empty($lineParts['comment']) ? null : $lineParts['comment']);
                $job->setIsPaused($lineParts['cronCommentSign'] != '');
                $job->setExpression($lineParts['expression']);
                $job->setCommand($lineParts['command']);
                
                $this->_jobs[] = $job;
            }
        }
        
        return $this;
    }
    
    /**
     * Handles errors encountered while attempting to read the crontab.
     * 
     * @param string $errorOutput
     * @return Crontab
     * @throws RuntimeException if either error output is empty or error
     *     is not recognized by CronKeep
     * @throws UserNotAllowedException usually when the web server's user
     *     is denied access by means of /etc/cron.deny
     * @throws SpoolUnreachableException if crontab is denied access to
     *     /var/spool/cron usually in a SELinux-enabled environment
     * @throws PamUnreadableException if the web server is denied access
     *     to read PAM configuration file
     */
    protected function _handleReadError($errorOutput)
    {
        $errorOutput = trim($errorOutput);
        
        // Unknown error condition
        if (empty($errorOutput)) {
            throw new RuntimeException('There has been an error reading the crontab.');
        }
        
        // Do nothing if no cron jobs exist (not an error)
        if (preg_match(self::ERROR_EMPTY, $errorOutput)) {
            return $this;
        }

        if (preg_match(self::ERROR_USER_NOT_ALLOWED, $errorOutput)) {
            throw new UserNotAllowedException($errorOutput);
        }
        
        if (preg_match(self::ERROR_SPOOL_UNREACHABLE, $errorOutput)) {
            throw new SpoolUnreachableException($errorOutput);
        }
        
        if (preg_match(self::ERROR_PAM_UNREADABLE, $errorOutput)) {
            throw new PamUnreadableException($errorOutput);
        }
        
        // Unrecognized error condition
        throw new RuntimeException(sprintf(
            'There has been an error reading the crontab. Here\'s the output from the shell: %s',
            $errorOutput));
    }
    
    /**
     * Implementation of Countable::count.
     *
     * @return int
     */
    public function count()
    {
        return count($this->_jobs);
    }
    
    /**
     * Implementation of IteratorAggregate::getIterator.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_jobs);
    }
}
