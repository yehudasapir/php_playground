<?php

class URLInfo
{

	private $url = "";
	private $domain = "";
	private $url_id = null;
	private $ref_url_id = null;
	private $red_url_id = null;

	public function __construct($_info = [])
	{
		$this->url = $_info["Url"];
		$this->url_id = $_info["UrlId"];
		$this->ref_url_id = $_info["RefUrlId"];
		$this->red_url_id = $_info["RedUrlId"];
		$this->domain = parse_url($_info["Url"], PHP_URL_HOST);
	}

	public function get_parent()
	{
		return $this->ref_url_id;
	}

	public function get_id()
	{
		return $this->url_id;
	}

	public function get_url()
	{
		return $this->url;
	}

	public function get_domain()
	{
		return $this->domain;
	}
}

class URLNode
{
	private $url_info = null;
	private $parent = null;
	private $childs = [];

	public function __construct($_info)
	{
		$this->url_info = $_info;
	}

	public function get_childs()
	{
		return $this->childs;
	}

	public function get_parent()
	{
		return $this->parent;
	}

	public function set_parent($_par_id)
	{
		$this->parent = $_par_id;
	}

	public function get_info()
	{
		return $this->url_info;
	}

	public function add_child($url_node)
	{
		$child_id = $url_node->get_info()->get_id();
		$this->childs[$child_id] = $url_node;
	}
}

class PageUrlsTree
{
	private static $indent_str = "-";
	private $head = null;
	private $original_arr = null;
	private $urls_arr = [];

	public function __construct($_info)
	{
		$this->original_arr = $_info;
		$this->set_head();
		$this->init();
	}

	private function set_head()
	{
		$first = $this->original_arr[0];
		if(empty($first))
		{
			$head_info = new URLInfo(["Url" => "http://HEAD", "UrlId" => 0 , "RefUrlId" =>0,"RedUrlId" => "0"]);
		}
		else
		{
 			$head_info = new URLInfo($first);
 			unset($this->original_arr[0]);

		}
		$this->head = new URLNode($head_info);
		$this->urls_arr[$head_info->get_id()] = $this->head;
	}

	private function init()
	{
		foreach ($this->original_arr as $id => $value) 
		{
			$url_info = new URLInfo($value);
			$url_node = new URLNode($url_info);
			$this->urls_arr[$url_info->get_id()] = $url_node;
			$parent = $url_info->get_parent();
			$url_node->set_parent($parent);
			$parent_node = $this->urls_arr[$parent];
			$parent_node->add_child($url_node);
		}
	}

	private function print_tree_rec($node, $indent_level = 0)
	{
		for ($i=0; $i < $indent_level; $i++) 
		{ 
			echo PageUrlsTree::$indent_str;
		}
		echo $node->get_info()->get_domain()."\n";
		$childs = $node->get_childs();
		foreach ($childs as $key => $info) 
		{
		 	$this->print_tree_rec($info, $indent_level + 1);
		} 
	}

	public function print_tree()
	{
		$this->print_tree_rec($this->head,0);
	}

	public function get_path_by_url_id($_url_id)
	{
		$node = $this->urls_arr[$_url_id];
		if(!isset($node))
		{
			echo "no node for path\n";
			return;
		}
		else
		{
			$this->path_up($node);
			echo "\n";
		}
	}

	private function path_up($_node)
	{
		if(!isset($_node))
		{
			return;
		}
		$parent = $_node->get_parent();
		echo "->".$_node->get_info()->get_id();
		$this->path_up($this->urls_arr[$parent]);
	}	

}

/*
Test array - uncomment the code bellow for testing
*/

/*
$arr = array
(
    "0" => Array
        (
            "Url" => "http://www.domain.com/somepath",
            "ResponseStatus" => 200,
            "UrlId" => 1,
            "RefUrlId" => 0,
            "RedUrlId" => 0,
            "Flags" => 0,
            "WinId" => 60,
            "StartTS" => 1518825606434,
            "EndTS" => 1518825606842,
            "ContentType" => "text/javascript",
            "ContentLength" => 287
        ),

    "1" => Array
        (
            "Url" => "https://www.otherdomain.com/other_path",
            "ResponseStatus" => 200,
            "UrlId" => 2,
            "RefUrlId" => 1,
            "RedUrlId" => 0,
            "Flags" => 0,
            "WinId" => 60,
            "StartTS" => 1518825606846,
            "EndTS" => 1518825606873,
            "ContentType" => "application/javascript",
            "ContentLength" => 33007,
            "JSCodeUrlId" => 2
        )
);
$page = new PageUrlsTree($arr);
$page->print_tree();
*/


/*
Test with file - uncomment the code bellow to test with file including array converted to json in the follwoing way
$encodedString = json_encode($someArray);
file_put_contents('json_array.txt', $encodedString);: 
*/ 

/*
$json = file_get_contents('json_array.txt');
$json_data = json_decode($json,true);
echo "start\n";

foreach ($json_data as $json_info) 
{
	echo "------------new info-------------"."\n";
	$info = json_decode($json_info,true);
	//print_r($info);
	$page = new PageUrlsTree($info);
	$page->print_tree();
	$page->get_path_by_url_id(15);
}
*/



