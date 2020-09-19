<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: X-Requested-With, content-type, access-control-allow-headers, access-control-allow-origin, access-control-allow-methods');
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/fxn.php';
$fxn = new fxn();
$request = json_decode(file_get_contents('php://input'), TRUE);
if (json_last_error() === JSON_ERROR_NONE) {
    $arr = ['LOG', 'SNU', 'FAPS', 'FPC', 'APS', 'POS', 'COS'];
    if (in_array($request['key'], $arr)) {
        $code = null; $data = null; $result;
        // login api
        if($request['key'] == 'LOG'){
            $email = trim(strtolower($request['email']));                          
            $pass = $request['password'];
            $match = $fxn->isEmailPasswordMatch($email, $pass);
            if($match > 0){
                $code = '00'; 
                $data = array(
                    'name' => $match[0]["user_name"], 
                    'phone' => $match[0]["user_phone"], 
                    'email' => $match[0]["user_email"],  
                    'address' => $match[0]["user_address"], 
                    'userid' => $match[0]["user_id"],
                    'message' => "Login Successful");
            } else {$code = '01'; $data = 'Login Failed!';}
        } 
        // signup api
        if($request['key'] == 'SNU'){
            $name = $request['name'];                          
            $phone = $request['phone'];
            $email = $request['email'];                      
            $pass = $request['password'];                         
            $address = $request['address'];
            $res = $fxn->isEmailRegistered($email);
            if($name && $phone && $email && $pass && $address){
                if($res === FALSE){
                    if($token = $fxn->createUser($name,$phone,$email,$pass,$address)){
                        $code = '00'; $data = 'Sign Up Successful!';
                    } else {$code = '01'; $data = 'Sign Up Failed';}    
                } else {$code = '01'; $data = 'This Email is Registered!';}
            } else {$code = '01'; $data = 'Invalid Input Format!';}
        } 
        // get products
        if($request['key'] == 'FAPS'){
            if($items = $fxn->getAllProducts()){
                $code = '00'; $data = $items;
            } else {$code = '01'; $data = 'No product found!';}
        } 
        // get product categories
        if($request['key'] == 'FPC'){
            if($items = $fxn->getCategories()){
                $code = '00'; $data = $items;
            } else {$code = '01'; $data = 'No category found!';}
        } 
        // add product
        if($request['key'] == 'APS'){
            $name = trim(ucwords($request['name']));
            $category = trim(ucwords($request['category']));
            $price = $request['price'];
            $desc = $request['description'];
            $quantity = $request['quantity'];
            $userid = $request['userid'];
            if($items = $fxn->createProduct($name,$category,$price,$desc,$quantity,$userid)){
                $code = '00'; $data = 'Product Added Successfully!';
            } else {$code = '01'; $data = 'Product Added Failed!';}
        } 
        // get orders
        if($request['key'] == 'POS'){
            $userid = $request['userid'];
            if($items = $fxn->getOrders($userid)){
                $code = '00'; $data = $items;
            } else {$code = '01'; $data = 'No orders found!';}
        } 
        // create order
        if($request['key'] == 'COS'){
            $name = trim(ucwords($request['name']));
            $email = trim(strtolower($request['email']));
            $address = trim(ucwords($request['address']));
            $items = $request['cartItems'];
            $total = $request['total'];
            if($done = $fxn->createOrders($name,$email,$address,$items,$total)){
                $code = '00'; $data = "Order created successfully!";
            } else {$code = '01'; $data = 'Order creation failed!';}
        } 
        echo json_encode(['code'=> $code, 'data' => $data]);
    } else {echo 'wrong or empty key';}
} else {echo "Response not JSON";}
?> 