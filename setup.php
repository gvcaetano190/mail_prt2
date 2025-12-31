<?php
/*
-------------------------------------------------------------------------
Mail PRT2 plugin for GLPI (minimal skeleton)
------------------------------------------------------------------------- */

define('PLUGIN_MAIL_PRT2_VERSION', '0.1.0');

function plugin_init_mail_prt2() {
   global $PLUGIN_HOOKS;

   // obrigatório para proteção CSRF
   $PLUGIN_HOOKS['csrf_compliant']['mail_prt2'] = true;
}

function plugin_version_mail_prt2() {
   return [
      'name'         => 'Mail PRT2 (test)',
      'version'      => PLUGIN_MAIL_PRT2_VERSION,
      'author'       => 'Gabriel Caetano',
      'license'      => 'GPLv2+',
      'homepage'     => 'https://github.com/gvcaetano190/mail_prt2',
      'requirements' => [
         'glpi' => [
            'min' => '11.0.0',
            'max' => '12.0.0'
         ]
      ]
   ];
}

function plugin_mail_prt2_check_prerequisites() {
   return true;
}

function plugin_mail_prt2_check_config($verbose = false) {
   return true;
}

