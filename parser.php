<?php

function parseFile ($file) {

  $done = "";
  $warnings = "";
  $critical = "";

  $lines = file($file);

  $cont = 1;
  foreach($lines as $line_num => $line)
  {
    $line = rtrim($line);
    $errorline = false;
    $actual_line = "LINEA ".$cont." =>  ";
    $components = split(",",$line);

    if(count($components) == 9){

      list($code, $asig, $prof, $finicio, $ffin, $dia, $hinicio, $hfin, $aula) = $components;


    } elseif (count($components) == 10) {
      list($code, $asig, $prof, $finicio, $ffin, $dia, $hinicio, $hfin, $aula) = 
      array($components[0],$components[1],$components[3]." ".$components[2],$components[4],$components[5],$components[6],$components[7],$components[8],$components[9], );
      $warnings .= $actual_line.$prof."\n";

    } else {
      $critical .= $actual_line.$line."\nMOTIVO: Formato de línea incorrecto.\n\n";
      $errorline = true;
    }


    if(!$errorline) {
      $done .= $actual_line.$prof."\n";
    }

    $cont++;
  } //foreach

  return array($done, $warnings, $critical);
}