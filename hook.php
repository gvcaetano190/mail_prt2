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
   // Por enquanto não fazemos alterações diretas no banco,
   // para evitar o erro "Executing direct queries is not allowed!" do GLPI 11.
   // A criação de tabelas será feita depois usando a API de migração oficial.
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

