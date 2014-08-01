<?php
namespace models;
use \Symfony\Component\Process\Process;

/**
 * Crontab model.
 * 
 * @author Bogdan Ghervan <bogdan.ghervan@gmail.com>
 * @see man 5 crontab for details
 */
class Crontab implements \IteratorAggregate
{
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
	 * 
	 * @param \models\Crontab\Job $job
	 * @return \models\Crontab
	 */
	public function run(Crontab\Job $job)
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
	 * Pauses cron schedule by commenting the job in crontab.
	 * Additionally, Crontab::save should be called to persist the change.
	 * 
	 * @param \models\Crontab\Job $job
	 * @return \models\Crontab
	 */
	public function pause(Crontab\Job $job)
	{
		// Comment the cron job
		$originalJob = $job->getRaw();
		$job->pause();
		$newJob = $job->getRaw();
		
		// Replace the job definition in the raw crontab
		$this->_rawTable = str_replace($originalJob, $newJob, $this->_rawTable);
		
		return $this;
	}
	
	/**
	 * Resumes cron schedule by un-commenting the job in crontab.
	 * Additionally, Crontab::save should be called to persist the change.
	 * 
	 * @param \models\Crontab\Job $job
	 * @return \models\Crontab
	 */
	public function resume(Crontab\Job $job)
	{
		// Comment the cron job
		$originalJob = $job->getRaw();
		$job->resume();
		$newJob = $job->getRaw();
		
		// Replace the job definition in the raw crontab
		$this->_rawTable = str_replace($originalJob, $newJob, $this->_rawTable);
		
		return $this;
	}
	
	/**
	 * Deletes given job from crontab.
	 * Additionally, Crontab::save should be called to persist the change.
	 * 
	 * @param \models\Crontab\Job $job
	 * @return \models\Crontab
	 */
	public function delete(Crontab\Job $job)
	{
		$this->_rawTable = str_replace($job->getRaw(), '', $this->_rawTable);
		
		return $this;
	}
	
	/**
	 * Saves the current user's crontab.
	 * 
	 * @return \models\Crontab
	 * @throws \RuntimeException
	 */
	public function save()
	{
		$process = new Process('crontab');
		$process->setInput($this->_rawTable);
		$process->run();
		
		if (!$process->isSuccessful()) {
			$errorOutput = $process->getErrorOutput();
			if ($errorOutput) {
				throw new \RuntimeException(sprintf(
					'There has been an error saving the crontab. Here\'s the output from the shell: %s',
					trim($errorOutput)));
			}
			throw new \RuntimeException('There has been an error saving the crontab.');
		}
		
		return $this;
	}
	
	/**
	 * Loads system crontab.
	 * 
	 * @return \models\Crontab
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
	 * @return \models\Crontab
	 */
	protected function _readCrontab()
	{
		$process = new Process('crontab -l');
		$process->run();
		
		if (!$process->isSuccessful()) {
			$errorOutput = $process->getErrorOutput();
			if ($errorOutput) {
				throw new \RuntimeException(sprintf(
					'There has been an error reading the crontab. Here\'s the output from the shell: %s',
					trim($errorOutput)));
			}
			throw new \RuntimeException('There has been an error reading the crontab.');
		}
		
		$this->_rawTable = trim($process->getOutput());
		
		return $this;
	}
	
	/**
	 * Parses a crontab with commentaries. It assumes the comment for a cron job
	 * comes before the job definition.
	 * 
	 * Indentation inspired by:
	 * http://www.onlamp.com/lpt/a/4101
	 * 
	 * @return \models\Crontab
	 */
	protected function _parseCrontab()
	{
        $pattern = "/
            (?:
              (\#[^\r]*?)                     # match comment above cron
			  \s*                             # match any trailing whitespace
              [\n\r]{1,}                      # match any sort of line endings
            )?                                # comment is, however, optional
            (                                 # start command line
			  ^(?:\#\s*)?                     # line starting with a comment sign or not
              (                               # start to match time expression
                (?:
                  [\d\*\/\,\-\%]+             # match minute
                )
                \s                            # space
                (?:
                  [\d\*\/\,\-\%]+             # match hour
                )
                \s                            # space
                (?:
                  [\d\*\/\,\-\%]+             # match day of month
                )
                \s                            # space
                (?:
                  [\d\*\/\,\-\%]+|jan|feb|    # match month
                  mar|apr|may|jun|jul|aug|
                  sep|oct|nov|dec
                )
                \s                            # space
                (?:
                  [\d\*\/\,\-\%]+|mon|tue|    # match day of week
                  wed|thu|fri|sat|sun
                )
                |
                (?:
                  @hourly|@midnight|@daily|   # OR, match special string
                  @weekly|@monthly|@yearly|
                  @annually|@reboot)
              )                               # end matching time expression
              \s+                             # space
              ([^\r]*?)                       # command to be run (everything, but CR)
            )                                 # end command line
			\s*[\n\r]{1}                      # match trailing space and final line ending
          /imx";
		
		$this->_jobs = array();
		if (preg_match_all($pattern, $this->_rawTable, $lines, PREG_SET_ORDER)) {
			foreach ($lines as $lineParts) {
				$job = new Crontab\Job();
				$job->setRaw($lineParts[0]);
				$job->setComment(empty($lineParts[1]) ? null : $lineParts[1]);
				$job->setCommandLine($lineParts[2]);
				$job->setExpression($lineParts[3]);
				$job->setCommand($lineParts[4]);
				
				$this->_jobs[] = $job;
			}
		}
		
		return $this;
	}
	
    /**
     * Implementation of IteratorAggregate:getIterator
     *
	 * @return \ArrayIterator
     */
	public function getIterator()
	{
		return new \ArrayIterator($this->_jobs);
	}
}