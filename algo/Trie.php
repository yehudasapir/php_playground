<?php

interface iNode 
{
	public function get_value();
	public function get_child($_value);
	public function is_child_exists($_value);
	public function add_child($_value);
}

class EmptyNode implements iNode
{
	public function __construct($_value)
	{
		if(!empty($_value))
		{
			throw new Exception("Can't create Empty Node with value");
		}
	}

	public function get_child($_value)
	{
		return null;
	}

	public function get_value()
	{
		return null;
	}

	public function is_child_exists($_value)
	{
		return false;
	}

	public function add_child($_value)
	{
		throw new Exception("Can't add child to Empty Node");
	}

}

class Node implements iNode
{
	private $value = null;
	private $childs = [];

	public function __construct($_value)
	{
		if(empty($_value))
		{
			throw new Exception("Can't create Node for null or empty value");
		}
		$this->value = $_value;
	}

	public function get_value()
	{
		return $this->value;
	}

	public function get_child($_value)
	{
		return $this->childs[$_value];
	}

	public function get_childs()
	{
		return $this->childs;
	}

	public function is_child_exists($_value)
	{
		return (!empty($this->childs[$_value]));
	}

	public function add_child($_value)
	{
		if($this->is_child_exists($_value))
		{
			return;
		}
		$this->childs[$_value] = new Node($_value);
		return;
	}
}

class Trie
{
	private $head = null;

	public function __construct($_values)
	{
		$this->head = new Node("HEAD");
		$this->build_tree_from_arr($_values);

	}

	private function build_tree_from_arr($_values)
	{
		foreach ($_values as $str) 
		{
			$this->add_string($str);
		}
	}	

	private function add_string($str = "")
	{
		$strlen = strlen($str);
		$loop_head = $this->head;
		echo "loop_head: ".$loop_head->get_value()."\n";
		for($i = 0; $i < $strlen; $i++) 
		{
    		$char = substr( $str, $i, 1 );
    		echo "char: ".$char."\n";
    		// check if empty node
    		if(!$loop_head->is_child_exists($char))
    		{
    			$loop_head->add_child($char);	
    		}
    		$loop_head = $loop_head->get_child($char);
		}
	}

	private function print_tree($node)
	{
		if(!$node->get_value())
		{
			echo "Empty\n";
			return; 
		}
		echo $node->get_value()."\n";
		$childs  = $node->get_childs();
		print_r(array_keys($childs));
		foreach ($childs as $child => $child_node)
		{
			$this->print_tree($child_node);
		}
	}

	public function to_str()
	{
		$this->print_tree($this->head);
	}

}

//Test Trie with example array
$testArr = ["aab","aaa","bbcdef","bab","abc","aaa","acb","ade"];
$trie = new Trie($testArr);
$trie->to_str();

