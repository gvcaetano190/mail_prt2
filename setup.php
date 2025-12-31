<?php
/*
-------------------------------------------------------------------------
Mail PRT2 plugin for GLPI (minimal skeleton)
------------------------------------------------------------------------- */

define('PLUGIN_MAILPRT2_VERSION', '0.1.0');

function plugin_init_mailprt2() {
   global $PLUGIN_HOOKS;

   // obrigatório para proteção CSRF
   $PLUGIN_HOOKS['csrf_compliant']['mailprt2'] = true;

   // link de configuração na lista de plugins
   $PLUGIN_HOOKS['config_page']['mailprt2'] = 'front/config.form.php';

   // adiciona aba de configuração em Config > Geral
   Plugin::registerClass(PluginMailprt2Config::class, [
      'addtabon' => ['Config']
   ]);
}

function plugin_version_mailprt2() {
   return [
      'name'         => 'Mail PRT2 (test)',
      'version'      => PLUGIN_MAILPRT2_VERSION,
      'author'       => 'Gabriel Caetano',
      'license'      => 'GPLv2+',
      'homepage'     => 'https://github.com/gvcaetano190/mailprt2',
      'requirements' => [
         'glpi' => [
            'min' => '11.0.0',
            'max' => '12.0.0'
         ]
      ]
   ];
}

function plugin_mailprt2_check_prerequisites() {
   return true;
}

function plugin_mailprt2_check_config($verbose = false) {
   return true;
}

