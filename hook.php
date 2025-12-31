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
   /** @var DBmysql $DB */
   global $DB;

   // Tabela para controlar message-id já processados
   if (!$DB->tableExists('glpi_plugin_mail_prt2_message_id')) {
      $query = "CREATE TABLE `glpi_plugin_mail_prt2_message_id` (
         `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
         `message_id`       VARCHAR(255) NOT NULL DEFAULT '',
         `tickets_id`       INT UNSIGNED NOT NULL DEFAULT 0,
         `mailcollectors_id` INT UNSIGNED NOT NULL DEFAULT 0,
         PRIMARY KEY (`id`),
         UNIQUE KEY `uniq_message_mailcollector` (`message_id`,`mailcollectors_id`),
         KEY `tickets_id` (`tickets_id`)
      )
      ENGINE=InnoDB
      DEFAULT CHARSET=utf8mb4
      COLLATE=utf8mb4_unicode_ci";

      $DB->queryOrDie($query, 'Create glpi_plugin_mail_prt2_message_id table');
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

