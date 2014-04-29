==================
Copia de seguridad
==================

La copia de seguridad contiene todos los datos, tales como los directorios principales de los usuarios, carpetas compartidas, mensajes de correo electrónico, sino también todas las configuraciones del sistema. Funciona todos los días y pueden ser totales o incrementales, según el día de la semana y la configuración. Los medios disponibles para la copia de seguridad son: disco USB, recurso compartido de Windows y NFS compartidos. Al final del procedimiento de copia de seguridad, se enviará una notificación por correo electrónico al administrador o a una dirección personalizada.

General
=======

Habilitar copia de seguridad automática
    Esta opción activa o desactiva el procedimiento de copia de seguridad. El valor predeterminado es *habilitado*.

Horario de copia de seguridad
    El momento en el que la copia de seguridad se iniciará. Cambie el valor directamente en el campo.

Completo
    Al seleccionar esta opción se ejecutará una copia de seguridad completa cada día de la semana

Incremental
    Al seleccionar esta opción se ejecutará una copia de seguridad completa en el día seleccionado a través del campo específico, mientras que el resto de semana se ejecutará una copia de seguridad incremental.

Política de retención
    Introduzca el número de días en que se guardará la copia de seguridad.

Destino
=======

Disco USB
    Seleccione el destino de copia de seguridad en una unidad USB. El disco USB debe ser formateado con un sistema de archivos compatible (ext2/3/4 o FAT, NTFS no se admite) y una etiqueta configurada.

    * Sistema de Archivos de etiquetas: Enumera los discos USB conectados

Compartido de Windows (CIFS)
    Seleccione el destino de copia de seguridad, una parte de Windows (CIFS). Es necesaria la autenticación.

    * Servidor: La dirección IP o el FQDN del servidor de Windows de destino
    * Compartir: el nombre del recurso compartido en el sistema Windows de destino
    * Usuario: Nombre de usuario que se utilizará para la autenticación 
    * Contraseña: contraseña que se utilizará para la autenticación.

Compartir NFS
    Seleccione el destino de copia de seguridad en un recurso compartido NFS 

Host
   La dirección IP o el FQDN del servidor NFS 

   * Compartir: nombrar el objetivo compartido de NFS

Notificaciones
==============

En caso de error
    Enviar notificación, sólo en el caso de que falle el respaldo.

Siempre
    Siempre envía notificaciones, si tiene éxito o en caso de fallo.

Nunca
    Usted no recibirá ninguna notificación.

Enviar notificación al
    De entrada que recibirá la notificación por correo electrónico
   
    * Administrador de Sistemas: la notificación de la copia de seguridad se enviará al administrador del sistema (usuario admin)
    * Dirección personalizada: Se enviará la notificación de la copia de seguridad
