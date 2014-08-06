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
    $components= array_map('trim', $components);

    if (count($components) == 9){
      list($code, $asig, $prof, $finicio, $ffin, $dia, $hinicio, $hfin, $aula) = $components;
    } elseif (count($components) == 10) {
      list($code, $asig, $prof, $finicio, $ffin, $dia, $hinicio, $hfin, $aula) = 
      array($components[0],$components[1],$components[3]." ".$components[2],$components[4],$components[5],$components[6],$components[7],$components[8],$components[9] );
    } else {
      $critical .= $actual_line.$line." (MOTIVO: Formato de línea incorrecto.)\n\n";
      $errorline = true;
    }

    if (!$errorline) {
      //Comprobamos que exista el aula

      //MRBS ROOM
      // +------------------+-------------+------+-----+---------+----------------+
      // | Field            | Type        | Null | Key | Default | Extra          |
      // +------------------+-------------+------+-----+---------+----------------+
      // | id               | int(11)     | NO   | PRI | NULL    | auto_increment |
      // | disabled         | tinyint(1)  | NO   |     | 0       |                |
      // | area_id          | int(11)     | NO   |     | 0       |                |
      // | room_name        | varchar(25) | NO   |     |         |                |
      // | sort_key         | varchar(25) | NO   | MUL |         |                |
      // | description      | varchar(60) | YES  |     | NULL    |                |
      // | capacity         | int(11)     | NO   |     | 0       |                |
      // | room_admin_email | text        | YES  |     | NULL    |                |
      // | custom_html      | text        | YES  |     | NULL    |                |
      // +------------------+-------------+------+-----+---------+----------------+


      $query = "SELECT * FROM mrbs_room WHERE room_name = '".$aula."'";
      $result = $mysqli->query($query);

      if ($result->num_rows < 1) {
        $critical .= $actual_line.$line." (MOTIVO: El aula '$aula' no existe en el sistema.)\n\n";
      }else {
          $room = $result->fetch_assoc();

          //Comprobamos que esté disponible el aula para esa fecha y horas
          $libre = true;

          $query = 'SELECT * FROM my_table';
          // $result = $mysqli->query($query);

          if(!$libre){

            $warnings .= $actual_line.$line."\n";
          
          } else {
            
            //Comprobamos si ya existía una reserva
            $query = "SELECT COUNT(*) as count FROM mrbs_entry WHERE create_by = $pod_user_id AND room_name =".$aula;

            $count = 0;


            // while($row = $result->fetch_assoc()){
            //     echo $row['count'] . '<br />';
            // }

            // MRBS ENTRY
            // +---------------+---------------------+------+-----+-------------------+-----------------------------+
            // | Field         | Type                | Null | Key | Default           | Extra                       |
            // +---------------+---------------------+------+-----+-------------------+-----------------------------+
            // | id            | int(11)             | NO   | PRI | NULL              | auto_increment              |
            // | start_time    | int(11)             | NO   | MUL | 0                 |                             |
            // | end_time      | int(11)             | NO   | MUL | 0                 |                             |
            // | entry_type    | int(11)             | NO   |     | 0                 |                             |
            // | repeat_id     | int(11)             | NO   |     | 0                 |                             |
            // | room_id       | int(11)             | NO   |     | 1                 |                             |
            // | timestamp     | timestamp           | NO   |     | CURRENT_TIMESTAMP | on update CURRENT_TIMESTAMP |
            // | create_by     | varchar(80)         | NO   |     |                   |                             |
            // | name          | varchar(90)         | NO   |     |                   |                             |
            // | profesor      | varchar(70)         | NO   |     |                   |                             |
            // | type          | char(1)             | NO   |     | E                 |                             |
            // | description   | text                | YES  |     | NULL              |                             |
            // | Observaciones | text                | YES  |     | NULL              |                             |
            // | status        | tinyint(3) unsigned | NO   |     | 0                 |                             |
            // | reminded      | int(11)             | YES  |     | NULL              |                             |
            // | info_time     | int(11)             | YES  |     | NULL              |                             |
            // | info_user     | varchar(80)         | YES  |     | NULL              |                             |
            // | info_text     | text                | YES  |     | NULL              |                             |
            // | ical_uid      | varchar(255)        | NO   |     |                   |                             |
            // | ical_sequence | smallint(6)         | NO   |     | 0                 |                             |
            // | ical_recur_id | varchar(16)         | NO   |     |                   |                             |
            // +---------------+---------------------+------+-----+-------------------+-----------------------------+


            if($count == 0) {
              //Hacemos la reserva
              $query = "INSERT INTO mrbs_entry (start_time, end_time, entry_type, repeat_id, room_id, create_by, name, profesor, type, ical_uid, ical_recur_id) VALUES ($finicio, $ffin, entry_type, 1, $room['id'], '$pod_user_id', '$asig', '$prof', 'B', '20131017T093000Z', '00Z')";
            } else {
              //Actualizamos la reserva
              $query = "UDATE mrbs_entry WHERE create_by = $pod_user_id AND room_name =".$aula;
            }

            // $result = $mysqli->query($query); 
            $done .= $actual_line.$line."Produce: $query\n";
          }
      }
    }
    $cont++;

    // $result->close();
  } //foreach


  // Delete all POD old events
  //$query = "DELETE FROM mrbs_entry WHERE user_id = $pod_user_id AND timestamp < $fecha";
  // $result = $mysqli->query($query);
  // $result->close();

  $mysqli->close();

  return array($done, $warnings, $critical);
}