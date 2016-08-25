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

$sql = "SELECT * FROM `member`";
$result = $connect->db->prepare($sql);
$result->execute();
$row = $result->fetchAll(PDO::FETCH_ASSOC);
foreach($row as $value) {
    $dbUser = $value['user'];
    $dbBalance = $value['balance'];
}

$sql = "SELECT * FROM `detial`";
$result = $connect->db->prepare($sql);
$result->execute();
$row = $result->fetchAll(PDO::FETCH_ASSOC);
foreach($row as $value) {
    $dbTransId = $value['transid'];
}

if ($api == "addUser" && isset($_GET['user'])) {
    if ($dbUser == $user) {
        $info = array("massage" => "User are already exist");
        exit(json_encode($info));
    }
    else {
        $sql = "INSERT INTO `member`(`name`, `balance`) VALUES (:user, '10000')";
        $result = $connect->db->prepare($sql);
        $result->bindParam(':user',$user);
        $result->execute();

        $info = array("name" => "$user", "Massage" => "Successful");
        exit(json_encode($info));
    }
}

if ($api == "getBalance" && isset($_GET['user'])) {

    if ($dbUser != $user) {
        $info = array("Massage" => "No user");
        exit(json_encode($info));
    }
    else {
        $sql = "SELECT * FROM `member` WHERE `name` = :user";
        $result = $connect->db->prepare($sql);
        $result->bindParam(':user', $user);
        $result->execute();
        $row = $result->fetch();
        $dbBalance = $row['balance'];

        $info = array( "Result" => "True", "Balance" => "$dbBalance");
        exit(json_encode($info));
    }
}

if ($api == "Transfer" && isset($_GET['user']) && $_GET['type'] && $_GET['transid'] && $_GET['amount']) {

        if ($dbUser != $user) {
            $info = array("Massage" => "No user");
            exit(json_encode($info));
        }

        if ($transId == $dbTransId){
            $info = array("Massage" => "transId are already exist");
            exit(json_encode($info));
        }
        else {
            if($type =="IN") {
                $total =  $dbBalance + $amount;
            }

            if($type =="OUT") {
                $total =  $dbBalance - $amount;
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

            $sql = "UPDATE `member` SET `balance`= :total WHERE `name` = :user";
            $updata = $connect->db->prepare($sql);
            $updata->bindParam(':user', $user);
            $updata->bindParam(':total', $total);
            $updata->execute();

            $info = array( "Balance" => "$total", "Message" => "Successful");

            exit(json_encode($info));
        }
}

if ($api == "transferCheck" && isset($_GET['user']) && $_GET['transid']) {
    $sql = "SELECT * FROM `detial` WHERE `user` = :user AND `transid` = :transId";
    $result = $connect->db->prepare($sql);
    $result->bindParam(':user', $user);
    $result->bindParam(':transId', $transId);
    $result->execute();
    $row = $result->fetchAll(PDO::FETCH_ASSOC);
    foreach($row as $value) {
        $TransId = $value['transid'];
    }
    if ($transId == $TransId ) {
        $info = array("Massage" => "Successful");
        exit(json_encode($info));
    } else {
        $info = array("massage" => "Not Found Transaction!");
        exit(json_encode($info));
    }

}