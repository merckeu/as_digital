
# as_digital
Esse Addon tem como Objetivo enviar assinatura digital para mk-auth, 

# Resumo

tem como objetivo salvar assinatura digital no mk-auth para assinar contrato.

----------------------------------------------------------------------------------------------



1. Para funcionar na TUX 4.19 precisa adicionar permiss�es do " apparmor "

2. V� para o diret�rio /etc/apparmor.d e abra o arquivo usr.sbin.php-fpm7.3.

3. Adicione estas linha no arquivo:

 #Addons - Permitir apenas leitura e execu��o
 /opt/mk-auth/admin/addons/** rix,

 # Comandos estritamente necess�rios para execu��o
 /usr/bin/head ix,
 /usr/bin/tr ix,
 /usr/bin/mysql ix,
 /usr/bin/basename ix,
 /usr/bin/zip ix,
 /usr/bin/curl ix,
 /usr/bin/crontab ix,
 /bin/dash ix,
 /bin/date ixr,
 /bin/ls ixr,
 /bin/rm ixr,
 /bin/mv ixr,
 /bin/cp ixr,
 /bin/grep ixr,

 # Permiss�es de leitura e escrita no diret�rio de addons, mas restritas
 /opt/mk-auth/admin/addons/as_digital/uploads/signatures** rw,

 # Acesso a terminal restrito
 /dev/tty rw,

 # Permiss�es de leitura para arquivos de configura��o do MySQL
 /etc/mysql/conf.d/ r,
 /etc/mysql/conf.d/* r,
 /etc/mysql/mariadb.conf.d/ r,
 /etc/mysql/mariadb.conf.d/* r,
 /etc/mysql/mariadb.cnf r,

 # Permiss�es de escrita para cron jobs, apenas onde necess�rio
 /var/spool/cron/crontabs/tmp.* rwk,
 /var/spool/cron/crontabs/www-data rwk,

 # Permiss�o para criar diret�rios em /opt/mk-auth/admin/addons/as_digital/uploads/signatures
 /opt/mk-auth/admin/addons/as_digital/uploads/signatures rwk,
 /opt/mk-auth/admin/addons/as_digital/uploads/signatures** rwk,







 Caso n�o queira reiniciar o MK-auth s� dar esses dois comando abaixo.

```
sudo apparmor_parser -r /etc/apparmor.d/usr.sbin.php-fpm7.3
```
```
sudo service php7.3-fpm restart
```

