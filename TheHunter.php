<?php

/*#############################################################
 *  TheHunter [Version 1.0]
 *
 *  Código Principal, se encarga de iniciar el Intruder y
 *  empezar a mapear la Red. También notifica cuando se
 *  detecta que entra un cliente nuevo a la Red.
 *  
 *  Funcionamiento Básico:
 *
 *  Comienza escaneando la Red en la que se encuentra,
 *  después, por cada cliente nuevo que encuentra,
 *  lo bloquea mediante arp spoofing y notifica al admin,
 *  si se autoriza al cliente, se le pasa a la WhiteList,
 *  si no, se le mantiene bloqueado y/o se activan las
 *  pruebas unitarias de reconocimiento de Cliente.
 *
 *#############################################################*/

define('TELEGRAMWEB', 'https://api.telegram.org/bot');
define('TOKEN', '495885526:AAEvwnYpxrkwYWuazTP8f_z1MTSWr1_KZZM');
define('TELTIMEOUT', 20);
define('MYID', '152434382');
define('AUTOR', 'SECURY');


include('TheHunter_Intruder.php');
include('TheHunter_Querys.php');



Class ContactBot{

  // *Función que manda el mensaje seleccionar opción junto con el ReplyKeyboard. 
  public function sendMessageBoard($id,$text,$replyMarkup){
    $ch = curl_init();
    $options = array(
        CURLOPT_URL => TELEGRAMWEB.TOKEN."/sendMessage",
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
          'chat_id' => $id,
          'text' => $text,
          'reply_markup' => $replyMarkup,
        ]
    );
    curl_setopt_array($ch, $options);

    curl_exec($ch);
    curl_close($ch);
  }



  // *Función que envia mensajes sin teclado.
  public function sendMessage($id,$text){
    $ch = curl_init();
    $options = array(
        CURLOPT_URL => TELEGRAMWEB.TOKEN."/sendMessage",
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
          'chat_id' => $id,
          'text' => $text,
        ]
    );
    curl_setopt_array($ch, $options);

    curl_exec($ch);
    curl_close($ch);
  }

}



/*
 *  Bloque Principal del programa. Procesamiento de 
 */

$Hunter = new TheHunter;
$Bot = new ContactBot;
$HQuery = new HQuery;
$pid = null;
$flag = null;
$replyMarkup = array(
  'keyboard' => array(),
  'one_time_keyboard' => true,
  'resize_keyboard' => true,
);


// Obteniendo el segmento de red a escanear.
$ip_to_nmap = $Hunter::getRed();


/*
 *  Validación de Seguridad, expresiónr regular para comprobar que el dato pasado a
 *  la "shell_exec" se trata de una dirección IP.
 */
if(preg_match('/(\d+\.\d+\.\d+\.\d+\/\d+)/', $ip_to_nmap)){

  while (true){

    /*
     *  Escaneo rápido con Nmap al segmento del red filtrando por datos primarios y ajustando el comando.
     */
    $complete = "sudo nmap -sP -PI ".$ip_to_nmap." | grep 'Nmap scan report for\|MAC'";
    $complete = trim(preg_replace('/\s\s+/', ' ', $complete));

    $first_scan = shell_exec($complete);


    /*
     *  Expresión Regular para obtener cada Dirección IP, Mac y Marca de los resultados del escaneo con Nmap.
     *
     *  Fuente (para filtrar las MAC's): 
     *   > https://stackoverflow.com/questions/4260467/what-is-a-regular-expression-for-a-mac-address#answer-4260518
     */
    preg_match_all('/Nmap scan report for (\d+\.\d+\.\d+\.\d+)\s+MAC Address: ((?:[0-9A-F]{2}[:]?){5}(?:[0-9A-F]{2}?)) \((.*?)\)/', $first_scan, $user_data);

    //print_r($user_data);


    /*
     *  Extraemos los datos por cada cliente escaneado:
     *   -> IP, Mac y Marca.
     */
    for ($i=0; $i < sizeof($user_data[0]); $i++) { 
  	  echo "\nUser con IP (".$user_data[1][$i].") y MAC (".$user_data[2][$i].") con Marca (".$user_data[3][$i].")\n";
  	
  	  // Para descartar la IP del Router.
  	  $RouterIP = $Hunter->getApIP();
  	  if($RouterIP != $user_data[1][$i]){

  	    // Validando si ha entrado un nuevo cliente.
  	    $resultval = $HQuery->valNewClient($user_data[1][$i],$user_data[2][$i]);
  	    if($resultval == 0){

          // ArpSpoofing al nuevo cliente.
  	  	  $pid = $Hunter->makeArpSpoofing($user_data[1][$i]);

  	  	  // Añadiendo a la lista de Bloqueo Temporal.
  	      $HQuery->addBlockList($user_data[1][$i],$user_data[2][$i],$user_data[3][$i],$pid);

  	      // Enviando la info del nuevo cliente.
  	      $msg = "⚠️ Nuevo Cliente ⚠️ \n🌐 IP: ".$user_data[1][$i]."\n👁 MAC: ".$user_data[2][$i]."\n⚙️ Y fabricante: ".$user_data[3][$i]." ";
  	      $Bot->sendMessage(MYID,$msg);

  	      // Parseando cada opción que mandar al administrador.
  	      $content = array("✅ Verificar ".$user_data[1][$i],"🚫 Denegar ".$user_data[1][$i]);
          $content2 = array("😈 Empieza la caza de ".$user_data[1][$i]);
          array_push($replyMarkup['keyboard'], $content);
          array_push($replyMarkup['keyboard'], $content2);

          $flag = true;
  	    }
  	  }  
    }
    
    // Enviando para cuando se detecte un nuevo cliente, en otro caso, mejor no molestar.
    if($flag){
      // Codificando en JSON para enviar las opciones.
      $replyMarkup = json_encode($replyMarkup);
      $Bot->sendMessageBoard(MYID,"👉 Selecciona una opción:",$replyMarkup);
      $replyMarkup = json_decode($replyMarkup,true);
      $flag =false;
    }

    echo "\n=================================================================\n";
  }  
}

?>