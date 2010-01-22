<?php
 /*
 ****************************************************************************
 *
 * This file was contributed (in part or whole) by a third party, and is
 * released under the GNU LGPL.  Please see the CREDITS and LICENSE sections
 * below for details.
 *
 *****************************************************************************
 *
 * DETAILS
 *
 * This is an event-driven Jabber client class implementation.  This library
 * allows PHP scripts to connect to and communicate with Jabber servers.
 *
 *
 * CREDITS & COPYRIGHTS
 *
 * This class was originally based on Class.Jabber.PHP v0.4 (Copyright 2002,
 * Carlo "Gossip" Zottmann).
 *
 * This library is free software; you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation; either version 2.1 of the License, or (at your
 * option) any later version.
 *
 * This library is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this library; if not, write to the Free Software Foundation,
 * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * JABBER is a registered trademark of Jabber Inc.
 */


class Jabber
{
    var $server;
    var $port;
    var $username;
    var $password;
    var $resource;
    var $jid;


    var $connection;
    var $delay_disconnect;

    var $enable_logging;
    var $log_array;
    var $log_filename;
    var $log_filehandler;
    var $iq_sleep_timer;
    var $connected;
    var $packet_queue;

    var $CONNECTOR;



    function Jabber()
    {
        $this->server                = "localhost";
        $this->port                    = "5222";
        $this->username                = "larry";
        $this->password                = "curly";
        $this->resource                = NULL;


        $this->enable_logging        = FALSE;
        $this->log_array            = array();
        $this->log_filename            = '';
        $this->log_filehandler        = FALSE;

        $this->iq_sleep_timer        = 1;
        $this->delay_disconnect        = 1;
        $this->connection_class        = "CJP_StandardConnector";
        $this->packet_queue            = array();
    }



    function Connect()
    {
        $this->_create_logfile();

        $this->CONNECTOR = new $this->connection_class;

        if ($this->CONNECTOR->OpenSocket($this->server, $this->port))
        {
            $this->SendPacket("<?xml version='1.0' encoding='UTF-8' ?" . ">\n");
            $this->SendPacket("<stream:stream to='{$this->server}' xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams'>\n");

            sleep(2);

            if ($this->_check_connected())
            {
                $this->connected = TRUE;    // Nathan Fritz
                return TRUE;
            }
            else
            {
                $this->AddToLog("ERROR: Connect() #1");
                return FALSE;
            }
        }
        else
        {
            $this->AddToLog("ERROR: Connect() #2");
            return FALSE;
        }
    }



    function Disconnect()
    {
        if (is_int($this->delay_disconnect))
        {
            sleep($this->delay_disconnect);
        }

        $this->SendPacket("</stream:stream>");
        $this->CONNECTOR->CloseSocket();

        $this->_close_logfile();
        $this->PrintLog();
    }



    function SendPacket($xml)
    {
        $xml = trim($xml);

        if ($this->CONNECTOR->WriteToSocket($xml))
        {
            $this->AddToLog("SEND: $xml");
            return TRUE;
        }
        else
        {
            $this->AddToLog('ERROR: SendPacket() #1');
            return FALSE;
        }
    }

    function _check_connected()
    {
        $incoming_array = $this->_listen_incoming();

        if (is_array($incoming_array))
        {
            if ($incoming_array["stream:stream"]['@']['from'] == $this->server
                && $incoming_array["stream:stream"]['@']['xmlns'] == "jabber:client"
                && $incoming_array["stream:stream"]['@']["xmlns:stream"] == "http://etherx.jabber.org/streams")
            {
                $this->stream_id = $incoming_array["stream:stream"]['@']['id'];

                return TRUE;
            }
            else
            {
                $this->AddToLog("ERROR: _check_connected() #1");
                return FALSE;
            }
        }
        else
        {
            $this->AddToLog("ERROR: _check_connected() #2");
            return FALSE;
        }
    }


    function _create_logfile()
    {
        if ($this->log_filename != '' && $this->enable_logging)
        {
            $this->log_filehandler = fopen($this->log_filename, 'w');
        }
    }

