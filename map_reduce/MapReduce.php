<?php

class DataSource
{
	private $data_location = "";
	private $file_extension = "";
	private $data_source_arr = [];

	public function __construct($_data_location, $_ext)
	{
		echo "init Data obj: ".$_data_location."\n";
		$this->data_location = $_data_location;
		$this->file_extension = $_ext;
		$this->get_files();
		// TODO -> should return exception if can't get files
	}

	private function get_files()
	{
		echo "get files from: ".$this->data_location."\n";
		$this->data_source_arr = glob($this->data_location."/*.".$this->file_extension);
	}

	public function get_data_source()
	{
		return $this->data_source_arr;
	}
}

class DataUnit
{
	private $file_path;
	private $data_arr = [];

	public function __construct($_filepath)
    {
        $this->file_path = $_filepath;
        $this->read_data();
    }

    private function read_data()
    {
    	$this->data_arr = file($this->file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    }

    // TODO -> this object should implament Iterator!
    public function get_data()
    {
    	return $this->data_arr;
    }
}

class TupplesMap
{
	private $map = [];

	public function add_tupple($tuple)
	{
		//echo "adding:\n";
		//print_r($tuple);
		if(is_null($tuple))
		{
			return;
		}
		$key = $tuple->get_key();
		$curr = $this->map[$key];
		if(!$curr)
		{
			$this->map[$key] = $tuple;
			return;
		}
		$curr->combine($tuple);
	}

	public function combine($other)
	{
		if($other->is_empty())
		{
			return;
		}

		foreach ($other->get_map() as $other_tuple) 
		{
			$this->add_tupple($other_tuple);
		}
	}

	private function get_map()
	{
		return $this->map;
	}

	public function is_empty()
	{
		return (sizeof($this->map) == 0);
	}
}

class Tupple
{
	private $key = null;
	private $value = null;

	public function __construct($k,$v)
    {
        $this->key = $k;
        $this->value = $v;
    }

    public function get_key()
    {
    	return $this->key;
    }

    public function get_value()
    {
    	return $this->value;
    }

    public function combine($tuple)
    {
    	$this->value += $tuple->get_value();
    }
}

abstract class Mapper
{
	private $mt_map = [];
	private $input_data = [];

	public function __construct($_input_data)
	{
		$this->input_data = $_input_data;
	}

	public function get_mapped_data()
	{
		return $this->mt_map;
	}

	public function is_empty()
	{
		return (sizeof($this->mt_map) == 0);
	}

	protected function map()
	{
		echo "in map\n";
		foreach ($this->input_data as $raw) 
		{
			echo "line: ".$raw."\n";
			$this->mt_map = array_merge($this->mt_map,$this->map_line($raw));
		}
	}
	abstract protected function map_line($raw);
}

class KeywordsMapper extends Mapper
{
	public function __construct($_input_data)
	{
     	parent::__construct($_input_data);
     	parent::map();
  	}

	protected function map_line($raw)
	{
		$line_arr = explode(" ", $raw);
		return array_map(function($word)
				  {
				  	return new Tupple($word,1);
				  }, $line_arr);
	}
}

abstract class Reducer
{
	private $mapped_data = null;
	
	public function __construct($_data)
	{
		$this->mapped_data = $_data;
	}

	public function reduce()
	{
		foreach ($this->mapped_data as $tuple) 
		{
			$this->reduce_tuple($tuple);
		}
	}

	abstract protected function reduce_tuple($tuple);
	abstract public function get_reduced_data();

} 

class KeywordsReducer extends Reducer
{
	private $reduced_data = null;

	public function __construct($_data)
	{
		if(is_null($_data))
		{
			return;
		}
     	parent::__construct($_data);
     	$this->reduced_data = new TupplesMap();
     	parent::reduce();
  	}

  	protected function reduce_tuple($tuple)
  	{
  		$this->reduced_data->add_tupple($tuple);
  	}

  	public function get_reduced_data()
  	{
  		return $this->reduced_data;
  	}
}

class MapperTask extends Threaded
{
    private $data_unit;

    public function __construct($_data_unit)
    {
        $this->data_unit = $_data_unit;
    }

    public function run()
    {

        
    }
}

class MapReduce
{

	private $data_source = null;
	private $mapper = [];
	private $reducers = [];

	public function __construct($_data_source)
	{
		$this->data_source = $_data_source;
	}

	public function map()
	{
		$data_sources = $this->data_source->get_data_source();
		foreach ($data_sources as $single) 
		{
			$data_unit = new DataUnit($single);
			$mapped_data = new KeywordsMapper($data_unit->get_data());
			//print_r($mapped_data);
			$reduced_data = new KeywordsReducer($mapped_data->get_mapped_data());
			//print_r($reduced_data->get_reduced_data());
			$this->reducers[] = $reduced_data->get_reduced_data();
		}
		//echo "reducers size: " .sizeof($this->reducers)."\n";
	}

	public function reduce()
	{
		while (sizeof($this->reducers) > 1)
		{
			$this->reduce_chunk();
		}
		print_r($this->reducers);
	}

	public function reduce_chunk()
	{
		$first = array_pop($this->reducers);
		$second = array_pop($this->reducers);
		$first->combine($second);
		$this->reducers[] = $first;
	}

	public function init_data()
	{

	}

}

$my_data_source = new DataSource("./source","txt");
$map_reduce = new MapReduce($my_data_source);
$map_reduce->map();
$map_reduce->reduce();
//print_r($my_data->get_data_source());

