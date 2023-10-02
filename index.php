<?php

 require_once 'appcon.php'; 
 require_once 'funcs.php'; 
 require_once 'medoo.php'; 
 require_once 'db.php'; 




$login_failed = false; 

if(getGetVar('op') == 'logout') 
{   
    session_unset(); 
    session_destroy(); 
    header('Location: index.php'); 
}


if (!isset($_SESSION['user'])) 
{ 
    $user = getPostVar('user'); 
    $pass = getPostVar('pass'); 


    if($user != '' || $pass != '') 
    { 



        $users = $database->select( "users", "*", array( "AND" => array( "user" => $user, "pass" => md5($pass) ) ) ); 


        if(count($users) == 1) 
        { 
            $_SESSION["user"] = $user;
            $_SESSION["perm"] = $users[0]["perm"]; 
            $_SESSION["uid"] = $users[0]["id"]; 

            header('Location: index.php'); 
        } 
        else 
        { 
            $login_failed = true; 
            session_unset(); 
            session_destroy(); 
        } 
    } 
} 
 


$mobile = false; 
if (IsMobile()) 
    $mobile = true; 

echo '<!DOCTYPE html>'; 
echo '<html>'; 
echo '<head>'; 
echo '<meta charset="utf-8">'; 
echo '<link rel="stylesheet" type="text/css" media="all"  href="style.css" />'; 
echo '<script src="scripts.js"></script>'; 
echo '<title>'.$app['client_name'].'</title>'; 
echo '<meta name="description" content="Professional Frontend framework that makes building websites easier than ever.">'; 
echo '<meta name="viewport" content="width=device-width, initial-scale=1">'; 
echo '<meta http-equiv="cache-control" content="max-age=0" />'; 
echo '<link rel="shortcut icon" type="image/x-icon" href="images/site.ico"/>'; 
echo '</head>'; 
echo '<body>'; 



