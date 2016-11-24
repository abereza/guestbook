<?php
error_reporting(E_ALL);
include 'authCtrl.php';
include 'dbCtrl.php';
?>

<html>
    <head>
        <meta charset="UTF-8">
        <title>Гостевая книга</title>
    </head>
<body>
    <!--- разбор действий --->
    <?php            
        connectDB();
        session_start();    
        
        if (isset($_GET["act"])) {
            $action = $_GET["act"];
        }
        
        switch ($action) {
            case "add":
                if (isset($_POST["msgText"]) && $_POST["msgText"]) {
                    addNewRecord($_POST["msgText"]);
                }
                header('Location: http://'.$_SERVER['HTTP_HOST'].'/guestbook/index.php', true);
                break;
                
            case "auth":
            case "regist":
                if (isset($_SESSION["warrMsg"]) && $_SESSION["warrMsg"]) {
                    $msg = $_SESSION["warrMsg"];
                }
                
                $_SESSION["warrMsg"] = NULL;
                unset($_SESSION["warrMsg"]);
                
                if ($action == "auth") {
                    authForm($msg);
                }
                else {
                    registForm($msg);
                }    

                return;
                
            case "useradd":
                if (!addNewUser()) {
                    header('Location: http://'.$_SERVER['HTTP_HOST'].'/guestbook/index.php?act=regist', true);
                    return;
                }

                header('Location: http://'.$_SERVER['HTTP_HOST'].'/guestbook/index.php', true);
                break;
                
            case "userin":
                if (!authUser()) {
                    header('Location: http://'.$_SERVER['HTTP_HOST'].'/guestbook/index.php?act=auth', true);
                    return;
                }
                
                header('Location: http://'.$_SERVER['HTTP_HOST'].'/guestbook/index.php', true);
                break;
            
            case "edit":
                if (isset($_SESSION['editMsg']) && ($_SESSION['editMsg'] > 0)) {
                    $_SESSION['editFlag'] = $_SESSION['editMsg'];
                }
                //header('Location: http://'.$_SERVER['HTTP_HOST'].'/guestbook/index.php', true);
                break; 
                
            case "update":
                if (isset($_SESSION['editMsg']) && ($_SESSION['editMsg'] > 0) &&
                    isset($_POST["msgText"]) && $_POST["msgText"]) {
                        updateRecord($_SESSION['editMsg'], $_POST["msgText"]);
                }
                $_SESSION['editMsg'] = NULL;
                unset($_SESSION['editMsg']);
                
                header('Location: http://'.$_SERVER['HTTP_HOST'].'/guestbook/index.php', true);
                break; 
             
            case "quit":
                forgetUser();
                header('Location: http://'.$_SERVER['HTTP_HOST'].'/guestbook/index.php', true);
                break;
        }
    ?>
    
    
    <h4 aling="right">
        <?php 
            checkUserSatus();
        ?>
    </h4>
        
    <div align="center">
        <h2>Гостевая книга</h2>
    </div>
    
    <!--область вывода записей-->
    <div aling="middle">
       <?php
            if (isset($_GET["page"]) && $_GET["page"]) {
                showRecords($_GET["page"]);
            }
            else {
                showRecords(1);
            }
            
            if (!isset($_SESSION['editFlag'])) {
                echo <<< EOT
                <form action="index.php?act=add" method="post">
                <p><b>Оставьте ваш отзыв</b></p>
                <p><textarea rows="10" cols="75" name="msgText"></textarea></p>
                <p><input type="submit"></p>
                </form>
EOT;
            }
       ?>
        
        
    </div>
</body>
</html>