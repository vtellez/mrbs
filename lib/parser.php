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

  //Borramos todas las reservas previas del usuario pod
  $query = "DELETE FROM mrbs_entry WHERE create_by = '$pod_user_id'";
  $result = $mysqli->query($query);


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
    $components= array_map('trim', $components);

    if (count($components) == 9) {
      list($code, $asig, $prof, $finicio, $ffin, $dia, $hinicio, $hfin, $aula) = $components;
    } elseif (count($components) == 10) {
      list($code, $asig, $prof, $finicio, $ffin, $dia, $hinicio, $hfin, $aula) = 
      array($components[0],$components[1],$components[3]." ".$components[2],$components[4],$components[5],$components[6],$components[7],$components[8],$components[9] );
    } else {
      $critical .= $actual_line.$line." (MOTIVO: Formato de línea incorrecto.)\n\n";
      $errorline = true;
    }

    if(!$errorline){
      //calculate timestamps

      $finicio = str_replace('/', '-', $finicio);
      $finicio =  date('Y-m-d', strtotime($finicio)); // ej. 2010-05-25
      
      $ffin = str_replace('/', '-', $ffin);
      $ffin =  date('Y-m-d', strtotime($ffin)); // ej. 2010-05-25

      $tinicio = strtotime($finicio);
      $tfin = strtotime($ffin);


      if(!isValidTimeStamp($tinicio) || !isValidTimeStamp($tfin) || $finicio > $ffin) {

          $critical .= $actual_line.$line." (MOTIVO: Formato de fechas incorrecto.)\n\n";
          $errorline = true;
      
      } else {
        for ( $date = $tinicio; $date <= $tfin; $date += 60 * 60 * 24) {
            if ( strftime('%w', $date) == 1 )  {
                $lunes[] = strftime('%A %Y-%m-%d', $date);
            } if ( strftime('%w', $date) == 2 ) {
                $martes[] = strftime('%A %Y-%m-%d', $date);
            } if ( strftime('%w', $date) == 3 ) {
                $miercoles[] = strftime('%A %Y-%m-%d', $date);
            } if ( strftime('%w', $date) == 4 ) {
                $jueves[] = strftime('%A %Y-%m-%d', $date);
            } if ( strftime('%w', $date) == 5 ) {
                $viernes[] = strftime('%A %Y-%m-%d', $date);
            } if ( strftime('%w', $date) == 6 ) {
                $sabado[] = strftime('%A %Y-%m-%d', $date);
            } if ( strftime('%w', $date) == 0 ) {
                $domingo[] = strftime('%A %Y-%m-%d', $date);
            }
        }

        $reps = array();
        switch ($dia) {
          case 'LUN':
            $reps = $lunes;
            break;
          case 'MAR':
            $reps = $martes;
            break;
          case 'MIE':
            $reps = $miercoles;
            break;
          case 'JUE':
            $reps = $jueves;
            break;
          case 'VIE':
            $reps = $viernes;
            break;
          case 'SAB':
            $reps = $sabado;
            break;
          case 'DOM':
            $reps = $domingo;
            break;
          default:
            $critical .= $actual_line.$line." (MOTIVO: Formato de fechas incorrecto.)\n\n";
            $errorline = true;
            break;
        }




      }
    
    }

    if (!$errorline) {

      foreach ($reps as $repdate) {
        //calculate timestamps
        $date = str_replace('/', '-', $repdate);
        $date =  date('Y-m-d', strtotime($date)); // ej. 2010-05-25

        $tinicio = strtotime($date." ".$hinicio);
        $tfin = strtotime($date." ".$hfin);

        //Comprobamos que exista el aula
        $query = "SELECT * FROM mrbs_room WHERE room_name = '".$aula."'";
        $result = $mysqli->query($query);

        if ($result->num_rows < 1) {
          $critical .= $actual_line.$line." (MOTIVO: El aula '$aula' no existe en el sistema.)\n\n";
        }else {
            $room = $result->fetch_assoc();

            //Comprobamos que esté disponible el aula para esa fecha y horas
            $libre = true;

            $query = "SELECT * FROM mrbs_entry WHERE room_id = ".$room['id']." AND start_time <= $tinicio AND end_time >= $tfin AND create_by <> '$pod_user_id'";
            $result = $mysqli->query($query);
            // echo "<p>$query</p>";

            if($result->num_rows > 0){

              $warnings .= $actual_line.$line."\n";
            
            } else {
              
              //Comprobamos si ya existía una reserva
              $query = "SELECT * FROM mrbs_entry WHERE room_id = ".$room['id']." AND start_time <= $tinicio AND end_time >= $tfin AND create_by = '$pod_user_id'";
              $result = $mysqli->query($query);

              if($result->num_rows == 0) {
                //Hacemos la reserva
                $query = "INSERT INTO mrbs_entry (start_time, end_time, entry_type, repeat_id, room_id, create_by, name, profesor, type, ical_uid, ical_recur_id) VALUES ($tinicio, $tfin, 0, 0, ".$room['id'].", '$pod_user_id', '$asig', '$prof', 'B', '20131017T093000Z', '00Z')";
              }
             
              $result = $mysqli->query($query); 
              $done .= "\n".$actual_line.$line."  Produce: $query\n";
            }
        }

      }//foreach reps
    }

    $cont++;

    // $result->close();
  } //foreach


  $mysqli->close();
  return array($done, $warnings, $critical);
}







function isValidTimeStamp($timestamp)
{
    if(strtotime(date('d-m-Y H:i:s',$timestamp)) === (int)$timestamp) {
        return $timestamp;
    } else return false;
}