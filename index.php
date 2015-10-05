<?php
require_once 'config/config.php';
require_once 'lib/parser.php';
require_once $phpcas_path . '/CAS.php';

// Initialize phpCAS
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
// For production use set the CA certificate that is the issuer of the cert
// phpCAS::setCasServerCACert($cas_server_ca_cert_path);
phpCAS::setNoCasServerValidation();
phpCAS::forceAuthentication();
$uvus = phpCAS::getAttribute('uid');
$nombre = phpCAS::getAttribute('cn');
$doc = phpCAS::getAttribute('irispersonaluniqueid');
$mail = phpCAS::getAttribute('mail');

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
<script src="js/jquery-1.10.2.min.js"></script>
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
          if(isset($_POST['oculto']) && $_POST['oculto'] == "1"){

            $error = false;
            //Manage CSV upload
            if(!empty($_FILES['file'])) {
              $name=$_FILES['file']['name'];
              $size=$_FILES['file']['size'];
              $type=$_FILES['file']['type'];
              $tmp_name=$_FILES['file']['tmp_name'];
              $error=$_FILES['file']['error'];
              $maxsize ="102400";
              $location='/var/www/html/reservas/areasalud/pod/temp/';
              $final_name = time()."_".$uvus.".csv";

              if(move_uploaded_file($tmp_name, $location.$final_name)) {
                $res = parseFile($location.$final_name, $_POST['centro'], $uvus);
                if(!$res){
                  $error = true;
                } else {
                  list($done, $warnings, $critical) = $res;
                }

              } else {
                $error = true;
              }
            }

            if($error) { ?>
              <fieldset>
                <h2><i class="fa fa-times"></i>&nbsp;Se han encontrado errores</h2>
                <ul style="font-size: 1.3em;">
                  <li>Su uvus debe estar dado de alta en el sistema como administrador del centro que intenta actualizar.</li>
                  <li>El fichero debe tener <b>formato CSV</b> válido (ver <a href="doc/userdoc.pdf" target="_blank">documentación</a>).</li>
                  <li>El fichero debe tener un tamaño máximo de <b>1024 KBytes</b>.</li>
                </ul>
                <p style="font-size: 1.3em;"> Por favor revise que cumple estos requisitos para poder continuar.</p>
              </fieldset>

              <div class="buttons" style="margin: 30px; clear:both;">
                <center>
                  <button onclick="javascript:history.go(-1);" style="height: 70px; font-size: 1.4em; background-color: #248CC7; color: #fff;">
                    &nbsp;&nbsp;<i class="fa fa-arrow-left"></i>&nbsp;&nbsp;Revisar los datos del formulario&nbsp;&nbsp;
                  </a>
                </button>
              </div> 

              <?php
            } else {
              $headers =
              "From: Reserva de aulas online <pod-salud@us.es>\n".
              "Reply-to: reservaulamacarena@listas.us.es\n".
              "Content-Type: text/plain; charset=UTF-8; format=flowed\n".
              "Content-Transfer-Encoding: 8bit";


              $output = "Reservas confirmadas:\n\n".$done."\n\nReservas NO realizadas por ocupación:\n\n".$warnings."\n\nReservas NO realizadas por errores fatales:\n\n".$critical."\n\n";
              $title = "Nueva reserva de aulas online (por ".$uvus.")";
              mail("vtellez-ext@us.es",$title,$output,$headers);
              mail("rsierra@us.es",$title,$output,$headers);
              mail($mail,$title,$output,$headers);

              ?>
              <fieldset>
                <h2><i class="fa fa-cloud-upload"></i>&nbsp;Fichero recibido y procesado</h2>
                <p style="font-size: 1.3em;">Su fichero se ha recibido y procesado con éxito en el sistema de reservas, se le ha enviado un email como acuse de recibo a su cuenta de correo <b><?php echo $mail; ?></b>, incluyendo el siguiente informe:</p>

                <br/>
                <h3 style="color: #328113;"><i class="fa fa-check"></i>&nbsp; Reservas confirmadas:</h3>
                <textarea rows="10" style="width: 100%;"><?php echo $done; ?></textarea>

                <br/>
                <br/>
                <br/>
                <h3 style="color:#F89200;"><i class="fa fa-warning"></i>&nbsp; Reservas NO realizadas por ocupación:</h3>
                <textarea rows="10" style="width: 100%;"><?php echo $warnings; ?></textarea>


                <br/>
                <br/>
                <br/>
                <h3 style="color:#B24747;"><i class="fa fa-times"></i>&nbsp;Reservas NO realizadas por errores fatales:</h3>
                <textarea rows="10" style="width: 100%;"><?php echo $critical; ?></textarea>

              </fieldset>

              <div class="buttons" style="margin: 30px; clear:both;">
                <center>
                  <button onClick="location.href='index.php'" style="height: 70px; font-size: 1.4em; background-color: #248CC7; color: #fff;">
                    &nbsp;&nbsp;<i class="fa fa-plus"></i>&nbsp;&nbsp;Realizar nueva solicitud de reservas&nbsp;&nbsp;
                  </a>
                </button>
              </div>

              <?php } //else no errors

              } else { // no post process
              ?>
              <br/>
              <fieldset>
                <table border="0">
                  <tr>
                    <td style="width: 180px; vertical-align:text-top;">
                      <img src="css/img/logo-us.gif" style="float:left;"/></td>
                      <td>
                        <br/>
                        <p style="font-size: 1.2em;">Rellene el siguiente formulario para realizar la reserva masiva de aulas online.
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
                          <div class="content-separator"></div>
                          <h2><i class="fa fa-building"></i>&nbsp; Datos del centro</h2><br/>

                          <p style="clear:both; font-size: 1.2em;">Determine para que centro desea realizar las reservas:</a> </p>
                          <label>CENTROS ADSCRITOS</label>
                          <select name="centro" style="width: 50%;">
                            <option value="ci">CI - CENTRO INTERNACIONAL</option>
                            <option value="eps">EPS - ESCUELA POLITECNICA SUPERIOR</option>
                            <option value="etsi">ETSI - ESCUELA TECNICA SUPERIOR DE INGENIERIA</option>
                            <option value="etsia">ETSIA - ESCUELA TECNICA SUPERIOR DE INGENIERIA AGRONOMA</option>
                            <option value="etsiinf">ETSIINF - ESCUELA TECNICA SUPERIOR DE INGENIERIA INFORMATICA</option>
                            <option value="etsa">ETSA - ESCUELA TECNICA SUPERIOR DE ARQUITECTURA</option>
                            <option value="etsie">ETSIE - ESCUELA TECNICA SUPERIOR DE INGENIERIA DE EDIFICIACION</option>
                            <option value="fba">FBA - FACULTAD DE BELLAS ARTES</option>
                            <option value="fbio">FACTULAD DE BIOLOGIA</option>
                            <option value="fced">FCED - FACULTAD DE CIENCIAS DE LA EDUCACION</option>
                            <option value="fcee">FCEE - FACULTAD DE CIENCIAS ECONOMICAS Y EMPRESARIALES</option>
                            <option value="fcom">FCOM - FACULTAD DE COMUNICACION</option>
                            <option value="fct">FCT - FACULTAD DE CIENCIAS DEL TRABAJO</option>
                            <option value="fder">FDER - FACULTAD DE DERECHO</option>
                            <option value="fefp">FEFP - FACULTAD DE ENFERMERIA, FISIOTERAPIA Y PODOLOGIA</option>
                            <option value="ffa">FFA - FACULTAD DE FARMACIA</option>
                            <option value="ffilol">FFILOL - FACULTAD DE FILOLOGIA</option>
                            <option value="ffilos">FFILOS - FACULTAD DE FILOSOFIA</option>
                            <option value="ffis">FFIS - FACULTAD DE FISICA</option>
                            <option value="fgh">FGH - FACULTAD DE GEOGRAFIA E HISTORIA</option>
                            <option value="fmat">FMAT - FACULTAD DE MATEMATICAS</option>
                            <option value="fmed">FMED - FACULTAD DE MEDICINA</option>
                            <option value="fodon">FODON - FACULTAD DE ODONTOLOGIA</option>
                            <option value="fpsi">FPSI - FACULTAD DE PSICOLOGIA</option>
                            <option value="fqui">FQUI - FACULTAD DE QUIMICA</option>
                            <option value="ftf">FTF - FACULTAD DE TURISMO Y FINANZAS</option>
                          </select>

                          <br/><br/><br/>
                          <div class="content-separator"></div>
                          <h2><i class="fa fa-file"></i>&nbsp; Fichero de reservas</h2><br/>

                          <p style="clear:both; font-size: 1.2em;">Consulte, si lo desea, la <a href="doc/userdoc.pdf" target="_blank">documentación del formato del fichero CSV</a> </p>

                          <label>Fichero CSV</label>
                          <input type="file" name="file" id="file" />

                          <br/><br/><br/>
                        </td>
                      </tr>
                    </table>
                  </fieldset>

                  <div class="buttons" style="margin: 30px; clear:both;">
                    <center>
                      <button type="submit" id="sendbtn" style="height: 70px; font-size: 1.4em; background-color: #5BAF4B; color: #fff;" onclick="$('#sendbtn').toggle(); $('#loadingbtn').toggle();">
                        &nbsp;&nbsp;<i class="fa fa-send"></i>&nbsp;&nbsp;Realizar solicitud de reservas&nbsp;&nbsp;
                      </button>

                    </form>
                    <button id="loadingbtn" disabled="disabled" style="display: none; height: 70px; font-size: 1.4em; background-color: #78C969; color: #fff;">
                      &nbsp;&nbsp;<i class="fa fa-spinner fa-spin" style="font-size: 25px;"></i>&nbsp;&nbsp;Enviando fichero CSV&nbsp;&nbsp;
                    </button>
                  </center>
                </div>

                <?php } //else ?>

                <div style="clear:both; font-size: 1.2em;">
                  <center>
                    <a href="http://www.us.es/servicios/sic">Servicio de Informática y Comunicaciones</a> 
                    <br/> 
                    <a href="http://www.us.es/">Universidad de Sevilla</a> 
                  </center> 
                </div>  
              </div>
            </div>
          </div>
        </div>
      </div>
    </body>
    </html>