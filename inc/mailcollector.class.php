<?php
/*
-------------------------------------------------------------------------
Mail PRT2 plugin for GLPI
Copyright (C) 2025 by Gabriel Caetano

https://github.com/gvcaetano190/mailprt2
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

use Laminas\Mail\Storage\Imap;
use Laminas\Mail\Storage\Pop3;

/**
 * Class PluginMailPrt2MailCollector
 * Wrapper for mail connection handling
 */
class PluginMailPrt2MailCollector {

   private $storage = null;
   private $mailgateID;
   private $fields = [];

   /**
    * Summary of __construct
    * @param mixed $mailgateID the mailgate ID
    */
   function __construct($mailgateID) {
      $this->mailgateID = $mailgateID;
   }

   /**
    * Summary of connect
    * Connect to mail server using GLPI's MailCollector fields
    * @return bool
    */
   public function connect(): bool {
      global $DB;
      
      // Get mailcollector configuration
      $iterator = $DB->request([
         'FROM' => 'glpi_mailcollectors',
         'WHERE' => ['id' => $this->mailgateID]
      ]);
      
      if (count($iterator) === 0) {
         return false;
      }

      $row = $iterator->current();
      $this->fields = $row;

      try {
         // Determine connection type
         $host = $row['host'] ?? '';
         $login = $row['login'] ?? '';
         
         // GLPI 11: Get password using proper method
         $password = (new GLPIKey())->decrypt($row['passwd'] ?? '');
         
         // Determine SSL/TLS settings
         $ssl = false;
         $port = 143; // Default IMAP port
         
         if (strpos($host, 'ssl://') === 0) {
            $ssl = 'SSL';
            $host = substr($host, 6);
            $port = 993;
         } elseif (strpos($host, 'tls://') === 0) {
            $ssl = 'TLS';
            $host = substr($host, 6);
         }
         
         // Check for custom port
         if (preg_match('/^(.+):(\d+)\//', $host, $matches)) {
            $host = $matches[1];
            $port = (int)$matches[2];
         } elseif (preg_match('/^(.+):(\d+)$/', $host, $matches)) {
            $host = $matches[1];
            $port = (int)$matches[2];
         }
         
         // Remove any remaining protocol prefix
         $host = preg_replace('/^[a-z]+:\/\//', '', $host);
         
         // Determine if IMAP or POP3
         $isImap = (strpos($row['host'] ?? '', 'imap') !== false) || 
                   ($port == 143 || $port == 993);
         
         $config = [
            'host'     => $host,
            'user'     => $login,
            'password' => $password,
            'port'     => $port,
         ];
         
         if ($ssl) {
            $config['ssl'] = $ssl;
         }
         
         if ($isImap) {
            $this->storage = new Imap($config);
         } else {
            $this->storage = new Pop3($config);
         }
         
         return true;
         
      } catch (Exception $e) {
         Toolbox::logError("Mail PRT2: Connection failed - " . $e->getMessage());
         return false;
      }
   }

   /**
    * Summary of getHeaders
    * @param int $index Message index
    * @return mixed Headers object or false
    */
   public function getHeaders(int $index) {
      if ($this->storage === null) {
         return false;
      }
      
      try {
         $message = $this->storage->getMessage($index);
         return $message->getHeaders();
      } catch (Exception $e) {
         Toolbox::logError("Mail PRT2: Failed to get headers for message $index - " . $e->getMessage());
         return false;
      }
   }

   /**
    * Summary of getStorage
    * @return mixed Storage object
    */
   public function getStorage() {
      return $this->storage;
   }

   /**
    * Summary of getFields
    * @return array
    */
   public function getFields(): array {
      return $this->fields;
   }

   /**
    * Summary of close
    * Close the mail connection
    */
   public function close(): void {
      if ($this->storage !== null) {
         try {
            $this->storage->close();
         } catch (Exception $e) {
            // Ignore close errors
         }
         $this->storage = null;
      }
   }
}
