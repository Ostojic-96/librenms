<?php

/**
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2019 LibreNMS
 * @author     Pavle Obradovic <pobradovic08@gmail.com>
 */

/*
 * Priority packets handled by QFP
 */
$i = 1;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = $components['name'];
$rrd_list[$i]['ds_in'] = 'InPriorityPps';
$rrd_list[$i]['ds_out'] = 'OutPriorityPps';
$rrd_list[$i]['descr'] = 'Priority';
$rrd_list[$i]['colour_area_in'] = 'FACF5A';
$rrd_list[$i]['colour_area_out'] = 'FF5959';

/*
 * Non-priority packets handled by QFP
 */
$i = 2;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = $components['name'];
$rrd_list[$i]['ds_in'] = 'InNonPriorityPps';
$rrd_list[$i]['ds_out'] = 'OutNonPriorityPps';
$rrd_list[$i]['descr'] = 'NonPriority';
$rrd_list[$i]['colour_area_in'] = '085F63';
$rrd_list[$i]['colour_area_out'] = '49BEB7';

$units = 'pps';
$units_descr = 'Packets/s';
$colours_in = 'purples';
$multiplier = '1';
$colours_out = 'oranges';

$nototal = 1;

include 'includes/html/graphs/generic_multi_seperated.inc.php';
