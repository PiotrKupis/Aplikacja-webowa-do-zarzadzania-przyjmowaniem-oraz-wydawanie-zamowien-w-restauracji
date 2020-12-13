<?php
    session_start();

    if( !isset($_SESSION['login']) || !isset($_SESSION['position_name']) || $_SESSION['position_name']!="kelner" )
	{
        header('Location: index.php');
        exit();
	}
    
?>

<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8">
	<title>Dodawanie nowego zamówienia</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	
	<meta name="description" content="Strona do dodawnia nowego zamówienia">
	<meta name="author" content="Piotr Kupis">
	
	<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
	<link href="main.css" rel="stylesheet" type="text/css"/>
</head>

<body >

	<header>
        <nav class="navbar navbar-expand-sm navbar-dark bg-dark justify-content-center py-4"> 
            <ul class="navbar-nav">
                <li class="nav-item px-4">
                    <a class="nav-link" href="insert_new_order.php"><h3> Przyjmowanie zamówia </h3></a>
                </li>
                <li class="nav-item px-4">
                    <a class="nav-link" href="change_order_status.php"><h3> Zmiana statusu zamówienia </h3></a>
                </li>
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
        
        <main class="col-sm-12">
            
            <br/><h1>Przyjmowanie nowego zamówienia</h1><br/><br/>
            <h2>Treść zamówienia</h2><br/>

            <div class="table-wrapper-scroll-y order_scrollbar">

                    <table class="table table-bordered table-striped mb-0">
                        <tr class="table-active">
                            <th scope="col">Lp</th>
                            <th scope="col">Nazwa</th>
                            <th scope="col">Kategoria</th>
                            <th scope="col">Cena</th>
                            <th scope="col">Ilość</th>
                            <th scope="col">Usuń</th>
                        </tr>

                        <?php

                            //tworzenie tablic jeśli nie istnieją
                            if(!isset($_SESSION['dishes_id'])){
                                $_SESSION['dishes_id']=array();
                                $_SESSION['quantities']=array();
                            }

                            //dodawanie nowego dania do zamówienia
                            if(isset($_POST['add_dish']) ){

                                if($_POST['quantity']!=0){
                                    
                                    //sprawdzenie czy jest już takie danie w zamówieniu
                                    if(in_array($_POST['id_dish'],$_SESSION['dishes_id'])){
                                        
                                        $id_index=array_search($_POST['id_dish'],$_SESSION['dishes_id']);
                                        $_SESSION['quantities'][$id_index]=$_SESSION['quantities'][$id_index]+$_POST['quantity'];
                                    }
                                    else{
                                        array_push($_SESSION['dishes_id'], $_POST['id_dish']);
                                        array_push($_SESSION['quantities'], $_POST['quantity']);
                                    }    
                                }
                            }
                        
                            //usuwanie pozycji dań z listy zamówienia
                            if(isset($_POST['remove_dish'])){
                                
                                if(in_array($_POST['id_dish'],$_SESSION['dishes_id'])){
                                        
                                        $id_index=array_search($_POST['id_dish'],$_SESSION['dishes_id']);
                                    
                                        unset($_SESSION['quantities'][$id_index]);
                                        unset($_SESSION['dishes_id'][$id_index]);
                                    
                                        $_SESSION['quantities']=array_values($_SESSION['quantities']);
                                        $_SESSION['dishes_id']=array_values($_SESSION['dishes_id']);
                                }
                            }
                        
                            //wprowadzanie zamówienia do bazy
                            if(isset($_POST['submit_order'])){
        
                                //sprawdzenie poprawnośći danych
                                $_SESSION['table_number']=$_POST['table_number'];
                                $table_number=$_POST['table_number'];
                                
                                $_SESSION['order_comment']=trim($_POST['order_comment']);
                                $order_comment=trim($_POST['order_comment']);
                                
                                $total_price=$_POST['total_price'];
                                
                                if( count($_SESSION['dishes_id'])==0 ){
                                    $_SESSION['insertOrderMessage']='<span style="color:red">Nie dodano żadnego dania do listy</span><br/>';
                                }
                                else if( strlen($_POST['table_number'])==0 ){
                                    $_SESSION['insertOrderMessage']='<span style="color:red">Nie podano numeru stolika</span><br/>';
                                }
                                else if( !is_numeric($_POST['table_number'])){
                                    $_SESSION['insertOrderMessage']='<span style="color:red">Numer stolika musi być liczbą</span><br/>';
                                }
                                else if( $_POST['table_number']<1 ){
                                    $_SESSION['insertOrderMessage']='<span style="color:red">Podano nieprawidłowy numer stolika</span><br/>';
                                }
                                else{
                                        
                                    require_once "connect.php";
                                    $connection=@new mysqli($host, $db_user, $db_password, $db_name);

                                    if($connection->connect_error){
                                        $_SESSION['insertOrderMessage']='<span style="color:red">Błąd bazy danych, skontaktuj się z administratorem!</span>';
                                    }
                                    else{
                                        //dodanie zamówienia do bazy
                                        $date=date('Y-m-d H:i:s');
                                        $result=@$connection->query("
                                            INSERT INTO `order` (`table_number`, `order_date`, `service_time`, `id_status`, `id_payment`, `value`, `comment`) 
                                            VALUES ($table_number, '".$date."', NULL, 1, NULL, $total_price, '".$order_comment."')");

                                         //sprawdzenie dodania
                                        $result=@$connection->query("
                                            SELECT `id_order` 
                                            FROM `order` 
                                            WHERE table_number=$table_number AND order_date='".$date."' AND id_status=1 AND value=$total_price");

                                        if($result==TRUE){
                                            
                                            $data=$result->fetch_assoc();
                                            $id_order=$data['id_order'];
                                            $result->free_result();
                                            
                                            //dodanie poszczególnych potraw do bazy
                                            $stmt = $connection->prepare("
                                                INSERT INTO order_dishes (id_order, id_dish, quantity) 
                                                VALUES (?, ?, ?)");
                                            
                                            if($stmt){
                  
                                                $stmt->bind_param("iii", $id_order, $id_dish, $quantity);
                                                $dish_quantity=count($_SESSION['dishes_id']);
                                                
                                                for ($i = 0; $i < $dish_quantity; $i++) {

                                                    $id_dish=$_SESSION['dishes_id'][$i];
                                                    $quantity=$_SESSION['quantities'][$i];
                                                    $stmt->execute();                                    
                                                }
                                                
                                                unset($_SESSION['dishes_id']);
                                                unset($_SESSION['quantities']);
                                                unset($_SESSION['table_number']);
                                                unset($_SESSION['order_comment']);
                                                $_SESSION['dishes_id']=array();
                                                $_SESSION['quantities']=array();
                                                
                                                $_SESSION['insertOrderMessage']='<span style="color:green">Zamówienie zostało przyjęte</span>';

                                                $stmt->close();   
                                            }
                                            else{
                                                $_SESSION['insertOrderMessage']='<span style="color:red">Błąd bazy danych, skontaktuj się z administratorem!</span>';
                                            }
                                        }
                                        else{
                                            $_SESSION['insertOrderMessage']='<span style="color:red">Błąd bazy danych, skontaktuj się z administratorem!</span>';
                                        }
                                        $connection->close();
                                    }
                                }  
                            }
                                
                            //wyświetlenie dań dodanych do zamówienia
                            require_once "connect.php";
                            $connection=@new mysqli($host, $db_user, $db_password, $db_name);

                            if($connection->connect_error){
                               echo "Error: ".$connection->connect_errno;
                            }
                            else{
                                
                                $result=@$connection->query("
                                    SELECT d.id_dish,d.dish_name,dc.category_name as category_name,d.price 
                                    FROM dish as d INNER JOIN dish_category as dc ON d.id_category=dc.id_category WHERE d.id_dish in (".implode(',',$_SESSION['dishes_id']).")
                                    ORDER BY category_name");

                                $sum=0;
                                if($result){
                                    $number=1;
                                    while($data=$result->fetch_assoc()){

                                        $dish_name=$data['dish_name'];
                                        $category=$data['category_name'];
                                        $price=$data['price'];
                                        $quantity=$_SESSION['quantities'][$number-1];
                                        $id_dish=$_SESSION['dishes_id'][$number-1];

                                        $sum+=$price*$quantity;
                                        
                                        echo '<tr><form method="post" action="insert_new_order.php">';
                                        echo "
                                        <th scope='row' class='align-middle'>$number</th>
                                        <td class='align-middle'>$dish_name</td>
                                        <td class='align-middle'>$category</td>
                                        <td class='align-middle'>$price zł</td>
                                        <td class='align-middle'>$quantity</td>";

                                        echo '
                                        <td class="align-middle">
                                            <input type="hidden" name="id_dish" value="'.$id_dish.'"/>
                                            <input name="remove_dish" type="submit" value="Usuń"/>
                                        </td>';

                                        echo "</form></tr>";

                                        $number=$number+1;
                                    }
                                    $result->free_result();
                                 }
                                
                                $connection->close();
                                if(count($_SESSION['dishes_id'])==0){
                                    echo "
                                    <tr>
                                        <td>----</td>
                                        <td>----------</td>
                                        <td>---------</td>
                                        <td>----</td>
                                        <td>----</td>
                                        <td>----</td>
                                    </tr>";
                                }
                            }
                        ?>
                </table>
            </div>
            
            <form method="post" action="#position_after_submit_order">
        
                <table class="table table-bordered table-striped">

                    <tr>
                        <th scope="col">Całkowity koszt zamówienia: </th>
                        <th scope="col"><?php echo $sum?> zł</th>
                        <input type="hidden" name="total_price" value="<?php echo $sum ?>"/>
                    </tr>
                    
                    <tr id="position_after_submit_order">

                        <th class="align-middle">Numer stolika:</th>

                        <th class="align-middle">

                            <input class="text-center" type="number" min="1" step="1" name="table_number" value="<?php 
                            if(isset($_SESSION['table_number'])){
                                 echo $_SESSION['table_number'];
                                 unset($_SESSION['table_number']);
                             }
                             ?>" placeholder="Wprowadź numer stolika"/>

                        </th>
                    </tr>
                    
                    <tr>
                        <th class="align-middle">Uwagi do zamówienia:</th>

                        <th>
                            <textarea class="form-control" name="order_comment" rows="3" maxlength="200">
                            <?php 
                            if(isset($_SESSION['order_comment'])){
                                 echo $_SESSION['order_comment'];
                                 unset($_SESSION['order_comment']);
                             }
                             ?>
                            </textarea>
                        </th>
                    </tr>
                    
                </table>

                <input name="submit_order" type="submit" value="Złóż zamówienie"/>
            </form>
            
            <?php
            if(isset($_SESSION['insertOrderMessage']))
                echo $_SESSION['insertOrderMessage'];
                unset($_SESSION['insertOrderMessage']);
            ?>
            

            <br/><br/>
            <h2>Lista potraw</h2><br/>
            
            <div class="table-wrapper-scroll-y menu_scrollbar">
            
                <table class="table table-bordered table-striped mb-0">
                    <tr class="table-active">
                        <th scope="col">Lp</th>
                        <th scope="col">Nazwa</th>
                        <th scope="col">Kategoria</th>
                        <th scope="col">Cena</th>
                        <th scope="col">Ilość</th>
                        <th scope="col">Dodanie do zamówień</th>
                    </tr>

                <?php

                    require_once "connect.php";
                    $connection=@new mysqli($host, $db_user, $db_password, $db_name);

                    if($connection->connect_error){
                        echo "Error: ".$connection->connect_errno;
                    }
                    else{
                        $result=@$connection->query("
                            SELECT d.id_dish,d.dish_name,dc.category_name as category_name,d.price
                            FROM dish as d INNER JOIN dish_category as dc ON d.id_category=dc.id_category
                            ORDER BY category_name");

                        //wyświetlenie menu
                        if($result){
                            $number=1;
                            while($data=$result->fetch_assoc()){

                                $id_dish=$data['id_dish'];
                                $dish_name=$data['dish_name'];
                                $category=$data['category_name'];
                                $price=$data['price'];
                                $position="position".$number;
                                
                                echo '<tr><form method="post" action="#'.$position.'">';
                                    
                                echo "<th scope='row' id=$position class='align-middle'>$number</th><td class='align-middle'>$dish_name</td><td class='align-middle'>$category</td><td class='align-middle'>$price zł</td>";

                                echo '<td><input type="number" min="0" step="1" value="0" name="quantity"/></td>';

                                echo '<td><input type="hidden" name="id_dish" value="'.$id_dish.'"/><input name="add_dish" type="submit" value="Dodaj do zamówienia"/></td>';

                                echo "</form></tr>";

                                $number=$number+1;
                            }
                            $result->free_result();
                        }
                        $connection->close();
                    }
                 ?>
                </table>
            </div>
            <br/><br/>
        </main>
    </div>	
</body>
</html>