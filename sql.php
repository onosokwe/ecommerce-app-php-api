<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
define('HOST','localhost');
define('USER', 'root');
define('PASS', '');
define('DB','dev_ecom');
class mainGuy {
	private static $_conn;	
	public function iConnect($db='',$create = false){
	if(empty($db)){
		if ($create){$_conn = (empty($_conn)) ? new MySQLi(HOST,USER,PASS) : $_conn;}
		else {$_conn = (empty($_conn)) ? new MySQLi(HOST,USER,PASS,DB) : $_conn;}
	} else {$_conn = ($db === 'X') ? new MySQLi(HOST,USER,PASS) : new MySQLi(HOST,USER,PASS,$db);}
		return $_conn;
	}
}
class EcommerceApp extends mainGuy {
	public $conn;	public $sql;	public $result;	
	public function __construct($db=''){
    $this->conn = $this->iConnect($db);
		$this->result = NULL;
	}
	public function strEscape($string){ 
		$string = strip_tags(htmlentities(htmlspecialchars(stripslashes(trim($string)))));
		$escString = $this->conn->real_escape_string($string);
		return $escString; 
	}
	public  function clean($data_Array){ 
		if(!is_array($data_Array)){ die("Function 'clean()' expects an Array as parameter"); }
		$esValues = array_map(array($this,'strEscape'),$data_Array);
		return $esValues;
	} 
	private function squeries($sql){ 
		$this->sql = $sql; 
		$this->result = $this->conn->query($this->sql);  
		if($this->result){   
			return $this->result;
		}else{ die($this->conn->error."; Problem with Query \"".$this->sql."\"\n"); }
	}
	public function insert($table, $cols, $vals){
		$cols = (is_array($cols)) ? implode(',',$cols) : $cols;
		$vals = (is_array($vals)) ? implode(',',$vals) : $vals;
		if(substr($vals,0,1) == '('){
			$sqlin = $this->squeries("INSERT INTO {$table} ({$cols}) VALUES {$vals}");
		}else{
			$sqlin = $this->squeries("INSERT INTO {$table} ({$cols}) VALUES ({$vals})");
		}
		if($sqlin){ return $this->conn->affected_rows; }else{ return FALSE; }
	}
	public function insert_check($table, $cols, $vals,$where=''){
		if(empty($where)){ die("Please, define a 'WHERE ..' clause for this operation"); }
		$slct = $this->select($table, $cols, $where);
		if(!$this->result->num_rows){
			return $this->insert($table, $cols, $vals);
		}else{return 0;} 
	}
	public function select($table,$cols='',$where='',$orderBy='',$limit='',$joinTbl='',$on=''){
		list($tableA,$tableB) = (is_array($table)) 	 ? $table 	: array($table,'');
		list($colsA,$colsB)	  = (is_array($cols))	 	 ? $cols 		: array($cols,'');
		list($whrA,$whrB)	 	  = (is_array($where)) 	 ? $where 	: array($where,'');
		list($orderA,$orderB) = (is_array($orderBy)) ? $orderBy : array($orderBy,'');
		list($limitA,$limitB)	= (is_array($limit)) 	 ? $limit 	: array($limit,'');
		list($joinA,$joinB)		= (is_array($joinTbl)) ? $joinTbl : array($joinTbl,'');
		list($onA,$onB)				= (is_array($on)) 	 	 ? $on 			: array($on,''); 
 		if(empty($colsA)){ $colsA = '*'; }else{	$colsA = (is_array($colsA)) ? implode(',',$colsA) : $colsA;	} 
		if(empty($colsB)){ $colsB = '*'; }else{	$colsB = (is_array($colsB)) ? implode(',',$colsB) : $colsB;	} 
		if(is_array($whrA)){ 
			list($mark,$col) = $whrA;
			if($mark == 'wS'){  // WHERE (SELECT...
				$whrA = "WHERE {$col} = (SELECT {$colsB} FROM {$tableB} {$joinB} {$onB} {$whrB} {$orderB} {$limitB})";
				$slct	= "SELECT {$colsA} FROM {$tableA} {$joinA} {$onA} {$whrA} {$orderA} {$limitA}";
			}elseif($mark == 'sI'){ // SELECT INTO...
				$slct	 = "SELECT {$colsA} FROM {$tableA} {$joinA} {$onA} {$whrA} {$orderA} {$limitA}";
				$slct	.= "INTO {$tableB} {$colsB}";
			}
		}elseif(!empty($tableB)){ // UNION SELECT
			$slct	 = "SELECT {$colsA} FROM {$tableA} {$joinA} {$onA} {$whrA} {$orderA} {$limitA}";
			$slct .= " UNION SELECT {$colsB} FROM {$tableB} {$joinB} {$onB} {$whrB} {$orderB} {$limitB}";
		}else{ 
			$slct	 = "SELECT {$colsA} FROM {$tableA} {$joinA} {$onA} {$whrA} {$orderA} {$limitA}";
		}
		if($this->squeries($slct)){ 
			return $this->result->num_rows; 
		}
		return FALSE; 
	}
	public function select_f($table,$cols='',$where='',$orderBy='',$limit=''){
		$list = $this->select($table,$cols,$where,$orderBy,$limit);
        return ($list) ? $this->result : false;
	}
	public function select_fetch($table,$cols='',$where='',$orderBy='',$limit='',$joinTbl='',$on=''){
		if($slct = $this->select($table,$cols,$where,$orderBy,$limit,$joinTbl,$on)){
			$fetch = array(); $sn = 0;
			while($row = $this->result->fetch_assoc()){
				// $row['usn'] = ++$sn; 
				$fetch[] = $row;
			} return $fetch;
		}else{ return ($slct === 0) ? $slct : FALSE; }
	}
	public function update($table,$colsVals,$where,$joinTbl='',$on=''){
		if(empty($where)){ die("Please, define a [or an Array of two] ' WHERE..' clause(s) for this operation"); }
		if(empty($colsVals)){ die("Please, specify COLUMN=['VALUE'] set for this operation"); }
		if(is_array($where)){ 
			list($where,$whrUNIQUE) = $where; 
			if($this->select($table,'',$whrUNIQUE)){ return FALSE; }
		}
		$colsVals = (is_array($colsVals)) ? implode(',',$colsVals) : $colsVals; 
		$sqlup	= $this->squeries("UPDATE {$table} {$joinTbl} {$on} SET {$colsVals} {$where}"); 
		if($sqlup){ return $this->conn->affected_rows; }else{ return FALSE; } 
	}
}
?>