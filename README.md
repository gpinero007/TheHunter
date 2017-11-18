![TheHunter logo](https://stationx11.es/wp-images/TheHunter.jpg)

# The Hunter, un Bot que Administra una red Wi-Fi
The Hunter es una herramienta (en PHP) que nos permite gestionar y controlar las conexiones a una Red.
Establece una conversación vía Telegram con el Administrador de la Red para notificarle sobre nuevos
accesos y para esperar instrucciones.


## Requerimientos:
:skull:**Arch Linux**
   * Nmap
   * Dsniff
   * MariaDB
   * Php<br>
 
``` sudo pacman -S nmap dsniff mariadb php php-curl ```

:cyclone:**Debian**
   * Nmap
   * Dsniff
   * MariaDB
   * Php<br>
 
``` sudo apt-get install nmap dsniff mysql-server php php-curl ```


## Configuración de la Base de Datos:

Por si no tenías una base de datos...

```sudo mysql_secure_installation```

Seguir los pasos de Instalación [aquí](https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-debian)

:cyclone:**DEBIAN:**<br>
```sudo service mysql start```

:skull:**ARCH LINUX:**<br>
```sudo systemctl start mysqld```

Ahora entramos entramos en la Base de Datos:<br>
```mysql -u root -p```

_Y en la Base de datos:_
```
MariaDB [(none)]> CREATE DATABASE Hunter;


MariaDB [(none)]> USE Hunter;


MariaDB [Hunter]> CREATE TABLE IF NOT EXISTS Friends (id INT(10) PRIMARY KEY AUTO_INCREMENT, ip VARCHAR(15) NOT NULL, mac VARCHAR(40) NOT NULL, marca VARCHAR(100) DEFAULT NULL);


MariaDB [Hunter]> CREATE TABLE IF NOT EXISTS Blocks (id INT(10) PRIMARY KEY AUTO_INCREMENT, ip VARCHAR(15) NOT NULL, mac VARCHAR(40) NOT NULL, marca VARCHAR(100) DEFAULT NULL, pid INT(10) NOT NULL);


MariaDB [Hunter]> CREATE TABLE IF NOT EXISTS Intruders (id INT(10) PRIMARY KEY AUTO_INCREMENT, ip VARCHAR(15) NOT NULL, mac VARCHAR(40) NOT NULL, marca VARCHAR(100) DEFAULT NULL, pid INT(10) NOT NULL, hostname VARCHAR(100) DEFAULT NULL, ports VARCHAR(50) DEFAULT NULL, portsdata VARCHAR(300) DEFAULT NULL, so VARCHAR(50) DEFAULT NULL);


MariaDB [Hunter]> SHOW TABLES;  
```
## :imp: Empezando a Cazar!
Tenemos los 4 archivos:
 * TheHunter.php
 * TheHunter_Bot.php
 * TheHunter_Intruder.php
 * TheHunter_Querys.php
 
 Sólo vamos a ejecutar los dos primeros. El **primero** se encarga de _realizar escaneos de la red continuos e informar de nuevos clientes, además de mantenerlos en bloqueados_. Y el **segundo** es el :alien:_Bot que se comunicará mediante Telegram con el administrador de la Red_, especificado su ID en ambos programas en la cabecera. 
 
 Para ello en una terminal nos dirigimos a la ruta del Hunter:<br>
 ```sudo php TheHunter_Bot.php```
 <br>Y en otra terminal (Ctrl+Shift+T) escribimos:<br>
 ```sudo php TheHunter.php```
 
 <br>También podemos dejarlos en segundo plano mediante:<br>
 ```sudo php TheHunter.php &```
 <br><br>Y matar el proceso o pararlo mediante:<br>
 ```sudo kill -9 PID``` (sustituyendo PID por el número del proceso que tenga)
 <br> Puedes consultar el PID mediante:<br>
 ```ps aux|grep "sudo php TheHunter_Bot.php"| awk {'print $2'}``` (el primer número obtenido)
 
 <br>:books: No te olvides echar un vistazo a la **Documentación** que hay subida.