    function _close_logfile()
    {
        if ($this->log_filehandler)
        {
            fclose($this->log_filehandler);
        }
    }

    function PrintLog()
    {
        if ($this->enable_logging)
        {
            if ($this->log_filehandler)
            {
                echo "<h2>Logging enabled, logged events have been written to the file {$this->log_filename}.</h2>\n";
            }
            else
            {
                echo "<h2>Logging enabled, logged events below:</h2>\n";
                echo "<pre>\n";
                echo (count($this->log_array) > 0) ? implode("\n\n\n", $this->log_array) : "No logged events.";
                echo "</pre>\n";
            }
        }
    }


    function AddToLog($string)
    {
        if ($this->enable_logging)
        {
            if ($this->log_filehandler)
            {
                fwrite($this->log_filehandler, $string . "\n\n");
            }
            else
            {
                $this->log_array[] = htmlspecialchars($string);
            }
        }
    }

    function GetInfoFromIqType($packet = NULL)
    {
        return (is_array($packet)) ? $packet['iq']['@']['type'] : FALSE;
    }

    function SendIq($to = NULL, $type = 'get', $id = NULL, $xmlns = NULL, $payload = NULL, $from = NULL)
    {
        if (!preg_match("/^(get|set|result|error)$/", $type))
        {
            unset($type);

            $this->AddToLog("ERROR: SendIq() #2 - type must be 'get', 'set', 'result' or 'error'");
            return FALSE;
        }
        elseif ($id && $xmlns)
        {
            $xml = "<iq type='$type' id='$id'";
            $xml .= ($to) ? " to='$to'" : '';
            $xml .= ($from) ? " from='$from'" : '';
            $xml .= ">
                        <query xmlns='$xmlns'>
                            $payload
                        </query>
                    </iq>";

            $this->SendPacket($xml);
            sleep($this->iq_sleep_timer);
            $this->Listen();

            return (preg_match("/^(get|set)$/", $type)) ? $this->GetFromQueueById("iq", $id) : TRUE;
        }
        else
        {
            $this->AddToLog("ERROR: SendIq() #1 - to, id and xmlns are mandatory");
            return FALSE;
        }
    }

    function Listen()
    {
        unset($incoming);

        while ($line = $this->CONNECTOR->ReadFromSocket(4096))
        {
            $incoming .= $line;
        }

        $incoming = trim($incoming);

        if ($incoming != "")
        {
            $this->AddToLog("RECV: $incoming");
        }

        if ($incoming != "")
        {
            $temp = $this->_split_incoming($incoming);

            for ($a = 0; $a < count($temp); $a++)
            {
                $this->packet_queue[] = $this->xmlize($temp[$a]);
            }
        }

        return TRUE;
    }


    function _split_incoming($incoming)
    {
        $temp = preg_split("/<(message|iq|presence|stream)/", $incoming, -1, PREG_SPLIT_DELIM_CAPTURE);
        $array = array();

        for ($a = 1; $a < count($temp); $a = $a + 2)
        {
            $array[] = "<" . $temp[$a] . $temp[($a + 1)];
        }

        return $array;
    }


    function xmlize($data)
    {
        $vals = $index = $array = array();
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, $data, $vals, $index);
        xml_parser_free($parser);

        $i = 0;

        $tagname = $vals[$i]['tag'];
        $array[$tagname]['@'] = $vals[$i]['attributes'];
        $array[$tagname]['#'] = $this->_xml_depth($vals, $i);

