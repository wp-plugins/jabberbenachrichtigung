<?php if( defined( 'ABSPATH' ) == false ) // unbedingt drinnenlassen, schuetzt vor direktaufruf!
        die();
/*
Plugin Name: Jabbernotification
Plugin URI: http://www.entartete-kunst.com/jabberbenachrichtigung-bei-neuen-kommentaren/
Description: A fully-configurable Wordpress plugin which informs the admin about new comments through Jabber. <a href="options-general.php?page=jabbernot.php">Settings</a>
Version: 3.141592
Author: Missi
Author URI: http://www.entartete-kunst.com/

--------------------------------------------------------------------------------
GPL Lizenz FTW!

 This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
--------------------------------------------------------------------------------

*/
// Localization
	$locale = get_locale();
	if ( !empty( $locale ) ) {
	$mofile = ABSPATH . 'wp-content/plugins/jabbernot2/lang/jabbernotification-'.$locale.'.mo';
//	echo $mofile;
	//exit;
	load_textdomain('jabbernotification', $mofile);
	}


// Nothing more to see here. All configuration is (hopefully) done via the admin panel.
// Move along.
         
	function jabbernot_admin_menu(){        // Adding option panel
         if (function_exists('add_options_page')) {
         add_options_page('options-general.php', 'Jabbernotification', 10, basename(__FILE__), 'jabbernot_options_subpanel');
         }
}
          function jabbernot_options_subpanel(){         // Content of the option panel **Still under heavy construction**
/* Lets add some default options if they don't exist */
         add_option('jabbernot_server', 'Jabberserver');
         add_option('jabbernot_port', '5222');
         add_option('jabbernot_username', 'Username');
         add_option('jabbernot_password', '');
         add_option('jabbernot_resource', 'WordPress');
         add_option('jabbernot_destination', 'deine@jabber-id.tld');

/* check form submission and update options */
         if (isset($_POST['stage']) && ('process' == $_POST['stage']) && (!empty($_POST['jabbernot_server'])) && (!empty($_POST['jabbernot_port'])) && (!empty($_POST['jabbernot_username'])) && (!empty($_POST['jabbernot_password'])) && (!empty($_POST['jabbernot_resource'])) && (!empty($_POST['jabbernot_destination'])) )
         {

         $jabbernot_server = $_POST['jabbernot_server'];
         $jabbernot_port = $_POST['jabbernot_port'];
         $jabbernot_username = $_POST['jabbernot_username'];
         $jabbernot_password = $_POST['jabbernot_password'];
         $jabbernot_resource = $_POST['jabbernot_resource'];
         $jabbernot_destination = $_POST['jabbernot_destination'];
         update_option('jabbernot_server', $jabbernot_server);
         update_option('jabbernot_port', $jabbernot_port);
         update_option('jabbernot_username', $jabbernot_username);
         update_option('jabbernot_password', $jabbernot_password);
         update_option('jabbernot_resource', $jabbernot_resource);
         update_option('jabbernot_destination', $jabbernot_destination);
         }

/* Get options for form fields */
         $jabbernot_server = get_option('jabbernot_server');
         $jabbernot_port = get_option('jabbernot_port');
         $jabbernot_username = get_option('jabbernot_username');
         $jabbernot_password = get_option('jabbernot_password');
         $jabbernot_resource = get_option('jabbernot_resource');
         $jabbernot_destination = get_option('jabbernot_destination');
?>
<div class="wrap">
        <h2><?php _e("Jabberbenachrichtigung: Optionen", 'jabbernotification')?></h2>
        <p><?php _e("Bitte trage die gew&uuml;nschten Jabber-Account-Daten sowie die Jabber-ID, an welche die Benachrichtigung gesendet werden soll ein. Beachte, dass du zun&auml;chst den Account manuell registrieren musst.", 'jabbernotification')?> (<?php _e("Hilfe?", 'jabbernotification')?> =&gt; <a href="http://www.entartete-kunst.com/yet-another-jabber-faq/">Jabber- FAQ (deutsch)</a>| <a href="http://archive.jabber.org/userguide/">Jabber- FAQ (english)</a> !)</p>
        <form name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=jabbernot.php&updated=true">
                <input type="hidden" name="stage" value="process" />
                <fieldset class="options">
                        <legend>Notifier</legend>
                        <table width="100%" cellspacing="2" cellpadding="5" class="editform">
                                <tr valign="top">
                                        <th scope="row"> <?php _e("Server", 'jabbernotification') ?> </th>
                                        <td>
                                                <input name="jabbernot_server" type="text" id="jabbernot_server" value="<?php echo $jabbernot_server; ?>" size="20" /><br />
                                                <?php _e("Gib hier die Adresse des Jabberservers ein. Eine Liste freier Jabberserver findest du", 'jabbernotification')?>
                                                <a href="http://www.jabberes.org/servers/servers_by_times_online.html"><?php _e("hier.", 'jabbernotification')?></a></td>
                                </tr>
                                <tr valign="top">
                                        <th scope="row">Port</th>
                                        <td>
                                                <input name="jabbernot_port" type="text" id="jabbernot_port" value="<?php echo $jabbernot_port; ?>" maxlength=5 size="10" /><br />
                                                <?php _e("Port, der benutzt werden soll. Standard:", 'jabbernotification') ?>
                                                <code>5222</code></td>
                          </tr>
                                <tr valign="top">
                                        <th scope="row"<?php _e("Benutzername", 'jabbernotification') ?></th>
                                <td>
                                                <input name="jabbernot_username" type="text" id="jabbernot_username" value="<?php echo $jabbernot_username; ?>" size="20" ><br />
                                                <?php _e("Hier den Benutzernamen eintragen", 'jabbernotification') ?></td>
                                </tr>
                                <tr valign="top">
                                        <th scope="row"><?php _e("Passwort", 'jabbernotification') ?></th>
                                        <td>
                                                <input name="jabbernot_password" id="jabbernot_password" type="password" value="<?php echo $jabbernot_password; ?>" size="20" ><br />
                                                <?php _e("Hier das Passwort eintragen.", 'jabbernotification') ?></td>
                                </tr>
                                <tr valign="top">
                                        <th scope="row">Ressource</th>
                                        <td>
                                                <input name="jabbernot_resource" id="jabbernot_resource" type="text" value="<?php echo $jabbernot_resource; ?>" size="20" ><br />
                                                <?php _e("Hier die gew&uuml;nschte Resource eintragen. (Beispielsweise <em>Wordpress</em> oder<em> Notifier</em>).", 'jabbernotification') ?></td>
                                </tr>
                        </table>
                        <legend><?php _e("Zieldaten", 'jabbernotification') ?></legend>
                        <p><?php _e("Trage hier die Jabber-ID ein, an die die Benachrichtigung gesendet werden soll.", 'jabbernotification') ?></p>
                                <table width="100%" cellspacing="2" cellpadding="5" class="editform">
                                <tr valign="top">
                                        <th scope="row">Jabber-ID</th>
                                <td><input name="jabbernot_destination" type="text" id="jabbernot_destination" value="<?php echo $jabbernot_destination; ?>" size="30" /><br /> <?php _e("Beispiel:<em>meine-jabber-id@jabberserver.com", 'jabbernotification') ?></em></td>
                                </tr>
                        </table>
          </fieldset>
                        <p class="submit">
                                <input type="submit" name="Submit" value="<?php _e('Update Options', 'jabbernotification') ?> &raquo;" />
                        </p>
        </form>
<hr>
                <p style="margin-top: 30px; text-align: center; font-size: .85em;">Proudly presented by <a href="http://www.entartete-kunst.com/">Pinky</a> and the  <a href="http://burnachurch.com/">Brain</a>. <!--They're Pinky and the Brain, Brain, Brain, Brain, Brain, Brain, Brain, Brain, NARF!-->user-friendly adapted by <a href="http://jeremy.lonien.de/">Jeremy</a>.<br />
                Distributed under the terms of the <a href="http://www.fsf.org/licensing/licenses/gpl.html"> GPL -(Don't ask for support or features, do it on your own (but let me know!)). License</a> </p>
</div>
<?php }  // End jabbernot_options_subpanel


// ok, genug vom mausschubser- backend, lets go... (end of frontend, backend starts here)
function jabberbenachrichtigung($comment_id = 0)
{
        global $wpdb;
        if ($comment_id != 0)
        {
                require_once("class.jabber.php");

                $query = "SELECT $wpdb->posts.post_author, $wpdb->posts.post_title, $wpdb->comments.user_id, $wpdb->comments.comment_post_ID, $wpdb->comments.comment_author, $wpdb->comments.comment_content FROM $wpdb->comments INNER JOIN $wpdb->posts ON $wpdb->comments.comment_post_ID = $wpdb->posts.ID WHERE $wpdb->comments.comment_ID='$comment_id' AND comment_approved != 'spam'";


                $query_res = $wpdb->get_row($query);

                if (! $query_res->comment_post_ID)
                {
                        return;
                }

                $siteurl = get_option('siteurl');
                if ( $query_res->post_author != $query_res->user_id)
                {


                        $JABBER = new Jabber;
                        $JABBER->server         = get_option('jabbernot_server');
                        $JABBER->port           = get_option('jabbernot_port');
                        $JABBER->username       = get_option('jabbernot_username');
                        $JABBER->password       = get_option('jabbernot_password');
                        $JABBER->resource       = get_option('jabbernot_resource');
                        $JABBER->Connect() /*or die("Couldn't connect!")*/;
                        $JABBER->SendAuth() /*or die("Couldn't authenticate!")*/;
                        $content = array();
                        $content['body'] =
                                htmlspecialchars("Neuer Kommentar: $query_res->comment_author zu \"$query_res->post_title\" :\n\"$query_res->comment_content\"\n\n Alle Kommentare:\n$siteurl/wp-admin/edit.php?p=" . $query_res->comment_post_ID . "&c=1\n\nKommentar editieren:\n$siteurl/wp-admin/post.php?action=editcomment&comment=" . $comment_id . "\n\nKommentar loeschen:\n$siteurl/wp-admin/post.php?action=deletecomment&p=" . $query_res->comment_post_ID . "&comment=".$comment_id."\n\nModerieren:\n$siteurl/wp-admin/post.php?action=unapprovecomment&p=" . $query_res->comment_post_ID . "&comment=".$comment_id."\n\n");
                        $JABBER->SendMessage(get_option('jabbernot_destination'), "normal", NULL, $content);
                        $JABBER->Disconnect();
                }
        }
}

/* Actions & Filters */
add_action('comment_post', 'jabberbenachrichtigung');
add_action('admin_menu', 'jabbernot_admin_menu');
?>
