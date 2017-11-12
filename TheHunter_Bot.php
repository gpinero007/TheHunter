<?php

/*#############################################################
 *  TheHunter Bot [Version 1.0]
 *
 *  Estructura y cuerpo del Bot de Telegram.
 *  
 *  Funciones y procedimientos de mensajería y notificaciones
 *  con el administrador de la Red.
 *
 *#############################################################*/

define('TELEGRAMWEB', 'https://api.telegram.org/bot');
define('TOKEN', '495885526:AAEvwnYpxrkwYWuazTP8f_z1MTSWr1_KZZM');
define('TELTIMEOUT', 20);
define('MYID', '152434382');
define('AUTOR', 'SECURY');

include('TheHunter_Querys.php');
include('TheHunter_Intruder.php');

Class Bot{



  // *Función Principal, el Bot se mantiene mandando peticiones de Actualizaciones.
  public function run($Query,$Hunter){
    $last_msg_id = 1;

    while(true){
      $content = $this->getUpdates($last_msg_id);
      $decoded = json_decode($content,true);

      /*
       * El mensaje de respuesta al GetUpdates falla, el json no es válido o no hay.
       */
      if( (!isset($decoded)) || (!isset($decoded['ok'])) || (!$decoded['ok']) ){
        sleep(2);
        continue;
      }
      
      /*
       * Recorriendo el array resultante del json en busca de nuevos mensajes.
       */
      foreach ($decoded['result'] as $update) {
        if( (!empty($update['update_id'])) && ($update['update_id'] > $last_msg_id) ){
          $last_msg_id = $update['update_id'];

          // Valida que sea un chat privado y que el usuario sea el administrador.
          if((strcmp($update['message']['chat']['type'], "private") == 0) && (strcmp($update['message']['chat']['id'], MYID) == 0)){
            
            switch ($update['message']['text']) {
            	case '/start':
            		$this->sendMessage(MYID,"Hola ".$update['message']['from']['first_name']." puedes usar /help para consultar las opciones.");
            		break;
            	
            	case '/help':
            		$msghelp = "Por ahora no hay comandos relevantes, haga uso de los botones.";
            		$this->sendMessage(MYID,$msghelp);
            		break;

            	default:
            	  /*
            	   *  Bloque de comprobaciones por respuestas de botones.
            	   *   1. Verificar: mueve de tabla al cliente hacia la de Friends.
            	   *   2. Denegar: mueve de tabla al cliente hacia la Blacklist.
            	   *   3. Cazar: empieza la caza, el objetivo ha empezado a correr.
            	   */
            		if(preg_match('/✅\s+Verificar\s+(\d+\.\d+\.\d+\.\d+)/',$update['message']['text'])){

            		  preg_match_all('/(\d+\.\d+\.\d+\.\d+)/',$update['message']['text'], $matches);

                      // Ctrl+X al Cliente de Blocks a Friends.
                      $pid = $Query->retrievePID($matches[0][0]);
                      $Query->moveTo($matches[0][0],"verify");
                      $Query->deleteTo($matches[0][0],"blocks");
                      $Hunter->stopPoisoning($pid);
                      $this->sendMessage(MYID,"Cliente Verificado 👍🏻");

            		}else if(preg_match('/🚫\s+Denegar\s+(\d+\.\d+\.\d+\.\d+)/', $update['message']['text'])){
                      
            		  preg_match_all('/(\d+\.\d+\.\d+\.\d+)/',$update['message']['text'], $matches);            			

                      // Ctrl+X al Cliente de Blocks a Intruders.
                      $Query->moveTo($matches[0][0],"denegate");
                      $Query->deleteTo($matches[0][0],"blocks");
                      $this->sendMessage(MYID,"Cliente enviado a la Blacklist 👍🏻");

            		}else if(preg_match('/😈\sEmpieza\sla\scaza\sde\s(\d+\.\d+\.\d+\.\d+)/', $update['message']['text'])){
                      
            		  preg_match_all('/(\d+\.\d+\.\d+\.\d+)/',$update['message']['text'], $matches);
                      $this->sendMessage(MYID,"Empieza la caza!");

                      // Ctrl+X al Cliente de Blocks a Intruders. (+ Bonus Extra)
                      $Query->moveTo($matches[0][0],"denegate");
                      $Query->deleteTo($matches[0][0],"blocks");
                      //Empiza el bonus
                      $this->sendMessage(MYID,"Objetivo Identificado");
                      $this->sendMessage(MYID,"Objetivo Abatido!");

            		}else{
                      $this->sendMessage(MYID,"Comando incorrecto. Prueba con /help.");
            		}
            		break;
            }
          }
        }
      }
    }
  }



  /*Funcion para obtener mensajes de la API de telegram mediante el getUpdates*/
  public function getUpdates($last_msg_id){
    $ch = curl_init();
    $options = array(
        CURLOPT_URL => TELEGRAMWEB.TOKEN."/getUpdates",
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
          'offset' => $last_msg_id + 1,
          'limit' => 100,
          'timeout' => TELTIMEOUT,
        ]
    );
    curl_setopt_array($ch, $options);

    $content = curl_exec($ch);
    curl_close($ch);

    return $content;
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

} //Fin de Clase del Bot

$Query = new HQuery;
$Hunter = new TheHunter;

$Bot = new Bot;
$Bot->run($Query,$Hunter);

?>