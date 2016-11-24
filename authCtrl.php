<?php

function isUserAuth()
{
    if(isset($_SESSION['c_login']) && $_SESSION['c_login']) {
        return true;
    }
    
    return false;
}

function checkUserSatus()
{
    if (isUserAuth()) {
        $username = $_SESSION['c_login'];
        echo '<p>Вы вошли как <b>'.$username.'</b>&nbsp&nbsp<a href="index.php?act=quit">Выход</a></p>';
    } else {
        echo '<a href="index.php?act=auth">Авторизоваться</a>&nbsp&nbsp';
        echo '<a href="index.php?act=regist">Регистрация</a>';
    }
}

function rememberUser($ulogin)
{
    $_SESSION['c_login'] = $ulogin;
}

function forgetUser()
{
    if (isset( $_SESSION['c_login'])) {
         $_SESSION['c_login'] - FALSE;
         unset($_SESSION['c_login']);
    }
}

function authForm($warrMsg)
{
    echo '<p><b><a href="index.php">Назад</a></b></p>';
    
    if ($warrMsg) {
        echo '<p><b><font color="red">'.$warrMsg.'</font></b></p>';
    }
        
    echo '<form action="index.php?act=userin" method="post">
            <p><b>Авторизация</b></p>
            <p>Логин: <input type="text" name="ulogin"></p>
            <p>Пароль: <input type="password" name="upass"></p>
            <p><input type="submit"></p>
        </form>';
}

function authUser()
{
    //login
    if (isset($_POST['ulogin']) && $_POST['ulogin']) {
        $ulogin = $_POST['ulogin'];
    }
    else {
        $_SESSION['warrMsg'] = "Укажите логин";
        return FALSE;
    }
    
    //pass
    if (isset($_POST['upass']) && $_POST['upass']) {
        $upass = $_POST['upass'];
    }
    else {
        $_SESSION['warrMsg'] = "Укажите пароль";
        return FALSE;
    }
    
    if (!checkUserPass($ulogin, $upass)) {
        $_SESSION['warrMsg'] = "Неверный логин или пароль";
        return FALSE;
    }
    
    rememberUser($ulogin);
    
    return TRUE;
}


function registForm($warrMsg)
{
    echo '<p><b><a href="index.php">Назад</a></b></p>';
    
    if ($warrMsg) {
        echo '<p><b><font color="red">'.$warrMsg.'</font></b></p>';
    }

    echo '<form action="index.php?act=useradd" method="post">
            <p><b>Регистрация</b></p>
            <p>Логин: <input type="text" name="ulogin"></p>
            <p>Пароль: <input type="password" name="upass"></p>
            <p>Пароль еще раз: <input type="password" name="upass_two"></p>
            <p><input type="submit"></p>
        </form>';
}

function  addNewUser()
{
    //login
    if (isset($_POST['ulogin']) && $_POST['ulogin']) {
        $ulogin = $_POST['ulogin'];

        if (!isLoginUniqe($ulogin)) {
            $_SESSION['warrMsg'] = "Этот логин уже занят";
            return FALSE;
        }
    }
    else {
        $_SESSION['warrMsg'] = "Укажите логин";
        return FALSE;
    }
    
    //pass
    if (isset($_POST['upass']) && $_POST['upass']) {
        $upass = $_POST['upass'];
    }
    else {
        $_SESSION['warrMsg'] = "Укажите пароль";
        return FALSE;
    }
    
    //pass two
    if (isset($_POST['upass_two']) && $_POST['upass_two']) {
        if ($upass != $_POST['upass_two']) {
            $_SESSION['warrMsg'] = "Пароли не совпадают";
            return FALSE;
        }
    }
    else {
        $_SESSION['warrMsg'] = "Подтвердите пароль";
        return FALSE;
    }

    addUserToDB($ulogin, $upass);
    
    rememberUser($ulogin);
    
    return TRUE;
}
