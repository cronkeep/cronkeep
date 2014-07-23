<?php
namespace models;

class Crontab implements \IteratorAggregate
{
	protected $_raw;
	
	/**
	 * Cron jobs.
	 * 
	 * @var array
	 */
	protected $_jobs;
	
	/**
	 * Loads system crontab.
	 * 
	 * @return \models\Crontab
	 */
	public function load()
	{
		$this->_raw = `crontab -l`;
		$this->_parseRawCrontab($this->_raw);
		
		return $this;
	}
	
	/**
	 * Parses a crontab with commentaries. It assumes the comment for a cron job
	 * comes before the job definition.
	 * 
	 * @param type $rawCrontab
	 * @return \models\Crontab
	 */
	protected function _parseRawCrontab($rawCrontab)
	{
		$pattern = '/(?:(\#.*)\s)?((?:[\d\*\/\,\-\%]+\s?){5})\s(.*)/im';
		
		if (preg_match_all($pattern, $rawCrontab, $lines, PREG_SET_ORDER)) {
			foreach ($lines as $lineParts) {
				$job = new Crontab\Job();
				$job->setComment(empty($lineParts[1]) ? null : $lineParts[1]);
				$job->setExpression($lineParts[2]);
				$job->setCommand($lineParts[3]);
				
				$this->_jobs[] = $job;
			}
		}
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