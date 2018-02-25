<?php

class FibSeries
{

	private $index = null;

	public function __construct($_index)
	{
		$this->index = $_index;
	}


	private function recursive_culc($cur)
	{
		if($cur == 0)
		{
			return 0;
		}
		if($cur == 1)
		{
			return 1;
		}
		return $this->recursive_culc($cur - 1) + $this->recursive_culc($cur - 2);
	}

	private function linear_culc()
	{
		if($this->index == 0)
		{
			return 0;
		}
		if($this->index == 1)
		{
			return 1;
		}
		$previous = 0;
		$last = 1;
		for ($i=2; $i <= $this->index; $i++) 
		{ 
			$tmp = $last;
			$last = $tmp + $previous;
			$previous = $tmp;
		}
		return $last;
	}

	function microtime_float()
	{
    	list($usec, $sec) = explode(" ", microtime());
    	return ((float)$usec + (float)$sec);
	}

	public function culc($algo_name = "")
	{
		if(empty($algo_name))
		{
			echo "algo_name can't be empty or null\n";
			return;
		}
		$time_start = $this->microtime_float();
		echo $algo_name." answer: ".$this->$algo_name($this->index)."\n";
		$time_end = $this->microtime_float();
		$time = $time_end - $time_start;
		echo $algo_name." time is: ".$time."\n";
	}
}

$myfib = new FibSeries(40);
$myfib->culc("recursive_culc");
$myfib->culc("linear_culc");