if (!isset($_SESSION['user'])) 
{ 
    echo '<div class="site-header">'; 
    echo '<center>'; 
    echo '<div class="cell">'; 
    echo '<h1>'.$app['client_name'].'</h1>'; 
    echo '</div>'; 
    echo '</center>'; 
    echo '<center>'; 
    echo '<div class="cell">'; 
    echo '<h3>'; 

    if($login_failed) 
        echo "<h2>Eroare (".getUserIP().")</h2>"; 
    else 
        echo "<h2>Va rugam sa va autentificati.</h2>"; 

    echo '</h3>'; 
    echo '</div>'; 
    echo '</center>'; 
    echo '</div>'; 
    echo '<center>'; 
    //echo '<form action="index.php?e=aparate" method="post" accept-charset="windows-1252">'; 
    echo '<form action="index.php?e=aparate" method="post">'; 
    echo 'User<br>'; 
    echo '<input name="user" class="input-lg" type="text" placeholder="User" /><br>'; 
    //echo '<input name="user" type="text"/><br>'; 
    echo 'Parola<br>'; 
    echo '<input name="pass" type="password" class="input-lg" placeholder="Parola" /><br>'; 
    //echo '<input name="pass" type="password"/><br>'; 
    echo '<button type="submit" class="btn_alt">Autentifica</button>'; 
    //echo '<input type="submit">Autentifica'; 
    echo '</form>'; 
    echo '</center>'; 

} 
else
{


    echo '<header>'; 
    echo '<div class="inner">'; 
    echo '<nav>'; 
    echo '<a href="index.php" class="logo">'.$app['client_name'].'</a>'; 
    echo '<input type="checkbox" id="nav" class="hide" /><label for="nav"></label>'; 
    echo '<ul>'; 
    echo '<li><a href="index.php">Aparate</a></li>'; 

    if(isset($_SESSION["perm"]) && $_SESSION["perm"] <= "0") 
    { 
        echo '<li><a href="index.php?e=3">'.Adauga_Diacritice("Configur\a_uri useri").'</a></li>'; 

        if(isset($_GET['e']) && ($_GET['e'] == "1" || $_GET['e'] == "2") ) 
            echo '<li><a href="index.php?e=5&imei='.$_GET["imei"].'">'.Adauga_Diacritice("Configureaz\a_u aparat").'</a></li>'; 

    } 

    echo '<li><a href="index.php?op=logout" >'.Adauga_Diacritice("Ie\sire").'('.$_SESSION['user'].')</a></li>'; 
    echo '</ul>'; 
    echo '</nav>'; 
    echo '</div>'; 
    echo '</header>'; 


    if ( isset($_GET['e']) && $_GET['e'] == "1") 
    { 
        $TotalEvents = $database->count( "log", [ "ORDER" => "id DESC", "imei" => $_GET['imei'] ] ); 
        $TotalPages = ceil($TotalEvents / $app['EventsPerPage']); 
        $PageNo = 1; 

        if(isset($_GET['p']) && is_numeric($_GET['p']) && ($_GET['p'] > 0) && ($_GET['p'] <= $TotalPages) ) 
            $PageNo = $_GET['p']; 

        $datas = $database->select( "log", "*", [ "ORDER" => "id DESC", "imei" => $_GET['imei'], "LIMIT" => [($app['EventsPerPage'] * ($PageNo - 1)) , $app['EventsPerPage']] ] ); 
        $date_old = (new DateTime($datas[0]["date"]))->getTimestamp(); 
        $event_old = $datas[0]["type"]; 
        $event_array = array("1", "2", "3", "4"); 

        $i = 0; 
        $first = false; 

        echo'<span class="sub_titlu">'.$datas[0]["vtype"].', '.$datas[0]["simnum"].'</span><br>'; 
        echo '<span class="sub_titlu">'; 
        echo $datas[0]["loc_town"].', '.$datas[0]["loc_place"].', '.$datas[0]["loc_details"]; 
        echo '</span><br>'; 
        echo '<p class="spatiu"> </p>'; 
        echo '<table class="tftable">'; 
        echo '<thead>'; 
        echo '<tr>'; 
        echo '<th align="left">Semnal</th>'; 
        echo '<th align="left">Eveniment</th>';

        if($_SESSION["perm"] == "-1") 
            echo '<th align="left">ID</th>'; 

        echo '<th align="left">Data</th>'; 
        echo '<th align="left">Total</th>'; 
        echo '</tr>'; 
        echo '</thead>'; 
        echo '<tbody style=" tr:hover a:color: #000000">'; 

        $device = $database->select( "devices", "*", [ "imei" => $_GET['imei'] ] ); 


        foreach($datas as $data) 
        { 
            $date = (new DateTime($data["date"]))->getTimestamp(); 
            $eveniment = GetEventInfo($data["type"],$data["error"]); 
            $event = $data["type"]; 
            $rssi = explode(",", $data["rssi"]); 

            if($rssi[0] < $app['rssi_semnal_fslab']) 
            { 
                $semnal = 'images/necunoscut.png'; 
                $i++; 
            } 
            else 
                $i=0; 

            if($rssi[0] >= $app['rssi_semnal_fslab']) 
                $semnal = 'images/fslab.png'; 

            if($rssi[0] >= $app['rssi_semnal_slab']) 
                $semnal = 'images/slab.png'; 

            if($rssi[0] >= $app['rssi_semnal_mediu']) 
                $semnal = 'images/mediu.png'; 

            if($rssi[0] >= $app['rssi_semnal_bun']) 
                $semnal = 'images/bun.png'; 

            if($rssi[0] >= $app['rssi_semnal_fbun']) 
                $semnal = 'images/fbun.png'; 

            $total = $device[0]['canal1'] * $data["bill1"] + $device[0]['canal2'] * $data["bill2"] + $device[0]['canal3'] * $data["bill3"] + $device[0]['canal4'] * $data["bill4"]; 
            $elapsed = $date_old - $date; 

            if($elapsed > $app['secunde_tranzactii'] && in_array($event_old, $event_array) && in_array($event, $event_array)) 
                if($_SESSION["perm"] == "-1") 
                    echo '<tr><td colspan="5" bgcolor="'.$app['culoare_rand_gol_intre_tranzactii_de_la_persoane_diferite'].'"></td></tr>'; 
                else 
                    echo '<tr><td colspan="4" bgcolor="'.$app['culoare_rand_gol_intre_tranzactii_de_la_persoane_diferite'].'"></td></tr>'; 

            echo '<tr bgcolor="'.$eveniment["bg_color"].'">'; 
            echo '<td><a href="index.php?e=2&id='.$data["id"].'&imei='.$_GET["imei"].'" style="color: '.$eveniment["font_color"].';"><img src="'.$semnal.'"></a></td>'; 
            echo '<td><a href="index.php?e=2&id='.$data["id"].'&imei='.$_GET["imei"].'" style="color: '.$eveniment["font_color"].';">'.$eveniment["nume"]; 

            if($data["type"]==1 || $data["type"]==2 ||$data["type"]==3 || $data["type"]==4) 
                echo ' ('.$device[0]['canal'.$data["type"]].' '.($device[0]['canal'.$data["type"]] == 1 ? 'Leu':'Lei').')'; 

            echo '</a></td>'; 

            if($_SESSION["perm"] == "-1") 
                echo '<td><a href="index.php?e=2&id='.$data["id"].'&imei='.$_GET["imei"].'" style="color: '.$eveniment["font_color"].';">'.$data["id"].'</a></td>'; 

            if($_SESSION["perm"] == "-1") 
                echo '<td><a href="index.php?e=2&id='.$data["id"].'&imei='.$_GET["imei"].'" style="color: '.$eveniment["font_color"].';">'. RODate($data["date"], $app['format_data_mai_multe'], true, true) .'</a></td>';
            else 
                echo '<td><a href="index.php?e=2&id='.$data["id"].'&imei='.$_GET["imei"].'" style="color: '.$eveniment["font_color"].';">'. RODate($data["date"], $app['format_data_detalii'], true, true) .'</a></td>'; 


            if($total == 1) 
                echo '<td><a href="index.php?e=2&id='.$data["id"].'&imei='.$_GET["imei"].'" style="color: '.$eveniment["font_color"].';">'.$total.' leu</a></td>';
            else 
                echo '<td><a href="index.php?e=2&id='.$data["id"].'&imei='.$_GET["imei"].'" style="color: '.$eveniment["font_color"].';">'.$total.' lei</a></td>'; 

            echo "</tr>"; 

            $date_old = $date; 
            $event_old = $event; 

        }// end foreach


        echo '</tbody></table>'; 
        echo '<br><center>'; 
        

        for($p=$PageNo; $p < $PageNo + $app['EventPagesPerGroup']; $p++) 
        { 
            if($p > $TotalPages) 
                break; 

            if($p==$PageNo) 
            { 
                if(($pgo = $p - $app['EventPagesPerGroup'] + 1) < 1) $pgo = 1; 
            } 
            else 
                $pgo = $p; 

            echo '<a class="pagbar" href="index.php?e=1&imei='.$_GET["imei"].'&p='.$pgo.'">'.$p.'</a> '; 
        } 

        echo '</center>';

    } // if ( isset($_GET['e']) && $_GET['e'] == "1") 
    else 
        if ( isset($_GET['e']) && $_GET['e'] == "2") 
        { 
            $data = $database->select( "log", "*", [ "id" => $_GET['id'], "LIMIT" => 1 ] ); 
            $data_reset = $database->max( "pending_update", "date", [ "AND" => [ "imei" => $data[0]["imei"], "cmd" => "B" ] ] ); 
            $device = $database->select( "devices", "*", [ "imei" => $data[0]["imei"] ] ); 
            $rssi = $data[0]["rssi"]; 
            $semnal = 'images/necunoscut.png'; 
            if($rssi[0] >= $app['rssi_semnal_fslab']) 
                $semnal = 'images/fslab.png'; 

            if($rssi[0] >= $app['rssi_semnal_slab']) 
                $semnal = 'images/slab.png'; 

            if($rssi[0] >= $app['rssi_semnal_mediu']) 
                $semnal = 'images/mediu.png'; 

            if($rssi[0] >= $app['rssi_semnal_bun']) 
                $semnal = 'images/bun.png'; 

            if($rssi[0] >= $app['rssi_semnal_fbun']) 
                $semnal = 'images/fbun.png'; 

            $eveniment = GetEventInfo($data[0]["type"],$data[0]["error"]); 
            echo'<span class="sub_titlu">Detalii eveniment</span><br>'; 
            echo '<table class="tftable">'; 
            echo '<thead>'; 
            echo '<th colspan="2">Tip eveniment</th>'; 
            echo '</thead>'; 
            echo '<tr bgcolor="'.$eveniment["bg_color"].'">'; 
            echo '<td><font color="'.$eveniment["font_color"].'">Eveniment</font></td>'; 
            echo '<td><font color="'.$eveniment["font_color"].'">'.$eveniment["nume"]; 

            if($data[0]["type"]==1 || $data[0]["type"]==2 ||$data[0]["type"]==3 || $data[0]["type"]==4) 
                echo ' ('.$device[0]['canal'.$data[0]["type"]].' '.($device[0]['canal'.$data[0]["type"]] == 1 ? 'Leu':'Lei').')'; 

            echo '</font></td>'; 
            echo '</tr>'; 
            echo '<td colspan="2" style="background-color: white; ">'.$eveniment["detalii"]; 

            if($data[0]["type"]==1 || $data[0]["type"]==2 ||$data[0]["type"]==3 || $data[0]["type"]==4) 
                echo ' ('.$device[0]['canal'.$data[0]["type"]].' '.($device[0]['canal'.$data[0]["type"]] == 1 ? 'Leu':'Lei').')'; 

            echo '</td>'; 
            echo '<tr bgcolor="#FFFFFF">'; 
            echo '<td>Data</td>'; 
            echo '<td>'.RODate($data[0]["date"],$app['format_data_mai_multe'], false, false).'</td>'; 
            echo '</tr>'; echo '<tr bgcolor="#FFFFFF">'; 
            echo '<td>ID</td>'; 
            echo '<td>'.$data[0]["id"].'</td>'; 
            echo '</tr>'; 
            echo '<th colspan="2">Detalii aparat</th>'; 
            echo '<tr bgcolor="#FFFFFF">'; 
            echo '<td>IMEI</td>'; 
            echo '<td>'.$data[0]["imei"].'</td>'; 
            echo '</tr>'; 
            echo '<tr bgcolor="#FFFFFF">'; 
            echo '<td>SIMSN</td>'; 
            echo '<td>'.$data[0]["simsn"].'</td>'; 
            echo '</tr>'; 
            echo '<tr bgcolor="#FFFFFF">'; 
            echo '<td><b>'.Adauga_Diacritice("Num\a_ur de telefon").'</b></td>'; 
            echo '<td><b>'.$data[0]["simnum"].'</b></td>'; 
            echo '</tr>'; 
            echo '<tr bgcolor="#FFFFFF">'; 
            echo '<td>Operator</td>'; 
            echo '<td>'.$data[0]["operator"].'</td>'; 
            echo '</tr>'; 
            echo '<tr bgcolor="#FFFFFF">'; 
            echo '<td>IP</td>'; 
            echo '<td>'.$data[0]["ip"].'</td>'; 
            echo '</tr>'; echo '<tr bgcolor="#FFFFFF">'; 
            echo '<td>Semnal</td>'; 
            echo '<td><img src="'.$semnal.'" height="20" width="20"></td>'; 
            echo '</tr>'; 
            echo '<tr bgcolor="#FFFFFF">'; 
            echo '<td>Tip validator</td>'; 
            echo '<td>'.$data[0]["vtype"].'</td>'; 
            echo '</tr>'; 
            echo '<th colspan="2">'.Adauga_Diacritice("Loca\tie").'</th>'; 
            echo '<tr bgcolor="#FFFFFF">'; 
            echo '<td>'.Adauga_Diacritice("Ora\s").'</td>'; 
            echo '<td>'.$data[0]["loc_town"].'</td>'; 
            echo '</tr>'; 
            echo '<tr bgcolor="#FFFFFF">'; 
            echo '<td>'.Adauga_Diacritice("Loc").'</td>'; 
            echo '<td>'.$data[0]["loc_place"].'</td>'; 
            echo '</tr>'; 
            echo '<tr bgcolor="#FFFFFF">'; 
            echo '<td>'.Adauga_Diacritice("Detalii loca\tie").'</td>'; 
            echo '<td>'.$data[0]["loc_details"].'</td>'; 
            echo '</tr>'; 

            if(isset($_SESSION["perm"]) && $_SESSION["perm"] <= "0") 
            { 
                $total = $device[0]['canal1'] * $data[0]["bill1"] + $device[0]['canal2'] * $data[0]["bill2"] + $device[0]['canal3'] * $data[0]["bill3"] + $device[0]['canal4'] * $data[0]["bill4"]; 

                echo '<th colspan="2">Monetar</th>'; 
                echo '<tr bgcolor="#FFFFFF">'; 
                echo '<td>Bancnote tip #1 ('.$device[0]['canal1'].' '.($device[0]['canal1'] == 1 ? 'Leu':'Lei').')</td>'; 
                echo '<td>'.$data[0]["bill1"].' buc.</td>'; 
                echo '</tr>'; 
                echo '<tr bgcolor="#FFFFFF">'; 
                echo '<td>Bancnote tip #2 ('.$device[0]['canal2'].' '.($device[0]['canal2'] == 1 ? 'Leu':'Lei').')</td>'; 
                echo '<td>'.$data[0]["bill2"].' buc.</td>'; 
                echo '</tr>'; 
                echo '<tr bgcolor="#FFFFFF">'; 
                echo '<td>Bancnote tip #3 ('.$device[0]['canal3'].' '.($device[0]['canal3'] == 1 ? 'Leu':'Lei').')</td>'; 
                echo '<td>'.$data[0]["bill3"].' buc.</td>'; 
                echo '</tr>'; 
                echo '<tr bgcolor="#FFFFFF">'; 
                echo '<td>Bancnote tip #4 ('.$device[0]['canal4'].' '.($device[0]['canal4'] == 1 ? 'Leu':'Lei').')</td>'; 
                echo '<td>'.$data[0]["bill4"].' buc.</td>'; 
                echo '</tr>'; 

                if($total == 1) 
                    echo '<th colspan="2"><b>Total general: '.$total.' leu din data de<br>'.RODate($data_reset,$app['format_data_mai_multe'], true, true).'</b></th>'; 
                else 
                    echo '<th colspan="2"><b>Total general: '.$total.' lei din data de<br>'.RODate($data_reset,$app['format_data_mai_multe'], true, true).'</b></th>'; 

                echo '</tr>'; 
            } 
            
            echo '</table>'; 

        }// if ( isset($_GET['e']) && $_GET['e'] == "2") 
        else
            if (isset($_GET['e']) && $_GET['e'] == "3" && isset($_SESSION["perm"]) && $_SESSION["perm"] <= "0") 
            { 
                echo'<span class="sub_titlu">Configurari useri</span><br><br>'; 
            
                if(isset($_POST["da"])) 
                    if(isset($_POST['button'])) 
                        if($_POST['button'] == "res_pass") 
                        { 
                            if(isset($_POST['useri']) && isset($_POST['new_pass']) && isset($_POST['conf_new_pass'])) 
                                if($_POST['new_pass'] != $_POST['conf_new_pass']) 
                                { 
                                    $msg = "Parolele nu sunt la fel."; 
                                } 
                                else 
                                { 
                                    $pass=md5($_POST['new_pass']); 
                                    $database->update( "users", ["pass" => $pass], ["user" => $_POST['useri']] ); 
                                    $msg = Adauga_Diacritice("Schimbarea de parol\a_u a fost efectuat\a_u cu succes."); 
                                } 
                        }
                        else 
                            if($_POST['button'] == "dlt_acc") 
                            { 
                                $user_de_sters = $database->select( "users", "*", ["user" => $_POST['useri'], "LIMIT" => 1] );

                                if($user_de_sters[0]['perm'] > "0") 
                                { 
                                    $database->delete("users", ["user" => $_POST['useri']]); 
                                    $msg = Adauga_Diacritice("Contul ".$_POST['useri']." a fost \sters cu succes."); 
                                } 
                                else 
                                { 
                                    $msg = Adauga_Diacritice("Contul ".$_POST['useri']." nu a fost \sters deoarece are drepturi de admin."); 
                                } 
                            } 

                echo '<form name="myform" method="post" action="index.php?e=3">'; 
                echo 'User<br>'; 
                echo '<select name="useri" id="mySelect">'; 
                echo '<option value="">--Alegeti un user--</option>'; 
                
                $users = $database->query('SELECT * FROM users WHERE perm != -1')->fetchAll(); 
                
                foreach($users as $user) 
                    echo '<option value="'.$user["user"].'">'.$user["user"].'</option>'; 
                
                echo '</select><br><br>'; 
                echo Adauga_Diacritice("Parol\a_u nou\a_u").'<br>'; 
                echo '<input id="pass1" name="new_pass" class="input-sm" type="password" placeholder="'.Adauga_Diacritice("Parol\a_u nou\a_u").'"><br>'; 
                echo Adauga_Diacritice("Confirmare parol\a_u nou\a_u").'<br>'; 
                echo '<input id="pass2" name="conf_new_pass" class="input-sm" type="password" placeholder="'.Adauga_Diacritice("Confirmare parol\a_u").'"><br><br>'; 
                echo '<button onclick="validate(this.form);" type="submit" class="btn_alt">'.Adauga_Diacritice("Reseteaz\a_u parola").'</button><br>'; 
                echo '<button onclick="dlt(this.form);" type="submit" class="btn_alt">'.Adauga_Diacritice("\Sterge cont").'</button><br>'; 
                echo '<button onclick="add_aparat(this.form, \'index.php?e=4\');" type="submit" class="btn_alt">'.Adauga_Diacritice("Adaug\a_u cont").'</button><br>'; 
                echo '<button onclick="add_acc(this.form, \'index.php?e=41\');" type="submit" class="btn_alt">'.Adauga_Diacritice("List\a_u aparate").'</button><br>'; 
                echo '<input type="hidden" name="button" id="button">'; 
                echo '<input type="hidden" name="msg" id="msg_useri">'; 
                echo '</form>'; 
                
                if(isset($msg)) 
                    echo $msg; 
                
                if(isset($_POST['msg'])) 
                    echo $_POST['msg']; 

            } // if (isset($_GET['e']) && $_GET['e'] == "3" && isset($_SESSION["perm"]) && $_SESSION["perm"] <= "0") 
            else 
                if(isset($_GET['e']) && $_GET['e'] == "4" && isset($_SESSION["perm"]) && $_SESSION["perm"] <= "0") 
                { 
                    echo'<span class="sub_titlu">Adauga useri</span><br><br>'; 
                    
                    if(isset($_POST["da"])) 
                        if(isset($_POST["user"]) && isset($_POST["new_pass"]) && isset($_POST["conf_new_pass"])) 
                            if($_POST["new_pass"] != $_POST["conf_new_pass"]) 
                            { 
                                $msg = "Parolele nu sunt la fel."; 
                            } 
                            else 
                            { 
                                $exista = "nu"; 
                                $users = $database->select( "users", "*" ); 

                                foreach($users as $user) 
                                    if($user['user'] == $_POST['user']) 
                                    { 
                                        $exista = "da"; 
                                        break; 
                                    } 
                                    
                                if($exista == "nu") 
                                { 
                                    $pass=md5($_POST['new_pass']); 
                                    $database->insert( "users", [ "user" => $_POST['user'], "pass" => $pass, "perm" => "1" ] ); 
                                    $msg = Adauga_Diacritice("Contul a fost ad\a_uugat cu succes."); 
                                } 
                                else 
                                { 
                                    $msg = Adauga_Diacritice("Contul nu a fost ad\a_uugat deaorece mai exist\a_u un cont cu acela\si nume."); 
                                } 
                            }

                    echo '<form name="myform2" method="post" action="index.php?e=4">'; 
                    echo 'User<br>'; 
                    echo '<input id="name" name="user" class="input-sm" type="text" placeholder="User nou"><br>'; 
                    echo Adauga_Diacritice("Parol\a_u").'<br>'; echo '<input id="pass1_f2" name="new_pass" class="input-sm" type="password" placeholder="'.Adauga_Diacritice("Parol\a_u").'"><br>'; 
                    echo Adauga_Diacritice("Confirmare parol\a_u").'<br>'; 
                    echo '<input id="pass2_f2" name="conf_new_pass" class="input-sm" type="password" placeholder="'.Adauga_Diacritice("Confirmare parol\a_u").'"><br>'; 
                    echo '<button onclick="add_acc_form2(this.form)" type="button" class="btn_alt" name="add_account">'.Adauga_Diacritice("Creaz\a_u cont").'</button><br>'; 
                    echo '<input type="hidden" name="msg" id="msg_add_useri">'; 
                    echo '</form>'; 

                    if(isset($_POST['msg'])) 
                        if(strpos($_POST['msg'], 'Sunteti sigur ca vreti sa resetati parola') !== false || strpos($_POST['msg'], 'Sunteti sigur ca vreti sa stergeti contul') !== false); 
                        else 
                            echo $_POST['msg']; 


                } // if(isset($_GET['e']) && $_GET['e'] == "4" && isset($_SESSION["perm"]) && $_SESSION["perm"] <= "0") 
                else 
                    if(isset($_GET['e']) && $_GET['e'] == "41" && isset($_SESSION["perm"]) && $_SESSION["perm"] <= "0")
                    { 
                        echo'<span class="sub_titlu">Lista aparate disponibile</span><br><br>'; 
                
                        if(!isset($_POST["useri"])) die(); 
                            $user = $database->select("users", "*", [ "user" => $_POST["useri"], "LIMIT" => 1, ] ); 
                
                        $uid = $user[0]['id']; 

                        if(isset($_POST["aparat"]) && isset($_GET["a"])) 
                        { 
                            $imei = $_POST["aparat"]; 
                
                            if($_GET["a"] == "add") 
                            { 
                                $q = $database->select( "user_devices", "*", [ "AND" => [ "user" => $uid, "imei" => $imei ] ] ); 

                                if(!$q) 
                                { 
                                    $database->insert('user_devices',array( "id" => "" ,"user" => $uid ,"imei" => $imei )); 
                                } 
                            } 
                            else 
                                if($_GET["a"] == "rem") 
                                { 
                                    $q = $database->select( "user_devices", "*", [ "AND" => [ "user" => $uid, "imei" => $imei ] ] ); 

                                    if($q) 
                                    { 
                                        $database->delete( "user_devices", [ "AND" => [ "user" => $uid, "imei" => $imei ] ] ); 
                                    } 
                                } 
                        } 

                        $qstr = "SELECT log.imei, log.loc_town, log.loc_place, log.loc_details, log.vtype, devices.probe, user_devices.user FROM log "; 
                        $qstr .= "INNER JOIN devices ON log.imei = devices.imei "; 
                        $qstr .= "LEFT JOIN user_devices ON log.imei = user_devices.imei "; 
                        $qstr .= "WHERE log.id in ( SELECT max(log.id) FROM log group by imei) "; 
                        $qstr .= "AND devices.probe='0' "; 
                        $qstr .= "ORDER BY log.loc_town ASC"; 
                        $aparate = $database->query($qstr)->fetchAll(); 
                        echo '<form name="myform3" method="post" action="index.php?e=41">'; 
                        echo '<select name="aparat" id="myAparat">'; 

                        foreach($aparate as $aparat) 
                            echo '<option value="'.$aparat["imei"].'">'.$aparat["loc_town"].', '.$aparat["loc_place"].', '.$aparat["loc_details"].'</option>'; 

                        echo '</select><br><br>'; 
                        echo '<button onclick="add_aparat(this.form, \'index.php?a=add&e=41\');" type="submit" class="btn_alt">'.Adauga_Diacritice("Adaug\a_u").'</button><br>'; 
                        echo '<button onclick="add_aparat(this.form, \'index.php?a=rem&e=41\');" type="submit" class="btn_alt">'.Adauga_Diacritice("Scoate").'</button><br>'; 
                        echo '<input type="hidden" name="useri" value="'.$_POST["useri"].'">'; 
                        echo '<form>'; 
                        echo '<span class="sub_titlu">Lista aparate '.$_POST["useri"].'</span><br>'; 
                        echo '<table class="tftable2">'; 
                        echo '<thead>'; 
                        echo '<tr>'; 
                        echo '<th align="left">'.Adauga_Diacritice("Loca\tie").'</th>'; 

                        if(!$mobile) 
                            echo '<th align="left">IMEI</th>'; 

                        echo '<th align="left">'.Adauga_Diacritice("Tip").'</th>'; 
                        echo '</tr>'; 
                        echo '</thead>'; 
                        echo '<tbody>'; 

                        foreach($aparate as $data) 
                        { 
                            if($data["user"] != $uid) 
                                continue; 

                            $eveniment = GetEventInfo($data["type"],$data["error"]); 
                            echo '<tr bgcolor="'.(($data["probe"] == "1" )? '#DCDCDC': $eveniment["bg_color"]).'">'; 
                            echo '<td><a href="index.php?e=1&imei='.$data["imei"].'">'; 
                            echo $data["loc_town"]; 
                            echo $mobile? '<br>' : ', '; 
                            echo $data["loc_place"]; 
                            echo $mobile? '<br>' : ', '; 
                            echo $data["loc_details"]; 
                            echo '</a></td>'; 

                            if(!$mobile) 
                                echo '<td><a href="index.php?e=1&imei='.$data["imei"].'">'.$data["imei"].'</a></td>'; 

                            echo '<td><a href="index.php?e=1&imei='.$data["imei"].'">'.$data["vtype"]; 
                            echo '</a></td>'; 
                            echo '</tr>'; 
                        } 
    
                        echo '</tbody>'; 
                        echo '</table>'; 

                    } // if(isset($_GET['e']) && $_GET['e'] == "41" && isset($_SESSION["perm"]) && $_SESSION["perm"] <= "0")
                    else 
                        if(isset($_GET['e']) && $_GET['e'] == "5" && isset($_SESSION["perm"]) && $_SESSION["perm"] <= "0") 
                        {
                            echo'<span class="sub_titlu">'.Adauga_Diacritice("Configureaz\a_u aparat").'</span><br><br>'; 
                            $data = $database->select( "log", "*", [ "imei" => $_GET['imei'], "LIMIT" => 1, "ORDER" => "date DESC" ] ); 
                            $device = $database->select( "devices", "*", [ "imei" => $_GET['imei'] ] ); 

                            if(isset($_POST["da"])) 
                            { 
                                if($_POST['town'] == "" || $_POST['place'] == "" || $_POST['details'] == "" || $_POST['simnum'] == "") 
                                { 
                                    $msg = "<b>Eroare:</b> Va rugam sa completati toate campurile."; 
                                } 
                                else 
                                { 
                                    $comenzi_aparat = array( "V" => false, "T" => false, "P" => false, "D" => false, "N" => false, "S" => false, "B" => false, "X" => false, "Q" => false ); 
                                    $commands = $database->select( "pending_update", "*", ["imei" => $_GET['imei']] ); 
                                    $msg = "<br>"; 
                                    $msg1 = Adauga_Diacritice("<br>Au fost introduse \in lista de a\steptare urm\a_utoarele modific\a_uri:<br>"); 
                                    $msg2 = Adauga_Diacritice("<br>Urmatoarele comenzi nu au putut fi modificate deorece sunt deja \in lista de a\steptare:<br>"); 
                                    $msg3 = Adauga_Diacritice("Nu s-a modificat nimic.<br>"); 
                                    $modificat = "nu"; 
                                    $nr_erori = 0; 
                                    $nr_modificari = 0; 
                                    $date_now = new DateTime(); 
                                    $mysqldate = $date_now->format('Y-m-d G:i:s'); 
                                    $modificari_obs_sau_probe = 0; 

                                    foreach($commands as $command) 
                                    { 
                                       //echo "\n\r | CoPa ... cmd:".$command['cmd']." | pend: ".$command['pend']." \n\r";


                                        $cmd_first_letter=$command['cmd'][0]; 
                                        $comenzi_aparat[$cmd_first_letter] = true; 

                                        if($command['pend'] == 0) 
                                        { 
                                            $id=$command['id']; 
                                        
                                            switch($cmd_first_letter) 
                                            { 
                                                case "V": 
                                                    if($_SESSION["perm"] == "-1") 
                                                    { 
                                                        if($_POST["tip"] != $data[0]["vtype"]) 
                                                        { 
                                                            $nr_modificari++; 
                                                            $msg1 .= 'Tip validator: '.$data[0]["vtype"].' => '.$_POST['tip'].'<br>'; 
                                                            $database->update('pending_update', [ "cmd" => 'V'.$_POST['tip'], "pend" => "1", "date" => $mysqldate ], ["id" => $id] ); 
                                                        } 
                                                    } 
                                                    break; 

                                                case "T": 
                                                    if($_POST["town"] != $data[0]["loc_town"]) 
                                                    { 
                                                        $nr_modificari++; 
                                                        $msg1 .= Adauga_Diacritice("Ora\s, Localitate: ").$data[0]["loc_town"].' => '.$_POST["town"].'<br>'; 
                                                        $database->update ( "pending_update", [ "cmd" => 'T'.$_POST['town'], "pend" => "1", "date" => $mysqldate ], ["id" => $id] ); 
                                                    } 
                                                    break; 

                                                case "P": 
                                                    if($_POST["place"] != $data[0]["loc_place"]) 
                                                    { 
                                                        $nr_modificari++; 
                                                        $msg1 .= Adauga_Diacritice("Loca\tie: ").$data[0]["loc_place"].' => '.$_POST["place"].'<br>'; 
                                                        $database->update ( "pending_update", [ "cmd" => 'P'.$_POST['place'], "pend" => "1", "date" => $mysqldate ], ["id" => $id] );
                                                    } 
                                                    break; 

                                                case "D": 
                                                    if($_POST["details"] != $data[0]["loc_details"]) 
                                                    { 
                                                        $nr_modificari++; 
                                                        $msg1 .= Adauga_Diacritice("Detalii loca\tie: ").$data[0]["loc_details"].' => '.$_POST["details"].'<br>'; 
                                                        $database->update ( "pending_update", [ "cmd" => 'D'.$_POST['details'], "pend" => "1", "date" => $mysqldate ], ["id" => $id] ); 
                                                    } 
                                                    break; 


                                                case "N": 
                                                    if($_POST["simnum"] != $data[0]["simnum"]) 
                                                    { 
                                                        $nr_modificari++; 
                                                        $msg1 .= Adauga_Diacritice("Num\a_ur de telefon: ").$data[0]["simnum"].' => '.$_POST["simnum"].'<br>'; 
                                                        $database->update ( "pending_update", [ "cmd" => 'N'.$_POST['simnum'], "pend" => "1", "date" => $mysqldate ], ["id" => $id] ); 
                                                    } 
                                                    break; 

                                                case "B": 
                                                    if(isset($_POST["cmd_reinit"]) && $_POST["cmd_reinit"] == "rein") 
                                                    { 
                                                        $nr_modificari++; 
                                                        $msg1 .= Adauga_Diacritice("Reini\tializare monetar<br>"); 
                                                        $database->update ( "pending_update", [ "cmd" => "B", "pend" => "1", "date" => $mysqldate ], ["id" => $id] );
                                                    } 
                                                    break; 

                                                case "X": 
                                                    if($_SESSION["perm"] == "-1") 
                                                    { 
                                                        if(isset($_POST["cmd_reset"]) && $_POST["cmd_reset"] == "reset") 
                                                        { 
                                                            $nr_modificari++; 
                                                            $msg1 .= Adauga_Diacritice("Reset aparat<br>"); 
                                                            $database->update ( "pending_update", [ "cmd" => "X", "pend" => "1", "date" => $mysqldate ], ["id" => $id] ); 
                                                        } 
                                                    } 
                                                    break; 

                                                case "Q": 
                                                    if($_SESSION["perm"] == "-1") 
                                                    { 
                                                        if(isset($_POST["cmd_reset_val"]) && $_POST["cmd_reset_val"] == "reset_val") 
                                                        { 
                                                            $nr_modificari++; 
                                                            $msg1 .= Adauga_Diacritice("Reset validator<br>"); 
                                                            $database->update ( "pending_update", [ "cmd" => "Q", "pend" => "1", "date" => $mysqldate ], ["id" => $id] ); 
                                                        } 
                                                    } 
                                                    break; 

                                                case "S": 
                                                    if($_SESSION["perm"] == "-1") 
                                                    { 
                                                        if($_POST['server'] != "") 
                                                        { 
                                                            $nr_modificari++; 
                                                            $msg1 .= Adauga_Diacritice("Server : ".$_POST['server']."<br>"); 
                                                            $database->update ( "pending_update", [ "cmd" => 'S'.$_POST['server'], "pend" => "1", "date" => $mysqldate ], ["id" => $id] ); 
                                                        } 
                                                    } 
                                                    break; 

                                            } // switch

                                        } // if($command['pend'] == 0) 
                                        else 
                                        { 
                                            echo "\n\r |+++ CoPa ... cmd:".$command['cmd']." | pend: ".$command['pend']." letter: ".$cmd_first_letter." \n\r";


                                            switch($cmd_first_letter) 
                                            {                                                 

                                                case "V": 
                                                    if($_SESSION["perm"] == "-1") 
                                                        if($_POST["tip"] != $data[0]["vtype"]) 
                                                        { 
                                                            $msg2.= 'Tip validator: '.$data[0]["vtype"].' => '.$_POST['tip'].'<br>'; $nr_erori++; 
                                                        } 
                                                    break; 

                                                case "T": 
                                                    if($_POST["town"] != $data[0]["loc_town"]) 
                                                    { 
                                                        $msg2.= Adauga_Diacritice("Ora\s, Localitate: ").$data[0]["loc_town"].' => '.$_POST["town"].'<br>'; 
                                                        $nr_erori++; 
                                                    } 
                                                    break; 

                                                case "P": 
                                                    if($_POST["place"] != $data[0]["loc_place"]) 
                                                    { 
                                                        $msg2.= Adauga_Diacritice("Loca\tie: ").$data[0]["loc_place"].' => '.$_POST["place"].'<br>'; 
                                                        $nr_erori++; 
                                                    } 
                                                    break; 

                                                case "D": 
                                                    if($_POST["details"] != $data[0]["loc_details"]) 
                                                    { 
                                                        $msg2.= Adauga_Diacritice("Detalii loca\tie: ").$data[0]["loc_details"].' => '.$_POST["details"].'<br>'; 
                                                        $nr_erori++; 
                                                    } 
                                                    break; 

                                                case "N": 
                                                    if($_POST["simnum"] != $data[0]["simnum"]) 
                                                    { 
                                                        $msg2.= Adauga_Diacritice("Num\a_ur de telefon: ").$data[0]["simnum"].' => '.$_POST["simnum"].'<br>';
                                                        $nr_erori++; 
                                                    } 
                                                    break; 

                                                case "B": 
                                                    if(isset($_POST["cmd_reinit"]) && $_POST["cmd_reinit"] == "rein") 
                                                    { 
                                                        $msg2.= Adauga_Diacritice("Reini\tializare monetar<br>"); 
                                                        $nr_erori++;
                                                    } 
                                                    break; 

                                                case "X": 
                                                    if($_SESSION["perm"] == "-1") 
                                                        if(isset($_POST["cmd_reset"]) && $_POST["cmd_reset"] == "reset") 
                                                        { 
                                                            $msg2.= Adauga_Diacritice("Reset aparat<br>"); 
                                                            $nr_erori++;
                                                        } 
                                                    break; 

                                                case "Q": 
                                                    if($_SESSION["perm"] == "-1") 
                                                        if(isset($_POST["cmd_reset_val"]) && $_POST["cmd_reset_val"] == "reset_val") 
                                                        { 
                                                            $msg2.= Adauga_Diacritice("Reset validatort<br>"); 
                                                            $nr_erori++;
                                                        } 
                                                    break; 

                                                case "S": 
                                                    if($_SESSION["perm"] == "-1") 
                                                        if($_POST['server'] != "") 
                                                        { 
                                                            $msg2.= Adauga_Diacritice("Server: ".$_POST['server']."<br>"); 
                                                            $nr_erori++;
                                                        } 
                                                    break; 

                                            } // switch

                                        } // else if($command['pend'] == 0) 

                                    } // foreach($commands as $command)

                                    if($comenzi_aparat["V"] == false) 
                                        if($_SESSION["perm"] == "-1") 
                                            if($_POST["tip"] != $data[0]["vtype"]) 
                                            { 
                                                Insert_Db($_GET['imei'], 'V'.$_POST["tip"].'', 1); 
                                                $nr_modificari++; 
                                                $msg1 .= 'Tip validator: '.$data[0]["vtype"].' => '.$_POST['tip'].'<br>'; 
                                            } 
                                            else 
                                                Insert_Db($_GET['imei'], 'V'.$_POST["tip"].'', 0); 

                                        else 
                                            Insert_Db($_GET['imei'], 'V'.$data[0]["vtype"].'', 0); 

                                    if($comenzi_aparat["T"] == false) 
                                        if($_POST["town"] != $data[0]["loc_town"]) 
                                        { 
                                            Insert_Db($_GET['imei'], 'T'.$_POST["town"].'', 1); 
                                            $nr_modificari++; 
                                            $msg1 .= Adauga_Diacritice("Ora\s, Localitate: ").$data[0]["loc_town"].' => '.$_POST["town"].'<br>';
                                        }
                                        else
                                            Insert_Db($_GET['imei'], 'T'.$_POST["town"].'', 0); 

                                    if($comenzi_aparat["P"] == false) 
                                        if($_POST["place"] != $data[0]["loc_place"]) 
                                        { 
                                            Insert_Db($_GET['imei'], 'P'.$_POST["place"].'', 1); 
                                            $nr_modificari++; 
                                            $msg1 .= Adauga_Diacritice("Loca\tie: ").$data[0]["loc_place"].' => '.$_POST["place"].'<br>';
                                        } else
                                            Insert_Db($_GET['imei'], 'P'.$_POST["place"].'', 0); 

                                    if($comenzi_aparat["D"] == false) 
                                        if($_POST["details"] != $data[0]["loc_details"]) 
                                        { 
                                            Insert_Db($_GET['imei'], 'D'.$_POST["details"].'', 1); 
                                            $nr_modificari++; 
                                            $msg1 .= Adauga_Diacritice("Detalii loca\tie: ").$data[0]["loc_details"].' => '.$_POST["details"].'<br>'; 
                                        } 
                                        else 
                                            Insert_Db($_GET['imei'], 'D'.$_POST["details"].'', 0); 

                                    if($comenzi_aparat["N"] == false) 
                                        if($_POST["simnum"] != $data[0]["simnum"]) 
                                        { 
                                            Insert_Db($_GET['imei'], 'N'.$_POST["simnum"].'', 1); 
                                            $nr_modificari++; 
                                            $msg1 .= Adauga_Diacritice("Num\a_ur de telefon: ").$data[0]["simnum"].' => '.$_POST["simnum"].'<br>'; 
                                        } 
                                        else 
                                            Insert_Db($_GET['imei'], 'N'.$_POST["simnum"].'', 0); 

                                    if($comenzi_aparat["S"] == false) 
                                        if($_SESSION["perm"] == "-1") 
                                            if($_POST['server'] != "") 
                                            { 
                                                Insert_Db($_GET['imei'], 'S'.$_POST['server'].'', 1); 
                                                $nr_modificari++; 
                                                $msg1 .= Adauga_Diacritice("Server : ".$_POST['server']."<br>"); 
                                            } 
                                            else 
                                                Insert_Db($_GET['imei'], 'S', 0);                                                                   
                                        else 
                                            Insert_Db($_GET['imei'], 'S', 0); 

                                    if($comenzi_aparat["B"] == false) 
                                        if(isset($_POST["cmd_reinit"]) && $_POST["cmd_reinit"] == "rein") 
                                        { 
                                            Insert_Db($_GET['imei'], 'B', 1); 
                                            $nr_modificari++; 
                                            $msg1 .= Adauga_Diacritice("Reini\tializare monetar<br>"); 
                                        } 
                                        else 
                                            Insert_Db($_GET['imei'], 'B', 0); 

                                    if($comenzi_aparat["Q"] == false) 
                                        if($_SESSION["perm"] == "-1") 
                                            if(isset($_POST["cmd_reset_val"]) && $_POST["cmd_reset_val"] == "reset_val") 
                                            { 
                                                Insert_Db($_GET['imei'], 'Q', 1); 
                                                $nr_modificari++; 
                                                $msg1 .= Adauga_Diacritice("Reset validator<br>"); 
                                            } 
                                            else 
                                                Insert_Db($_GET['imei'], 'Q', 0); 
                                        else 
                                            Insert_Db($_GET['imei'], 'Q', 0); 

                                } // else if($_POST['town'] == "" || $_POST['place'] == "" || $_POST['details'] == "" || $_POST['simnum'] == "") 



                                if($_SESSION["perm"] == "-1") 
                                { 
                                    if($_POST["obs"] != $device[0]["obs"]) 
                                    { 
                                        $msg.= Adauga_Diacritice("Observa\tia a fost ad\a_uugat\a_u \in baza de date.<br>"); 
                                        $database->update ( "devices", ["obs" => $_POST['obs']], ["imei" => $_GET['imei']] ); 
                                        $modificari_obs_sau_probe++; 
                                    } 

                                    $modified = false; 

                                    if($_POST["canal1"] !== $device[0]["canal1"]) 
                                        $modified = true; 

                                    if($_POST["canal2"] !== $device[0]["canal2"]) 
                                        $modified = true; 

                                    if($_POST["canal3"] !== $device[0]["canal3"]) 
                                        $modified = true; 

                                    if($_POST["canal4"] !== $device[0]["canal4"]) 
                                        $modified = true; 

                                    if($modified) 
                                    { 
                                        $msg.= Adauga_Diacritice("Tipurile de bancnote au fost modificate cu success \in baza de date.<br>"); 
                                        $database->update("devices",[ "canal1" => $_POST['canal1'], "canal2" => $_POST['canal2'], "canal3" => $_POST['canal3'], "canal4" => $_POST['canal4'] ],[ "imei" => $_GET['imei'] ] ); 
                                        $modificari_obs_sau_probe++;
                                    } 
                                    
                                    if(isset($_POST["probe"])) 
                                    { 
                                        $modificari_obs_sau_probe++; 
                                        if($_POST["probe"] == "nu") 
                                        { 
                                            $msg.= Adauga_Diacritice("Aparatul nu mai este \in probe.<br>"); 
                                            $database->update ( "devices", ["probe" => "0"], ["imei" => $_GET["imei"]] );
                                        } 
                                        else 
                                            if($_POST["probe"] == "da") 
                                                { 
                                                    $msg.= Adauga_Diacritice("Aparatul este \in probe.<br>"); 
                                                    $database->update ( "devices", ["probe" => "1"], ["imei" => $_GET["imei"]] ); 
                                                } 
                                    } 
                                } 

                                if($nr_modificari>0 || $nr_erori>0) 
                                { 
                                    if($nr_erori == 0) $msg .= $msg1; 
                                    else 
                                        if($nr_modificari == 0) 
                                            $msg .= $msg2; 
                                        else $msg .= $msg1 ."<br><br>". $msg2;
                                } 
                                else 
                                    if($modificari_obs_sau_probe == 0) 
                                        $msg.=$msg3; 


                            } // if(isset($_POST["da"])) 


                            $device = $database->select( "devices", "*", [ "imei" => $_GET['imei'] ] ); 
                            echo '<form name="myform3" method="post" action="index.php?e=5&imei='.$_GET["imei"].'">'; 
                            echo 'IMEI<br>'; 
                            echo '<input id="imei" name="imei_de_afisat" class="input-sm" type="text" placeholder="'.$_GET["imei"].'" value="" readonly><br>'; 
                            echo '<input type="hidden" name="imei" id="imei_hidden" value="'.$_GET["imei"].'">'; 
                            echo Adauga_Diacritice("Ora\s, Localitate").'<br>'; 
                            echo '<input id="town" name="town" class="input-sm" type="text" placeholder="Oras, Localitate" value="'.$data[0]["loc_town"].'" oninput="check(this.id)"><br>'; 
                            echo Adauga_Diacritice("Loca\tie").'<br>'; 
                            echo '<input id="place" name="place" class="input-sm" type="text" placeholder="Locatie" value="'.$data[0]["loc_place"].'" oninput="check(this.id)"><br>'; 
                            echo Adauga_Diacritice("Detalii loca\tie").'<br>'; 
                            echo '<input id="details" name="details" class="input-sm" type="text" placeholder="Detalii locatie" value="'.$data[0]["loc_details"].'" oninput="check(this.id)"><br>'; 
                            echo Adauga_Diacritice("Num\a_ur de telefon").'<br>'; 
                            echo '<input id="simnum" name="simnum" class="input-sm" type="text" placeholder="'.Adauga_Diacritice("Num\a_ur de telefon").'" value="'.$data[0]["simnum"].'" oninput="check(this.id)"><br>'; 

                            if($_SESSION["perm"] == "-1") 
                            { 
                                echo '
                            Server<br>
                            <input id="server" name="server" class="input-sm" type="text" placeholder="Server"><br>
                            <input type="hidden" id="button_reconf" name="button_conf">
                            <br>
                            '; 
                                echo Adauga_Diacritice("Observa\tii").'<br><textarea name="obs" rows="4" cols="40">'.$device[0]["obs"].'</textarea><br><br>'; 
                                echo '
                                <span class="sub_titlu">Tip Validator:</span><br>
                                <input type="radio" name="tip" id="NV9" value="NV9" '. (($data[0]["vtype"]=="NV9") ? 'checked="checked"':'') .'>NV9<br>
                                <input type="radio" name="tip" id="NV10" value="NV10" '. (($data[0]["vtype"]=="NV10") ? 'checked="checked"':'') .'>NV10<br>
                                <input type="hidden" id="user" value="su">
                                <br>'; 
                            
                                echo '<span class="sub_titlu">Tip Bancnote:</span><br>'; 
                            
                                echo 'Canal #1 <input id="canal1" name="canal1" class="input-sm" type="text" size="4" value="'.$device[0]["canal1"].'" > '.($device[0]["canal1"] == '1' ? 'Leu' : 'Lei').'<br>'; 
                                echo 'Canal #2 <input id="canal2" name="canal2" class="input-sm" type="text" size="4" value="'.$device[0]["canal2"].'" > '.($device[0]["canal2"] == '1' ? 'Leu' : 'Lei').'<br>'; 
                                echo 'Canal #3 <input id="canal3" name="canal3" class="input-sm" type="text" size="4" value="'.$device[0]["canal3"].'" > '.($device[0]["canal3"] == '1' ? 'Leu' : 'Lei').'<br>'; 
                                echo 'Canal #4 <input id="canal4" name="canal4" class="input-sm" type="text" size="4" value="'.$device[0]["canal4"].'" > '.($device[0]["canal4"] == '1' ? 'Leu' : 'Lei').'<br>'; 
                            } 
                            else 
                                echo '<input type="hidden" id="user" value="admin">'; 

                            echo '<br>'; 
                            echo '<span class="sub_titlu">Comenzi:</span><br>'; 
                            echo '<input type="checkbox" name="cmd_reset_val" value="reset_val">'.Adauga_Diacritice("Reseteaz\a_u validator").'<br>'; 

                            if($_SESSION["perm"] == "-1") 
                            { 
                                echo '<input type="checkbox" name="cmd_reinit" value="rein">'.Adauga_Diacritice("Reini\tializeaz\a_u monetar").'<br>'; 

                                if($device[0]["probe"] == "1") 
                                    echo '<input type="checkbox" name="probe" value="probe" checked>'.Adauga_Diacritice("\In probe").'<br>'; 
                                else 
                                    echo '<input type="checkbox" name="probe" value="probe">'.Adauga_Diacritice("\In probe").'<br>'; 
                            } 

                            echo '<br>'; 
                            echo '<button onclick="reconf(this.form);" type="button" class="btn_alt" name="reconf_app">'.Adauga_Diacritice("Reconfigureaz\a_u aparat").'</button><br>'; 
                            echo '<input type="hidden" id="msg" name="msg">'; 
                            echo '</form>'; 
                            echo '<input class="btn_alt" type="button" value="&#206;napoi la log" onclick="window.location.href=\'index.php?e=1&imei='.$_GET["imei"].'\'">'; 
                            echo '<div id="msg-error"></div>'; 

                            if(isset($_POST['msg'])) 
                                echo Adauga_Diacritice($_POST['msg']); 
                            if(isset($msg)) 
                                echo Adauga_Diacritice($msg); 

                        }// if(isset($_GET['e']) && $_GET['e'] == "5" && isset($_SESSION["perm"]) && $_SESSION["perm"] <= "0") 
                        else 
                            if(isset($_GET['e']) && $_GET['e'] == "6" && isset($_SESSION["perm"]) && $_SESSION["perm"] <= "0") 
                            { 
                                if(isset($_POST['msg'])) 
                                { 
                                    if(strpos($_POST['msg'], 'Sunteti sigur ca vreti sa schimbati datele aparatului?') !== false) 
                                    { 
                                        $data = $database->select("log", "*", [ "imei" => $_POST["imei"], "LIMIT" => 1, "ORDER" => "id DESC" ] ); 
                                        $device = $database->select( "devices", "*", [ "imei" => $_POST["imei"] ] ); 
                                        echo '<br><form name="myform4" method="post" action="index.php?e=5&imei='.$_POST["imei"].'">'; 
                                        echo '<p>'.Adauga_Diacritice("Sunte\ti sigur c\a_u vre\ti s\a_u schimba\ti datele aparatului ".$_POST["imei"]."?").'</p><br>'; 
                                        echo 'Date vechi:<br>'.Adauga_Diacritice("Ora\s, Localitate").': '.$data[0]["loc_town"].'<br>'.Adauga_Diacritice("Loca\tie").': '.$data[0]["loc_place"].'<br>'.Adauga_Diacritice("Detalii loca\tie").': '.$data[0]["loc_details"].'<br>'.Adauga_Diacritice("Num\a_ur de telefon").': '.$data[0]["simnum"].'<br>'; 
                                        if($_SESSION["perm"] == "-1") 
                                            echo 'Validator: '.$data[0]["vtype"].'<br>'; 

                                        echo '<br><br>'; 
                                        echo 'Date noi:<br>'.Adauga_Diacritice("Ora\s, Localitate").': '.$_POST["town"].'<br>'.Adauga_Diacritice("Loca\tie").': '.$_POST["place"].'<br>'.Adauga_Diacritice("Detalii loca\tie").': '.$_POST["details"].'<br>'.Adauga_Diacritice("Num\a_ur de telefon").': '.$_POST["simnum"].'<br>'; 
                                        if($_SESSION["perm"] == "-1") 
                                        { 
                                            echo 'Validator: '.$_POST["tip"].'<br>'; 

                                            if($_POST["server"] !="") 
                                                echo 'Server: '.$_POST["server"].'<br>'; 
                                        } 

                                        echo '<br><br>'; 

                                        if(isset($_POST["cmd_reinit"]) && $_POST["cmd_reinit"] == "rein")
                                        { 
                                            echo '<input type="hidden" name="cmd_reinit" value="'.$_POST["cmd_reinit"].'">'; 
                                            echo '<b>IMPORTANT!</b> Ati selectat reinitializarea monetarului, acesta va deveni 0.<br><br>'; 
                                        } 

                                        echo '<input type="hidden" name="town" value="'.$_POST["town"].'">'; 
                                        echo '<input type="hidden" name="place" value="'.$_POST["place"].'">'; 
                                        echo '<input type="hidden" name="details" value="'.$_POST["details"].'">'; 
                                        echo '<input type="hidden" name="imei" value="'.$_POST["imei"].'">'; 
                                        echo '<input type="hidden" name="simnum" value="'.$_POST["simnum"].'">'; 

                                        if($_SESSION["perm"] == "-1") 
                                        { 
                                            echo '<input type="hidden" name="obs" value="'.$_POST["obs"].'">'; 
                                            echo '<input type="hidden" name="canal1" value="'.$_POST["canal1"].'">'; 
                                            echo '<input type="hidden" name="canal2" value="'.$_POST["canal2"].'">'; 
                                            echo '<input type="hidden" name="canal3" value="'.$_POST["canal3"].'">'; 
                                            echo '<input type="hidden" name="canal4" value="'.$_POST["canal4"].'">'; 
                                            echo '<input type="hidden" name="tip" value="'.$_POST["tip"].'">'; 
                                            echo '<input type="hidden" name="server" value="'.$_POST["server"].'">'; 

                                            if(isset($_POST["cmd_reset"]) && $_POST["cmd_reset"] == "reset") 
                                            { 
                                                echo '<b>IMPORTANT!</b> Ati selectat resetarea aparatului.<br><br>'; 
                                                echo '<input type="hidden" name="cmd_reset" value="'.$_POST["cmd_reset"].'">'; 
                                            } 
                                            if(isset($_POST["cmd_reset_val"]) && $_POST["cmd_reset_val"] == "reset_val") 
                                            { 
                                                echo '<b>IMPORTANT!</b> Ati selectat resetarea validatorului.<br><br>'; 
                                                echo '<input type="hidden" name="cmd_reset_val" value="'.$_POST["cmd_reset_val"].'">'; 
                                            } 
                                            if(!isset($_POST["probe"]) && $device[0]["probe"] == "1") 
                                            { 
                                                echo '<b>IMPORTANT!</b> Aparatul selectat nu va mai fi in probe.<br><br>'; 
                                                echo '<input type="hidden" name="probe" value="nu">'; 
                                            } 
                                            else 
                                                if(isset($_POST["probe"]) && $device[0]["probe"] == "0") 
                                                { 
                                                    echo '<b>IMPORTANT!</b> Aparatul selectat va fi in probe.<br><br>'; 
                                                    echo '<input type="hidden" name="probe" value="da">'; 
                                                } 
                                        } 

                                        echo '<button type="submit" class="btn_alt" name="da">Da</button>'; 
                                        echo '<button onclick="sbt(this.form);" type="button" class="btn_alt" name="nu">Nu</button>'; 
                                    } 
                                    else 
                                        if(strpos($_POST['msg'], 'Sunteti sigur ca vreti sa resetati parola') !== false) 
                                        { 
                                            echo '<br><form name="myform4" method="post" action="index.php?e=3">'; 
                                            echo '<p>'.Adauga_Diacritice("Sunte\ti sigur c\a_u vre\ti s\a reseta\ti parola contului ".$_POST["useri"]."?").'</p>'; 
                                            echo '<button type="submit" class="btn_alt" name="da">Da</button>'; 
                                            echo '<button onclick="sbt(this.form);" type="button" class="btn_alt" name="nu">Nu</button>'; 
                                            echo '<input type="hidden" name="useri" value="'.$_POST["useri"].'">'; 
                                            echo '<input type="hidden" name="new_pass" value="'.$_POST["new_pass"].'">'; 
                                            echo '<input type="hidden" name="conf_new_pass" value="'.$_POST["conf_new_pass"].'">'; 
                                            echo '<input type="hidden" name="button" value="'.$_POST["button"].'">'; 
                                        } 
                                        else 
                                            if(strpos($_POST['msg'], 'Sunteti sigur ca vreti sa stergeti contul') !== false) 
                                            { 
                                                echo '<br><form name="myform4" method="post" action="index.php?e=3">'; 
                                                echo '<p>'.Adauga_Diacritice("Sunte\ti sigur c\a_u vre\ti s\a_u \sterge\ti contul ".$_POST["useri"]."?").'</p>'; 
                                                echo '<button type="submit" class="btn_alt" name="da">Da</button>'; 
                                                echo '<button onclick="sbt(this.form);" type="button" class="btn_alt" name="nu">Nu</button>'; 
                                                echo '<input type="hidden" name="useri" value="'.$_POST["useri"].'">'; 
                                                echo '<input type="hidden" name="button" value="'.$_POST["button"].'">'; 
                                            } 
                                            else 
                                                if(strpos($_POST['msg'], 'Sunteti sigur ca vreti sa adaugati un cont nou') !== false) 
                                                { 
                                                    echo '<br><form name="myform4" method="post" action="index.php?e=4">'; 
                                                    echo '<p>'.Adauga_Diacritice("Sunte\ti sigur c\a_u vre\ti s\a_u ad\a_uuga\ti un cont nou cu numele ".$_POST["user"]."?").'</p>'; 
                                                    echo '<button type="submit" class="btn_alt" name="da">Da</button>'; 
                                                    echo '<button onclick="sbt(this.form);" type="button" class="btn_alt" name="nu">Nu</button>'; 
                                                    echo '<input type="hidden" name="user" value="'.$_POST["user"].'">'; 
                                                    echo '<input type="hidden" name="new_pass" value="'.$_POST["new_pass"].'">'; 
                                                    echo '<input type="hidden" name="conf_new_pass" value="'.$_POST["conf_new_pass"].'">'; 
                                                } 

                                    echo '</form>'; 

                                } // if(isset($_POST['msg'])) 

                            } // if(isset($_GET['e']) && $_GET['e'] == "6" && isset($_SESSION["perm"]) && $_SESSION["perm"] <= "0") 
                            else 
                                if(isset($_GET['e']) && $_GET['e'] == "7" && isset($_SESSION["perm"]) && $_SESSION["perm"] <= "0") 
                                { 
                                    if($app['sistem_de_operare'] == "windows") 
                                    { 
                                        exec("C:\\wamp64\\bin\\mysql\\mysql5.7.11\\bin\\mysqldump -ugsm -pgsmPassword --databases gsm >C:\\WINDOWS\\Temp\\gsm_".date("d.m.Y",time()).".sql"); 
                                        $file = "C:\\WINDOWS\\Temp\\gsm_".date("d.m.Y",time()).".sql"; 
                                    } 
                                    else 
                                    { 
                                        exec("mysqldump -ugsm -pgsmPassword --databases gsm >/tmp/gsm_".date("d.m.Y",time()).".sql"); 
                                        $file = "/tmp/gsm_".date("d.m.Y",time()).".sql"; 
                                    } 

                                    if (file_exists($file)) 
                                    { 
                                        header('Content-Description: File Transfer'); 
                                        header('Content-Type: application/octet-stream'); 
                                        header('Content-Disposition: attachment; filename="'.basename($file).'"'); 
                                        header('Expires: 0'); 
                                        header('Cache-Control: must-revalidate'); 
                                        header('Pragma: public'); 
                                        header('Content-Length: ' . filesize($file)); 
                                        readfile($file); 
                                        header('Location: index.php?e=aparate'); 
                                    } 

                                }// if(isset($_GET['e']) && $_GET['e'] == "7" && isset($_SESSION["perm"]) && $_SESSION["perm"] <= "0")
                                else 
                                    if(isset($_GET['e']) && $_GET['e'] == "8" && isset($_SESSION["perm"]) && $_SESSION["perm"] <= "0") 
                                    { 
                                        echo'<span class="sub_titlu">'.Adauga_Diacritice("Restaureaz\a_u baza de date").'</span><br>'; 

                                        if(isset($_POST["submit"])) 
                                        { 
                                            if($app['sistem_de_operare'] == "windows") 
                                                $target_dir = "C:\\WINDOWS\\Temp"; 
                                            else 
                                                $target_dir =""; 

                                            $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]); 
                                            print_r2($_FILES); 
                                        } 
                                        echo ' <form action="index.php/e=8" method="post" enctype="multipart/form-data">'; 
                                        echo Adauga_Diacritice("Selecta\ti baza de date pe care vre\ti s\a_u o importa\ti:").'<br><br>'; 
                                        echo '<input type="file" name="fileToUpload" id="fileToUpload"><br><br>'; 
                                        echo '<input type="submit" value="'.Adauga_Diacritice("Import\a_u baz\a_u de date").'" name="submit" class="btn_alt">'; 
                                        echo '</form>'; 

                                    }// if(isset($_GET['e']) && $_GET['e'] == "8" && isset($_SESSION["perm"]) && $_SESSION["perm"] <= "0") 
                                    else 
                                    { 
                                        echo'<span class="sub_titlu">'.Adauga_Diacritice("List\a_u aparate").'</span><br>'; 
                                        echo '<table class="tftable">'; 
                                        echo '<thead>'; 
                                        echo '<tr>'; 
                                        echo '<th align="left">'; 
                                        echo '<table class="srttable">'; 
                                        echo '<tbody>'; 
                                        echo '<tr>'; 
                                        echo '<td rowspan="3">'.Adauga_Diacritice("Loca\tie").'</td>'; 
                                        echo '<td rowspan="3" style="width: 5px;overflow:hidden"></td>'; 
                                        echo '<td><a href="index.php?srt=1"><img style="vertical-align:bottom" src="images/supb1.png"></a></td>'; 
                                        echo '</tr>'; 
                                        echo '<tr>'; 
                                        echo '<td style="height: 2px;overflow:hidden"></td>'; 
                                        echo '</tr>'; 
                                        echo '<tr>'; 
                                        echo '<td><a href="index.php?srt=2"><img style="vertical-align:top" src="images/sdwb1.png"></a></td>'; 
                                        echo '</tr>'; 
                                        echo '</tbody>'; 
                                        echo '</table>'; 
                                        echo '</th>'; 
                                        if(!$mobile) 
                                            echo '<th align="left">IMEI</th>'; 
                                        echo '<th align="left">'; 
                                        echo '<table class="srttable">'; 
                                        echo '<tbody>'; 
                                        echo '<tr>'; 
                                        echo '<td rowspan="3">'.Adauga_Diacritice("Tip").'</td>'; 
                                        echo '<td rowspan="3" style="width: 5px;overflow:hidden"></td>'; 
                                        echo '<td><a href="index.php?srt=1"><img style="vertical-align:bottom" src="images/supb1.png"></a></td>';
                                        echo '</tr>'; 
                                        echo '<tr>'; 
                                        echo '<td style="height: 2px;overflow:hidden"></td>'; 
                                        echo '</tr>'; 
                                        echo '<tr>'; 
                                        echo '<td><a href="index.php?srt=2"><img style="vertical-align:top" src="images/sdwb1.png"></a></td>'; 
                                        echo '</tr>'; 
                                        echo '</tbody>'; 
                                        echo '</table>'; 
                                        echo '</th>'; 
                                        echo '<th align="left">Total (din data)</th>'; 
                                        echo '</tr>'; 
                                        echo '</thead>';    
                                        echo'<tbody>'; 


                                        $qstr = "SELECT log.*, devices.probe FROM log "; 
                                        $qstr .= "INNER JOIN devices ON log.imei = devices.imei "; 

                                        if(!($_SESSION["perm"] == "-1" || $_SESSION["perm"] == "0")) 
                                            $qstr .= "INNER JOIN user_devices ON log.imei = user_devices.imei "; 
                                        $qstr .= "WHERE log.id in ( SELECT max(log.id) FROM log group by imei) "; 

                                        if($_SESSION["perm"] != "-1") 
                                            $qstr .= "AND devices.probe='0' "; 

                                        if(!($_SESSION["perm"] == "-1" || $_SESSION["perm"] == "0")) 
                                            $qstr .= "AND user_devices.user='".$_SESSION["uid"]."' "; 

                                        if(isset($_GET['srt']) ) 
                                        { 
                                            if($_GET['srt'] == "1") 
                                                $qstr .= "ORDER BY log.loc_town ASC"; 
                                            else 
                                                if($_GET['srt'] == "2") 
                                                    $qstr .= "ORDER BY log.loc_town DESC"; 
                                        } 
                                        else 
                                            $qstr .= "ORDER BY log.id DESC"; 

                                        echo '<h>    '.$qstr.'     </h>';




                                        $datas = $database->query($qstr)->fetchAll(); 




                                        foreach($datas as $data) 
                                        { 
                                            $data_reset = $database->max( "pending_update", "date", [ "AND" => [ "imei" => $data["imei"], "cmd" => "B" ] ] ); 
                                            $device = $database->select( "devices", "*", [ "imei" => $data["imei"] ] ); 
                                            $total = 0; 

                                            $total = $device[0]['canal1'] * $data["bill1"] + $device[0]['canal2'] * $data["bill2"] + $device[0]['canal3'] * $data["bill3"] + $device[0]['canal4'] * $data["bill4"]; 

                                            $eveniment = GetEventInfo($data["type"],$data["error"]); 

                                            echo '<tr bgcolor="'.(($data["probe"] == "1" )? '#DCDCDC': $eveniment["bg_color"]).'">'; 
                                            echo '<td><a href="index.php?e=1&imei='.$data["imei"].'">'; 
                                            echo $data["loc_town"]; echo $mobile? '<br>' : ', '; 
                                            echo $data["loc_place"]; echo $mobile? '<br>' : ', '; 
                                            echo $data["loc_details"]; echo '</a></td>'; 

                                            if(!$mobile) 
                                                echo '<td><a href="index.php?e=1&imei='.$data["imei"].'">'.$data["imei"].'</a></td>'; 

                                            echo '<td><a href="index.php?e=1&imei='.$data["imei"].'">'.$data["vtype"]; 

                                            if($_SESSION["perm"] == "-1") 
                                                echo ' ('.$data["appver"].')'; 

                                            echo '</a></td>'; 
                                            echo '<td><a href="index.php?e=1&imei='.$data["imei"].'">'; 
                                                
                                            if($total == 1) 
                                                echo $total.' Leu'; 
                                            else 
                                                echo $total.' Lei'; 

                                            echo '<br>'.RODate($data_reset, $app['format_data_aparate'], $mobile?true:false, $mobile?true:false).'</a></td>'; 

                                            echo '</tr>'; 
                                        } 






                                        echo '</tbody>'; 
                                        echo '</table>'; 
                                    } 


}// end else if (!isset($_SESSION['user']))  

echo '</body>'; 
echo '<footer>'; 
echo '<center>'.$app['app_title'].' , &#169;'.$app['app_copy_year'].' <a href="'.$app['app_copy_url'].'">'.$app['app_copy_text'].'</a></center>'; 
echo '</footer>'; 
echo '</html>'; 
?>