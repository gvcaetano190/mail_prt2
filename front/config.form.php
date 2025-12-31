<?php
/*
-------------------------------------------------------------------------
Mail PRT2 plugin for GLPI
Copyright (C) 2025 by Gabriel Caetano

https://github.com/gvcaetano190/mailprt2
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

// GLPI 11: No need to include inc/includes.php anymore - it is loaded automatically

Session::setActiveTab('Config', 'PluginMailprt2Config$1');
Html::redirect($CFG_GLPI["root_doc"]."/front/config.form.php");

