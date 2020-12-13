<?php 
    session_start();

    if(!isset($_POST['login']) || !isset($_POST['password'])){
        header('Location: index.php');
        exit();
    }    

    require_once "connect.php";
    $connection=@new mysqli($host, $db_user, $db_password, $db_name);
    if($connection->connect_error){
        echo "Error: ".$connection->connect_errno;
    }
    else{
        $login=$_POST['login'];
        $password=$_POST['password'];
        
        $login=htmlentities($login,ENT_QUOTES,"UTF-8");
        $result=@$connection->query("SELECT * FROM account WHERE login='".$login."'");
        
        if($result){
            if($result->num_rows==1){
                $data=$result->fetch_assoc();
                
                if(password_verify($password,$data['password'])){
                    
                    $_SESSION['id_account']=$data['id_account'];
                    $id_position=$data['id_position'];
                    
                    $result->free_result();
                    $result=@$connection->query("SELECT position_name FROM position WHERE id_position=$id_position");
                    
                    if($result){
                        $position_data=$result->fetch_assoc();
                        
                        if($result->num_rows==1){
                            $_SESSION['position_name']=$position_data['position_name'];
                            $_SESSION['login']=true;
                            
                            unset($_SESSION['error']);
                            $result->free_result();
                            
                            //ustawienie flagi konta na zalogowany
                            $result=@$connection->query("UPDATE account SET online=1 WHERE id_account=".$_SESSION['id_account']);
                            if($result){
                                if($_SESSION['position_name']=="admin"){
                                    header('Location: insert_new_employee.php');
                                }
                                else if($_SESSION['position_name']=="kelner"){
                                    header('Location: insert_new_order.php');
                                }
                                else if($_SESSION['position_name']=="kucharz"){
                                    header('Location: chef_orders_list.php');
                                }
                            }
                            else{
                                $_SESSION['error']='<span style="color:red">Błąd bazy danych!</span>';
                                header('Location: index.php');
                            }
                        }
                        else{
                            $_SESSION['error']='<span style="color:red">Błąd bazy danych!</span>';
                            header('Location: index.php');
                        }
                    }
                    else{
                        $_SESSION['error']='<span style="color:red">Błąd bazy danych!</span>';
                        header('Location: index.php');
                    }
                }
                else{
                    $_SESSION['error']='<span style="color:red">Nieprawidłowy login lub hasło!</span>';
                    header('Location: index.php');
                }  
            }
            else{
                $_SESSION['error']='<span style="color:red">Nieprawidłowy login lub hasło!</span>';
                header('Location: index.php');
            }
        }
        $connection->close(); 
    }
?>