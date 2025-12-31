<?php
/*
-------------------------------------------------------------------------
Mail PRT2 plugin for GLPI
Copyright (C) 2025 by Gabriel Caetano

https://github.com/gvcaetano190/mail_prt2
-------------------------------------------------------------------------

LICENSE

This file is part of Mail PRT2 plugin for GLPI.

This file is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this plugin. If not, see <http://www.gnu.org/licenses/>.
--------------------------------------------------------------------------
 */

/**
 * Script to decode Thread-Index from base64
 * Usage: php threadindex.php <base64_thread_index>
 */

if (!isset($argv[1])) {
    echo "Usage: php threadindex.php <base64_thread_index>\n";
    exit(1);
}

echo bin2hex(substr(base64_decode($argv[1]), 6, 16 )) . "\n";

