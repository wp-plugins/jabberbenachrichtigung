<?php if( defined( 'ABSPATH' ) == false ) // unbedingt drinnenlassen, schuetzt vor direktaufruf!
        die();
/*
Plugin Name: Jabbernotification
Plugin URI: http://www.entartete-kunst.com/jabberbenachrichtigung-ueber-neue-kommentare-reload/
Description: A fully-configurable Wordpress plugin which informs the admin about new comments through Jabber. <a href="options-general.php?page=jabbernot.php">Settings</a>
Version: 0.99-RC2
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
	$mofile = ABSPATH . 'wp-content/plugins/jabberbenachrichtigung/lang/jabbernotification-'.$locale.'.mo';
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
        <h2><?php _e("Jabbernotification: Options", 'jabbernotification')?></h2>
        <p><?php _e("Insert here the desired Jabber account settings, including your Jabber ID where the notification should be sent to. Please note, that this account must exist and won't be created for you.", 'jabbernotification')?> (<?php _e("Help?", 'jabbernotification')?> =&gt; <a href="http://www.entartete-kunst.com/yet-another-jabber-faq/">Jabber- FAQ (deutsch)</a>| <a href="http://archive.jabber.org/userguide/">Jabber- FAQ (english)</a>)</p>
        <form name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=jabbernot.php&updated=true">
                <input type="hidden" name="stage" value="process" />
                <fieldset class="options">
                        <legend>Notifier</legend>
                        <table width="100%" cellspacing="2" cellpadding="5" class="editform">
                                <tr valign="top">
                                        <th scope="row"> <?php _e("Server", 'jabbernotification') ?> </th>
                                        <td>
                                                <input name="jabbernot_server" type="text" id="jabbernot_server" value="<?php echo $jabbernot_server; ?>" size="20" /><br />
                                                <?php _e("Insert here the address of your Jabber server. You'll find a list of freely available Jabber server ", 'jabbernotification')?>
                                                <a href="http://www.jabberes.org/servers/servers_by_times_online.html"><?php _e("here.", 'jabbernotification')?></a></td>
                                </tr>
                                <tr valign="top">
                                        <th scope="row">Port</th>
                                        <td>
                                                <input name="jabbernot_port" type="text" id="jabbernot_port" value="<?php echo $jabbernot_port; ?>" maxlength=5 size="10" /><br />
                                                <?php _e("Port to be used. Default:", 'jabbernotification') ?>
                                                <code>5222</code></td>
                          </tr>
                                <tr valign="top">
                                        <th scope="row"<?php _e("Username", 'jabbernotification') ?></th>
                                <td>
                                                <input name="jabbernot_username" type="text" id="jabbernot_username" value="<?php echo $jabbernot_username; ?>" size="20" ><br />
                                                <?php _e("Insert your username here", 'jabbernotification') ?></td>
                                </tr>
                                <tr valign="top">
                                        <th scope="row"><?php _e("Password", 'jabbernotification') ?></th>
                                        <td>
                                                <input name="jabbernot_password" id="jabbernot_password" type="password" value="<?php echo $jabbernot_password; ?>" size="20" ><br />
                                                <?php _e("Insert your password here", 'jabbernotification') ?></td>
                                </tr>
                                <tr valign="top">
                                        <th scope="row">Ressource</th>
                                        <td>
                                                <input name="jabbernot_resource" id="jabbernot_resource" type="text" value="<?php echo $jabbernot_resource; ?>" size="20" ><br />
                                                <?php _e("Insert the desired resource here. (For example <em>Wordpress</em> or <em>Notifier</em>). ", 'jabbernotification') ?></td>
                                </tr>
                        </table>
                        <legend><?php _e("Recipient", 'jabbernotification') ?></legend>
                        <p><?php _e("Insert here the Jabber ID of the recipient that will receive the notification. ", 'jabbernotification') ?></p>
                                <table width="100%" cellspacing="2" cellpadding="5" class="editform">
                                <tr valign="top">
                                        <th scope="row">Jabber-ID</th>
                                <td><input name="jabbernot_destination" type="text" id="jabbernot_destination" value="<?php echo $jabbernot_destination; ?>" size="30" /><br /> <?php _e("Eg.:<em>my-jabber-id@jabberserver.com</em>", 'jabbernotification') ?></td>
                                </tr>
                        </table>
          </fieldset>
                        <p class="submit">
                                <input type="submit" name="Submit" value="<?php _e('Update Options', 'jabbernotification') ?> &raquo;" />
                        </p>
        </form>
<hr>
                <p style="margin-top: 30px; text-align: center; font-size: .85em;">Proudly presented by <a href="http://www.entartete-kunst.com/">Pinky</a> and the  <a href="http://burnachurch.com/">Brain</a>. <!--They're Pinky and the Brain, Brain, Brain, Brain, Brain, Brain, Brain, Brain, NARF!-->User-friendly adapted by <a href="http://jeremy.lonien.de/">Jeremy</a>.<br />
                Distributed under the terms of the <a href="http://www.fsf.org/licensing/licenses/gpl.html"> GPL -(Don't ask for support or features, do it on your own (but let me know!)) License.</a> </p>
</div>
<?php }  // End jabbernot_options_subpanel


// end of frontend, backend starts here

function jabbernotification($comment_id = 0)
{
        global $wpdb;
        if ($comment_id != 0)
        {

                require_once("class.jabber.php");
		$comment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->comments WHERE comment_ID=%d LIMIT 1", $comment_id));
        	$post = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID=%d LIMIT 1", $comment->comment_post_ID));
        	$comment_author_domain = @gethostbyaddr($comment->comment_author_IP);
        	$comments_waiting = $wpdb->get_var("SELECT count(comment_ID) FROM $wpdb->comments WHERE comment_approved = '0'");
        	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);


                if (! $comment->comment_post_ID)
                {
                        return;
                }

                if ( $post->post_author != $comment->user_id)
                {
			switch ($comment->comment_type)
			{
		       	 	case 'trackback':
		       	        	$notify_message  = sprintf( __('A new trackback on the post #%1$s "%2$s" is waiting for your approval', 'jabbernotification'), $post->ID, $post->post_title ) . "\r\n";
		       	        	$notify_message .= get_permalink($comment->comment_post_ID) . "\r\n\r\n";
		        	        $notify_message .= sprintf( __('Website : %1$s (IP: %2$s , %3$s)', 'jabbernotification'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
		                	$notify_message .= sprintf( __('URL    : %s', 'jabbernotification'), $comment->comment_author_url ) . "\r\n";
		                	$notify_message .= __('Trackback excerpt: ', 'jabbernotification') . "\r\n" . $comment->comment_content . "\r\n\r\n";
		                	break;
		        	case 'pingback':
		                	$notify_message  = sprintf( __('A new pingback on the post #%1$s "%2$s" is waiting for your approval', 'jabbernotification'), $post->ID, $post->post_title ) . "\r\n";
		                	$notify_message .= get_permalink($comment->comment_post_ID) . "\r\n\r\n";
		                	$notify_message .= sprintf( __('Website : %1$s (IP: %2$s , %3$s)', 'jabbernotification'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
		                	$notify_message .= sprintf( __('URL    : %s', 'jabbernotification'), $comment->comment_author_url ) . "\r\n";
		                	$notify_message .= __('Pingback excerpt: ', 'jabbernotification') . "\r\n" . $comment->comment_content . "\r\n\r\n";
		                	break;
		       
				 default: //Comments
		               		$notify_message  = sprintf( __('A new comment on the post #%1$s "%2$s" is waiting for your approval', 'jabbernotification'), $post->ID, $post->post_title ) . "\r\n";
		                	$notify_message .= get_permalink($comment->comment_post_ID) . "\r\n\r\n";
		                	$notify_message .= sprintf( __('Author : %1$s (IP: %2$s , %3$s)', 'jabbernotification'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
		                	$notify_message .= sprintf( __('E-mail : %s', 'jabbernotification'), $comment->comment_author_email ) . "\r\n";
		                	$notify_message .= sprintf( __('URL    : %s', 'jabbernotification'), $comment->comment_author_url ) . "\r\n";
		                	$notify_message .= sprintf( __('Whois  : http://ws.arin.net/cgi-bin/whois.pl?queryinput=%s', 'jabbernotification'), $comment->comment_author_IP ) . "\r\n";
		                	$notify_message .= __('Comment: ', 'jabbernotification') . "\r\n" . $comment->comment_content . "\r\n\r\n";
		                	break;
			}

			$notify_message .= sprintf( __('Approve it: %s', 'jabbernotification'),  admin_url("comment.php?action=approve&c=$comment_id") ) . "\r\n";
			if ( EMPTY_TRASH_DAYS )
		        	$notify_message .= sprintf( __('Trash it: %s', 'jabbernotification'), admin_url("comment.php?action=trash&c=$comment_id") ) . "\r\n";
			else
		        	$notify_message .= sprintf( __('Delete it: %s', 'jabbernotification'), admin_url("comment.php?action=delete&c=$comment_id") ) . "\r\n";
			
			$notify_message .= sprintf( __('Spam it: %s', 'jabbernotification'), admin_url("comment.php?action=spam&c=$comment_id") ) . "\r\n";
			//$notify_message .= sprintf( _n('Currently %s comment is waiting for approval. Please visit the moderation panel:',
			  //      'Currently %s comments are waiting for approval. Please visit the moderation panel:', $comments_waiting), number_format_i18n($comments_waiting) ) . "\r\n";
			$notify_message .= sprintf( _n('Currently %s comment is waiting for approval. Please visit the moderation panel:',
'Currently %s comments are waiting for approval. Please visit the moderation panel:', $comments_waiting, 'jabbernotification'), number_format_i18n($comments_waiting) ) . "\r\n";
			$notify_message .= admin_url("edit-comments.php?comment_status=moderated") . "\r\n";



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
                                htmlspecialchars($notify_message);
                        $JABBER->SendMessage(get_option('jabbernot_destination'), "normal", NULL, $content);
                        $JABBER->Disconnect();
                }
        }
}

/* Actions & Filters */
add_action('comment_post', 'jabbernotification');
add_action('admin_menu', 'jabbernot_admin_menu');
?>
