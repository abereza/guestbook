<?php

const MAX_RECORDS_ON_PAGE = 5; 
const ULOGIN = 0;
const UID = 1;
const UIP = 2;
const UDATE = 3;
const UTEXT = 4;

function connectDB() 
{
    if (!($dbconn = pg_connect("host=localhost dbname=guestbook user=admin password=admin"))) {
        echo 'Could not connect: ' . pg_last_error();
    }
}

function recordsCount()
{
    $sQuery = "SELECT COUNT(*) FROM \"RECORDS\";";
    
    if (($result = pg_query($sQuery)) === FALSE) {
        echo '[recordsCount]Ошибка запроса: ' . pg_last_error();
        return 0;
    }

    return pg_fetch_result($result, 0, 0);
}

function showRecords($pageNumber)
{
    if (!($rowCount = recordsCount())) {
        echo '<br>В гостевой книге нет записей. Станьте the first one!<br>';
        return;
    }
    
    $pageCount = ($rowCount%MAX_RECORDS_ON_PAGE)?
                 ((int)($rowCount/MAX_RECORDS_ON_PAGE) + 1):
                 ((int)($rowCount/MAX_RECORDS_ON_PAGE));
    
    if ($pageNumber > $pageCount) {
        $pageNumber = $pageCount;
    } 
    elseif ($pageNumber < 1) {
        $pageNumber = 1;
    }
    
    $offset = ($pageNumber - 1) * MAX_RECORDS_ON_PAGE;
    
    $sQuery = "SELECT b.user_login, a.id, a.user_ip, a.date_r, a.content "
            . "FROM \"RECORDS\" AS a LEFT JOIN \"USERS\" AS b "
            . "ON a.user_id = b.id "
            . "ORDER BY a.id DESC "
            . "LIMIT ".MAX_RECORDS_ON_PAGE." OFFSET $offset;";
        
    if (($result = pg_query($sQuery)) === FALSE) {
        echo '[showRecords]Ошибка запроса: ' . pg_last_error();
        return;
    }
    
    printRecords($pageNumber, $result, $pageCount);    
}

function printRecords($pageNumber, $table, $pageCount)
{
    while ($row = pg_fetch_row($table)) {
        if (isset($_SESSION['editFlag']) && $_SESSION['editFlag'] == $row[UID]) {
            echo <<< EOT
                <form action="index.php?act=update" method="post">
                <p>Редактирование сообщения</p>
                <p><textarea rows="10" cols="75" name="msgText">{$row[UTEXT]}</textarea></p>
                <p><input type="submit"></p>
                </form>
EOT;
                $_SESSION['editMsg'] = $row[UID];
                $_SESSION['editFlag'] = NULL;
                unset($_SESSION['editFlag']);
        }
        else {
            printSimpleMsg($row);
        }
    }

    if ($pageCount == 1) {
        return; 
    }
    
    // << <
    if ($pageNumber != 1) {
        echo '<a href="index.php?page=1"><b><<&nbsp</b></a>';
        echo '<a href="index.php?page='.($pageNumber-1).'"><b>&nbsp<&nbsp</b></a>';
    }
    else {
        echo '<b><<&nbsp&nbsp<&nbsp</b>';
    }
    
    // > >>
    if ($pageNumber < $pageCount) {
        echo '<a href="index.php?page='.($pageNumber+1).'"><b>&nbsp>&nbsp</b></a>';
        echo '<a href="index.php?page='.$pageCount.'"><b>&nbsp>> </b></a>';
    }
    else {
        echo '<b>&nbsp>&nbsp&nbsp>> </b>';
    }
}


function printSimpleMsg($row)
{
    if (!$row[ULOGIN]) {
        echo '<p><b>Гость</b>';
    }
    else {
        echo '<p><b>'.$row[ULOGIN].'</b>';
    }
        
    echo ', от '.$row[UDATE].', IP['.$row[UIP].']';
       
    if ($row[ULOGIN] &&    
        trim($row[ULOGIN]) == $_SESSION['c_login']) {
            $bonus = "";
            if (isset($_GET["page"]) && ($_GET["page"] > 1)) {
                $bonus = "&page=".$_GET["page"];
            }
            echo ' <a href="index.php?act=edit'.$bonus.'">Редактировать</a>';
            $_SESSION['editMsg'] = $row[UID];
    }
        
    echo '<br><font color="gray">'.$row[UTEXT].'</font></p>';
    echo '<hr align="left" width="50%"><br>';
}



function getUserID($userName)
{
    $sQuery = "SELECT id FROM \"USERS\" WHERE USER_LOGIN='$userName';";
    
    if (($result = pg_query($sQuery)) === FALSE) {
        echo '[getUserID]Ошибка запроса: ' . pg_last_error();
        return -1;
    }

    return pg_fetch_result($result, 0, 0);
}

function addNewRecord($msgText)
{
    $userID = -1;
    if (isUserAuth()) {
        $userID = getUserID($_SESSION['c_login']);
    }
    
    if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']) {
        $userIP = $_SERVER['REMOTE_ADDR'];
    }
    
    $sDate = date(DATE_RFC822);
    
    $sQuery = "INSERT INTO \"RECORDS\" (user_id, user_ip, date_r, content) " 
            . " VALUES($userID, '$userIP', '$sDate', '$msgText');";
      
    if ( pg_query($sQuery) == FALSE) {
        echo '[addNewRecord]Ошибка запроса: ' . pg_last_error();
    }
}


function updateRecord($msgID, $msgText)
{
    if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']) {
        $userIP = $_SERVER['REMOTE_ADDR'];
    }
    
    $sQuery = "UPDATE \"RECORDS\" SET user_ip='$userIP', content='$msgText' " 
            . " WHERE id=$msgID;";
      
    if ( pg_query($sQuery) == FALSE) {
        echo '[updateRecord]Ошибка запроса: ' . pg_last_error();
    }
}

function addUserToDB($ulogin, $upass)
{
    $sQuery = "INSERT INTO \"USERS\" (user_login, user_pass) "
            . " VALUES('".$ulogin."','".md5($upass)."');";
    
    if (pg_query($sQuery) == FALSE) {
        echo '[addUserToDB]Ошибка запроса: ' . pg_last_error();
    }
}

function getUserPass($ulogin)
{
    $sQuery = "SELECT user_pass FROM \"USERS\" WHERE user_login='".$ulogin."';";
    
    if (($result = pg_query($sQuery)) === FALSE) {
        echo '[getUserPass]Ошибка запроса: ' . pg_last_error();
        return FALSE;
    }
    
    return pg_fetch_result($result, 0, 0);
}

function isLoginUniqe($ulogin)
{
    if (getUserPass($ulogin) != FALSE) {
        return FALSE;
    }
    
    return TRUE;
}


function checkUserPass($ulogin, $upass)
{
    $cache = md5($upass);
    $bd_cache = getUserPass($ulogin);
    if ($cache == $bd_cache) {
        return TRUE;
    }
    
    return FALSE;
}


