<?php

require "configUS.php";

function parseFile ($file) {

  //Comprobamos que existe el fichero
  if (!file_exists($file)) {
    return false;
  }

  //Abrimos conexión con la base de datos
  $link = mysqli_connect($bdhost, $bduser, $bdpass);
  if(!$link) 
    return false;
 
  $bdselected = mysqli_select_db($bdname);
  if(!$bdselected) 
    return false;

  $done = "";
  $warnings = "";
  $critical = "";

  $lines = file($file);

  $cont = 1;
  foreach ($lines as $line_num => $line) {
    $line = rtrim($line);
    $errorline = false;
    $actual_line = "LINEA ".$cont." =>  ";
    $components = split(",",$line);

    if (count($components) == 9){
      list($code, $asig, $prof, $finicio, $ffin, $dia, $hinicio, $hfin, $aula) = $components;
    } elseif (count($components) == 10) {
      list($code, $asig, $prof, $finicio, $ffin, $dia, $hinicio, $hfin, $aula) = 
      array($components[0],$components[1],$components[3]." ".$components[2],$components[4],$components[5],$components[6],$components[7],$components[8],$components[9], );
    } else {
      $critical .= $actual_line.$line."\nMOTIVO: Formato de línea incorrecto.\n\n";
      $errorline = true;
    }

    if (!$errorline) {
      //Comprobamos que exista el aula
      $existe_aula = true;

      $query = 'SELECT * FROM my_table';
      $result = mysqli_query($query);

      if (!$existe_aula) {
        $critical .= $actual_line.$line."\nMOTIVO: El aula solicitada no existe en el sistema.\n\n";
      }else {
          //Comprobamos que esté disponible el aula para esa fecha y horas
          $libre = true;

          $query = 'SELECT * FROM my_table';
          $result = mysqli_query($query);

          if(!$libre){
            $warnings .= $actual_line.$prof."\n";
          } else {
            //Hacemos la reserva
            $query = 'SELECT * FROM my_table';
            $result = mysqli_query($query);  

            $done .= $actual_line.$prof."\n";
          }
      }
    }
    $cont++;
  } //foreach


  mysqli_free_result($result);
  mysqli_close($link);

  return array($done, $warnings, $critical);
}