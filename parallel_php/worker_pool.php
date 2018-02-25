<?php


class Shared
{
	private $shared_data = [];

	public function add_data($data)
	{
		//echo "adding: ".$data."\n";
		$this->shared_data[] = $data;
		//print_r($this->shared_data);
	}

	public function get_data()
	{
		return $this->shared_data;
	}
}

class Task extends Threaded
{
    private $value;
    private $shared = null;
    protected $complete;

    public function __construct(int $i, $shared)
    {
    	$this->shared = $shared;
    	$this->complete = false;
        $this->value = $i;
    }

    public function run()
    {
    	$this->shared->add_data($this->value);
        usleep(250000);
        echo "Task: {$this->value}\n";
        $this->complete = true;
    }

    public function get_value()
    {
    	return $this->value;
    }

    //public function isGarbage() 
    //{
    //    return $this->complete;
    //}
}

class MyPool
{
	private $threads_num;
	private $pool = null;
	private $main_arr = [];

	public function __construct($i = 0,$islazy = 0)
    {
    	echo "islazy: ".$islazy."\n";
    	echo "threads_num: ".$i."\n";
        $this->threads_num = $i;
        if(!$islazy)
        {
        	$this->init_pool();
        }
    }

    public function get_data()
    {
    	return $this->main_arr;
    }

    public function init_pool()
    {
    	echo "initializing pool\n";
    	$this->pool = new Pool($this->threads_num);
    }

    public function add_task($task)
    {
    	if (!is_a($task, 'Threaded')) 
    	{
			echo "task must be of type Threaded\n";
			return;
		}
		$this->pool->submit($task);
    }

    public function add_bulk($tasks_arr)
    {
    	foreach ($tasks_arr as $task) 
    	{
    		$this->add_task($task);
    	}

    }

    public function shutdown()
    {
    	echo "shutting down\n";
    	$this->pool->shutdown();
    }

    public function queue_size()
    {
    	return $this->pool->collect(
    		function (Task $task) {
                // If a task was marked as done
                // collect its results
                echo "collect: {$task->value}\n";
                if ($task->isGarbage()) {
                    $tmpObj = new stdclass();
                    $tmpObj->complete = $task->complete;
                    $tmpObj->value = $task->get_value();
                    //this is how you get your completed data back out [accessed by $pool->process()]
                    $this->main_arr[] = $tmpObj;
                }
                return $task->isGarbage();
            });
    }

}

$pool = new MyPool(50);
$shared = new Shared();
//$shared->add_data("test");

for ($i = 0; $i < 150; ++$i) 
{
	$task = new Task($i,$shared); 
    $pool->add_task($task);
}

while ($pool->queue_size());
//$pool->shutdown();
print_r($pool->get_data());


