<?php
////////USA/////// id 5 porta 3005
date_default_timezone_set('America/Sao_Paulo');
$data = date('d/m');
$hora = date('H:i:s');



$ip_address = "000.000.000.000"; // seu ip
$port = "3005"; // porta

$server = stream_socket_server("tcp://$ip_address:$port", $errno, $errorMessage);
if ($server === false) {
    die("stream_socket_server error: $errorMessage");
}
$client_sockets = array();
while (true) {
    // prepare readable sockets
    $read_sockets = $client_sockets;
    $read_sockets[] = $server;
    // start reading and use a large timeout
    if(!stream_select($read_sockets, $write, $except, 300000)) {
        die('stream_select error.');
    }
    // new client
    if(in_array($server, $read_sockets)) {
        $new_client = stream_socket_accept($server);
        if ($new_client) {
            //print remote client information, ip and port number
            echo "-----------------------------------------------------------------------------------------------------------------------\n";
            echo 'Nova Conexção USA: ' . stream_socket_get_name($new_client, true) . "\n";
            $client_sockets[] = $new_client;
            
            $ip = stream_socket_get_name($new_client, true);
            $cnx = mysql_connect('000.000.000.000', 'login', 'senha');                
            mysql_select_db('rastreamento', $cnx);
            mysql_query ("INSERT INTO log (imei,acao,ip,porta,dados)VALUES('$imei','GPS ON','$ip','$port','$data')", $cnx) or die( mysql_error());                
            //mysql_query ("UPDATE aparelhos SET status = 'ON' WHERE imei = '$imei' ", $cnx);     
            
            
            echo "Total de Rastreadores: ". count($client_sockets) . "\n";
            echo "-----------------------------------------------------------------------------------------------------------------------\n";
            // $output = "hello new client.\n";
            // fwrite($new_client, $output);
        }
        //delete the server socket from the read sockets
        unset($read_sockets[ array_search($server, $read_sockets) ]);
    }
    // message from existing client
    foreach ($read_sockets as $socket) {
        $data = fread($socket, 128);
        
        //echo "data: " . $data . "\n";
        $tk103_data = explode( ',', $data);
        $response = "";		
        $contador = (count($tk103_data)); // conta quantas casas entre as virgulas 
        
        if ($contador == 1){
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////        
                // case 1: 359710049095095 -> Padraão para "ON" Resposta
                $response = "ON";
                $imei = substr($tk103_data[0], 0,-1 );//
                echo "Enviando ON    para o Rastreador USA imei:$imei";
                $cnx = mysql_connect('000.000.000.000', 'login', 'senha');                
                mysql_select_db('rastreamento', $cnx);
                mysql_query ("INSERT INTO log (imei,acao,ip,dados)VALUES('$imei','GPS ON','$port','$data')", $cnx) or die( mysql_error());                
                mysql_query ("UPDATE aparelhos SET status = 'ON' WHERE imei = '$imei' ", $cnx);     

                $sql = "SELECT a.*, v.* ,v.placa as plac FROM agenda_saidas as a 

                LEFT JOIN veiculos AS v
                ON a.placa = v.id_vei 
                where v.imei = '$imei' ";
                $resul_sel = mysql_query($sql) or die( mysql_error());
                while ($row = mysql_fetch_assoc($resul_sel)) {
                echo 'Saida '.$id_saida = $row['id_saida'];
                $status1 = $row['status1'];
                echo ' ----  Veiculo'.$plac = $row['plac'];
                $email = $row['email'];
                $id_motorista  = $row['id_motorista'];
                /////////////////////////////////////////////
                $origem = $row['origem'];                 ///
                $destino = $row['destino'];               ///
                /////////////////////////////////////////////
                }
                 
                
                echo "\n";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////                
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////                
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////                
                // case 3: ##,imei:359710049095095,A -> Padrão para  "LOAD" Resposta
                }elseif( $contador == 3 ){
                if ($tk103_data[0] == "##") {
                $response = "LOAD";
                $imei = $tk103_data[1] ;//
                echo "Enviando LOAD  para o Rastreador USA $imei";
               
                $cnx = mysql_connect('000.000.000.000', 'login', 'senha');                
                mysql_select_db('rastreamento', $cnx);
                mysql_query ("INSERT INTO log (imei,acao,ip,dados)VALUES('$imei','GPS ON','$port','$data')", $cnx) or die( mysql_error());                
                mysql_query ("UPDATE aparelhos SET status = 'ON' WHERE imei = '$imei' ", $cnx);     

                $sql = "SELECT a.*, v.* ,v.placa as plac FROM agenda_saidas as a 
 
                LEFT JOIN veiculos AS v
                ON a.placa = v.id_vei 
                where v.imei = '$imei' ";
                $resul_sel = mysql_query($sql) or die( mysql_error());
                while ($row = mysql_fetch_assoc($resul_sel)) {
                echo $id_saida = $row['id_saida'];
                $id_motorista = $row['id_motorista'];
                $id_vei = $row['id_vei'];
                echo '  ---  Veiculo'.$plac = $row['plac'];
                $nome_saida = $row['p_nome'];
                
                $status1 = $row['status1'];
                $email = $row['email'];
                $id_motorista  = $row['id_motorista'];
                /////////////////////////////////////////////
                $origem = $row['origem'];                 ///
                //echo ' Destino '.$destino = $row['destino'];               ///
                /////////////////////////////////////////////
                }

                echo "\n"; 
                }
      
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////                          
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////INICIO DAS REGRAS DO BANCO/////////////////////////////////////////////////////////   
   
   
   
   
                // case 13: imei:359710049095095,tracker,151006012336,,F,172337.000,A,5105.9792,N,11404.9599,W,0.01,322.56,,0,0,,,  -> this is our gps data
                }elseif( $contador > 10 ){
                echo "Dados Completos: USA ";
                
               
           echo $imei = substr($tk103_data[0], 5);                   //   0 = imei:000000000000000	[imei]

                 $alarm = $tk103_data[1];                             //   1 = tracker [Msg: help me / low battery / stockade /
                 $gps_time = nmea_to_mysql_time($tk103_data[2]);      //   2 = 0809231929			[acquisition time: YYMMDDhhmm +8GMT cn]
           //echo  $bat = $tk103_data[4];                              //   3 = 13554900601			[adminphone?]   
                 $bat = $tk103_data[4];                              //   4 = F						[Data: F - full / L - low]                                                                      //   5 = 112909.397			[Time (HHMMSS.SSS)]                
            echo $valida = $tk103_data[6];                           //   6 = A						[A = available?]
            echo $latitude = degree_to_decimal($tk103_data[7], $tk103_data[8]);  //   7 = 2234.4669		[Latitude (DDMM.MMMM)]//   8 = N						[Lat direction: N / S]
            echo $longitude = degree_to_decimal($tk103_data[9], $tk103_data[10]);//   9 = 11354.3287	[Longitude (DDDMM.MMMM)] //  10 = E						[Lon direction: E / O]
                 $speed_in_knots = $tk103_data[11]; //  11 = 0.11  [speed Mph]					
                 $speed_in_mph = 1.15078 * $speed_in_knots; 
                 $bearing = $tk103_data[12];			

               
                
                If (strstr($imei , '##') !== False){ 
                $erro = 'sim';
                }else{ 
                $erro = 'nao'; 
                } 
                
                If (strstr($imei , 'imei') !== False){ 
                $erro2 = 'sim';
                }else{ 
                $erro2 = 'nao'; 
                }
                
                $conti = strlen($imei);
                if($conti <> 15){
                    
                $erro3 = 'sim';
                }else{ 
                $erro3 = 'nao'; 
                }
                
                
                
                $sql = "SELECT a.*, v.* ,v.placa as plac FROM agenda_saidas as a 
 
                LEFT JOIN veiculos AS v
                ON a.placa = v.id_vei 
                where v.imei = '$imei' ";
                $resul_sel = mysql_query($sql) or die( mysql_error());
                while ($row = mysql_fetch_assoc($resul_sel)) {
                echo $id_saida = $row['id_saida'];
                $id_motorista = $row['id_motorista'];
                $id_vei = $row['id_vei'];
                echo '   Veiculo '.$plac = $row['plac'];
                $nome_saida = $row['p_nome'];
                
                $status1 = $row['status1'];
                $email = $row['email'];
                $id_motorista  = $row['id_motorista'];
                /////////////////////////////////////////////
                $origem = $row['origem'];                 ///
                //echo ' Destino '.$destino = $row['destino'];               ///
                /////////////////////////////////////////////
                }
                date_default_timezone_set('America/Sao_Paulo');
                $data = date('d/m');
                echo '   '.$hora = date('H:i:s');
               
                $cnx = mysql_connect('000.000.000.000', 'login', 'senha');                
                mysql_select_db('rastreamento', $cnx);
   
   
                
   
                
                if ((isset ($id_vei))AND($valida == 'A')AND ($erro == 'nao')AND ($erro2 =='nao')AND ($erro3 =='nao')   ){
                 
//                if ($valida == 'A'){
                mysql_query ("INSERT INTO gps (imei,id_transfer,id_motorista,id_veiculo,id_saida,nome_saida,lat,longi,status,frase,speed,txt) VALUES ('$imei','5', '$id_motorista','$id_vei', '$id_saida', '$nome_saida' ,'$latitude', '$longitude','$status','$frase', '$speed', '$stringOriginal')", $cnx);
                mysql_query ("INSERT INTO log (imei,acao,ip,dados)VALUES('$imei','DADOS COMPLETO','$port','$data')", $cnx) or die( mysql_error());              
                }else{
                    echo 'Dados com Erro';
                }
                //include"sc1_regras.php";
              
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    
                if ($alarm == "help me") {
                $response = "**,imei:" + $imei + ",E;";
                }
                echo "\n";
                }
   
   
	 
       
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


            if (!$data) {
                unset($client_sockets[ array_search($socket, $client_sockets) ]);
                @fclose($socket);
                echo "Rastreador Desconectado. Total de Clientes: ". count($client_sockets) . "\n";
                continue;
            }
            //send the message back to client
            if (sizeof($response) > 0) {
                fwrite($socket, $response);
            }
        }
} // end while loop
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////




function nmea_to_mysql_time($date_time){
    $year = substr($date_time,0,2);
    $month = substr($date_time,2,2);
    $day = substr($date_time,4,2);
    $hour = substr($date_time,6,2);
    $minute = substr($date_time,8,2);
    $second = substr($date_time,10,2);
    return date("Y-m-d H:i:s", mktime($hour,$minute,$second,$month,$day,$year));
}
function degree_to_decimal($coordinates_in_degrees, $direction){
    $degrees = (int)($coordinates_in_degrees / 100); 
    $minutes = $coordinates_in_degrees - ($degrees * 100);
    $seconds = $minutes / 60;
    $coordinates_in_decimal = $degrees + $seconds;
    if (($direction == "S") || ($direction == "W")) {
        $coordinates_in_decimal = $coordinates_in_decimal * (-1);
    }
    return number_format($coordinates_in_decimal, 6,'.','');
}
