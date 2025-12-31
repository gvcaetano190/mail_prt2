<?php
/*
-------------------------------------------------------------------------
Mail PRT2 plugin for GLPI (minimal skeleton)
------------------------------------------------------------------------- */

function plugin_mail_prt2_install() {
   // nada por enquanto (sem tabelas)
   return true;
}

function plugin_mail_prt2_uninstall() {
   // nada para remover
   return true;
}

// Nenhuma lógica adicional por enquanto


   /**
    * Summary of getMailReferences
    * @param string $threadindex 
    * @param string $references 
    * @return string[]
    */
   private static function getMailReferences(string $threadindex, string $references): array {

      $messages_id = []; // by default

      if (!empty($threadindex)) {
          $messages_id[] = $threadindex;
      }

      // search for 'References'
      if (!empty($references)) {
         // we may have a forwarded email that looks like reply-to
         if (preg_match_all('/<.*?>/', $references, $matches)) {
            $messages_id = array_merge($messages_id, $matches[0]);
         }
      }

      // clean $messages_id array
      return array_filter($messages_id, function($val) {return $val != trim('', '< >');});
   }


   /**
    * Summary of plugin_item_purge_mail_prt2
    * @param mixed $item
    */
   static function plugin_item_purge_mail_prt2($item): void {
      /** @var \DBmysql $DB */
      global $DB;
      // the ticket is purged, then we are going to purge the matching rows in glpi_plugin_mail_prt2_message_id table
      $DB->delete('glpi_plugin_mail_prt2_message_id', ['tickets_id' => $item->getID()]);
   }
}
