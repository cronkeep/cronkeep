<?php
namespace models;

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
	 * @var type 
	 */
	protected $_raw;
	
	/**
	 * Loaded list of cron jobs.
	 * 
	 * @var array
	 */
	protected $_jobs;
	
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
	 * Loads system crontab.
	 * 
	 * @return \models\Crontab
	 */
	protected function _load()
	{
		$this->_raw = `crontab -l`;
		$this->_parseRawCrontab($this->_raw);
		
		return $this;
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
	 * Parses a crontab with commentaries. It assumes the comment for a cron job
	 * comes before the job definition.
	 * 
	 * Indentation inspired by:
	 * http://www.onlamp.com/lpt/a/4101
	 * 
	 * @param type $rawCrontab
	 * @return \models\Crontab
	 */
	protected function _parseRawCrontab($rawCrontab)
	{
		$pattern = $pattern = "/
			(?:
			  (\#[^\r]*)                    # match comment above cron (optional)
			  [\s\r]*						# any sort of whitespace (including CR)
			)?
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
			([^\r]*)                        # command to be run (everything, but CR)
		/imx";
		
		if (preg_match_all($pattern, $rawCrontab, $lines, PREG_SET_ORDER)) {
			foreach ($lines as $lineParts) {
				$job = new Crontab\Job();
				$job->setRaw($lineParts[0]);
				$job->setComment(empty($lineParts[1]) ? null : $lineParts[1]);
				$job->setExpression($lineParts[2]);
				$job->setCommand($lineParts[3]);
				
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