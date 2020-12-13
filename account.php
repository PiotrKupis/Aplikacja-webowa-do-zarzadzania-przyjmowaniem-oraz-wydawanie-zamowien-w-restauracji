<?php
    session_start();

    if( !isset($_SESSION['login']) || !isset($_SESSION['position_name']) )
	{
        header('Location: index.php');
        exit();
	}

    //zmiana hasła
    if(isset($_POST['change_password'])){
        
        $ok=true;
        
        $current_password=$_POST['current_password'];
        $current_password=htmlentities($current_password,ENT_QUOTES,"UTF-8");
        if(strlen($current_password)<1){
            $ok=false;
            $_SESSION['e_current_password']='<span style="color:red">Nie podano obecnego hasła</span><br/>';
        }
        
        $new_password=$_POST['new_password'];
        $new_password=htmlentities($new_password,ENT_QUOTES,"UTF-8");
        
        $new_password_repeated=$_POST['new_password_repeated'];
        $new_password_repeated=htmlentities($new_password_repeated,ENT_QUOTES,"UTF-8");
        
        if(strlen($new_password)<8 || strlen($new_password)>80){
            $ok=false;
            $_SESSION['e_new_password']='<span style="color:red">Nowe hasło musi mieć minimalnie 8 znaków oraz 80 maksymalnie</span><br/>';
        }
        
        if($new_password!=$new_password_repeated){
            $ok=false;
            $_SESSION['e_new_password']='<span style="color:red">Podane hasła nie są takie same</span><br/>';
        }
        
        
        if($ok){
            //sprawdzenie czy podano prawidłowe hasło do konta
            require_once "connect.php";
            $connection=@new mysqli($host, $db_user, $db_password, $db_name);

            if($connection->connect_error){
                $_SESSION['e_new_password']='<span style="color:red">Błąd połączenia z bazą, skontaktuj się z administratorem!</span><br/>';
            }
            else{
                $result=@$connection->query("SELECT * FROM account WHERE id_account=".$_SESSION['id_account']);
                
                if($result){
                    if($result->num_rows>0){
                        $data=$result->fetch_assoc();

                        if(password_verify($current_password,$data['password'])){
                            
                            $result->free_result();
                            $new_password=password_hash($new_password, PASSWORD_DEFAULT);
                            
                            $result=@$connection->query("
                                UPDATE `account` 
                                SET `password`='".$new_password."' 
                                WHERE id_account=".$_SESSION['id_account']);
                                
                            $_SESSION['e_new_password']='<span style="color:green">Hasło zostało zmienione</span><br/>';
                        }
                    }
                }
                else{
                    $_SESSION['e_new_password']='<span style="color:red">Błąd połączenia z bazą, skontaktuj się z administratorem!</span><br/>';
                }
                $connection->close();
            }
            
        }    
    } 

?>

<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8">
	<title>Moje konto</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	
	<meta name="description" content="Strona do zmiany ustawień konta">
	<meta name="author" content="Piotr Kupis">
	
	<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
	<link href="main.css" rel="stylesheet" type="text/css"/>
</head>

<body >

	<header>
        <nav class="navbar navbar-expand-sm navbar-dark bg-dark justify-content-center py-4"> 
            <ul class="navbar-nav">
                
                <?php
                    if($_SESSION['position_name']=="admin"){
                        echo '
                            <li class="nav-item px-4">
                                <a class="nav-link" href="insert_new_employee.php"><h3> Dodawanie pracowników </h3></a>
                            </li>
                        ';
                    }
                    else if($_SESSION['position_name']=="kelner"){
                        echo '
                            <li class="nav-item px-4">
                                <a class="nav-link" href="insert_new_order.php"><h3> Przyjmowanie zamówia </h3></a>
                            </li>
                            <li class="nav-item px-4">
                                <a class="nav-link" href="change_order_status.php"><h3> Zmiana statusu zamówienia </h3></a>
                            </li>
                        ';
                        
                        if(isset($_SESSION['dishes_id'])){
                            unset($_SESSION['quantities']);
                            unset($_SESSION['dishes_id']);
                        }
                    }
                    else if($_SESSION['position_name']=="kucharz"){
                        echo '
                            <li class="nav-item px-4">
                                <a class="nav-link" href="chef_orders_list.php"><h3> Lista zamówień do przygotowania </h3></a>
                            </li>
                        ';
                    }
                ?>
                <li class="nav-item px-4">
                    <a class="nav-link" href="account.php"><h3> Moje konto </h3></a>
                </li>
                <li class="nav-item px-4">
                    <a class="nav-link" href="logout.php"><h3> Wyloguj się </h3></a>
                </li>
                
            </ul>
        </nav>
	</header>
	
    
    <div class="container">
        
        <main class="col-sm-8 offset-sm-2">
            
            <br/><h1>Zmiana danych konta</h1><br/>
            <h2>Zmiana hasła</h2><br/>
            
            <form method="post">
                    
                    <label for="current_password">Twoje bieżące hasło</label>
                    <input type="password" name="current_password" placeholder="Wprowadź bieżące hasło"/><br/>
                    <?php
                        if(isset($_SESSION['e_current_password'])){
                            echo $_SESSION['e_current_password'];
                            unset($_SESSION['e_current_password']);
                        }     
                    ?>
                
                    <label for="new_password">Nowe hasło</label>
                    <input type="password" name="new_password" placeholder="Wprowadź nowe hasło"/><br/>
                
                    <label for="new_password_repeated">Powtórz nowe hasło</label>
                    <input type="password" name="new_password_repeated" placeholder="Powtórz nowe hasło"/><br/>
                
                    <?php
                        if(isset($_SESSION['e_new_password'])){
                            echo $_SESSION['e_new_password'];
                            unset($_SESSION['e_new_password']);
                        }     
                    ?>
                
                <br/><input type="submit" name="change_password" value="Zmień hasło"/><br/><br/>
            </form>
            <br/><br/>
            
        </main>
    </div>	
</body>
</html>