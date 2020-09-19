<?php
include_once('sql.php');
date_default_timezone_set ("Africa/Lagos");
class fxn extends EcommerceApp {
	public $conn;
	public function __construct(){
		$this->conn = new EcommerceApp(DB); 
		return $this->conn;
	}
	// USER
	public function isEmailRegistered($email){
		$table = "users"; 
		$cols = "*";
		$where = "WHERE `user_email` = '".trim(strtolower($email))."'";
		if($this->conn->select_fetch($table,$cols,$where)){
		return TRUE;} else {return FALSE;}
	}
	public function isEmailPasswordMatch($email, $pass){
		$table = "users"; $cols = "*"; 
		$ps = sha1(md5($pass));
		$where = "WHERE `user_email` = '".trim(strtolower($email))."' && `user_password`='".trim($ps)."'";
		if($fet = $this->conn->select_fetch($table,$cols,$where)){
		return $fet;} else {return FALSE;}
	}
	public function createUser($name,$phone,$email,$pass,$address){ 
		$userid = rand(10000000, 99999999);
		$newpass = sha1(md5($pass)); 
		$today = date('Y-m-d H:i:s'); 
		$table = "users"; 
		$cols = "`user_name`,`user_email`,`user_phone`,`user_password`,`user_address`,`user_id`,`date_created`,`status`"; 
		$vals = "'$name','$email','$phone','$newpass','$address','$userid','$today','1'"; 
		if($this->conn->insert($table,$cols,$vals)){return TRUE;} else {return FALSE;}
	}
	// PRODUCTS
	public function createProduct($name,$category,$price,$desc,$quantity,$userid){
		$table = "products"; 
		$today = date('Y-m-d H:i:s');
		$cols = "`product_category`,`product_name`,`product_desc`,`product_price`,`quantity`, `creator_id`,`date_created`"; 
		$vals = "'$category','$name','$desc','$price','$quantity','$userid','$today'"; 
		if($this->conn->insert($table,$cols,$vals)){return true;} else {return FALSE;}
	}
	public function getAllProducts(){
		$table = "products"; 
		$cols = "*";
		$where = "WHERE `quantity` > '0'";
		$found = $this->conn->select_fetch($table,$cols,$where);
		if($found > 0){return $found;} else {return false;} return false;
	}
	public function getCategories(){
		$table = "categories"; 
		$cols = "category_name";
		$where = "WHERE `cid` > '0' && `status` = '1'";
		$found = $this->conn->select_fetch($table,$cols,$where);
		if($found > 0){return $found;} else {return false;} return false;
	}
	// ORDERS
	public function getOrders($ownerid){
		$table = "orders"; 
		$cols = "*";
		$where = "WHERE `oid` > '0' && `owner_id` = '$ownerid'";
		$found = $this->conn->select_fetch($table,$cols,$where);
		if($found > 0){return $found;} else {return false;} return false;
	}
	public function createOrders($name,$email,$address,$items,$total){
		$c = count($items);
		$order_id = rand(1111,9999).rand(1000, 9999);
		$today = date('Y-m-d H:i:s');
		foreach($items as $key => $value){
			$productid = $value['pid'];
			$product = $value['product_name'];
			$price = $value['product_price'];
			$owner = $value['creator_id'];
			$table = "orders"; 
			$cols = "`name`,`email`,`address`,`total`,`product_id`,`prod_name`,`prod_price`,`owner_id`,`order_id`,`createdAt`"; 
			$vals = "'$name','$email','$address','$total','$productid','$product','$price','$owner','$order_id','$today'"; 
			if($this->conn->insert($table,$cols,$vals)){$c--;}
		} if($c === 0){return true;} else {return FALSE;}
	}
}
?>