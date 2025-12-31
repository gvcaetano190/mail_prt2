<?php

/*
-------------------------------------------------------------------------
Mail PRT2 plugin for GLPI
Database install / uninstall hooks
------------------------------------------------------------------------- */

/**
 * Install hook
 *
 * @return bool
 */
function plugin_mailprt2_install() {
   global $DB;

   // Migration object, as used in official example plugin
   $migration = new Migration(PLUGIN_MAILPRT2_VERSION);

   // Simple log to confirm install hook is executed
   error_log('mailprt2: running plugin_mailprt2_install');

   $default_charset   = DBConnection::getDefaultCharset();
   $default_collation = DBConnection::getDefaultCollation();
   $default_key_sign  = DBConnection::getDefaultPrimaryKeySignOption();

   // Tabela para controlar message-id já processados
   if (!$DB->tableExists('glpi_plugin_mail_prt2_message_id')) {
      $query = "CREATE TABLE `glpi_plugin_mail_prt2_message_id` (
                  `id` int {$default_key_sign} NOT NULL auto_increment,
                  `message_id` varchar(255) NOT NULL default '',
                  `tickets_id` int {$default_key_sign} NOT NULL default '0',
                  `mailcollectors_id` int {$default_key_sign} NOT NULL default '0',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `uniq_message_mailcollector` (`message_id`,`mailcollectors_id`),
                  KEY `tickets_id` (`tickets_id`)
               ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
   error_log('mailprt2: creating table glpi_plugin_mail_prt2_message_id');

   $DB->doQuery($query);
   }

   return true;
}

/**
 * Uninstall hook
 *
 * @return bool
 */
function plugin_mailprt2_uninstall() {
   // Por segurança, não removemos a tabela (histórico pode ser útil)
   return true;
}

// Demais hooks (pre_item_add, item_add, etc.) serão reativados em etapas
// Nenhuma lógica adicional por enquanto

