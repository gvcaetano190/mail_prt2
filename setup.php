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

   // hooks para tratamento de tickets criados via coletor de e-mail
   // pré-criação: deduplicação / conversão em acompanhamento
   $PLUGIN_HOOKS['pre_item_add']['mailprt2'] = [
      'Ticket' => 'plugin_pre_item_add_mailprt2',
   ];

   // pós-criação: atualizar tabela de controle com o ID do ticket
   $PLUGIN_HOOKS['item_add']['mailprt2'] = [
      'Ticket' => 'plugin_item_add_mailprt2',
   ];

   // purge: limpeza de registros órfãos
   $PLUGIN_HOOKS['item_purge']['mailprt2'] = [
      'Ticket' => 'plugin_item_purge_mailprt2',
   ];
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

