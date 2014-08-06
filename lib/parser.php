<?php

/**
 * Main parser
 *
 *
 * @file     config.php
 * @category Configuration
 * @author   Víctor Téllez <tellez.victor@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */


function parseFile ($file, $bdhost, $bduser, $bdpass, $bdname, $pod_user_id) {
  //Comprobamos que existe el fichero
  if (!file_exists($file)) {
    return false;
  }

  //Abrimos conexión con la base de datos
  $mysqli = new mysqli($bdhost, $bduser, $bdpass, $bdname);
 
  if($mysqli->connect_errno) {
    return false;
  }

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

      $query = "SELECT COUNT(*) as count FROM mrbs_entry WHERE create_by = $pod_user_id AND room_name =".$aula;
      // $result = $mysqli->query($query);

      if (!$existe_aula) {
        $critical .= $actual_line.$line."\nMOTIVO: El aula solicitada no existe en el sistema.\n\n";
      }else {
          //Comprobamos que esté disponible el aula para esa fecha y horas
          $libre = true;

          $query = 'SELECT * FROM my_table';
          // $result = $mysqli->query($query);

          if(!$libre){

            $warnings .= $actual_line.$prof."\n";
          
          } else {
            
            //Comprobamos si ya existía una reserva
            $query = "SELECT COUNT(*) as count FROM mrbs_entry WHERE create_by = $POD_USER_ID AND room_name =".$aula;

            if($count == 0) {
              //Hacemos la reserva
              $query = "INSERT INTO mrbs_entry (start_time, end_time, entry_type, repeat_id, room_id, timestamp, create_by, name, profesor, type, description, Observaciones, status, reminded, info_time, info_user, ical_uid, ical_sequence, ical_recur_id) VALUES ($finicio, $ffin, entry_type, repeat_id, room_id, timestamp, $pod_user_id, name, profesor, type, description, Observaciones, status, reminded, info_time, info_user, ical_uid, ical_sequence, ical_recur_id)";
            } else {
              //Actualizamos la reserva
              $query = "UDATE mrbs_entry WHERE create_by = $pod_user_id AND room_name =".$aula;
            }

            // $result = $mysqli->query($query); 
            $done .= $actual_line.$prof."\n";
          }
      }
    }
    $cont++;

    // $result->close();
  } //foreach


  // Delete all POD old events
  $query = "DELETE FROM mrbs_entry WHERE user_id = $pod_user_id AND timestamp < $fecha";
  // $result = $mysqli->query($query);
  // $result->close();

  $mysqli->close();

  return array($done, $warnings, $critical);
}