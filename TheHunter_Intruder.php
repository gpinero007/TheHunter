<?php

/*#############################################################
 *  TheHunter Intruder [Version 1.0]
 *
 *  Archivo de control de la red y contacto con la terminal.
 *  
 *  Funciones y mecanismo de extracción de datos de la red,
 *  control y manipulación de esta, además de responsable de
 *  la ejecución de comandos de cara a la terminal.
 *
 *#############################################################*/

Class TheHunter{



  // *Función que obtiene la red y su mascara, necesaria para empezar el escaneo a todo el rango de IP's en red.
  public function getRed(){
    $ip_to_nmap = shell_exec("ip route show default | grep kernel | awk {'print $1'}");
    $ip_to_nmap = trim(preg_replace('/\s\s+/', ' ', $ip_to_nmap));
    return $ip_to_nmap;
  }



  // *Función que obtiene la dirección IP del Enrutador o Gateway.
  public function getApIP(){
    $ip_ap = shell_exec("ip route | grep default | awk {'print $3'}");
    $ip_ap = trim(preg_replace('/\s\s+/', ' ', $ip_ap));
    return $ip_ap;
  }



  // *Función que hace ArpSpoofing a una dirección IP, por consiguiente, se le impide navegar.
  public function makeArpSpoofing($user_ip){
  	$ap_ip = $this->getApIP();
  	$pid = 0;
  
    try{
  	  /*
  	   * Le digo al cliente que yo soy el AP y todo el tráfico pasa a traves de mí. Me retorna el PID para
  	   * luego poder matar el proceso en el caso de que el administrador lo autentifique.
       */
  	  if((preg_match('/(\d+\.\d+\.\d+\.\d+)/', $user_ip)) && (preg_match('/(\d+\.\d+\.\d+\.\d+)/', $ap_ip)) ){
  	    $command = "nohup arpspoof -i wlp2s0 -t ".$user_ip." ".$ap_ip." > /dev/null 2>&1 & echo $!";
  	    $command = trim(preg_replace('/\s\s+/', ' ', $command));
    
        $pid = shell_exec($command);
        $pid = trim(preg_replace('/\s\s+/', ' ', $pid));
      }else{
      	throw new Exception("Error la IP no tiene el formato correcto", 1);
      }

    }catch(PDOException $exception){
      print 'Error ' . $exception -> getMessage(); 
    } 

    echo "{".$command."} con PID ".$pid."\n";
    return $pid;
  }



  // *Función que detiene un proceso de Spoofing sobre un Cliente.
  public function stopPoisoning($pid){
    
    try{

      // Validando que el PID se trata de un valor numérico.
      if(preg_match('/(\d+)/', $pid)){
        $command = "kill -9 ".$pid;
        $command = trim(preg_replace('/\s\s+/', ' ', $command));

        $pid = shell_exec($command);
      }else{
      	throw new Exception("Error PID is not a number", 1);
      }

    }catch(PDOException $exception){
      print 'Error ' . $exception -> getMessage(); 
    } 

  }



} // Fin Clase TheHunter


?>