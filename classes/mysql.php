<?php
class dbase_wraper extends mysqli {
	public $query_id;
	public $query;
	var $sec;
	var $row;

	function query($sql) {
		$secs = microtime(true);
		$this->result = parent :: query($sql);
		$secs = round(microtime(true) - $secs, 3);
		if (!$this->result) {
			return false;
		} else {
			$rows = array ();
			if ($this->result===TRUE)
			{
			return true;	
			} else
			{
				while($row = $this->result->fetch_array(MYSQL_ASSOC))
				{
				$rows[] = $row;
				}
			return $rows;
			}
		}
	}

	function filter($str, $html=false,$jsenable=false)
	{
		$search  = array("ي","ك");
		$replace = array("ی","ک");
		$str = trim($str);
		$str = str_replace($search, $replace, $str);
	        $str = str_replace("\0", '', $str);

		if (!$html){
		$str = strip_tags($str);
		$str = htmlentities ($str, ENT_QUOTES, 'UTF-8');
		$str = str_replace('script', 'scirpt', $str);
		}
		$str = $this->real_escape_string($str);
		$str = addcslashes($str, '%');
		return $str;
	}

	function defilter($str,$html=false,$jsenable=false)
	{
		$str = stripslashes($str);
		return $str;
	}
}
?>

