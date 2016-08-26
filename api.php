<?php
require_once 'Connect.php';

header("content-type: text/html; charset=utf-8");

$url = $_SERVER['REQUEST_URI'];
$url = explode('/', $url);
$url = explode('?', $url[3]);
$api = $url[0];
$info = array();

$user = $_GET['user'];
$type = $_GET['type'];
$transId = $_GET['transid'];
$amount = $_GET['amount'];

$connect = new Connect;

if ($api == "addUser" && isset($user) && $user != NULL) {

    $sql = "SELECT * FROM `member` WHERE `user` = :user";
    $result = $connect->db->prepare($sql);
    $result->bindParam(':user',$user);
    $result->execute();
    $memberRow = $result->fetchAll(PDO::FETCH_ASSOC);
    $dbUser = $memberRow[0]['user'];

    if ($dbUser == NULL) {
        $sql = "INSERT INTO `member`(`user`, `balance`) VALUES (:user, '10000')";
        $result = $connect->db->prepare($sql);
        $result->bindParam(':user',$user);
        $result->execute();

        $info = array("name" => "$user", "Massage" => "Successful");
        exit(json_encode($info));
    } else {
        $info = array("massage" => "User are already exist");
        exit(json_encode($info));
    }
}

if ($api == "getBalance" && isset($user)) {
    $sql = "SELECT * FROM `member` WHERE `user` = :user";
    $result = $connect->db->prepare($sql);
    $result->bindParam(':user', $user);
    $result->execute();
    $memberRow = $result->fetchAll(PDO::FETCH_ASSOC);
    $dbUser = $memberRow[0]['user'];

    if ($user == $dbUser) {
        $dbBalance = $memberRow[0]['balance'];
        $info = array( "Result" => "True", "Balance" => "$dbBalance");
        exit(json_encode($info));
    } else {
        $info = array("Massage" => "No user");
        exit(json_encode($info));
    }

}

if ($api == "Transfer" && $user != "" && $type != "" && $transId != "" && $amount != "" && $amount >= 0) {
    $sql = "SELECT * FROM `member` WHERE `user` = :user";
    $result = $connect->db->prepare($sql);
    $result->bindParam(':user',$user);
    $result->execute();
    $memberRow = $result->fetchAll(PDO::FETCH_ASSOC);
    $dbUser = $memberRow[0]['user'];
    $dbBalance = $memberRow[0]['balance'];

    $sql = "SELECT * FROM `detial` WHERE `user` = :user ORDER BY `id` DESC";
    $result = $connect->db->prepare($sql);
    $result->bindParam(':user',$user);
    $result->execute();
    $detialRow = $result->fetchAll(PDO::FETCH_ASSOC);
    $dbTransId = $detialRow[0]['transid'];

    if ($dbUser != $user) {
        $info = array("Massage" => "No user");
        exit(json_encode($info));
    }
    if ($transId == $dbTransId){
        $info = array("Massage" => "transId are already exist");
        exit(json_encode($info));
    }
     else {
        if($type == "IN" or $type == "OUT") {
            if($type =="IN") {
            $total =  $dbBalance + $amount;
            }
            if($type =="OUT") {
                $total =  $dbBalance - $amount;
                if ($dbBalance < $amount) {
                    $info = array("Massage" => "You have no enough money ");
                    exit(json_encode($info));
                }
            }
            $sql = "INSERT INTO `detial`(`user`, `transid`, `amount`, `balance`) VALUES (:user, :transId, :amount,:total)";
            $result = $connect->db->prepare($sql);
            $result->bindParam(':user', $user);
            $result->bindParam(':amount', $amount);
            $result->bindParam(':total', $total);
            $result->bindParam(':transId', $transId);
            $result->execute();
            $row = $result->fetch();
            $balance = $row['balance'];

            $sql = "UPDATE `member` SET `balance`= :total WHERE `user` = :user";
            $updata = $connect->db->prepare($sql);
            $updata->bindParam(':user', $user);
            $updata->bindParam(':total', $total);
            $updata->execute();

            $info = array( "Balance" => "$total", "Message" => "Successful");
            exit(json_encode($info));
        } else {
            $user_info = array("message" => "Type error!");
            exit(json_encode($user_info));
        }
    }
}

if ($api == "transferCheck" && $user != "" && $transId != "") {
    $sql = "SELECT * FROM `detial` WHERE `user` = :user AND `transid` = :transId";
    $result = $connect->db->prepare($sql);
    $result->bindParam(':user', $user);
    $result->bindParam(':transId', $transId);
    $result->execute();
    $checkRow = $result->fetchAll(PDO::FETCH_ASSOC);
    $transIdRow = $checkRow[0]['transid'];
    if ($transId == $transIdRow ) {
        $info = array("Massage" => "Successful");
        exit(json_encode($info));
    }else {
        $info = array("massage" => "Not Found Transaction!");
        exit(json_encode($info));
    }
}

$info = array("massage" => "Input error!");

exit(json_encode($info));