        return $array;
    }


    function _xml_depth($vals, &$i)
    {
        $children = array();

        if ($vals[$i]['value'])
        {
            array_push($children, trim($vals[$i]['value']));
        }

        while (++$i < count($vals))
        {
            switch ($vals[$i]['type'])
            {
                case 'cdata':
                    array_push($children, trim($vals[$i]['value']));
                     break;

                case 'complete':
                    $tagname = $vals[$i]['tag'];
                    $size = sizeof($children[$tagname]);
                    $children[$tagname][$size]['#'] = trim($vals[$i]['value']);
                    if ($vals[$i]['attributes'])
                    {
                        $children[$tagname][$size]['@'] = $vals[$i]['attributes'];
                    }
                    break;

                case 'open':
                    $tagname = $vals[$i]['tag'];
                    $size = sizeof($children[$tagname]);
                    if ($vals[$i]['attributes'])
                    {
                        $children[$tagname][$size]['@'] = $vals[$i]['attributes'];
                        $children[$tagname][$size]['#'] = $this->_xml_depth($vals, $i);
                    }
                    else
                    {
                        $children[$tagname][$size]['#'] = $this->_xml_depth($vals, $i);
                    }
                    break;

                case 'close':
                    return $children;
                    break;
            }
        }

        return $children;
    }

    function SendAuth()
    {
        $this->auth_id    = "auth_" . md5(time() . $_SERVER['REMOTE_ADDR']);

        $this->resource    = ($this->resource != NULL) ? $this->resource : ("Class.Jabber.PHP " . md5($this->auth_id));
        $this->jid        = "{$this->username}@{$this->server}/{$this->resource}";

        // request available authentication methods
        $payload    = "<username>{$this->username}</username>";
        $packet        = $this->SendIq(NULL, 'get', $this->auth_id, "jabber:iq:auth", $payload);

        // was a result returned?
        if ($this->GetInfoFromIqType($packet) == 'result' && $this->GetInfoFromIqId($packet) == $this->auth_id)
        {
            // yes, now check for auth method availability in descending order (best to worst)

            if (!function_exists(mhash))
            {
                $this->AddToLog("ATTENTION: SendAuth() - mhash() is not available; screw 0k and digest method, we need to go with plaintext auth");
            }

            // auth_0k
            if (function_exists(mhash) && isset($packet['iq']['#']['query'][0]['#']['sequence'][0]["#"]) && isset($packet['iq']['#']['query'][0]['#']['token'][0]["#"]))
            {
                return $this->_sendauth_0k($packet['iq']['#']['query'][0]['#']['token'][0]["#"], $packet['iq']['#']['query'][0]['#']['sequence'][0]["#"]);
            }
            // digest
            elseif (function_exists(mhash) && isset($packet['iq']['#']['query'][0]['#']['digest']))
            {
                return $this->_sendauth_digest();
            }
            // plain text
            elseif ($packet['iq']['#']['query'][0]['#']['password'])
            {
                return $this->_sendauth_plaintext();
            }
            // dude, you're fucked
            {
                $this->AddToLog("ERROR: SendAuth() #2 - No auth method available!");
                return FALSE;
            }
        }
        else
        {
            // no result returned
            $this->AddToLog("ERROR: SendAuth() #1");
            return FALSE;
        }
    }


    function _sendauth_plaintext()
    {
        $payload = "<username>{$this->username}</username>
                    <password>{$this->password}</password>
                    <resource>{$this->resource}</resource>";

        $packet = $this->SendIq(NULL, 'set', $this->auth_id, "jabber:iq:auth", $payload);

        // was a result returned?
        if ($this->GetInfoFromIqType($packet) == 'result' && $this->GetInfoFromIqId($packet) == $this->auth_id)
        {
            return TRUE;
        }
        else
        {
            $this->AddToLog("ERROR: _sendauth_plaintext() #1");
            return FALSE;
        }
    }

    function _sendauth_0k($zerok_token, $zerok_sequence)
    {
        // initial hash of password
        $zerok_hash = mhash(MHASH_SHA1, $this->password);
        $zerok_hash = bin2hex($zerok_hash);

        // sequence 0: hash of hashed-password and token
        $zerok_hash = mhash(MHASH_SHA1, $zerok_hash . $zerok_token);
        $zerok_hash = bin2hex($zerok_hash);

        // repeat as often as needed
        for ($a = 0; $a < $zerok_sequence; $a++)
        {
            $zerok_hash = mhash(MHASH_SHA1, $zerok_hash);
            $zerok_hash = bin2hex($zerok_hash);
        }

        $payload = "<username>{$this->username}</username>
                    <hash>$zerok_hash</hash>
                    <resource>{$this->resource}</resource>";

        $packet = $this->SendIq(NULL, 'set', $this->auth_id, "jabber:iq:auth", $payload);

        // was a result returned?
        if ($this->GetInfoFromIqType($packet) == 'result' && $this->GetInfoFromIqId($packet) == $this->auth_id)
        {
            return TRUE;
        }
        else
        {
            $this->AddToLog("ERROR: _sendauth_0k() #1");
            return FALSE;
        }
    }



    function _sendauth_digest()
    {
        $payload = "<username>{$this->username}</username>
                    <resource>{$this->resource}</resource>
                    <digest>" . bin2hex(mhash(MHASH_SHA1, $this->stream_id . $this->password)) . "</digest>";

        $packet = $this->SendIq(NULL, 'set', $this->auth_id, "jabber:iq:auth", $payload);

        // was a result returned?
        if ($this->GetInfoFromIqType($packet) == 'result' && $this->GetInfoFromIqId($packet) == $this->auth_id)
        {
            return TRUE;
        }
        else
        {
            $this->AddToLog("ERROR: _sendauth_digest() #1");
            return FALSE;
        }
    }

    function _listen_incoming()
    {
        unset($incoming);

        while ($line = $this->CONNECTOR->ReadFromSocket(4096))
        {
            $incoming .= $line;
        }

        $incoming = trim($incoming);

        if ($incoming != "")
        {
            $this->AddToLog("RECV: $incoming");
        }

        return $this->xmlize($incoming);
    }

    function GetFromQueueById($packet_type, $id)
    {
        $found_message = FALSE;

        foreach ($this->packet_queue as $key => $value)
        {
            if ($value[$packet_type]['@']['id'] == $id)
            {
                $found_message = $value;
                unset($this->packet_queue[$key]);

                break;
            }
        }

        return (is_array($found_message)) ? $found_message : FALSE;
    }

    function GetInfoFromIqId($packet = NULL)
    {
        return (is_array($packet)) ? $packet['iq']['@']['id'] : FALSE;
    }

    function SendMessage($to, $type = "normal", $id = NULL, $content = NULL, $payload = NULL)
    {
        if ($to && is_array($content))
        {
            if (!$id)
            {
                $id = $type . "_" . time();
            }

            $content = $this->_array_htmlspecialchars($content);

            $xml = "<message to='$to' type='$type' id='$id'>\n";

            if ($content['subject'])
            {
                $xml .= "<subject>" . $content['subject'] . "</subject>\n";
            }

            if ($content['thread'])
            {
                $xml .= "<thread>" . $content['thread'] . "</thread>\n";
            }

            $xml .= "<body>" . $content['body'] . "</body>\n";
            $xml .= $payload;
            $xml .= "</message>\n";


            if ($this->SendPacket($xml))
            {
                return TRUE;
            }
            else
            {
                $this->AddToLog("ERROR: SendMessage() #1");
                return FALSE;
            }
        }
        else
        {
            $this->AddToLog("ERROR: SendMessage() #2");
            return FALSE;
        }
    }

    function _array_htmlspecialchars($array)
    {
        if (is_array($array))
        {
            foreach ($array as $k => $v)
            {
                if (is_array($v))
                {
                    $v = $this->_array_htmlspecialchars($v);
                }
                else
                {
                    $v = htmlspecialchars($v);
                }
            }
        }

        return $array;
    }

}

class CJP_StandardConnector
{
    var $active_socket;

    function OpenSocket($server, $port)
    {
        if ($this->active_socket = fsockopen($server, $port))
        {
            socket_set_blocking($this->active_socket, 0);
            socket_set_timeout($this->active_socket, 31536000);

            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }



    function CloseSocket()
    {
        return fclose($this->active_socket);
    }



    function WriteToSocket($data)
    {
        return fwrite($this->active_socket, $data);
    }



    function ReadFromSocket($chunksize)
    {
        set_magic_quotes_runtime(0);
        $buffer = fread($this->active_socket, $chunksize);
        set_magic_quotes_runtime(get_magic_quotes_gpc());

        return $buffer;
    }
}
?>