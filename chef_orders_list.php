<?php
    session_start();

    if( !isset($_SESSION['login']) || !isset($_SESSION['position_name']) || $_SESSION['position_name']!="kucharz" )
	{
        header('Location: index.php');
        exit();
	}
    
    
    //zmiana statusu zamówienia
    if(isset($_POST['change_status'])){

        require_once "connect.php";
        
        $connection=@new mysqli($host, $db_user, $db_password, $db_name);

        if($connection->connect_error){
            echo "Error: ".$connection->connect_errno;
        }
        else{
            $id_order=$_POST['id_order'];           
            
            $result=@$connection->query("
                UPDATE `order` 
                SET `id_status`=2 
                WHERE id_order=$id_order");
            
            $connection->close();
        }
    }

?>

<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8">
	<title>Lista zamówień do przygotowania</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	
	<meta name="description" content="Strona zmiany statusu zamówienia przez kucharzy">
	<meta name="author" content="Piotr Kupis">
	
	<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
	<link href="main.css" rel="stylesheet" type="text/css"/>
</head>

<body >

	<header>
        <nav class="navbar navbar-expand-sm navbar-dark bg-dark justify-content-center py-4"> 
            <ul class="navbar-nav">
                <li class="nav-item px-4">
                    <a class="nav-link" href="chef_orders_list.php"><h3> Lista zamówień do przygotowania </h3></a>
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
            
            <br/><h1>Lista zamówień do przygotowania</h1><br/><br/>
            
                <table class="table table-bordered table-striped mb-0">
                    <tr class="table-active">
                        <th scope="col">Lp</th>
                        <th scope="col">Potrawa</th>
                        <th scope="col">Ilość</th>
                        <th scope="col">Uwagi do zamówienia</th>
                        <th scope="col">Nr stolika</th>
                        <th scope="col">Zmiana statusu</th>
                    </tr>

                <?php

                    require_once "connect.php";
                    $connection=@new mysqli($host, $db_user, $db_password, $db_name);

                    if($connection->connect_error){
                        echo "Error: ".$connection->connect_errno;
                    }
                    else{
                        $order_result=@$connection->query("
                        SELECT o.id_order as id_order, o.table_number as table_number, o.comment as comment 
                        FROM `order` as o 
                            INNER JOIN `status` as s ON o.id_status=s.id_status
                        WHERE s.status='Przyjęte do realizacji'
                        ORDER BY o.order_date");

                        //wyświetlenie zamówień do przygotawania
                        if($order_result){
                            $number=1;
                            while($order_data=$order_result->fetch_assoc()){

                                $id_order=$order_data['id_order'];
                                $table_number=$order_data['table_number'];
                                $comment=$order_data['comment'];
                                $order_position="order_position".$number;

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
                                    $order_content_result->free_result();
                                    
                                    echo '<tr><form method="post" action="#'.$order_position.'">';
                                    
                                    echo "
                                    <th scope='row' class='align-middle' id=$order_position>$number</th>
                                        <td class='align-middle text-nowrap'>$order_content</td>
                                        <td class='align-middle'>$order_quantities</td>
                                        <td class='align-middle'>$comment</td>
                                        <td class='align-middle'>$table_number</td>";

                                    echo '
                                    <td class="align-middle">
                                        <input type="hidden" name="id_order" value="'.$id_order.'"/>
                                        <input name="change_status" type="submit" value="Gotowe do odbioru"/>
                                    </td>';

                                    echo "</form></tr>";

                                    $number=$number+1;
                                }
                            }
                            //brak zamówień do zmiany
                            if($number==1){
                                echo "
                                <tr>
                                    <td>----</td>
                                    <td>----</td>
                                    <td>----</td>
                                    <td>------------------</td>
                                    <td>----</td>
                                    <td>----</td>
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