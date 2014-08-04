<?php
include('libopensso-php/OpenSSO.php');
$o = new OpenSSO();
$res1 = $o->check_and_force_sso();


if($res1 == false)
{
        echo "ERROR: No se pudo establecer una conexión con el sistema Single Sign-On";
        exit();
}

if($_GET['logout'] == 1){
	$o->logout(TRUE);
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html dir="ltr" xmlns="http://www.w3.org/1999/xhtml"><head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="author" content="">
	<link href="css/css.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" type="text/css" href="css/estilo.css" media="screen">
	<link rel="shortcut icon" type="image/x-icon" href="css/img/favicon.ico">

	<title>Solicitud de una lista de distribución | Servicio de Correo electrónico</title>

	<script language="JavaScript" type="text/javascript">
	function cambio(id){
	if (document.getElementById){ //se obtiene el id
	var el = document.getElementById(id); //se define la variable "el" igual a nuestro div
	el.style.display = (el.style.display == 'none') ? 'block' : 'none'; //damos un atributo display:none que oculta el div
	}
	}
	
	function cambio2(id,id2,id3){
	//Cambia el estado de id y oculta id2 e id3
		oculta(id2);
		oculta(id3);
		cambio(id);		
	}
	
	function oculta(id){
	var el = document.getElementById(id); 
	el.style.display = 'none';
	}
	
	function muestra(id){
	var el = document.getElementById(id); 
	el.style.display = 'block';
	}

	window.onload = function(){
	cambio('oculto');
	}
</script>


</head><body>
<div id="content-wrapper">
	<div class="center-wrapper">
		<div class="content">
			<div id="main">
		<img src="css/img/logo.png" style="margin-top: -25px;">
<br/><br/>

<fieldset>

<?php

	if($_POST['oculto'] == "1"){

	$error = false;

	if($_POST['igual'] != "1"){
		if($_POST['adni'] == "" || $_POST['anombre'] == "" || $_POST['aapellidos'] == "" || $_POST['apuesto'] == "" || $_POST['acentro'] == "" ||   $_POST['acorreo'] == "" ){ 
			$error = true;}
	}	
	

	if ($_POST['puesto'] == "" || $_POST['centro'] == "" || $_POST['nombrelista'] == "" ){
		$error = true;
	}

	if($error){
	?>


	<legend>Faltan datos en el formulario</legend>
	<p>Se ha detectado que ciertos campos del formulario no tenían un valor especificado.</p>
	<p> Por favor rellene todos los datos del formulario para que pueda enviarse la solicitud.</p>
        <br/><p><h3><a href="javascript:history.go(-1)"><< Volver a editar los datos del formulario</a></h3></p>
        </fieldset>	

	<?php
	
	}

	if(!$error){
	$out = "";
	$out = $out."SOLICITUD DE NUEVA LISTA DE DISTRIBUCIÓN<br/>";
	$out = $out."------------------------------------------------------------<br/>";
	$out = $out."DATOS DEL SOLICITANTE<br/>";
	$out = $out."- Uvus: ".$o->attribute('uid')." <br/>";
	$out = $out."- Número de documento: ".$o->attribute('irispersonaluniqueid')." <br/>";
	$out = $out."- Nombre y apellidos: ".$o->attribute('givenname')." ".$o->attribute('sn')." <br/>";
	$out = $out."- Dirección de correo: ".$o->attribute('irismailmainaddress')." <br/>";
	$out = $out."- Puesto: ".$_POST['puesto']." <br/>";
	$out = $out."- Centro: ".$_POST['centro']." <br/>";
	
	$out = $out."<br/>";
	$out = $out."DATOS DEL ADMINISTRADOR<br/>";
	if($_POST['igual'] == "1"){
		$out = $out."- El administrador es el propio solicitante.<br/>";
        }else{
	        $out = $out."- Número de documento: ".$_POST['adni']." <br/>";
        	$out = $out."- Nombre y apellidos: ".$_POST['anombre']." ".$_POST['aapellidos']." <br/>";
	        $out = $out."- Dirección de correo: ".$_POST['acorreo']." <br/>";
	        $out = $out."- Puesto: ".$_POST['apuesto']." <br/>";
	        $out = $out."- Centro: ".$_POST['acentro']." <br/>";
	}
        $out = $out."<br/>";
        $out = $out."DATOS DE LA LISTA<br/>";
	$out = $out."- Nombre de la lista: ".$_POST['nombrelista']."@listas.us.es <br/>";
	$out = $out."- Descripción: ".$_POST['descripcion']." <br/>";
	$out = $out."- Privacidad: ".$_POST['privi']." <br/>";
	$out = $out."- Control de subscripción: ".$_POST['visi']." <br/>";
	$out = $out."- Moderación: ".$_POST['mod']." <br/>";


	//enviamos por correo la solicitud

	$headers =
                "From: listas.us.es <no-responder@listas.us.es>\n".
                "Content-Type: text/plain; charset=UTF-8; format=flowed\n".
                "Content-Transfer-Encoding: 8bit";
	
	$output = str_replace("<br/>","\n\n",$out);
	$output = "NOTA: Este es un mensaje automático enviado desde la aplicación web alojada  en https://listas.us.es/solicitud/\n\n\n".$output;
	$title = "Solicitud de nueva lista de correo (".$_POST['nombrelista']."@listas.us.es)";
        mail("jenrique@us.es",$title,$output,$headers);
        mail("correo@us.es",$title,$output,$headers);

	?>

        <legend>Solicitud enviada con éxito</legend>
        <p>Se ha enviado con éxito la siguiente solicitud. Recibirá una notificación en su cuenta de correo (<?php echo $_POST['correo']; ?>) cuando ésta sea procesada.</p><br/>
        <p><?php echo $out; ?></p>
        <br/><p><h3><a href="?logout=1">Cerrar sesión de usuario</a></h3></p>
        </fieldset>

		<?php 	}//error

	}else{
?>
<legend>Formulario de solicitud de nueva lista</legend>

	<table border="0">
		<tr>
			<td style="width: 180px; vertical-align:text-top;">
			<img src="css/img/logo-us.gif" style="float:left;"/></td>
			<td>

<p>Rellene el siguiente formulario con la información de su solicitud y recibirá una notificación en su correo corporativo relativa a la creación de dicha lista. TODOS los campos son obligatorios.
</p>

<p>Para cualquier duda o sugerencia, puede hacerlo a través de <a href="https://webapps.us.es/sos" target="_blank">nuestra plataforma de gestión de incidencias</a>.</p>

<div class="content-separator"></div>

<form method="POST" action="index.php">
<input type="hidden" name="oculto" value="1" />

        <h2>Datos del solicitante</h2><br/>

        <label>UVUS</label>
        <input type="text" name="uvus" maxlength="150" size="50" style="width:50%"  value="<?php   echo $o->attribute('uid'); ?>" readonly="readonly"/>
        <br/><br/>

        <label>Apellidos</label>
        <input type="text" name="apellidos" maxlength="150" size="50" style="width:50%"  value="<?php echo $o->attribute('sn'); ?>" readonly="readonly"/>
        <br/><br/>

        <label>Nombre</label>
        <input type="text" name="nombre" maxlength="150" size="50" style="width:50%"  value="<?php echo $o->attribute('givenname'); ?>" readonly="readonly"/>
        <br/><br/>

        <label>DNI/PASAPORTE</label>
        <input type="text" name="dni" maxlength="150" size="50" style="width:50%"  value="<?php echo $o->attribute('irispersonaluniqueid'); ?>" readonly="readonly"/>
        <br/><br/>

        <label>Dirección de correo</label>
        <input type="text" name="correo" maxlength="150" size="50" style="width:50%"  value="<?php echo $o->attribute('irismailmainaddress'); ?>" readonly="readonly"/>
        <br/><br/>

        <label>Puesto que ocupa</label>
        <input type="text" name="puesto" maxlength="150" size="50" style="width:50%"  value=""/>
        <br/><br/>

        <label>Servicio/Centro/Depto./Grupo</label>
        <input type="text" name="centro" maxlength="150" size="50" style="width:50%"  value=""/>
        <br/><br/>

	<div class="content-separator"></div>
	<h2>Datos del administrador</h2>
	<br/>
	<input type="checkbox" name="igual" checked="checked" value="1" onclick="javascript:cambio('oculto');" /> Solicitante y administrador de la lista son la misma persona.
	<br/><br/>
	<div id="oculto">
 	<label>Apellidos</label>
        <input type="text" name="aapellidos" maxlength="150" size="50" style="width:50%"  value=""/>
        <br/><br/>

        <label>Nombre</label>
        <input type="text" name="anombre" maxlength="150" size="50" style="width:50%"  value=""/>
        <br/><br/>

        <label>DNI/PASAPORTE</label>
        <input type="text" name="adni" maxlength="150" size="50" style="width:50%"  value=""/>
        <br/><br/>

        <label>Dirección de correo</label>
        <input type="text" name="acorreo" id="" maxlength="150" size="50" style="width:50%"  value=""/>
        <br/><br/>

        <label>Puesto que ocupa</label>
        <input type="text" name="apuesto" maxlength="150" size="50" style="width:50%"  value=""/>
        <br/><br/>

        <label>Servicio/Centro/Depto./Grupo</label>
        <input type="text" name="acentro" id="" maxlength="150" size="50" style="width:50%"  value=""/>
        <br/><br/>
	</div>
	<div class="content-separator"></div>
	<h2>Datos de la lista</h2><br/>
        <label>Nombre de la lista</label>
        <input type="text" name="nombrelista" value="" id="mfrom" maxlength="20" size="50" style="width:38%"  value="vtellez@us.es" />@listas.us.es
        <br/><br/>

        <label>Descripción de la lista</label>
		<textarea style="width:50%;" rows="4" name="descripcion"></textarea>
	<br/><br/>

	<label>Características de la lista</label>
        <a href="http://www.us.es/campus/servicios/sic/correo/listasdis/intrlistas" target="_blank">(Ver información sobre características de listas)</a>
	<br/><br/>
        
	<label>Privacidad</label>
	<input name="privi" type="radio" value="Pública"/>Pública&nbsp;&nbsp;&nbsp;
	<input name="privi" type="radio" checked="checked" value="Privada"/>Privada
	<br/><br/>


        <label>Control de subscripción</label>
        <input name="visi" type="radio" value="Abierta"/>Abierta&nbsp;&nbsp;&nbsp;&nbsp;
        <input name="visi" type="radio" checked="checked" value="Cerrada"/>Cerrada
        <br/><br/>


        <label>Moderación</label>
        <input name="mod" type="radio" value="Moderada"/>Moderada
        <input name="mod" type="radio" checked="checked" value="No moderada"/>No moderada
        <br/><br/><br/>
   			</td>
		</tr>
	</table>
</fieldset>
<br/>
  <div class="buttons" style="float: right; ">
	<input type="submit" value="Enviar solicitud de nueva lista" />
  </div>

<?php }//else ?>

<br/>
<br/>
<br/>
		<center>
			<a href="http://www.us.es/servicios/sic">Servicio de Informática y Comunicaciones</a> <br/> <a href="http://www.us.es/">Universidad de Sevilla</a> 
		</center>		
				</div>
			</div>
		</div>
	</div>
</div>
</body>
</html>
