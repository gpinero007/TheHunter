![TheHunter logo](https://stationx11.es/wp-images/TheHunter.jpg)

# The Hunter, un Bot que Administra una red Wi-Fi
The Hunter es una herramienta (en PHP) que nos permite gestionar y controlar las conexiones a una Red.
Establece una conversación vía Telegram con el Administrador de la Red para notificarle sobre nuevos
accesos y para esperar instrucciones.


## Requerimientos:
**Arch Linux**
   * Nmap
   * Dsniff
   * MariaDB
   * Php<br>
 
``` sudo pacman -S nmap dsniff mariadb php php-curl ```

**Debian**
   * Nmap
   * Dsniff
   * MariaDB
   * Php<br>
 
``` sudo apt-get install nmap dsniff mysql-server php php-curl ```


## Configuración de la Base de Datos:

Por si no tenías una base de datos...

```sudo mysql_secure_installation```

[Seguir los pasos de Instalación...](https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-debian)

**DEBIAN:** ```sudo service mysql start```

**ARCH LINUX:** ```sudo systemctl start mysqld```

```mysql -u root -p```

__Y en la Base de datos:__
```
MariaDB [(none)]> CREATE DATABASE Hunter;


MariaDB [(none)]> USE Hunter;


MariaDB [Hunter]> CREATE TABLE IF NOT EXISTS Friends (id INT(10) PRIMARY KEY AUTO_INCREMENT, ip VARCHAR(15) NOT NULL, mac VARCHAR(40) NOT NULL, marca VARCHAR(100) DEFAULT NULL);


MariaDB [Hunter]> CREATE TABLE IF NOT EXISTS Blocks (id INT(10) PRIMARY KEY AUTO_INCREMENT, ip VARCHAR(15) NOT NULL, mac VARCHAR(40) NOT NULL, marca VARCHAR(100) DEFAULT NULL, pid INT(10) NOT NULL);


MariaDB [Hunter]> CREATE TABLE IF NOT EXISTS Intruders (id INT(10) PRIMARY KEY AUTO_INCREMENT, ip VARCHAR(15) NOT NULL, mac VARCHAR(40) NOT NULL, marca VARCHAR(100) DEFAULT NULL, pid INT(10) NOT NULL, hostname VARCHAR(100) DEFAULT NULL, ports VARCHAR(50) DEFAULT NULL, portsdata VARCHAR(300) DEFAULT NULL, so VARCHAR(50) DEFAULT NULL);


MariaDB [Hunter]> SHOW TABLES;  
```
