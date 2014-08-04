#!/usr/bin/perl/
#
# POD-mrbs config file
# -----------------------------------------------------------------------
#/*
# *    Copyright 2014 Víctor Téllez Lozano <tellez.victor@gmail.com>
# *
# *    This file is part of POD-mrbs.
# */

use warnings;
use DBI;

#System path to src folder
$PATH = "";

$POD_USER_ID = 'pod';

#Database credentials
$db = "nombre-de-bbdd";
$host = "ip-servidor-bbdd";
$port = "puerto";
$userid = "usuario-bbdd";
$passwd = "password-bbdd";
$connectionInfo = "DBI:mysql:database=$db;$host:$port";