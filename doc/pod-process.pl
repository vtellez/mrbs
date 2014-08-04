#!/usr/bin/perl
#
# POD-mrbs definition file load
# -----------------------------------------------------------------------
#/*
# *    Copyright 2014 Víctor Téllez Lozano <tellez.victor@gmail.com>
# *
# *    This file is part of POD-mrbs.
# */

use utf8;
binmode(STDOUT, ":utf8");

# Realizamos la conexión a la base de datos
#$dbh = DBI->connect($connectionInfo,$userid,$passwd, {mysql_enable_utf8=>1}) or die "Can't connect to the database.\n";

#local vars
my %warnings=();

my $filename = 'horario1.csv';

if (open(my $fh, '<:encoding(UTF-8)', $filename)) {
  while (my $row = <$fh>) {
    chomp $row;
    
 	if ($row=~/(.+);(.+);(.+);(.+);(.+);(.+);(.+);(.+);(.+);(.+);(.+);(.+);(.+);(.+);(.+);(.+);(.+);(.+)/) {
		#convertimos la fecha tai64 de qmail a timestamp de unix
		$fecha{$2}=tai2unix($1);

	    #PL;ES;C.ASIG.;ASIGNATURA;R;DUR.;GRP.;CAP.;CRED.;DNI;PROFESOR;F_DESDE;F_HASTA;C.DIA;DIA;H_INICIO;H_FIN;AULA
	    my ($pl, $es, $centro, $asignatura, $r, $dur, $grp, $cap, $cred, $dni, $profesor, $finicio, $ffin, $cdia, $dia, $hinicio, $hfin, $aula) = split /;/, $row;

	    #exists
	    $query = "SELECT COUNT(*) as count FROM mrbs_room WHERE room_name =".$aula;
	    $count = $query['count'];

	    if($count eq 1) {

	    	$query = "SELECT COUNT(*) as count FROM mrbs_entry WHERE create_by = $POD_USER_ID AND room_name =".$aula;
	    	$count = $query['count'];

		    if($count eq 0) {
	    		$query = "INSERT INTO mrbs_entry (start_time, end_time, entry_type, repeat_id, room_id, timestamp, create_by, name, profesor, type, description, Observaciones, status, reminded, info_time, info_user, ical_uid, ical_sequence, ical_recur_id) VALUES ($finicio, $ffin, entry_type, repeat_id, room_id, timestamp, $POD_USER_ID, name, profesor, type, description, Observaciones, status, reminded, info_time, info_user, ical_uid, ical_sequence, ical_recur_id)";
		    } else {
	    		$query = "UDATE mrbs_entry WHERE create_by = $POD_USER_ID AND room_name =".$aula;
		    }

    	} else {
    		#Room not exists, update warnings
    		$warnings{$fecha} = $pl;
    	}
    }

  }
} else {
  warn "Could not open file '$filename' $!";
}


#Delete all POD old events
$query = "DELETE FROM mrbs_entry WHERE user_id = $POD_USER_ID AND timestamp < $fecha";

#Database disconnect
$dbh->disconnect;

#Release memory
undef %warnings;

close(IN);
exit;