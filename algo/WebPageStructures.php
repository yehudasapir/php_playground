<?php

class URLInfo
{

	private $url = "";
	private $domain = "";
	private $url_id = null;
	private $ref_url_id = null;
	private $red_url_id = null;
	private $js_creator = null;

	public function __construct($_info = [])
	{
		$this->url = $_info["Url"];
		$this->url_id = $_info["UrlId"];
		$this->ref_url_id = $_info["RefUrlId"];
		$this->red_url_id = $_info["RedUrlId"];
		$this->js_creator = empty($_info["JSCodeUrlId"]) ? "0" : $_info["JSCodeUrlId"];
		$this->domain = parse_url($_info["Url"], PHP_URL_HOST);
	}

	public function get_parent()
	{
		//parent can be the creator or the refferer
		if($this->js_creator > 0)
		{
			return $this->js_creator;
		}
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

	public function get_js_creator()
	{
		return $this->js_creator;
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
		$this->build_tree();
	}

	private function set_head()
	{
		if(array_key_exists(0,$this->original_arr))
		{
			$first = $this->original_arr[0];
			$head_info = new URLInfo($first);
 			unset($this->original_arr[0]);
		}
		else
		{
			$head_info = new URLInfo(["Url" => "http://HEAD", "UrlId" => 0 , "RefUrlId" => "0","RedUrlId" => "0", "JSCodeUrlId" => "0"]);
		}
		$this->head = new URLNode($head_info);
		$this->urls_arr[$head_info->get_id()] = $this->head;
	}

	private function build_tree()
	{
		foreach ($this->original_arr as $id => $value) 
		{
			$url_info = new URLInfo($value);
			$url_node = new URLNode($url_info);
			$this->urls_arr[$url_info->get_id()] = $url_node;
			$parent = $url_info->get_parent();
			$this->set_parent($parent,$url_node);
		}
	}

	private function set_parent($_parent_id,$url_node)
	{
		if(array_key_exists($_parent_id,$this->urls_arr))
		{
			$parent_node = $this->urls_arr[$_parent_id];
		}
		else
		{
			$parent_info = new URLInfo(["Url" => "http://MISSING_ID", "UrlId" => $_parent_id , "RefUrlId" => "0","RedUrlId" => "0", "JSCodeUrlId" => "0"]);
			$parent_node = new URLNode($parent_info);
			$this->urls_arr[$_parent_id] = $parent_node; 
		}
		$url_node->set_parent($_parent_id);
		$parent_node->add_child($url_node);
	}

	public function get_node_by_url($_url)
	{
		foreach ($this->urls_arr as $key => $node) 
		{
			$info = $node->get_info();
			if(strcmp($info->get_url(),$_url) == 0)
			{
				return $node;
			}
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

	public function get_path_by_url($_url)
	{
		$node = $this->get_node_by_url($_url);
		$this->get_path_by_node($node);
	}

	public function get_path_by_node($_node)
	{
		$info = $_node->get_info();
		$url_id = $info->get_id();
		$this->get_path_by_url_id($url_id);
	}

	public function get_path_by_url_id($_url_id)
	{
		$node = $this->urls_arr[$_url_id];
		if(!isset($node))
		{
			echo "no node for path\n";
			return;
		}
		echo "Print path for tree with head: ".$this->head->get_info()->get_url()."\n";
		$this->path_up($node);
		echo "\n";
	}

	private function path_up($_node)
	{
		if(!isset($_node))
		{
			return;
		}
		echo "[id: ".$_node->get_info()->get_id()." => ";
		echo "url :".$_node->get_info()->get_url()."] -> ";
		$parent = $_node->get_parent();
		if(!array_key_exists($parent,$this->urls_arr))
		{
			echo "DONE\n";
			return;
		}
		$par_node = $this->urls_arr[$parent];
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



