<?php
    session_start();

    if( !isset($_SESSION['login']) || !isset($_SESSION['position_name']) || $_SESSION['position_name']!="kelner" )
	{
        header('Location: index.php');
        exit();
	}
    
    if(isset($_SESSION['dishes_id'])){
        unset($_SESSION['quantities']);
        unset($_SESSION['dishes_id']);
    }

    //zmiana statusu na dostarczone
    if(isset($_POST['delivered'])){

        require_once "connect.php";
        $connection=@new mysqli($host, $db_user, $db_password, $db_name);

        if($connection->connect_error){
            echo "Error: ".$connection->connect_errno;
        }
        else{
            $id_order=$_POST['id_order'];           
            
            $result=@$connection->query("
                UPDATE `order` 
                SET `id_status`=3 
                WHERE id_order=$id_order");
            
            $connection->close();
        }
    }

    //zmiana statusu na zapłacone
    if(isset($_POST['paid'])){

        require_once "connect.php";
        $connection=@new mysqli($host, $db_user, $db_password, $db_name);

        if($connection->connect_error){
            echo "Error: ".$connection->connect_errno;
        }
        else{
            $id_order=$_POST['id_order'];     
            $id_payment=$_POST['payment_type'];
            
            //pobieranie daty złozenia zamówienia
            $result=@$connection->query("
                SELECT order_date 
                FROM `order` 
                WHERE id_order=$id_order");
                
            if($result){
                $data=$result->fetch_assoc();
                $order_date=$data['order_date'];
                $result->free_result();
                
                $date=date('Y-m-d H:i:s');
                $interval = date_diff(date_create($order_date),date_create($date)); 

                $hours=$interval->h;
                $minutes=$interval->i;
                $second=$interval->s;
                $service_time=date('H:i:s', strtotime($hours.":".$minutes.":".$second));
                        
                $result=@$connection->query("
                    UPDATE `order` 
                    SET `id_payment`=$id_payment, `id_status`=4, `service_time`='".$service_time."' 
                    WHERE id_order=$id_order");
           }
            $connection->close();
        }
    }
?>

<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8">
	<title>Zmiana statusu zamówienia</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	
	<meta name="description" content="Strona do zmiany statusu zamówienia przez kelnera">
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
            
            <br/><h1>Obecne zamówienia</h1><br/><br/>

                    <table class="table table-bordered table-striped mb-0">
                        <tr class="table-active">
                            <th scope="col" class="align-middle">Lp</th>
                            <th scope="col" class="align-middle">Potrawa</th>
                            <th scope="col" class="align-middle">Ilość</th>
                            <th scope="col" class="align-middle">Uwagi do zamówienia</th>
                            <th scope="col" class="align-middle">Kwota do zapłaty</th>
                            <th scope="col" class="align-middle">Nr stolika</th>
                            <th scope="col" class="align-middle">Zmiana statusu</th>
                        </tr>

                    <?php

                    require_once "connect.php";
                    $connection=@new mysqli($host, $db_user, $db_password, $db_name);

                    if($connection->connect_error){
                        echo "Error: ".$connection->connect_errno;
                    }
                    else{
                        $order_result=@$connection->query("
                        SELECT o.id_order as id_order, o.table_number as table_number, o.comment as comment, s.status as status, o.value as total_price 
                        FROM `order` as o 
                            INNER JOIN `status` as s ON o.id_status=s.id_status
                        WHERE s.status='Przygotowane' OR s.status='Dostarczone'
                        ORDER BY s.status DESC, table_number
                        ");

                        //wyświetlenie zamówień
                        if($order_result){
                            
                            $number=1;
                            while($order_data=$order_result->fetch_assoc()){

                                $id_order=$order_data['id_order'];
                                $table_number=$order_data['table_number'];
                                $comment=$order_data['comment'];
                                $order_position="order_position".$number;
                                $order_status=$order_data['status'];
                                $total_price=$order_data['total_price'];

                                //pobieranie potraw i ich ilośći z konkretnego zamówienia
                                $order_content="";
                                $order_quantities="";
                                
                                $order_content_result=@$connection->query("
                                    SELECT d.dish_name as name,od.quantity as quantity
                                    FROM `order_dishes` as od 
                                        INNER JOIN `order` as o ON od.id_order=o.id_order
                                        INNER JOIN `dish` as d on od.id_dish=d.id_dish
                                     WHERE o.id_order=$id_order");
                                
                                if($order_content_result){
                                    
                                    while($order_content_data=$order_content_result->fetch_assoc()){
                                        
                                        $order_content.=$order_content_data['name']."<br/>";
                                        $order_quantities.=$order_content_data['quantity']."<br/>";
                                    }
                                    
                                    echo '<tr><form method="post" action="#'.$order_position.'">';
                                    
                                    echo "
                                    <th scope='row' class='align-middle' id=$order_position>$number</th>
                                        <td class='align-middle text-nowrap'>$order_content</td>
                                        <td class='align-middle'>$order_quantities</td>
                                        <td class='align-middle'>$comment</td>
                                        <td class='align-middle'>$total_price zł</td>
                                        <td class='align-middle'>$table_number</td>";

                                    echo '<td class="align-middle"><input type="hidden" name="id_order" value="'.$id_order.'"/>';
                                    
                                    
                                    //wyświetlenie odpowiedniego przycisku do zmiany statusu zamówienia
                                    if($order_status=='Przygotowane'){
                                        echo '<input name="delivered" type="submit" value="Dostarczone"/></td>';
                                    }
                                    else{
                                        
                                        $payment_type_result=@$connection->query("
                                            SELECT id_payment, payment_type as type
                                            FROM `payment`");
                                        
                                        if($payment_type_result){
                                            echo '<input name="paid" type="submit" value="Zapłacone"/>';
                                            echo '<select name="payment_type">';
                                            while($payment_type_data=$payment_type_result->fetch_assoc()){
                                                
                                                $payment_type=$payment_type_data['type'];
                                                $id_payment=$payment_type_data['id_payment'];
                                                echo "<option value=$id_payment>$payment_type</option>";
                                            }
                                            echo '</select></td>';
                                            $payment_type_result->free_result();
                                        }
                                    }
                                    
                                    $number=$number+1;
                                    $order_content_result->free_result();
                                }
                                echo "</form></tr>";
                            }
                            //brak zamówień do zmiany
                            if($number==1){
                                echo "
                                <tr>
                                    <td>----</td>
                                    <td>----</td>
                                    <td>----</td>
                                    <td>------------------</td>
                                    <td>---------</td>
                                    <td>----</td>
                                    <td>----------</td>
                                </tr>";
                            }
                            $order_result->free_result();
                        }
                        $connection->close();
                    }
                 ?>
                </table>

            <br/><br/>
        </main>
    </div>	
</body>
</html>