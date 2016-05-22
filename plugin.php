<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////

class talkwikitome {

    /*
     * When talkwikitome is created, down below in this file, the constructor registers the function:
     * talkwikitomeTranslate to the content rendering, allowing for the plugin
     * to perform the translation of the mundane bracket/tag to the url
     */
    function talkwikitome() {
        add_filter('the_content', array(&$this, 'talkwikitomeTranslate'));
    }

    /*
     * Utilizes the wordpress database global: $wpdb
     * http://codex.wordpress.org/Function_Reference/wpdb_Class
     *
     * The format: [BRACKETSTYLE]TAG|TERM|TEXT[BRACKETSTYLE]
     *
     * Based on the bracket style specified, the system looks through
     * the content to find the formated text.  Then attempts to look up
     * the link information based on the TAG used.
     *
     * Then appends the TERM to end of the url in the database.  Utilizes
     * all the other specified settings.
     *
     * The link will appear with the TEXT specified in the anchor:
     * <a href=""...>TEXT</a>
     *
     */
    function talkwikitomeTranslate($content) {
        global $wpdb, $post;
        $existing_tags = get_post_meta($post->ID, 'talkwikitome_tags', true);
        if (!$existing_tags) $existing_tags = array();
        $summaries = get_post_meta($post->ID, 'talkwikitome_wiki_summaries', true);

        // TODO: This should simply be a global variable.
        $db_linktable = $wpdb->prefix . "talkwikitome_links";

        // Load the style of bracketing from the database
        $talkwikitome_brackets = get_option("talkwikitome_brackets");

        switch ($talkwikitome_brackets) {
            case "1":
                $pattern = "!\[\[([^\|]+)\|([^\|]+)\|([^\]]+)\]\]!isU";
                break;
            case "2":
                $pattern = "!\(\(([^\)]*)\|(.*)\|(.*)\)\)!isU";
                break;
            case "3":
                $pattern = "!\{\{([^\}]*)\|(.*)\|(.*)\}\}!isU";
                break;
            default:
                $pattern = "!\[\[([^\]]*)\|(.*)\|(.*)\]\]!isU";
                break;
        }

        // Look to see if the pattern is a match: (url)|(term)|(text)

        $match = array();
	$refBullets = array();
	$tags = array();
        $position = 0;

        // Continue to look through the content for all the matchnes, moving forward
        //  and keeping track of the position...
        while (($position = preg_match($pattern, $content, $match, 0, $position))) {
                $tag = $wpdb->escape( $match[1] );
                $term = urlencode(str_replace(' ', '_', $match[2]));
                $text = $match[3];

                if (!strlen($text)) $text = $term;

                $link = $wpdb->get_row ( "SELECT * FROM $db_linktable WHERE tag='$tag' " );
                $url = $link->url . str_replace(' ', '_', $match[2]);
                $anchor = "<a href=\"$url\" ";
            
                if ( $link->follow == 1 )  {
                        $anchor .= " nofollow ";
                }

                if ( $link->window == 0 )  {
                        $anchor .= "target=\"_blank\" ";
                } else {
                        $anchor .= "target=\"_top\" ";
                }

                if ( $link->alt_attribute == 0 )  {
                        $anchor .= "alt=\"$text\" ";
                }

                if ( $link->title_attribute == 0 )  {
                        $anchor .= "title=\"$text\" ";
                }

                if ( $link->style_override != null )  {
                        $anchor .= "style=\"" . $link->style_override . "\" ";
                }

                $ref = array(
                        'link' => $anchor,
                        'text' => $text
                );
                array_push($refBullets, $ref);
                array_push($tags, array(
                        'position' => $position,
                        'tag' => $term
                ));

                $content = str_replace($match[0],$anchor . ">$text</a>",$content);
        }

        $check_new_data = false;
        if (count($tags) > 0) {
                if (count($summaries) == 0) {
                        error_log('No summaries, should fetch...');
                        $check_new_data = true;
                }
                $diff = array_diff_assoc($existing_tags, $tags);
                if (count($diff)) {
                        update_post_meta($post->ID, 'talkwikitome_tags', $tags);
                        $check_new_data = true;
                        error_log('There looks to be new tags.');
                        error_log('Old num: '.count($existing_tags).', new num: '.count($tags));
                }
        }
        
        // For debug
        //$check_new_data = true;
        if ($check_new_data) {
                /*
                 * Should add some kind of check to make sure the wiki summaries are available on the server.
                 * Should also make this server driven by the link table.
                 */
        	$feedURL = 'http://'.$_SERVER['SERVER_NAME'].'/wiki/api.php?action=summaries&format=json&pages=';
        	
        	for ($i = 0; $i < count($tags); $i++) {
        		if ($i == 0) {
        			$feedURL .= $tags[$i]['tag'];
        		} else {
        			$feedURL .= '|'.$tags[$i]['tag'];
        		} 
        	}
        	error_log("wiki feed URL: ".$feedURL);
        	
        	$urlHandle = fopen($feedURL, "r");
        	if ($urlHandle) {
        		$jsonData = stream_get_contents($urlHandle);
                        fclose($urlHandle);
                        $data = json_decode($jsonData, true);
                        $summaries = $data['summaries'];
                        update_post_meta($post->ID, 'talkwikitome_wiki_summaries', $summaries);
                }
        }

        $leftRight = "float: left;";
        for ($i = 0; $i < count($summaries); $i++) {
                $summary = $summaries[$i];
                if (strlen($summary['summary'])) {
                        $replacement = '<div class="wiki-info-sidebar">
                                          <div class="wiki-info-sidebar-header">
                                            <h3><a href="'.$summary['link'].'">'.$summary['title'].'</a></h3>
                                          </div>
                                          <div class="wiki-info-sidebar-body">
                                            '.trim($summary['summary']).'<a href="'.$summary['link'].'" class="wiki-info-sidebar-and">...</a>
                                          </div>
                                        </div>';
										
                        $pos = strpos($content, '/wiki/index.php/'.$tags[$i]['tag']);
                        $nextPos = strpos($content, "</p>", $pos);
                        if ($nextPos > $pos) {
                                $lastPos = strpos($content, "</p>", $nextPos+1);
                                        if ($lastPos > $nextPos) {
                                                $pos = $lastPos;
                                        } else {
                                                $pos = $nextPos;
                                        } 
                        }
                        $content = substr_replace($content, '</p>'.$replacement, $pos, 4);
                }
        }

        return $content;
    }
    
    function findWithin($list, $tag) {
    	for ($i = 0; $i < count($list); $i++) {
    		if ($list[$i]['title'] == $tag) {
    			return $list[$i];
    		}
    	}
    	return false;
    }

}


// Creates and registers the talkwikitome with wordpress
$sign &= new talkwikitome();

////////////////////////////////////////////////////////////////////////////////////////////////////////

?>
