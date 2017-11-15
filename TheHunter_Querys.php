<?php

/*#############################################################
 *  TheHunter Querys [Version 1.0]
 *
 *  Fichero de consultas a la Base de Datos del "Hunter".
 *  
 *  Funciones de acceso, modificacion y eliminacion de
 *  datos en las tablas sobre la base de datos Hunter.
 *
 *#############################################################*/

Class HQuery{
	

  
  // *Creando la PDO para conectar con la base de datos.
  public function database(){
  	$conex = new PDO("mysql:host=localhost;dbname=Hunter;","","");
	$conex->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $conex;
  }


  // *Cerrando la conexion con la Base de datos.
  public function closedb($statement){
    
    // Destruyendo la variable.
	unset($statement);
  }



  // *Añade un Cliente a la WhiteList.
  public function addtoWhiteList($ip,$mac,$brand){
  	$db = $this->database();

    try{    

      $query = "INSERT INTO Friends (ip,mac,marca) VALUES (:ip,:mac,:marca)";

      $statement = $db->prepare($query);

      // Bindear los parametros para prevenir SQLi.
      $statement->bindParam(':ip',$ip, PDO::PARAM_STR);
      $statement->bindParam(':mac',$mac, PDO::PARAM_STR);
      $statement->bindParam(':marca',$brand, PDO::PARAM_STR);

      $statement->execute();

    }catch(PDOException $exception){
      print 'Error ' . $exception -> getMessage(); 
    }

    // Cerrando la conexion.
    $this->closedb($statement);
  }



  // *Añade un Cliente a la Lista de Espera de Verificación.
  public function addBlockList($ip,$mac,$brand,$pid){
  	$db = $this->database();

    try{    

      $query = "INSERT INTO Blocks (ip,mac,marca,pid) VALUES (:ip,:mac,:marca,:pid)";

      $statement = $db->prepare($query);

      // Bindear los parametros para prevenir SQLi.
      $statement->bindParam(':ip',$ip, PDO::PARAM_STR);
      $statement->bindParam(':mac',$mac, PDO::PARAM_STR);
      $statement->bindParam(':marca',$brand, PDO::PARAM_STR);
      $statement->bindParam(':pid',$pid, PDO::PARAM_STR);

      $statement->execute();

    }catch(PDOException $exception){
      print 'Error ' . $exception -> getMessage(); 
    }

    // Cerrando la conexion.
    $this->closedb($statement);
  }



  // *Añade a un Intruso a la BlackList.
  public function addBlackList($ip,$mac,$brand,$pid){
  	$db = $this->database();

    try{    

      $query = "INSERT INTO Intruders (ip,mac,marca,pid) VALUES (:ip,:mac,:marca,:pid)";

      $statement = $db->prepare($query);

      // Bindear los parametros para prevenir SQLi.
      $statement->bindParam(':ip',$ip, PDO::PARAM_STR);
      $statement->bindParam(':mac',$mac, PDO::PARAM_STR);
      $statement->bindParam(':marca',$brand, PDO::PARAM_STR);
      $statement->bindParam(':pid',$pid, PDO::PARAM_STR);

      $statement->execute();

    }catch(PDOException $exception){
      print 'Error ' . $exception -> getMessage(); 
    }

    // Cerrando la conexion.
    $this->closedb($statement);
  }



  // *Elimina una dirección IP de la Lista de Espera de Verificación.
  public function removeFromBlockList($ip,$mac,$brand,$pid){
  	$db = $this->database();

    try{    

      $query = "DELETE FROM Blocks WHERE mac=:mac";

      $statement = $db->prepare($query);

      // Bindear los parametros para prevenir SQLi.
      $statement->bindParam(':mac',$mac, PDO::PARAM_STR);

      $statement->execute();

    }catch(PDOException $exception){
      print 'Error ' . $exception -> getMessage(); 
    }

    // Cerrando la conexion.
    $this->closedb($statement);
  }



  // *Función que valida si un cliente esta en la lista de bloqueados o admitidos (ninguna de estas-> Nuevo Cliente).
  public function valNewClient($user_ip,$user_mac){
    $val = 0;

    if(($this->searchIn($user_ip,$user_mac,"blocks") == 0) && ($this->searchIn($user_ip,$user_mac,"friends") == 0) && ($this->searchIn($user_ip,$user_mac,"intruders") == 0)){
      $val = 1;
    }else{
      $val = 0;
    }
    
    return $val;
  }




  public function searchIn($user_ip,$user_mac,$where){
    $returned = 0;
    
    $db = $this->database();

    try{    
      
      if(strcmp($where, "blocks") == 0){
        $query = "SELECT 1 FROM Blocks WHERE ip=:ip AND mac=:mac LIMIT 1";
      }elseif(strcmp($where, "friends") == 0){
        $query = "SELECT 1 FROM Friends WHERE ip=:ip AND mac=:mac LIMIT 1";
      }elseif(strcmp($where, "intruders") == 0){
        $query = "SELECT 1 FROM Intruders WHERE ip=:ip AND mac=:mac LIMIT 1";
      }

      $statement = $db->prepare($query);

      // Bindear los parametros para prevenir SQLi.
      $statement->bindParam(':ip',$user_ip, PDO::PARAM_STR);
      $statement->bindParam(':mac',$user_mac, PDO::PARAM_STR);

      $statement->execute();
      $resultado = $statement->fetch();

      if($resultado[1] == 1){
        $returned = 1;
      }else{
        $returned = 0;
      }

    }catch(PDOException $exception){
      print 'Error ' . $exception -> getMessage(); 
    }

    // Cerrando la conexion.
    $this->closedb($statement);
     
    return $returned;
  }


  // *Función que mueve a un cliente a la WhiteList o a la Blacklist.
  public function moveTo($ip,$mode){
    $query = null;

    $db = $this->database();

    try{    

      // Parseando de donde sacar al cliente y a donde llevarlo. Si se le valida o se le deniega.
      if(strcmp($mode, "verify") == 0){
      	$query = "INSERT INTO Friends (ip,mac,marca) SELECT ip,mac,marca FROM Blocks WHERE ip=:ip";
      }elseif(strcmp($mode, "denegate") == 0){
      	$query = "INSERT INTO Intruders (ip,mac,marca,pid) SELECT ip,mac,marca,pid FROM Blocks WHERE ip=:ip";
      }else{
      	throw new Exception("Error Processing Request Mode", 1);
      }

      $statement = $db->prepare($query);

      // Bindear los parametros para prevenir SQLi.
      $statement->bindParam(':ip',$ip, PDO::PARAM_STR);

      $statement->execute();

    }catch(PDOException $exception){
      print 'Error ' . $exception -> getMessage(); 
    }

    // Cerrando la conexion.
    $this->closedb($statement);     
  }


  // *Función que elimina a un cliente de una de las Tablas.
  public function deleteTo($ip,$where){
    $query = null;

    $db = $this->database();

    try{    

      // Parseando de donde se tiene que eliminar al cliente.
      if(strcmp($where, "blocks") == 0){
        $query = "DELETE FROM Blocks WHERE ip=:ip";
      }elseif(strcmp($where, "friends") == 0){
        $query = "DELETE FROM Friends WHERE ip=:ip";
      }elseif(strcmp($where, "intruders") == 0){
        $query = "DELETE FROM Intruders WHERE ip=:ip";
      }	

      $statement = $db->prepare($query);

      // Bindear los parametros para prevenir SQLi.
      $statement->bindParam(':ip',$ip, PDO::PARAM_STR);

      $statement->execute();

    }catch(PDOException $exception){
      print 'Error ' . $exception -> getMessage(); 
    }

    // Cerrando la conexion.
    $this->closedb($statement);     
  }



  // *Función que devuelve el PID (de un arpspoofing) de un Cliente.
  public function retrievePID($ip){
    $pid = null;
    $db = $this->database();

    try{    

      $query = "SELECT pid FROM Blocks WHERE ip=:ip";

      $statement = $db->prepare($query);

      // Bindear los parametros para prevenir SQLi.
      $statement->bindParam(':ip',$ip, PDO::PARAM_STR);

      $statement->execute();
      $resultado = $statement->fetch();

      $pid = $resultado["pid"];

    }catch(PDOException $exception){
      print 'Error ' . $exception -> getMessage(); 
    }

    // Cerrando la conexion.
    $this->closedb($statement);

    return $pid;
  }



  // *Función que devuelve todas las IP's de la Blacklist.
  public function dumpFrom($where){
    $ips = null;
    $db = $this->database();

    try{    
      
      // Parseando según la solicitud.
      if(strcmp($where, "whitelist") == 0){
        $query = "SELECT ip FROM Friends";
      }elseif(strcmp($where, "blocks") == 0){
        $query = "SELECT ip FROM Blocks";
      }elseif(strcmp($where, "blacklist") == 0){
        $query = "SELECT ip FROM Intruders";
      }

      $statement = $db->prepare($query);

      $statement->execute();
      $resultado = $statement->fetchAll();

      $len = sizeof($resultado);

      if($len > 0){
        for ($i=0; $i < $len; $i++) { 
          $ips .= "Cliente: ".$resultado[$i]["ip"]."\n";
        }
      }

    }catch(PDOException $exception){
      print 'Error ' . $exception -> getMessage(); 
    }

    // Cerrando la conexion.
    $this->closedb($statement);

    return $ips;
  }



  // *Función que introduce en la BD los valores obtenidos del análisis al intruso.
  public function haveHunted($ip,$content,$datacod){
    $query = null;

    $db = $this->database();

    try{

      // Parseando que valores tienen que ingresarse en el Análisis del Intruso.
      if(strcmp($datacod, "ports") == 0){

        $query = "UPDATE Intruders SET ports=:ports WHERE ip=:ip";

        $statement = $db->prepare($query);
        // Bindear los parametros para prevenir SQLi.
        $statement->bindParam(':ports',$content, PDO::PARAM_STR);

      }elseif(strcmp($datacod, "portsdata") == 0){
        
        $query = "UPDATE Intruders SET portsdata=:portsdata WHERE ip=:ip";
        
        $statement = $db->prepare($query);        
        // Bindear los parametros para prevenir SQLi.
        $statement->bindParam(':portsdata',$content, PDO::PARAM_STR);

      }elseif(strcmp($datacod, "os") == 0){
        
        $query = "UPDATE Intruders SET so=:so WHERE ip=:ip";

        $statement = $db->prepare($query);        
        // Bindear los parametros para prevenir SQLi.
        $statement->bindParam(':so',$content, PDO::PARAM_STR);

      }elseif(strcmp($datacod, "hostname") == 0){
        
        $query = "UPDATE Intruders SET hostname=:hostname WHERE ip=:ip";
        
        $statement = $db->prepare($query);        
        // Bindear los parametros para prevenir SQLi.
        $statement->bindParam(':hostname',$content, PDO::PARAM_STR);
      } 

      $statement->bindParam(':ip',$ip, PDO::PARAM_STR);

      $statement->execute();

    }catch(PDOException $exception){
      print 'Error ' . $exception -> getMessage(); 
    }

    // Cerrando la conexion.
    $this->closedb($statement);     
  }


  // *Función que devuelve datos de una IP de la Blacklist.
  public function giveDataIntruder($ip){
    $data = null;
    $db = $this->database();

    try{    
      
      $query = "SELECT * FROM Intruders WHERE ip=:ip";

      $statement = $db->prepare($query);

      // Bindear los parametros para prevenir SQLi.
      $statement->bindParam(':ip',$ip, PDO::PARAM_STR);

      $statement->execute();
      $resultado = $statement->fetchAll();

      //print_r($resultado);
      
      if($resultado[0] > 1){
        $data = "IP: ".$resultado[0]["ip"]."\nMac: ".$resultado[0]["mac"]."\nMarca: ".$resultado[0]["marca"]."\nHostname: ".$resultado[0]["hostname"]."\nPuertos: ".$resultado[0]["ports"]."\nInfo Puertos: ".$resultado[0]["portsdata"]."\nSistema Operativo: ".$resultado[0]["so"]."\n";
      } 

    }catch(PDOException $exception){
      print 'Error ' . $exception -> getMessage(); 
    }

    // Cerrando la conexion.
    $this->closedb($statement);

    return $data;
  }



}

?>