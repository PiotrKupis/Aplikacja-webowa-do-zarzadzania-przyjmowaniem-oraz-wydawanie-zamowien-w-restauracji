<?php

    function randomPassword() {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }

    session_start();

    if ( !isset($_SESSION['login']) || !isset($_SESSION['position_name']) || $_SESSION['position_name']!="admin")
	{
        header('Location: index.php');
        exit();
	}

    if(isset($_POST['name'])){
        
        //sprawdzanie poprawności danych
        $ok=true;
        
        $name=$_POST['name'];
        $name=htmlentities($name,ENT_QUOTES,"UTF-8");
        
        if((strlen($name)<1) || (strlen($name)>50)){
            $ok=false;
            $_SESSION['e_name']='<span style="color:red">Długość imienia musi mieścić się między 1, a 50 znaków</span><br/>';
        }
        else if( preg_match('~[0-9]~', $name)==1 ){
            $ok=false;
            $_SESSION['e_name']='<span style="color:red">Imię nie może zawierać cyfr</span><br/>';
        }
        $_SESSION['name']=$name;
        
        
        $surname=$_POST['surname'];
        $surname=htmlentities($surname,ENT_QUOTES,"UTF-8");
        
        if((strlen($surname)<1) || (strlen($surname)>50)){
            $ok=false;
            $_SESSION['e_surname']='<span style="color:red">Długość nazwiska musi mieścić się między 1, a 50 znaków</span><br/>';
        }
        else if( preg_match('~[0-9]~', $surname)==1 ){
            $ok=false;
            $_SESSION['e_name']='<span style="color:red">Nazwisko nie może zawierać cyfr</span><br/>';
        }
        $_SESSION['surname']=$surname;
        
        
        $city=$_POST['city'];
        $city=htmlentities($city,ENT_QUOTES,"UTF-8");
        
        if((strlen($city)<1) || (strlen($city)>50)){
            $ok=false;
            $_SESSION['e_city']='<span style="color:red">Długość nazwy miasta musi mieścić się między 1, a 50 znaków</span><br/>';
        }
        else if( preg_match('~[0-9]~', $city)==1 ){
            $ok=false;
            $_SESSION['e_name']='<span style="color:red">Nazwa miasta nie może zawierać cyfr</span><br/>';
        }
        $_SESSION['city']=$city;
        
        
        $street=$_POST['street'];
        $street=htmlentities($street,ENT_QUOTES,"UTF-8");
        
        if((strlen($street)<1) || (strlen($street)>60)){
            $ok=false;
            $_SESSION['e_street']='<span style="color:red">Długość nazwy ulicy musi mieścić się między 1, a 60 znaków</span><br/>';
        }
        $_SESSION['street']=$street;
        
        
        $street_number=$_POST['street_number'];
        $street_number=htmlentities($street_number,ENT_QUOTES,"UTF-8");
        
        if((strlen($street_number)<1) || (strlen($street_number)>40)){
            $ok=false;
            $_SESSION['e_street_number']='<span style="color:red">Długość numeru domu/mieszkania musi mieścić się między 1, a 40 znaków</span><br/>';
        }
        $_SESSION['street_number']=$street_number;


        $area_code=$_POST['area_code'];
        $area_code=htmlentities($area_code,ENT_QUOTES,"UTF-8");
        
        if(strlen($area_code)==0){
            $ok=false;
            $_SESSION['e_area_code']='<span style="color:red">Pole numeru kierunkowego nie może być puste</span><br/>';
        }
        else if(!is_numeric($area_code)){
            $ok=false;
            $_SESSION['e_area_code']='<span style="color:red">Numer kierunkowy nie może zawierać innych znaków niż cyfry</span><br/>';
        }
        $_SESSION['area_code']=$area_code;
        
        
        $phone_number=$_POST['phone_number'];
        $phone_number=htmlentities($phone_number,ENT_QUOTES,"UTF-8");
        
        if(strlen($phone_number)!=9){
            $ok=false;
            $_SESSION['e_phone_number']='<span style="color:red">Numer telefonu musi zawierać 9 cyfr</span><br/>';
        }
        else if(!is_numeric($phone_number)){
            $ok=false;
            $_SESSION['e_phone_number']='<span style="color:red">Numer telefonu nie może zawierać innych znaków niż cyfry</span><br/>';
        }
        $_SESSION['phone_number']=$phone_number;
        
        
        $gross_pay=$_POST['gross_pay'];
        if($gross_pay<0){
            $ok=false;
            $_SESSION['e_gross_pay']='<span style="color:red">Pensja brutto nie może być liczbą ujemną</span><br/>';
        }
        else if(!is_numeric($gross_pay)){
            $ok=false;
            $_SESSION['e_gross_pay']='<span style="color:red">Pole pensji nie może być puste</span><br/>';
        }
        $_SESSION['gross_pay']=$gross_pay;
        $position_name=$_POST['position_name'];
        
        
        //wszystko w porządku
        if($ok){
            require_once "connect.php";
            $connection=@new mysqli($host, $db_user, $db_password, $db_name);

            if($connection->connect_error){
                $_SESSION['insertEmployeeMessage']='<span style="color:red">Błąd bazy danych, skontaktuj się z administratorem!</span>';
            }
            else{
                //pobranie id stanowiska
                $result=@$connection->query("
                    SELECT id_position 
                    FROM position 
                    WHERE position_name='".$position_name."'");
                
                if($result){
                    $data=$result->fetch_assoc();
                    $id_position=$data['id_position'];
                    
                    //dodanie pracownika
                    $result=@$connection->query("
                       INSERT INTO employee(name, surname, gross_pay) 
                       VALUES ('".$name."','".$surname."',$gross_pay)");

                     //sprawdzenie dodania
                    $result=@$connection->query("
                        SELECT id_employee 
                        FROM employee 
                        WHERE name='".$name."' AND surname='".$surname."' AND gross_pay=$gross_pay");
                    
                    if($result==TRUE){
                        $data=$result->fetch_assoc();
                        $id_employee=$data['id_employee'];
                        
                        //dodanie adresu pracownika
                        $result->free_result();
                        $result=@$connection->query("
                            INSERT INTO address(id_employee, city, street, street_number) 
                            VALUES ($id_employee, '".$city."','".$street."','".$street_number."')");

                        //sprawdzenie dodania
                        $result=@$connection->query("
                            SELECT id_address
                            FROM address 
                            WHERE id_employee=$id_employee AND city='".$city."' AND street='".$street."' AND street_number='".$street_number."'");
                        
                        if($result==TRUE){
                            
                            //dodanie telefonu pracownika
                            $result->free_result();
                            $result=@$connection->query("
                                INSERT INTO phone_number(phone_number, id_employee, area_code) 
                                VALUES ('".$phone_number."',$id_employee,$area_code)");
                            
                            //sprawdzenie dodania
                            $result=@$connection->query("
                                SELECT *
                                FROM phone_number 
                                WHERE phone_number='".$phone_number."' AND id_employee=$id_employee AND area_code=$area_code");
                            
                            
                            if($result==TRUE){
                                //dodawanie konta
                                $password=randomPassword();
                                $hashed_password=password_hash($password, PASSWORD_DEFAULT);
                                $login=$surname.$id_employee;
                                
                                $result->free_result();
                                $result=@$connection->query("
                                    INSERT INTO account (id_employee, id_position, login, password, online) 
                                    VALUES ($id_employee,$id_position,'".$login."','".$hashed_password."',0)");
                                
                                //sprawdzenie dodania
                                $result=@$connection->query("
                                    SELECT *
                                    FROM account 
                                    WHERE id_employee=$id_employee AND id_position=$id_position AND login='".$login."' AND password='".$hashed_password."' AND online=0");

                                
                                if($result==TRUE){
                                
                                    $_SESSION['insertEmployeeMessage']='<span style="color:green; font-size: 26px">Pomyślnie dodano pracownika!<br/>Login: '.$login.'<br/> Hasło: '.$password.'</span><br/><br/><br/>';
                                    
                                    unset($_SESSION['name']);
                                    unset($_SESSION['surname']);
                                    unset($_SESSION['city']);
                                    unset($_SESSION['street']);
                                    unset($_SESSION['street_number']);
                                    unset($_SESSION['area_code']);
                                    unset($_SESSION['phone_number']);
                                    unset($_SESSION['gross_pay']);
                                    
                                    $result->free_result();
                                }
                                else{
                                    $_SESSION['insertEmployeeMessage']='<span style="color:red">Błąd bazy danych, skontaktuj się z administratorem!</span>';
                                }
                            }
                            else{
                                 $_SESSION['insertEmployeeMessage']='<span style="color:red">Błąd bazy danych, skontaktuj się z administratorem!</span>';
                            }  
                        }
                        else{
                            $_SESSION['insertEmployeeMessage']='<span style="color:red">Błąd bazy danych, skontaktuj się z administratorem!</span>';
                        } 
                    }
                    else{
                        $_SESSION['insertEmployeeMessage']='<span style="color:red">Błąd bazy danych, skontaktuj się z administratorem!</span>';
                    }    
                }
                else{
                    $_SESSION['insertEmployeeMessage']='<span style="color:red">Błąd bazy danych, skontaktuj się z administratorem!</span>';
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
	<title>Dodawanie nowego pracownika</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	
	<meta name="description" content="Strona do dodawnia nowych pracowników">
	<meta name="author" content="Piotr Kupis">
	
	<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
	<link href="main.css" rel="stylesheet" type="text/css"/>
</head>

<body>

	<header>
        <nav class="navbar navbar-expand-sm navbar-dark bg-dark justify-content-center py-4"> 
            <ul class="navbar-nav">
                <li class="nav-item px-4">
                    <a class="nav-link" href="insert_new_employee.php"><h3> Dodawanie pracowników </h3></a>
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
    
        <br/><br/><h1>Dodawanie nowego pracownika</h1><br/>
        
        <?php
            if(isset($_SESSION['insertEmployeeMessage']))
                echo $_SESSION['insertEmployeeMessage'];
                unset($_SESSION['insertEmployeeMessage']);
        ?>
        
        <main class="col-sm-8 offset-sm-2">
            
                <form method="post">
                    
                    <label for="name">Imię</label>
                    <input type="text" value="<?php 
                    if(isset($_SESSION['name'])){
                        echo $_SESSION['name'];
                        unset($_SESSION['name']);
                    }
                    ?>" name="name" placeholder="Wprowadź imię pracownika"/><br/>

                    <?php
                        if(isset($_SESSION['e_name'])){
                            echo $_SESSION['e_name'];
                            unset($_SESSION['e_name']);
                        }     
                    ?>


                    <label for="surname">Nazwisko</label>
                    <input type="text" value="<?php 
                     if(isset($_SESSION['surname'])){
                         echo $_SESSION['surname'];
                        unset($_SESSION['surname']);
                     }
                     ?>" name="surname" placeholder="Wprowadź nazwisko pracownika"/><br/>

                    <?php
                        if(isset($_SESSION['e_surname'])){
                            echo $_SESSION['e_surname'];
                            unset($_SESSION['e_surname']);
                        }
                    ?>

                    
                    <label for="gross_pay">Pensja brutto</label>
                    <input type="number" min="0" step="0.01" value="<?php 
                    if(isset($_SESSION['gross_pay'])){
                         echo $_SESSION['gross_pay'];
                         unset($_SESSION['gross_pay']);
                     }
                     ?>" name="gross_pay" placeholder="Wprowadź pensje brutto pracownika"/><br/>

                    <?php
                        if(isset($_SESSION['e_gross_pay'])){
                            echo $_SESSION['e_gross_pay'];
                            unset($_SESSION['e_gross_pay']);
                        }   
                    ?>


                    <label for="position_name">Stanowisko</label>
                    <select name="position_name">
                    <?php
                        require_once "connect.php";
                        $connection=@new mysqli($host, $db_user, $db_password, $db_name);

                        if($connection->connect_error){
                            echo "Error: ".$connection->connect_errno;
                        }
                        else{
                            $result=@$connection->query("SELECT position_name FROM position");
                            if($result){
                                
                                while($position_data=$result->fetch_assoc()){
                                    $position_name=$position_data['position_name'];
                                    echo "<option value=$position_name>$position_name</option>";
                                }
                                $result->free_result();
                            }
                            $connection->close();
                        }
                    ?>
                    </select>
                    
                    
                    <br/><br/><h2>Dane kontaktowe:</h2>
                    
                    <label for="city">Miasto</label>
                    <input type="text" value="<?php 
                    if(isset($_SESSION['city'])){
                        echo $_SESSION['city'];
                        unset($_SESSION['city']);
                    }
                    ?>" name="city" placeholder="Wprowadź miasto"/><br/>

                    <?php
                        if(isset($_SESSION['e_city'])){
                            echo $_SESSION['e_city'];
                            unset($_SESSION['e_city']);
                        }            
                    ?>


                    <label for="street">Ulica</label>
                    <input type="text" value="<?php 
                    if(isset($_SESSION['street'])){
                        echo $_SESSION['street'];
                        unset($_SESSION['street']);
                    }
                    ?>" name="street" placeholder="Wprowadź ulicę"/><br/>

                    <?php
                        if(isset($_SESSION['e_street'])){
                            echo $_SESSION['e_street'];
                            unset($_SESSION['e_street']);
                        }      
                    ?>


                    <label for="street_number">Nr domu/mieszkania</label>
                    <input type="text" value="<?php 
                    if(isset($_SESSION['street_number'])){
                        echo $_SESSION['street_number'];
                        unset($_SESSION['street_number']);
                     }
                     ?>" name="street_number" placeholder="Wprowadź nr domu/mieszkania"/><br/>

                    <?php
                        if(isset($_SESSION['e_street_number'])){
                            echo $_SESSION['e_street_number'];
                            unset($_SESSION['e_street_number']);
                        }
                    ?>


                    <label for="phone_number">Nr kierunkowy telefonu</label>
                    <input type="number" min="0" step="1" value="<?php 
                    if(isset($_SESSION['area_code'])){
                        echo $_SESSION['area_code'];
                        unset($_SESSION['area_code']);
                     }
                     ?>" name="area_code" placeholder="Wprowadź nr kierunkowy"/><br/>

                    <?php
                        if(isset($_SESSION['e_area_code'])){
                            echo $_SESSION['e_area_code'];
                            unset($_SESSION['e_area_code']);
                        }    
                    ?>
                    
                    
                    <label for="phone_number">Nr telefonu</label>
                    <input type="text" value="<?php 
                    if(isset($_SESSION['phone_number'])){
                        echo $_SESSION['phone_number'];
                        unset($_SESSION['phone_number']);
                     }
                     ?>" name="phone_number" placeholder="Wprowadź numer telefonu"/><br/>

                    <?php
                        if(isset($_SESSION['e_phone_number'])){
                            echo $_SESSION['e_phone_number'];
                            unset($_SESSION['e_phone_number']);
                        }    
                    ?>

                    <br/><input type="submit" value="Dodaj pracownika"/><br/><br/>
                </form>
                <br/><br/>
        </main>
    </div>	
</body>
</html>