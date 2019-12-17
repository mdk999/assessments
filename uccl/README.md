# uc

## Project setup
```
This project depends on the following being installed
```
Nodejs > 11.x
```
NPM > 11.x
```
PHP > 7.x
```
PHP Composer
```
Mysql > 5.x
```
Apache > 2.4
```

# Installation
```
Unzip files into a folder ie. uc
```
run npm install
```
run composer install
```
create a database called uccldb
```
change the database credentials in config.php to match your own
```
import sql/uccl.sql into uccldb
```
Create a virtual host that looks like the following
```

<VirtualHost *:80>  
  ServerName uc.com    
  ServerAlias www.uc.com    
  DocumentRoot "/uc/dist"  
  <Directory "/uc/dist">  
    Options All          
    AllowOverride All          
    Require all granted          
  </Directory>
  Alias /api /uc/api  
 <Directory "/uc/">   
     Options All        
     AllowOverride All        
     Require all granted        
 </Directory>    
</VirtualHost>
```
**You can use whatever name you like for the ServerName/ServerAlias. You would need to however edit your hostfile to point it to 127.0.0.1  if you're running this locally
```
*if you want to use live data change 'testing' to false in the config.php file
```
go to the hostname you specified to load the page
```
