<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* [filename] is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<?php
class MessagesHandler {

    public static $msg_arr = array(
        1050 => 'No puede crear una relaci&oacute;n con usted mismo.',
        1051 => 'Su petici&oacute;n ha sido enviada para aprobaci&oacute;n',
        3000 => 'El c&oacute;digo zip debe tener un valor integral.',
        3001 => 'Lo sentimos, no puede unirse al grupo: <br />Raz&oacute;n:<br/>La invitaci&oacute;n puede no ser v&aacute;lida<br/> O est&acute; usando una invitaci&oacute;n antigua',
        5001 => ' El usuario no est&aacute; registrado hasta ahora.',
        5002 => ' Correo enviado satisfactoriamente. ',
        7001 => 'Se ha unido exitosamente a la red.',
        7002 => 'Por favor unase a una red para desarrollar alguna actividad.',
        7003 => 'Su cuenta ha sido temporalmente deshabilitada por el administrador de la red, por lo tanto no puede desarrollar la actividad solicitada.',
        7004 => '¡Congratulaciones! <br /> Su contrase&ntilde;a ha sido cambiada satisfactoriamente.',
        7005 => 'Por favor especifique el usuario a borrar.',
        7006 => 'Red creada satisfactoriamente.',
        7007 => 'Ha sido enviado el boletin de red.',
        7008 => 'Ha dejado la red de manera satisfactoria.',
        7009 => '¡Lo sentimos! No est&aacute; autorizado para ver el contenido de esta p&aacute;gina',
        7010 => 'Las configuraciones de m&oacute;dulo han sido hechas satisfactoriamente.',
        7011 => 'La notificaci&oacute;n ha sido guardad.',
        7012 => 'Las configuraciones de relaciones han sido realizadas.',
        7013 => 'Observe su correo electr&oacute;nico para activar su cuenta de PeopleAggregator.',
        7014 => 'Su cuenta ha sido activada. ¡Disfr&uacute;telo!',
        7015 => '¡Lo sentimos! No puede reusar una ficha.',
        7016 => 'La invitaci&oacute;n ha sido aceptada satisfactoriamente.',
        7017 => '¡Lo sentimos! La invitaci&oacute;n que intenta aceptar ya no es v&aacute;lida.',
        7018 => '¡Lo sentimos! La invitaci&oacute;n que intenta aceptar no est&aacute; dirigida a usted.',
        7019 => '¡Lo sentimos! La ficha no es v&aacute;lida, la firma es incorrecta.',
        7026 => 'Puede invitar usuarios internos y externos. Por favor provea o nombres de usuario o direcciones de correo',
        7701 => 'Plantilla cambiada satisfactoriamente - <br /> lo podr&aacute; observar en la p&aacute;gina inicial.',
        7702 => 'Lo sentimos, no est&aacute;n permitidas m&uacute;ltiples selecciones en esta p&aacute;gina.',
        7703 => 'No est&aacute; autorizado a cambiar la plantilla.',
        7704 => 'Las configuraciones de red han sido cambiadas satisfactoriamente <br /> lo podr&aacute; observar en la p&aacute;gina inicial.',
        7020 => 'Usuario(s) borrado(s) satisfactoriamente',
        7021 => 'Usted ya es miembro de esta red',
        7022 => 'Usuario(s) aprobado(s)',
        7023 => 'Usuario(s) denegado(s)',
        7024 => 'Contenido borrado satisfactoriamente.',
        7025 => 'Los comentarios han sido borrados satisfactoriamente',
        // Message Related to Group creation RANGE 90221 to 90240
        90223 => 'Lo sentimos, usted no est&aacute; autorizado para editar este grupo.',
        90222 => 'O el nombre del grupo est&aacte; vacio o este contiene caracteres ilegales.
                            Por favor ingrese el nombre de grupo nuevamente.',
        90221 => 'El grupo ha sido creado satisfactoriamente.',
        90231 => 'El grupo ha sido actualizado satisfactoriamente.',
        // msg for ad-center module
        //Range of Ad-center's related message is start with 19001-19020
        19007 => 'Ad ha sido actualizado satisfactoriamente',
        19008 => 'Ad ha sido agregado satisfactoriamente',
        19009 => 'Por favor ingrese una URL v&aacute;lida para el enlace',
        19010 => 'Ad ha sido deshabilitado satisfactoriamente',
        19011 => 'Ad ha sido habilitado satisfactoriamente',
        19012 => 'Por favor ingrese la URL o c&oacute;digo javascript',
        19013 => 'Ad ha sido borrado satisfactoriamente',
        // messsage related to people invite
        6001 => '<br />Por favor especifique o la direcci&oacute;n de correo o el nombre de usuario de PeopleAggregator.',
        6002 => '<br />No se puede invitar a usted mismo.',
        6003 => 'Por favor especfique le direcci&oacute;n de correo electr&oacute;nico - No puede estar en blanco.',
        6004 => 'La invitaci&oacute;n ha sido enviado satisfactoriamente',
        6005 => 'Por favor unase a por lo menos 1 grupo antes de enviar la invitaci&oacute;n',
        // message related to internal messaging
        //Messages for the My Message section.
        8001 => '<br /> Mensaje enviado satisfactoriamente.',
        'message_sent' => 'Su mensaje ha sido enviado satisfactoriamente.',

        /* TODO: Automatically alter this message when MAX_MESSAGE_LENGTH is changed from
           api_constants.php
        */
        8002 => 'El mensaje no puede ser m&aacute;s grande a los 15000 caracteres (aprox. 3000 palabras).',
        8003 => 'Por favor ingrese al menos una direcci&oacute;n en el campo <b>Para</b>.',
        //Messages for the Media Gallery Section.
        2001 => 'Imagen cargada',
        2002 => 'Audio cargado',
        2003 => 'Video cargado',
        2004 => 'Imagen borrado',
        2005 => 'Audio borrado',
        2006 => 'Video borrado',
        2007 => '%media% cargado satisfactoriamente',
        // Message for Customize User UI
        2008 => 'Perfil de usuario actualizado satisfactoriamente',
        // Messages for reporting abuse
        9002 => 'Un correo relacionado con este contenido ha sido enviado al Moderador de la red y al Moderador de grupo',
        9003 => 'Un correo relacionado con este contenido ha sido enviado al Moderador de la red',
        9004 => 'Su reporte no pudo ser enviado, debido a que usted no ha ingresado un mensaje',
        9005 => 'Lo sentimos, usted no es un miembro de este grupo de solo-invitados.<br /> Para unirse, necesita una invitaci&oacute;n del grupo.',
        9006 => 'Las configuraciones de grupo han sido cambiadas satisfactoriamente',
        9007 => 'El rol ha sido creado satisfactoriamente.',
        9009 => 'El rol ha sido actualizado satisfactoriamente.',
        // Message related to the Testimonials
        // Rage 9010 to 9020
        9010 => 'No est&aacute; autorizado para desarrollar esta operaci&oacute;n',
        9011 => 'El testimonio ha sido aprobado satisfactoriamente',
        9012 => 'El testimonio ha sido denegado',
        9013 => 'El testimonio ha sido enviado satisfactoriamente',
        9014 => 'El testimonio ha sido borrado satisfactoriamente',
        9015 => 'La tarea ha sido asignada satisfactoriamente. ',
        // Message Related to Comment posting ..9021 to 9031
        9021 => 'Lo sentimos, su comentario no puede ser publicado ya que parece spam. Intente removiendo cualquier enlace a sitios sospechosos y reenvie.',
        9022 => 'Su comentario ha sido publicado satisfactoriamente',
        9023 => 'Lo sentimos, su comentario no puede ser publicado ya que ha sido clasificado como spam por Akismet, o contiene enlaces a sitios en lista negra. Por favor observe los enlaces en su entrada, y que su nombre y direcci&oacute;n de correo electr&oacute;nico sean correctos.',
        9024 => 'Los comentario no pueden estar en blanco',
        9025 => 'Las configuraciones de su blog han sido guardado.',
        // messages related to content moderation are 1001
        1001 => 'Los sentimos su contenido no est&aacute; disponible para visualizar. ',
        1002 => 'Lo(s) comentario(s) ha(n) sido aceptado(s).',
        1003 => 'Lo(s) comentario(s) ha(n) sido denegado(s).',
        1004 => 'El contenido ha sido enviado para aprobaci&oacute;n.',
        1005 => 'Contenido multimedia ha sido enviado para aprobaci&oacute;n.',
        1006 => 'Por favor seleccione o aprobar o denegar desde la caja de selecci&oacute;n.',
        1007 => 'Por favor seleccione por lo menos un contenido para aprobar o denegar .',
    );

    /* This function is made for handling Dynamic msg */
    /* We can defind static message here and find the string between %-% and replace with dyanmic Messages */
    public static function get_message($msg_id, $dynamic_error_msg = null) {
        $msg = MessagesHandler::$msg_arr[$msg_id];
        if(!empty($dynamic_error_msg)) {
            $msg = preg_replace("/^%[a-z]*%/", $dynamic_error_msg, $msg);
        }
        return $msg;
    }
}
?>