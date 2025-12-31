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

define("PLUGIN_MAIL_PRT2_VERSION", "1.0.0");
// Minimal GLPI version, inclusive
define('PLUGIN_MAIL_PRT2_MIN_GLPI', '11.0.0');
// Maximum GLPI version, exclusive
define('PLUGIN_MAIL_PRT2_MAX_GLPI', '12.0');

/**
 * Summary of plugin_init_mailanalyzer
 * Init the hooks of the plugins
 */
function plugin_init_mail_prt2() {

   global $PLUGIN_HOOKS;

   Plugin::registerClass('PluginMailPrt2Mailprt2');

   $PLUGIN_HOOKS['csrf_compliant']['mail_prt2'] = true;

   $PLUGIN_HOOKS['pre_item_add']['mail_prt2'] = [
      'Ticket' => ['PluginMailPrt2Mailprt2', 'plugin_pre_item_add_mail_prt2'],
   ];

   $PLUGIN_HOOKS['item_add']['mail_prt2'] = [
      'Ticket' => ['PluginMailPrt2Mailprt2', 'plugin_item_add_mail_prt2']
   ];

   $PLUGIN_HOOKS['item_purge']['mail_prt2'] = [
      'Ticket' => ['PluginMailPrt2Mailprt2', 'plugin_item_purge_mail_prt2']
   ];

    if (Session::haveRightsOr("config", [READ, UPDATE])) {
        Plugin::registerClass('PluginMailPrt2Config', ['addtabon' => 'Config']);
        $PLUGIN_HOOKS['config_page']['mail_prt2'] = 'front/config.form.php';
    }

}


/**
 * Summary of plugin_version_mail_prt2
 * Get the name and the version of the plugin
 * @return array
 */
function plugin_version_mail_prt2() {
   return [
      'name'         => __('Mail PRT2', 'mail_prt2'),
      'version'      => PLUGIN_MAIL_PRT2_VERSION,
      'author'       => 'Gabriel Caetano',
      'license'      => 'GPLv2+',
      'homepage'     => 'https://github.com/gvcaetano190/mail_prt2',
      'requirements' => [
         'glpi' => [
            'min' => PLUGIN_MAIL_PRT2_MIN_GLPI,
            'max' => PLUGIN_MAIL_PRT2_MAX_GLPI
         ],
         'php' => [
            'min' => '8.1'
         ]
      ]
   ];
}


/**
 * Summary of plugin_mail_prt2_check_prerequisites
 * check prerequisites before install : may print errors or add to message after redirect
 * @return bool
 */
function plugin_mail_prt2_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '11.0', '>=')) {
      return true;
   } else {
      echo "GLPI version NOT compatible. Requires GLPI >= 11.0";
      return false;
   }
}


/**
 * Summary of plugin_mailanalyzer_check_config
 * @return bool
 */
function plugin_mail_prt2_check_config() {
   return true;
}

