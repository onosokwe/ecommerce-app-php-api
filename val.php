<?php
class VALIDATE {
    public function valid_number($value) {
        return ctype_digit($value) ? $value : false;
    }
    public function valid_string($value){
        if(!empty($value)){if(preg_match('/^[\p{L}]*$/', $value) == true){
        return $value;} else {return false;}} else {return false;}
    }
    public function valid_alnum($value) {
        return ctype_alnum($value) ? $value : false;
    }
    public function valid_pass($value) {
        if(!empty($value)){
        return preg_match("/^.*(?=.{8,})(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).*$/", $value) ? $value : false;}
        else {return false;}
    }
    public function valid_phone($value) {
        if(!empty($value)){if(preg_match('/^[0-9]{11,}$/', $value) == true && ctype_digit($value) == true) {
            return $value;} else {return false;}} else {return false;}
    }
    public function valid_id($value) {
        if(!empty($value)){if(preg_match('/^[0-9]{8}$/', $value) == true && ctype_digit($value) == true) {
            return $value;} else {return false;}} else {return false;}
    }
    public function valid_email($email) {
        return filter_var(filter_var(trim(strtolower($email)), FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL) ? $email : false;
    }
}
?>