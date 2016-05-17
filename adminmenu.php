<?php

    add_action('admin_menu', 'talkwikitome_Menu');

    // Creates the necessary javascript functions to add the AJAX support to the form elements.
    add_action('admin_print_scripts', 'talkwikitome_js_admin_header' );

    // Defined below are the paired responses to the AJAX calls defined in the header.

    add_action('wp_ajax_talkwikitome_getlinkinfo', 'talkwikitome_ajax_getlinkinfo' );
    add_action('wp_ajax_talkwikitome_updatelinkinfo', 'talkwikitome_ajax_updatelinkinfo' );
    add_action('wp_ajax_talkwikitome_updategeneralinfo', 'talkwikitome_ajax_updategeneralinfo' );
    add_action('wp_ajax_talkwikitome_createlink', 'talkwikitome_ajax_createlink' );
    add_action('wp_ajax_talkwikitome_deletelink', 'talkwikitome_ajax_deletelink' );


    /*
     * Creates the database table and the default values if they haven't been defined.
     * Then it goes on to add the options page to the administrative settings page.
     */
    function talkwikitome_Menu () {
        global $wpdb;

        // Initialize the variables and databases
        $db_linktable = $wpdb->prefix . "talkwikitome_links";
                
        // Look for our link table and we haven't found it, it is time to create it.
        if ( $wpdb->get_var("SHOW TABLES LIKE '$db_linktable'") != $db_linktable )
        {
            $sql = "CREATE TABLE " . $db_linktable . " (
                      id mediumint(9) NOT NULL auto_increment COMMENT 'While the name is generally the unique identification, this ID has also been included',
                      name TINYTEXT NOT NULL COMMENT 'The human readable name attached to this link setting',
                      description TEXT NULL COMMENT 'Provides information about the purpose of this link tag',
                      tag TINYTEXT NOT NULL COMMENT 'The tag is what is used in the entry/page between the brackets',
                      url VARCHAR(255) NOT NULL COMMENT 'The url that this tag represents',
                      follow TINYINT(4) NOT NULL default '0' COMMENT '0 (default) search engines will follow the link, 1 search engines will not follow the link',
                      window TINYINT(4) NOT NULL default '0' COMMENT '0 (default) the link is opened in a new window, 1 the link is opened in the same window.',
                      alt_attribute TINYINT(4) NOT NULL default '0' COMMENT '0 (default) ALT attribute is included in the link, 1 ALT attribute is not included.',
                      title_attribute TINYINT(4) NOT NULL default '0' COMMENT '0 (default) TITLE attribute is included in the link, 1 TITLE attribute is not included.',
                      style_override TEXT NULL COMMENT 'The link style can be overriden with the following CSS definition',
                      UNIQUE KEY id (id)
                    );";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            // Add these default tags as an example...

            $google = array ( "Google Search" , "Basic Google Search", "google", "www.google.com/search?source=ig&hl=en&rlz=&btnG=Google+Search&aq=f&q=" );
            $googleimage = array ( "Google Image" , "Basic Google Image Search", "image", "http://images.google.com/images?hl=en&q=wordpress&btnG=Search+Images&gbv=2&aq=f&oq=" );
            $wiki = array ( "Wiki" , "Wiki (English) Page, the term specified links direcly to the page", "wiki", "http://en.wikipedia.org/wiki/" );
            $wordpress = array ( "Wordpress" , "Links right to the wordpress search results for the term specified", "wordpress", "http://wordpress.org/search/" );

            // TODO: Allow a way for individuals to specify a special term in specified URL
            // like: specify http://msdn.microsoft.com/en-us/library/system.attribute.aspx [as] http://msdn.microsoft.com/en-us/library/system.{0}.aspx

            $default_tags = array ( $google, $googleimage, $wiki, $wordpress );

            foreach ( $default_tags as $tag )
            {
                $wpdb->insert ( $db_linktable, array (
                                'name' => $wpdb->escape($tag[0]),
                                'description' => $wpdb->escape($tag[1]),
                                'tag' => $wpdb->escape($tag[2]),
                                'url' => $wpdb->escape($tag[3]) ));
            }

        }
        
        // Add options
        add_option("talkwikitome_brackets","1");

        // Add the options page
        add_options_page('Talk Wiki To Me', 'Talk Wiki To Me',8,__FILE__,'talkwikitome_OptionsPage');
    }

    /*
     * As per the instruction: http://codex.wordpress.org/AJAX_in_Plugins this method
     * is used to register the javascript hooks for the forms defined below.  Here
     * these functions call the associated AJAX functions defined below.
     */
    function talkwikitome_js_admin_header ()  {
        wp_print_scripts( array ( 'sack') );
        ?>
        <script type="text/javascript">
        //<![CDATA[
        function talkwikitome_getlinkinfo( selection )  {

            var mysack = new sack(
            "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );

            mysack.execute = 1;
            mysack.method = 'POST';
            mysack.setVar( "action", "talkwikitome_getlinkinfo" );
            mysack.setVar( "selected", selection.options[selection.selectedIndex].value );
            mysack.encVar( "cookie", document.cookie, false );
            mysack.onError = function() { alert('Ajax error in looking up selection' )};
            mysack.runAJAX();

            return true;
        }

        function talkwikitome_updatelinkinfo( updateditem )  {
            
            var mysack = new sack(
            "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );

            mysack.execute = 1;
            mysack.method = 'POST';
            mysack.setVar( "action", "talkwikitome_updatelinkinfo" );

            mysack.setVar( "name", updateditem.name.substring( 'talkwikitome_'.length ) );
            mysack.setVar( "value", updateditem.value );

            var selection = document.getElementById('ctl_link_select');
            mysack.setVar( "selected", selection.options[selection.selectedIndex].value );

            mysack.encVar( "cookie", document.cookie, false );
            mysack.onError = function() { alert('Ajax error in looking up selection' )};
            mysack.runAJAX();

            return true;
        }

        function talkwikitome_updategeneralinfo ( generalinfo )  {

            var mysack = new sack(
            "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );

            mysack.execute = 1;
            mysack.method = 'POST';
            mysack.setVar( "action", "talkwikitome_updategeneralinfo" );

            mysack.setVar( "name", generalinfo.name );
            mysack.setVar( "value", generalinfo.value );

            mysack.encVar( "cookie", document.cookie, false );
            mysack.onError = function() { alert('Ajax error in looking up selection' )};
            mysack.runAJAX();

            return true;
        }

        function talkwikitome_deletelink ()  {

            var selection = document.getElementById('ctl_link_select');
            var linkname = selection.options[selection.selectedIndex].value;

            if ( linkname != "talkwikitome_link_select_base_option" ) {

                var removelink = confirm("Are you sure you want to remove " + linkname + "?");

                if ( removelink != undefined ) {

                    var mysack = new sack(
                    "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );

                    mysack.execute = 1;
                    mysack.method = 'POST';
                    mysack.setVar( "action", "talkwikitome_deletelink" );

                    mysack.setVar( "name", linkname );
                    
                    mysack.encVar( "cookie", document.cookie, false );
                    mysack.onError = function() { alert('Ajax error in looking up selection' )};
                    mysack.runAJAX();
                }
            }

            
            return true;
        }

        function talkwikitome_createlink ()  {

            var linkname = prompt("Name of the new link","");

            if ( linkname != undefined )  {
                var mysack = new sack(
                "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );

                mysack.execute = 1;
                mysack.method = 'POST';
                mysack.setVar( "action", "talkwikitome_createlink" );

                mysack.setVar( "name", linkname );
                
                mysack.encVar( "cookie", document.cookie, false );
                mysack.onError = function() { alert('Ajax error in looking up selection' )};
                mysack.runAJAX();
            }

            return true;
        }


        //]]>
        </script>
        <?php


    }

    /*
     * AJAX method calls to here when the associated fields click or update the
     * fields related to the particular link.
     */
    function talkwikitome_ajax_updatelinkinfo()  {

        global $wpdb;

        $db_linktable = $wpdb->prefix . "talkwikitome_links";

        $selection = $wpdb->escape($_POST['selected']);
        $name = $wpdb->escape($_POST['name']);
        $value = $wpdb->escape($_POST['value']);

        $wpdb->show_errors();

        $index = $wpdb->get_var("SELECT id FROM $db_linktable WHERE name='$selection'");
        $wpdb->update( $db_linktable, array ( $name => $value ), array (  'id' => $index ) );

        $wpdb->hide_errors();

        die();
    }

    /*
     * AJAX method called when the select changes to a new option.
     */
    function talkwikitome_ajax_getlinkinfo ()  {
        global $wpdb;

        $db_linktable = $wpdb->prefix . "talkwikitome_links";

        $selection = $_POST['selected'];

        $linkInfo = $wpdb->get_row ( "SELECT * FROM $db_linktable WHERE name='$selection' " );

        echo ( "document.getElementById('ctl_link_url').value = \"" . $linkInfo->url . "\";" );
        echo ( "document.getElementById('ctl_link_desc').value = \"" . $linkInfo->description . "\";" );
        echo ( "document.getElementById('ctl_link_tag').value = \"" . $linkInfo->tag . "\";" );
        echo ( "document.getElementById('ctl_style_override').value = \"" . $linkInfo->style_override . "\";" );

        if ( $linkInfo->follow == '0' )  {
            echo ( "document.getElementById('ctl_search_follow').checked = true;" );
        } else {
            echo ( "document.getElementById('ctl_search_nofollow').checked = true;" );
        }

        if ( $linkInfo->window == '0' )  {
            echo ( "document.getElementById('ctl_window_new').checked = true;" );
        } else {
            echo ( "document.getElementById('ctl_window_same').checked = true;" );
        }

        if ( $linkInfo->title_attribute == '0' )  {
            echo ( "document.getElementById('ctl_alt_yes').checked = true;" );
        } else {
            echo ( "document.getElementById('ctl_alt_no').checked = true;" );
        }

        if ( $linkInfo->alt_attribute == '0' )  {
            echo ( "document.getElementById('ctl_title_yes').checked = true;" );
        } else {
            echo ( "document.getElementById('ctl_title_no').checked = true;" );
        }

        //echo ( "document.getElementById('test_output_query').value = \"" . $query ."\";" );
        //die ( "document.getElementById('test_output_result').value = \"" . $linkInfo->url . "\";" );
        die();
    }

    /*
     * AJAX method calls the general information, information not stored in the
     * database table and more using the add_option, get_option, set_option, etc.
     */
    function talkwikitome_ajax_updategeneralinfo()  {
        update_option ( $_POST['name'], $_POST['value'] );
        die();
    }

    /*
     * AJAX method that is called to create a new link system.  This is after
     * a prompt in the talkwikitome_js_admin_header call which asks the user for
     * the name of new link.
     */
    function talkwikitome_ajax_createlink () {
        global $wpdb;

        $db_linktable = $wpdb->prefix . "talkwikitome_links";

        $name = $wpdb->escape($_POST['name']);
        $index = $wpdb->get_var("SELECT id FROM $db_linktable WHERE name='$name'");

        if ( $index == null ) {
            $wpdb->insert ( $db_linktable, array (
                                'name' => $name,
                                'description' => 'New custom link',
                                'tag' => '',
                                'url' => '' ));

            die( "var newOption = document.createElement('option');
                  newOption.appendChild(document.createTextNode('$name'));
                  document.getElementById('ctl_link_select').appendChild(newOption);" );
        }
        
        die();
    }

    /*
     * AJAX method that is called when a link that is selected is to be deleted.
     * A prompt appears before in the talkwikitome_js_admin_header that confirms
     * that the user wants to delete the option (and that it is not the default
     * select option).
     */
    function talkwikitome_ajax_deletelink () {
        global $wpdb;

        $db_linktable = $wpdb->prefix . "talkwikitome_links";

        $name = $wpdb->escape($_POST['name']);
        $index = $wpdb->get_var("SELECT id FROM $db_linktable WHERE name='$name'");

        if ( $index != null )  {
            $wpdb->query("DELETE FROM $db_linktable WHERE id = '$index' ");

            die( "var selection = document.getElementById('ctl_link_select');
                  for ( i = 1; i< selection.options.length; i++ )  {
                    if ( selection.options[i].value == '$name' ) {
                        selection.remove(i);
                    }
                  }" );
        }

        die();
    }


    /*
     * The page that is displayed in the administrative settings section.  This is
     * added through the talkwikitome_Menu function which makes the call:
     * add_options_page('','',8,__FILE__,talkwikitome_OptionsPage);
     *
     * Below, the options panel defines the user interface to configure the link system.
     * Sadly this is all done in the tables, because I wanted to get something done and
     * done with the maximum compatibility.
     *
     * Each input, textarea, etc is wired to fired to send an AJAX call to store the
     * settings changed.  These calls are wired through the additional functions that are defined.
     *
     * talkwikitome_jsadmin_header() defines the javascript functions that call the AJAX methods
     * defined below:
     *
     * The inputs at the top, related to the bracket call talkwikitome_ajax_updategeneralinfo()
     * The select calls the talkwikitome_ajax_getlinkinfo() to get and load all the information.
     * The create button calls talkwikitome_ajax_createlink()
     * The delete button calls talkwikitome_ajax_deletelink()
     * Each other input, textarea calls talkwikitome_ajax_updatelinkinfo()
     */
    function talkwikitome_OptionsPage () {
        global $wpdb;
        ?>

        <div class="wrap">
            <h1>Talk Wiki To Me</h1>
            <hr/>
            Talk Wiki To Me is based on the Better-[[Wiki]]-Links system.  "Talk Wiki" allows you to define your own wiki like tags to help you speed up linking
            to other sites and protect your links from becoming out-dated by allowing you to change them in one place.<br/>
            <p>[[TAG|TERM|TEXT]]</p>

            <strong>Examples:</strong><br/>

            <span style="color: gray; font-style: italic;">[[wiki|NASA|My dream job]]</span>
            would be converted to:
            <span style="color: gray; font-style: italic">&lt;a href="http://en.wikipedia.org/wiki/NASA"&gt;My dream job&lt;/a&gt;</span>
            <br/>
            <span style="color: gray; font-style: italic;">[[google|local food|Local places to eat]]</span>
            would be converted to:
            <span style="color: gray; font-style: italic">&lt;a href="http://www.google.com/search?q=local+food"&gt;Local places to eat&lt;/a&gt;</span>
            <hr/>
            
            <form id="form_bracketstyle" action="">

                <!-- Basic Bracket Settings -->

                <table width="100%" style="">
                    <tr>
                        <td width="300px" style="vertical-align: top; background: #F1F5F8; padding: 5px;">
                            <strong>Bracket Settings:</strong>
                            <span style="font-size: 10px; color: gray;">
                                The style of bracket you use in the entry to signal the start of the link.  Traditional wiki links use [[ ]], but you can specify a format that suits you.
                            </span>
                        </td>
                        <td style="background: #F1F5F8; padding: 5px;">
                            <input type="radio" name="talkwikitome_brackets" value="1" onclick="talkwikitome_updategeneralinfo(this)" <?php if ("1" == get_option("talkwikitome_brackets")) { echo "checked";}; ?>>[[ ]] <br>
                            <input type="radio" name="talkwikitome_brackets" value="2" onclick="talkwikitome_updategeneralinfo(this)" <?php if ("2" == get_option("talkwikitome_brackets")) { echo "checked";}; ?>>(( )) <br>
                            <input type="radio" name="talkwikitome_brackets" value="3" onclick="talkwikitome_updategeneralinfo(this)" <?php if ("3" == get_option("talkwikitome_brackets")) { echo "checked";}; ?>>{{ }}
                        </td>
                    </tr>
                </table>
            </form>

            <hr/>
            
            <form id="form_linkentry" action="<?php echo ($_SERVER["PHP_SELF"]); ?>">
                <table width="100%" style="">

                    <!-- Selection (Drop Down) of the current links -->

                    <tr>
                        <td width="300px" style="background: #7E90B8; padding: 5px;">
                            <strong>Current Talk Wiki Link</strong>
                        </td>
                        <td style="background: #7E90B8; padding: 5px;">
                             <select name="talkwikitome_link_select" id="ctl_link_select" onchange="talkwikitome_getlinkinfo(this)" style="width: 350px;">
                                <option value="talkwikitome_link_select_base_option">Select a Wiki Link ...</option>
                                <?php
                                    $db_linktable = $wpdb->prefix . "talkwikitome_links";

                                    $sqlResult = $wpdb->get_col ( "SELECT name FROM $db_linktable" );

                                    foreach ( $sqlResult as $name )
                                    {
                                        echo ("<option>$name</option>");
                                    }
                                ?>
                            </select>

                            <!-- Create and Delete Options -->

                            <input type="button" id="ctl_link_new" onclick="talkwikitome_createlink()" name="talkwikitome_create" value="create..." />
                            &nbsp;
                            <input type="button" id="ctl_link_delete" onclick="talkwikitome_deletelink()" name="talkwikitome_delete" value="delete" />
                            
                        </td>
                    </tr>

                    <!-- Description -->

                    <tr>
                        <td width="300px" style="vertical-align: top; background: #F1F5F8; padding: 5px;">
                            <strong>Description</strong>:<br/>
                            <span style="font-size: 10px; color: gray;">
                                A small description of the link to allow you to leave a small not about the purpose
                            </span>
                        </td>
                        <td style="background: #F1F5F8; padding: 5px;">
                            <!--<input id="ctl_link_desc" type="text" name="talkwikitome_description" size="80" onblur="talkwikitome_updatelinkinfo(this)" />-->
                            <textarea id="ctl_link_desc" name="talkwikitome_description" cols="65" rows="2" onblur="talkwikitome_updatelinkinfo(this)"></textarea>
                        </td>
                    </tr>

                    <!-- Tag used in the link -->

                    <tr>
                        <td width="300px" style="vertical-align: top; background: #FFFFFF; padding: 5px;">
                            <strong>Tag</strong>:<br/>
                        </td>
                        <td style="padding: 5px;">
                            <input id="ctl_link_tag" type="text" name="talkwikitome_tag" size="79" onblur="talkwikitome_updatelinkinfo(this)" />
                            <!--<textarea id="ctl_link_tag" name="talkwikitome_tag" cols="65" rows="1" onblur="talkwikitome_updatelinkinfo(this)"></textarea>-->
                        </td>
                    </tr>

                    <!-- Link URL -->

                    <tr>
                        <td width="300px" style="vertical-align: top; background: #F1F5F8; padding: 5px;">
                            <strong>Link URL</strong>:<br/>
                            <span style="font-size: 10px; color: gray;">
                                The url that the tag will transform into when shown in the entry/page
                            </span>
                        </td>
                        <td style="background: #F1F5F8; padding: 5px;">
                            <!--<input id="ctl_link_url" type="text" name="talkwikitome_url" size="80" onblur="talkwikitome_updatelinkinfo(this)" />-->
                            <textarea id="ctl_link_url" name="talkwikitome_url" cols="65" rows="2" onblur="talkwikitome_updatelinkinfo(this)"></textarea>
                        </td>
                    </tr>

                    <!-- Follow or No Follow -->

                    <tr>
                        <td width="300px" style="vertical-align: top; background: #FFFFFF; padding: 5px;">
                            <strong>Search Engine Behavior</strong><br/>
                            <span style="font-size: 10px; color: gray;">
                                <a href="http://en.wikipedia.org/wiki/Nofollow">nofollow</a> prevents the content from being indexed by some search engines
                            </span>

                        </td>
                        <td style="padding: 5px;">
                            <input id="ctl_search_follow" type="radio" name="talkwikitome_follow" onclick="talkwikitome_updatelinkinfo(this)" value="0" />&nbsp;Search engines follow (follow)
                            &nbsp;&nbsp;
                            <input id="ctl_search_nofollow" type="radio" name="talkwikitome_follow" onclick="talkwikitome_updatelinkinfo(this)" value="1" />&nbsp;Search engines ignore (nofollow)
                        </td>
                    </tr>

                    <!-- New Browser or Same Browser -->

                    <tr>
                        <td width="300px" style="vertical-align: top; background: #F1F5F8; padding: 5px;">
                            <strong>Open links in...</strong><br/>
                            <span style="font-size: 10px; color: gray;">When a user clicks on these links where do you want the new page to appear</span>
                        </td>
                        <td style="background: #F1F5F8; padding: 5px;">
                            <input id="ctl_window_new" type="radio" name="talkwikitome_window" onclick="talkwikitome_updatelinkinfo(this)" value="0" />&nbsp;New Browser
                            &nbsp;&nbsp;
                            <input id="ctl_window_same" type="radio" name="talkwikitome_window" onclick="talkwikitome_updatelinkinfo(this)" value="1" />&nbsp;Same Browser
                        </td>
                    </tr>

                    <!-- Tooltip use ALT-attribute (IEStyle) yes/no -->

                    <tr>
                        <td width="300px" style="vertical-align: top; background: #FFFFFF; padding: 5px;">
                            <strong>Use ALT-attribute (IE-Style)</strong><br/>
                            <span style="font-size: 10px; color: gray;">Include an ALT attribute with the link, to allow tooltips in IE</span>
                        </td>
                        <td style="padding: 5px;">
                            <input id="ctl_alt_yes" type="radio" name="talkwikitome_alt_attribute" onclick="talkwikitome_updatelinkinfo(this)" value="1" />&nbsp;yes
                            &nbsp;&nbsp;
                            <input id="ctl_alt_no" type="radio" name="talkwikitome_alt_attribute" onclick="talkwikitome_updatelinkinfo(this)" value="0" />&nbsp;no
                        </td>
                    </tr>

                    <!-- Tooltip use TITLE-attribute (MozillaStyle) yes/no -->

                    <tr>
                        <td width="300px" style="vertical-align: top; background: #F1F5F8; padding: 5px;">
                            <strong>Use TITLE-attribute (Mozilla-Style)</strong><br/>
                            <span style="font-size: 10px; color: gray;">Include an TITLE attribute with the link, to allow tooltips in Mozilla</span>
                        </td>
                        <td style="background: #F1F5F8; padding: 5px;">
                            <input id="ctl_title_yes" type="radio" name="talkwikitome_title_attribute" onclick="talkwikitome_updatelinkinfo(this)" value="1" />&nbsp;yes
                            &nbsp;&nbsp;
                            <input id="ctl_title_no" type="radio" name="talkwikitome_title_attribute" onclick="talkwikitome_updatelinkinfo(this)" value="0" />&nbsp;no
                        </td>
                    </tr>

                    <!-- Override style tag -->
                    
                    <tr>
                        <td width="300px" style="vertical-align: top; background: #FFFFFF; padding: 5px;">
                            <strong>Override <a href="http://www.w3schools.com/css/default.asp">(CSS)</a> Style</strong><br/>
                            <span style="font-size: 10px; color: gray;">Examples:
                            color:#FF0000; (<span style="color:#FF0000;">red lettering</span>)<br/>
                            background-color:yellow; (<span style="background-color:yellow;">yellow background</span>)<br/>
                            font-weight:bold; (<span style="font-weight:bold;">bold lettering</span>)</span>
                        </td>
                        <td style="padding: 5px;">
                            <!--<input id="ctl_style_override" type="text" name="talkwikitome_style_override" onblur="talkwikitome_updatelinkinfo(this)" size="80" /><br/>-->
                            <textarea id="ctl_style_override" name="talkwikitome_style_override" onblur="talkwikitome_updatelinkinfo(this)" cols="65" rows="2"></textarea>
                        </td>
                    </tr>
                </table>

                <!--
                <p>Debug</p>
                <textarea id="test_output_query" cols="20" rows="5"></textarea>
                <textarea id="test_output_result" cols="20" rows="5"></textarea>
                -->

            </form>
        </div>
        
    <?php }
?>
