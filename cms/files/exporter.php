<?php

  if (($_GET['key']==md5('РЫБА'.md5($_SERVER['HTTP_HOST'])))&& (isset($_GET['act']))){ 
     include '../public/api.php';
     switch ($_GET['act']){
        case 'get':       
            $link=$api->db->mysql_query("SELECT * FROM `logs`");
            while($row=mysql_fetch_array($link)){
                echo ($row['date'].'|'.$row['log'].'|'.$row['url'].'|'.$row['table'].'|'.$row['type']."\n");
            }
        break;
        case 'trunc':
             $api->db->mysql_query("TRUNCATE TABLE `logs`");
        break;
     }
     
  } else {
      die('DIE! MF! DIE!');
  }
?>
