<?php
	session_start();

    //ustawienie flagi konta na wylogowany
    require_once "connect.php";
    $connection=@new mysqli($host, $db_user, $db_password, $db_name);
    if($connection->connect_error){
        echo "Error: ".$connection->connect_errno;
    }
    else{
        $result=@$connection->query("UPDATE account SET online=0 WHERE id_account=".$_SESSION['id_account']);
    }

	session_unset();
	header('Location: index.php');
?>