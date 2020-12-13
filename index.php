<?php
    session_start();

    if ((isset($_SESSION['login'])) && ($_SESSION['login']==true) && (isset($_SESSION['position_name'])))
	{
        if($_SESSION['position_name']=="admin"){
            header('Location: insert_new_employee.php');
            exit();
         }
        else if($_SESSION['position_name']=="kelner"){
            header('Location: insert_new_order.php');
            exit();
        }
        else if($_SESSION['position_name']=="kucharz"){
            header('Location: chef_orders_list.php');
            exit();
        }
	}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8">
	<title>Logowanie</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	
	<meta name="description" content="Strona logowanie do aplikacji webowej restauracji">
	<meta name="author" content="Piotr Kupis">
	
	<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
	<link href="main.css" rel="stylesheet" type="text/css"/>

</head>

<body >
    
    <div class="container">
    
        <main class="col-sm-8 offset-sm-2">
             
                <br/><br/><br/><h1>Logowanie</h1>
                <form action="login.php" method="post">
                    
                    <label for="login">Login</label>
                    <input type="text" name="login" placeholder="Wprowadź swój login"/><br/>
                    
                    <label for="password">Hasło</label>
                    <input type="password" name="password" placeholder="Wprowadź swoje hasło"/><br/><br/>
                    
                    <input type="submit" value="Zaloguj się"/>
                    
                </form>

                <br/>
                <?php
                    if(isset($_SESSION['error']))
                        echo $_SESSION['error'];
                ?>
            <br/><br/><br/><br/>
      
        </main>

    </div> 
	
</body>
</html>