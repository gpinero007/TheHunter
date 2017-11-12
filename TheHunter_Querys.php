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
  	$returned = 0;
  	
  	$db = $this->database();

    try{    

      $query = "SELECT 1 FROM Blocks WHERE ip=:ip AND mac=:mac LIMIT 1";

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
      	$query = "INSERT INTO Intruders (ip,mac,marca) SELECT ip,mac,marca FROM Blocks WHERE ip=:ip";
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

}

?>