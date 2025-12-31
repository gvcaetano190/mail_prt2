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
                  return;

               } else {
                  // ticket creation, but linked to the closed one...
                  $parm->input['_link'] = ['link' => '1', 'tickets_id_1' => '0', 'tickets_id_2' => $row['tickets_id']];
               }
            }
         }

         // can't find ref into DB, then this is a new ticket, in this case insert refs and message_id into DB
         $messages_id[] = $messageId;

         // this is a new ticket
         // then add references and message_id to DB
         foreach ($messages_id as $ref) {
            $res = $DB->request([
               'FROM' => 'glpi_plugin_mail_prt2_message_id',
               'WHERE' => [
                  'message_id' => $ref,
                  'mailcollectors_id' => $mailgateId
               ]
            ]);
            if (count($res) <= 0) {
               $DB->insert('glpi_plugin_mail_prt2_message_id', ['message_id' => $ref, 'mailcollectors_id' => $mailgateId]);
            }
         }
      }
   }


    /**
     * Summary of plugin_item_add_mail_prt2
     * @param mixed $parm
     */
   public static function plugin_item_add_mail_prt2($parm): void {
      /** @var \DBmysql $DB */
      global $DB;
      if (isset($parm->input['_mailgate'])) {
         // this ticket have been created via email receiver.
         // update the ticket ID for the message_id only for newly created tickets (tickets_id == 0)

         // Are 'Thread-Index' or 'Refrences' present?
         $messages_id = self::getMailReferences(
             $parm->input['_head']['threadindex'] ?? '',
             $parm->input['_head']['references'] ?? ''
             );
         // GLPI 11: html_entity_decode is no longer needed
         $messages_id[] = $parm->input['_head']['message_id'];

         $DB->update(
            'glpi_plugin_mail_prt2_message_id',
            [
               'tickets_id' => $parm->fields['id']
            ],
            [
               'AND' => [
                  'tickets_id'  => 0,
                  'message_id' => $messages_id
               ]
            ]
         );
      }
   }


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
