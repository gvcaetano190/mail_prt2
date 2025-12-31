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

/**
 * Pre item add hook for tickets created from mailcollector.
 * Deduplicates emails and converts replies into followups when possible.
 *
 * @param Ticket $parm
 *
 * @return void
 */
function plugin_pre_item_add_mailprt2($parm) {
   global $DB;

   // Only handle tickets created via mailcollector
   $mailgateId = $parm->input['_mailgate'] ?? false;
   if (!$mailgateId) {
      return;
   }

   // Debug básico: registrar informações principais do email processado
   try {
      $subjectLog = $parm->input['name'] ?? '';
      $headLog    = $parm->input['_head'] ?? [];
      error_log('[mailprt2] pre_item_add: mailgate=' . $mailgateId
         . ' subject=' . $subjectLog
         . ' head_keys=' . implode(',', array_keys((array)$headLog))
      );
   } catch (Throwable $e) {
      // não interrompe o fluxo se logging falhar
   }

   // Load plugin configuration (use_threadindex kept for compatibility)
   $config          = Config::getConfigurationValues('plugin:mailprt2');
   $use_threadindex = !empty($config['use_threadindex']);

   // Ensure headers are present
   $head      = $parm->input['_head'] ?? [];
   $messageId = html_entity_decode($head['message_id'] ?? '');
   if ($messageId === '') {
      return;
   }

   // 1) Basic deduplication: same Message-ID & same mailcollector with an existing ticket
   $res = $DB->request(
      'glpi_plugin_mail_prt2_message_id',
      [
         'AND' => [
            'tickets_id'        => ['!=', 0],
            'message_id'        => $messageId,
            'mailcollectors_id' => $mailgateId,
         ],
      ]
   );

   if ($row = $res->current()) {
      // Email já processado em um ticket existente: cancela criação de novo ticket
      $parm->input = false;
      return;
   }

   // 2) Busca por Thread-Index / References para tentar achar ticket existente
   $messages_id = plugin_mailprt2_getMailReferences(
      $head['threadindex']  ?? '',
      html_entity_decode($head['references'] ?? '')
   );

   if (count($messages_id) > 0) {
      $res = $DB->request(
         'glpi_plugin_mail_prt2_message_id',
         [
            'AND'   => [
               'tickets_id'        => ['!=', 0],
               'message_id'        => $messages_id,
               'mailcollectors_id' => $mailgateId,
            ],
            'ORDER' => 'tickets_id DESC',
         ]
      );

      if ($row = $res->current()) {
         // Encontrou ticket existente ligado à conversa
         $locTicket = new Ticket();
         $locTicket->getFromDB((int)$row['tickets_id']);

         if ($locTicket->fields['status'] != CommonITILObject::CLOSED) {
            // Cria um acompanhamento em vez de novo ticket
            $ticketfollowup = new ITILFollowup();
            $input          = $parm->input;
            $input['items_id']   = $row['tickets_id'];
            $input['users_id']   = $parm->input['_users_id_requester'] ?? 0;
            $input['add_reopen'] = 1;
            $input['itemtype']   = 'Ticket';

            // Limpa o histórico citado do corpo do e-mail, mantendo só a resposta
            if (!empty($input['content'])) {
               $input['content'] = plugin_mailprt2_stripQuotedBody($input['content']);
            }
            if (!empty($input['content_text'] ?? '')) {
               $input['content_text'] = plugin_mailprt2_stripQuotedBody($input['content_text']);
            }

            unset($input['urgency'], $input['entities_id'], $input['_ruleid']);

            $ticketfollowup->add($input);

            // Registra o message-id na tabela para futuras referências
            $DB->insert(
               'glpi_plugin_mail_prt2_message_id',
               [
                  'message_id'        => $messageId,
                  'tickets_id'        => $input['items_id'],
                  'mailcollectors_id' => $mailgateId,
               ]
            );

            // Cancela criação do ticket (ficamos só com o acompanhamento)
            $parm->input = false;
            return;
         }

         // Ticket existente está fechado: criamos novo ticket mas o linkamos ao anterior
         $parm->input['_link'] = [
            'link'         => '1',
            'tickets_id_1' => '0',
            'tickets_id_2' => $row['tickets_id'],
         ];
      }
   }

   // 3) Nenhum ticket encontrado: consideramos novo ticket
   $messages_id[] = $messageId;

   foreach ($messages_id as $ref) {
      $res = $DB->request(
         'glpi_plugin_mail_prt2_message_id',
         [
            'message_id'        => $ref,
            'mailcollectors_id' => $mailgateId,
         ]
      );

      if (count($res) <= 0) {
         $DB->insert(
            'glpi_plugin_mail_prt2_message_id',
            [
               'message_id'        => $ref,
               'mailcollectors_id' => $mailgateId,
            ]
         );
      }
   }
}

/**
 * Post item add hook: atualiza tickets_id nas entradas recém-criadas.
 *
 * @param Ticket $parm
 *
 * @return void
 */
function plugin_item_add_mailprt2($parm) {
   global $DB;

   if (!isset($parm->input['_mailgate'])) {
      return;
   }

   $head        = $parm->input['_head'] ?? [];
   $messages_id = plugin_mailprt2_getMailReferences(
      $head['threadindex']  ?? '',
      html_entity_decode($head['references'] ?? '')
   );
   $messages_id[] = html_entity_decode($head['message_id'] ?? '');

   $DB->update(
      'glpi_plugin_mail_prt2_message_id',
      [
         'tickets_id' => $parm->fields['id'],
      ],
      [
         'WHERE' => [
            'AND' => [
               'tickets_id' => 0,
               'message_id' => $messages_id,
            ],
         ],
      ]
   );
}

/**
 * Ticket purge hook: limpa entradas órfãs na tabela de controle.
 *
 * @param Ticket $item
 *
 * @return void
 */
function plugin_item_purge_mailprt2($item) {
   global $DB;

   $DB->delete(
      'glpi_plugin_mail_prt2_message_id',
      ['tickets_id' => $item->getID()]
   );
}

/**
 * Helper para extrair Thread-Index / References como lista de chaves.
 *
 * @param string $threadindex
 * @param string $references
 *
 * @return array
 */
function plugin_mailprt2_getMailReferences(string $threadindex, string $references): array {
   $messages_id = [];

   if (!empty($threadindex)) {
      $messages_id[] = $threadindex;
   }

   if (!empty($references)) {
      if (preg_match_all('/<.*?>/', $references, $matches)) {
         $messages_id = array_merge($messages_id, $matches[0]);
      }
   }

   return array_filter($messages_id, function ($val) {
      return $val !== '' && $val !== trim('', '< >');
   });
}

/**
 * Remove o histórico citado de um corpo de e-mail, mantendo apenas o texto novo.
 * Tenta detectar padrões comuns como "On ... wrote:" ou "Em ... escreveu:".
 *
 * @param string $body
 * @return string
 */
function plugin_mailprt2_stripQuotedBody(string $body): string {
   $cutPos  = null;
   $markers = [
      // Inglês
      '/On .*?wrote:/is',
      '/^> .*/m',
      '/-----Original Message-----/i',
      // Português
      '/Em .*?escreveu:/is',
      '/----- Mensagem original -----/i',
   ];

   foreach ($markers as $pattern) {
      if (preg_match($pattern, $body, $m, PREG_OFFSET_CAPTURE)) {
         $pos = $m[0][1];
         if ($cutPos === null || $pos < $cutPos) {
            $cutPos = $pos;
         }
      }
   }

   if ($cutPos !== null && $cutPos > 0) {
      $body = substr($body, 0, $cutPos);
   }

   return trim($body);
}

