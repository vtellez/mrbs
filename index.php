<?php
require_once 'config.php';
require_once $phpcas_path . '/CAS.php';

// Initialize phpCAS
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);

// For production use set the CA certificate that is the issuer of the cert
// on the CAS server and uncomment the line below
// phpCAS::setCasServerCACert($cas_server_ca_cert_path);

// For quick testing you can disable SSL validation of the CAS server.
// THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION.
// VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL!
phpCAS::setNoCasServerValidation();

phpCAS::forceAuthentication();
$uvus = phpCAS::getAttribute('uid');
$nombre = phpCAS::getAttribute('cn');
$doc = phpCAS::getAttribute('irispersonaluniqueid');
$mail = phpCAS::getAttribute('mail');

//Logout
if (isset($_REQUEST['logout'])) {
  phpCAS::logout();
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html dir="ltr" xmlns="http://www.w3.org/1999/xhtml"><head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <meta name="description" content="">
  <meta name="keywords" content="">
  <meta name="author" content="Víctor Téllez tellez.victor@gmail.com">
  <link href="css/css.css" rel="stylesheet" type="text/css">
  <link rel="stylesheet" type="text/css" href="css/estilo.css" media="screen">
  <link link rel="stylesheet" href="css/awesome/css/font-awesome.min.css" rel="stylesheet">
  <link rel="shortcut icon" type="image/x-icon" href="css/img/favicon.ico">
  <title>Reserva de aulas online | Universidad de Sevilla</title>
</head>
<body>
<div id="content-wrapper">
  <div class="center-wrapper">
    <div class="content">
      <div id="main">
        <img src="css/img/logo.png" style="margin-top: -10px;">
        <div class="buttons" style="clear:both; float: right; padding:0px; padding-right: 30px;">
          <a href="?logout=1" style="font-size: 1.1em; padding: 8px; background-color: #CA4C52; color: #fff;">&nbsp;&nbsp;<i class="fa fa-sign-out"></i>&nbsp;Cerrar sesión&nbsp;&nbsp;</a>
        </div> 
<?php

  if($_POST['oculto'] == "1"){

  $error = false;

  if ($_POST['puesto'] == "" || $_POST['centro'] == "" || $_POST['nombrelista'] == "" ){
    $error = true;
  }

  $error = false;

  if($error){
  ?>

        <fieldset>
          <legend>Faltan datos en el formulario</legend>
          <p>Se ha detectado que ciertos campos del formulario no tenían un valor especificado.</p>
          <p> Por favor rellene todos los datos del formulario para que pueda enviarse la solicitud.</p>
          <br/>
          <p><h3><a href="javascript:history.go(-1)"><< Volver a editar los datos del formulario</a></h3></p>
        </fieldset> 

  <?php
  } else {
        $out = "";
        $out = $out."SOLICITUD DE NUEVA LISTA DE DISTRIBUCIÓN<br/>";
        $out = $out."------------------------------------------------------------<br/>";
        $out = $out."DATOS DEL SOLICITANTE<br/>";
        $out = $out."- Uvus: ".$uvus." <br/>";
        $out = $out."- Número de documento: ".$doc." <br/>";
        $out = $out."- Nombre y apellidos: ".$nombre." <br/>";
        $out = $out."- Dirección de correo: ".$mail." <br/>";
        $out = $out."- Puesto: ".$_POST['puesto']." <br/>";
        $out = $out."- Centro: ".$_POST['centro']." <br/>";
        
        $out = $out."<br/>";

        //enviamos por correo la solicitud

        $headers =
                      "From: Reserva de aulas online <pod-salud@us.es>\n".
                      "Reply-to: reservaulamacarena@listas.us.es\n".
                      "Content-Type: text/plain; charset=UTF-8; format=flowed\n".
                      "Content-Transfer-Encoding: 8bit";
        
        $output = str_replace("<br/>","\n\n",$out);
        $output = "NOTA: Este es un mensaje automático enviado desde la aplicación web alojada  en https://listas.us.es/solicitud/\n\n\n".$output;
        $title = "Nueva reserva de aulas online (por ".$uvus.")";
              mail("vtellez-ext@us.es",$title,$output,$headers);

        ?>
        <fieldset>
          <legend>Solicitud enviada con éxito</legend>
          <p>Se ha enviado con éxito la siguiente solicitud. Recibirá una notificación en su cuenta de correo (<?php echo $mail; ?>) cuando ésta sea procesada.</p><br/>
          <p><?php echo $out; ?></p>
        </fieldset>

    <?php  }//error

  } else {
?>

<fieldset>
  <table border="0">
    <tr>
      <td style="width: 180px; vertical-align:text-top;">
      <img src="css/img/logo-us.gif" style="float:left;"/></td>
      <td>
        <br/>
        <p style="font-size: 1.2em;">Rellene el siguiente formulario y adjunte su fichero csv (<a href="https://apoyotic-pre.us.es/reservas/areasalud/pod/" target="_blank">documentación de ayuda disponible aquí</a>) para realizar la reserva masiva de aulas online.
        </p>
        <p style="font-size: 1.2em;">Para cualquier duda o sugerencia, puede hacerlo a través de <a href="https://webapps.us.es/sos" target="_blank">nuestra plataforma de gestión de incidencias</a>.</p>
        <div class="content-separator"></div>
        <form method="POST" enctype="multipart/form-data" action="index.php" >
          <input type="hidden" name="oculto" value="1" />
          <h2><i class="fa fa-user"></i>&nbsp;Datos del solicitante</h2><br/>
          <label>UVUS</label>
          <input type="text" name="uvus" maxlength="150" size="50" style="width:50%"  value="<?php echo $uvus; ?>" readonly="readonly"/>
          <br/><br/>
          <label>Nombre completo</label>
          <input type="text" name="nombre" maxlength="150" size="50" style="width:50%"  value="<?php echo $nombre; ?>" readonly="readonly"/>
          <br/><br/>
          <label>DNI/PASAPORTE</label>
          <input type="text" name="dni" maxlength="150" size="50" style="width:50%"  value="<?php echo $doc; ?>" readonly="readonly"/>
          <br/><br/>
          <label>Dirección de correo</label>
          <input type="text" name="correo" maxlength="150" size="50" style="width:50%"  value="<?php echo $mail; ?>" readonly="readonly"/>
          <br/><br/>
          <label>Puesto que ocupa</label>
          <input type="text" name="puesto" maxlength="150" size="50" style="width:50%"  value=""/>
          <br/><br/>
          <label>Servicio/Centro/Depto./Grupo</label>
          <input type="text" name="centro" maxlength="150" size="50" style="width:50%"  value=""/>
          <br/><br/>
          <div class="content-separator"></div>
          <h2><i class="fa fa-file"></i>&nbsp; Fichero de reservas</h2><br/>
          <label>Fichero CSV</label>
          <input type="file" name="image_file" id="image_file" onchange="fileSelected();" />
          <br/><br/>
          <label>Descripción del fichero</label>
          <textarea style="width:50%;" rows="6" name="descripcion" placeholder="Añada una descripción del tipo de reservas. Ejemplo: 'Reserva de aulas para el segundo cuatrimestre de la facultad de Biología' "></textarea>
          <br/><br/><br/>
        </td>
    </tr>
  </table>
</fieldset>

  <div class="buttons" style="float: none; margin: 30px; clear:both;">
  <center>
    <button type="submit" style="height: 70px; font-size: 1.4em; background-color: #5BAF4B; color: #fff;">
      &nbsp;&nbsp;<i class="fa fa-send"></i>&nbsp;&nbsp;Realizar solicitud de reservas&nbsp;&nbsp;
    </button>
  </center>
  </div>

</form>
<?php }//else ?>



<div style="clear:both; font-size: 1.2em;">
  <center>
    <a href="http://www.us.es/servicios/sic">Servicio de Informática y Comunicaciones</a> <br/> <a href="http://www.us.es/">Universidad de Sevilla</a> 
  </center> 
</div>  
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>