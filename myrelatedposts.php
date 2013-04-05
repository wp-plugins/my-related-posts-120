<?php

/*
 Author: Gyurka Mircea and Atomixstar
 Plugin Name: My Related Posts
 Description: Plugin for lists of related posts.
 Version: 1.2
 Author URL: http://www.atomixstar.com
*/

global $wpdb;
global $myrelposts_db_version;
$myrelposts_db_version = "1.0";
global $myrelposts_meta_tag;
$myrelposts_meta_tag = "_my_related_posts";
global $myrelposts_tablename;
$myrelposts_tablename = $wpdb->prefix."myrelposts";
global $myrelposts_pagename;
$myrelposts_pagename = 'myrelatedposts';

/**
 * Installs the plugins database table, if it doesn't already
 * exist in the system.
 */
function myrelposts_install() {
  global $wpdb;
  global $myrelposts_version;
  global $myrelposts_tablename;
  
  // tests if the table already exists!
  if($wpdb->get_var("show tables like '$myrelposts_tablename'") != $myrelposts_tablename) {
    // no table exists, create the table
    $sql = "CREATE TABLE ".$myrelposts_tablename."(
        id mediumint(9) not null auto_increment,
        title tinytext not null,
        meta varchar(255) not null,
        active tinyint default 1,
        unique key id (id)
      );";
      
    require_once(ABSPATH.'wp-admin/includes/upgrade.php');
    // executes the sql
    dbDelta($sql);
    
    $rows_affected = $wpdb->insert($myrelposts_tablename, array(
      'time' => current_time('mysql'),
      'name' => $welcome_name,
      'text' => $welcome_text
    ));
    
    add_option("myrelposts_db_version", $myrelposts_version);
  }
}

register_activation_hook(__FILE__,'myrelposts_install');



/**
 * Adds the "related posts" menu item to the plugin menu
 */
function myrelposts_config_page() {
  global $myrelposts_pagename;
  
  if(function_exists('add_submenu_page')) {
    add_submenu_page(
      'plugins.php',
      __('My Related Posts'),
      __('My Related Posts'),
      'manage_options',
      $myrelposts_pagename,
      'myrelposts_conf'
    );
  }
}
// adds the menu to the admin menu
add_action('admin_menu', 'myrelposts_config_page');

function myrelposts_makemeta($title) {
  $meta = "_";
  $time = '_'.time();
  $strippedTitle = preg_replace('/[^a-z0-9]+/',"_",strtolower($title));
  
  // adds an underscore, to make it hidden
  // adds the stripped title
  // adds the current time, to make it more unique
  return $meta.$strippedTitle.$time;
}

/**
 * Tells wordpress where the plugin options page is.
 */
function myrelposts_conf() {
  global $myrelposts_tablename, $wpdb;
  
  // if a post is going on
  if(isset($_POST)) {
    // credits: akismet plugin
    if(function_exists('current_user_can') && !current_user_can('manage_options')) {
			die(__('Cheatin&#8217; uh?'));
		}
		
		// a new list is being created
    if(isset($_POST['create'])) {
      // inserts the new list into the database
      $wpdb->insert(
        $myrelposts_tablename,
        array(
          'title' => $_POST['title'],
          'meta' => myrelposts_makemeta($_POST['title'])
        )
      );
    }
    // a list is being disabled
    else if(isset($_POST['disable']) && isset($_POST['id'])) {
      $wpdb->update(
        $myrelposts_tablename,
        array(
          'active' => '0'
        ),
        array(
          'id' => $_POST['id']
        )
      );
    }
    // a list is being enabled
    else if(isset($_POST['enable']) && isset($_POST['id'])) {
      $wpdb->update(
        $myrelposts_tablename,
        array(
          'active' => '1'
        ),
        array(
          'id' => $_POST['id']
        )
      );
    }
    // a list title is being updated
    else if(isset($_POST['update']) && isset($_POST['id']) && isset($_POST['title'])) {
      $wpdb->update(
        $myrelposts_tablename,
        array(
          'title' => $_POST['title']
        ),
        array(
          'id' => $_POST['id']
        )
      );
    }
    
    // NOTE: meta information cannot be changed!
  }
  
  // fetches the active list
  // constructs the query to use, to find the related posts.
  $sql = "select * from ".$myrelposts_tablename." as myrelposts where myrelposts.active = 1";
  
  // executes the sql
  $active = $wpdb->get_results($sql, OBJECT);
  
  // fetches the hidden list
  // constructs the query to use, to find the related posts.
  $sql = "select * from ".$myrelposts_tablename." as myrelposts where myrelposts.active = 0";
  
  // executes the sql
  $hidden = $wpdb->get_results($sql, OBJECT);
?>

<div class="wrap">
  <h2><?php _e('My Related Posts Configuration'); ?></h2>
  
  <ul id="myrel_main">
    <div class="major_description">
      This plugin is based around the concept of lists.<br/>
      You can create a list, then assign posts to it.
      <em>(A post can only be on <strong>one</strong> list.)</em><br/>
      When a list is created, you edit the blog posts and assign 
      them to the list.<br/>
      When a blog post is assigned to a list, they can be listed in your blog posts.<br/>
      <br/>
      You can <strong>hide</strong> lists from the drop down in the Edit / Create post interface
      by making them <strong>inactive</strong>. <em>(This will not effect the functionality, it is
      just to remove them from the drop down.)</em><br/>
      <br/>
      <a id="toggle_extended_description">Show / Hide extended documentation</a>
      <div id="extended_description" style="display: none;">
        <h3>Display the related posts</h3>
        There are two ways to display the related posts in the blog posts:<br/>
        Using a shortcode or changing your theme.
      
        <h4>Changing your theme</h4>
        You can edit your theme (theme-folder/single.php) and call the following function:<br/>
        <strong>echo myrelposts_getrelated($title);</strong><br/>
        <em>(The <strong>$title</strong> is optional, but let's you specify a title to
        to appear before the actual related posts are listed.)</em>
      
        <h4>Using a shortcode!</h4>
        You edit your blog posts and add the following shortcode:<br/>
        <strong>[myrelposts-related title="my title"]</strong><br/>
        <em>(The <strong>title</strong> is optional, but let's you specify a title to
        to appear before the actual related posts are listed.)</em>
        
        <h4>Get related posts, using the list name!</h4>
        A new feature in version 1.2 is that you can get a list of related posts, by
        passing the list name in the shortcode tag!<br/>
        <small>(This requires at least MySQL DB version 4.1!)</small><br/>
        Example:<br/>
        <strong>[myrelposts-related list="my related post list"]</strong><br/>
        Using the list tag, causes the plugin NOT to look at the current post's
        meta information, so use this tag wisely.<br/>
        <em>(as a bonus feature, you can use wildcards, %text%, in the list title.)</em>
      </div>
    </div>
    
    <li class="list_changer">
      <h3>Active lists</h3>
      <div class="description">
        Active lists can be chosen when editing or creating a blog post
      </div>
      <?php if(sizeOf($active) == 0) { ?>
        <div class="description">
          You have no active lists yet. Would you like to <a href="#" id="myrelposts_create_new_link">create</a> one?
        </div>
      <?php } else { ?>
        <ul>
          <?php $change = false; ?>
          <?php foreach($active as $list) { ?>
          <li <?php echo (($change)?'class="alt_color"':"");?>>
            <form method="post" action="">
              <input type="hidden" name="id" value="<?php echo $list->id; ?>" />
              <ul class="myrel_list-options">
                <li class="title">
                  <div class="view_area">
                    <?php echo $list->title; ?>
                  </div>
                  <div class="edit_area" id="edit_area_<?php echo $list->id; ?>" style="display: none;">
                    <input type="text" name="title" value="<?php echo $list->title; ?>" />
                    <input type="submit" name="update" value="Save" />
                    or <a href="#" class="cancel_link">cancel</a>
                  </div>
                </li>
                <li class="edit"><a class="edit_link" href="#">Edit</li>
                <li><a class="disable_link" href="#">Disable</a></li>
              </ul>
              <input type="submit" name="disable" style="display:none;">
            </form>
          </li>
          <?php $change = !$change;?>
          <?php } ?>
        </ul>
      <?php } ?>
    </li>
    <li class="list_changer">
      <h3>Inactive lists</h3>
      <div class="description">
        Inactive lists <strong>cannot</strong> be chosen when editing or creating a blog post
      </div>
      <?php if(sizeOf($hidden) == 0) { ?>
        <div class="description">
          You have no inactive lists yet.
        </div>
      <?php } else { ?>
        <ul>
          <?php foreach($hidden as $list) { ?>
            <li>
              <form method="post" action="">
                <input type="hidden" name="id" value="<?php echo $list->id; ?>" />
                <ul class="myrel_list-options">
                  <li class="title">
                    <div class="view_area">
                      <?php echo $list->title; ?>
                    </div>
                    <div class="edit_area" id="edit_area_<?php echo $list->id; ?>" style="display: none;">
                      <input type="text" name="title" value="<?php echo $list->title; ?>" />
                      <input type="submit" name="update" value="Save" />
                      or <a href="#" class="cancel_link">cancel</a>
                    </div>
                  </li>
                  <li class="edit"><a class="edit_link" href="#">Edit</li>
                  <li><a class="enable_link" href="#">Enable</a></li>
                </ul>
                <input type="submit" name="enable" style="display:none;">
              </form>
            </li>
          <?php } ?>
        </ul>
      <?php } ?>
    </li>
    <li>
      <h3>Create a new list</h3>
      <div class="description">
        A <strong>list</strong> is used to "relate" your blog posts to each other.<br/>
        Create a new list, open your blog post and add it to the list.<br/>
        When you display your blog post, a list of related posts will be
        displayed as well. (<a id="myrelposts_find_out_how">find out how</a>)
      </div>
      <form method="post" action="">
        <label for="title">Write a list title</label>
        <input type="text" name="title" id="myrelposts_new_list_title">
        <input type="submit" name="create" value="Create" />
      </form>
    </li>
  </ul>
</div>


<?php
}

add_action('admin_init', 'myrelposts_admin_init');
/**
 * Loads some scripts etc for the admin interface.
 */
function myrelposts_admin_init() {
  $fileDir = "/wp-content/plugins/myrelatedposts";
  // loads the admin stylesheet
  wp_enqueue_style("functions", $fileDir."/functions/admin.css", false, "1.0", "all");
  // loads the admin javascript
  wp_enqueue_script("rm_script", $fileDir."/functions/admin.js", false, "1.0");
}



/**
 * Fetches the related posts to the current post.
 * Use this function to display the related posts. If you pass in a string, you
 * can customize the header being displayed before the related posts.
 *
 * Note: An empty string works as well. (Displays no header)
 *
 * @param string $title the title to use. Defaults to <h3>Related posts</h3>
 * @param string $sorting used for sorting the posts. Valid values: ASC or DESC
 * @param string|mixed $list the name of the list you want to fetch data for, or NULL
 * @return string the related posts out put (remember to echo/print it!)
 */
function myrelposts_getrelated($title = "<h3>Related posts</h3>", $sorting = 'ASC', $list = NULL) {
  global $post;
  global $wpdb;
  global $myrelposts_meta_tag;
  global $myrelposts_tablename;
  
  // placeholder for the return value.
  $returnValue = "";
  // placeholder for the SQL we are going to use
  $sql = "";
  
  // if an id is set, continue
  if($list == NULL && $post->ID != "") {
    // fetches the current posts meta data
    $meta = get_post_meta($post->ID, $myrelposts_meta_tag, true);
    
    // constructs the query to use, to find the related posts.
    $sql = "
      select wposts.*
        from wp_posts wposts
        left join wp_postmeta wpostmeta on wpostmeta.post_id = wposts.id
        where wpostmeta.meta_key = '".$myrelposts_meta_tag."'
        AND wpostmeta.meta_value = '".$meta."'
        AND wposts.post_status = 'publish' 
        AND (wposts.post_type = 'post' OR wposts.post_type = 'page')
    ";
  }
  else if($list) {
    // constructs the query to use, to find the related posts.
    $sql = "
      select wposts.*
        from wp_posts wposts
        left join wp_postmeta wpostmeta on wpostmeta.post_id = wposts.id
        where wpostmeta.meta_value = (select meta from ".$myrelposts_tablename." where title like '".$list."')
        AND wposts.post_status = 'publish' 
        AND (wposts.post_type = 'post' OR wposts.post_type = 'page')
    ";
  }
  
  // if the SQL has any data in it, continue
  if($sql != "") {
    // handles the sorting parameter given:
    // if a wrong sorting was given, fallback to ASC 
    if($sorting != 'ASC' && $sorting != 'DESC') {
      $sorting = 'ASC';
    }
    // adds the sorting to the SQL
    $sql .= sprintf(
      " ORDER BY wposts.post_date %s",
      $sorting
    );
    
    // executes the sql
    $relatedPosts = $wpdb->get_results($sql, OBJECT);
    
    $returnValue = "";
    // if there are any related posts (besides the current)
    if(sizeOf($relatedPosts) > 1) {
      // constructs the string that is returned
      $returnValue .= '<div id="my_related_posts">'."\n";
        // prints the title
        $returnValue .= $title."\n";
        // starts the related post list
        $returnValue .= '<ul>'."\n";
          foreach($relatedPosts as $relatedPost) {
            if($post->ID == $relatedPost->ID) {
              $returnValue .= '<li class="current">'."\n";
            }
            else {
              $returnValue .= '<li>'."\n";
            }
              $returnValue .= "<a href=\"$relatedPost->guid\">$relatedPost->post_title</a>"."\n";
            $returnValue .= '</li>'."\n";
          }
        $returnValue .= '</ul>'."\n";
      $returnValue .= '</div>'."\n";
    }
  }
  
  return $returnValue;
}


/**
 * Adds a shortcode tag for the "get related posts" function.
 * This shortcode can be used inside a post, which will then write the
 * posts that are related to the current post.
 *
 * The shortcode tag is:
 * [myrelposts-related]
 * 
 * You can also add a title to the shortcode tag:
 * [myrelposts-related title="my specific title"]
 *
 * @param $params the parameters passed in through the shortcode tag
 * @return string the executed code for the myrelposts_getrelated function.
 */
function myrelposts_shortcode($params) {
  extract(
    shortcode_atts(
      array(
        'title' => '<h3>Related posts</h3>',
        'sort' => 'ASC',
        'list' => NULL
      ),
      $params
    )
  );
  
  // calls the get related posts method, with the extracted parameters
  return myrelposts_getrelated($title, $sort, $list);
}
add_shortcode('myrelposts-related', 'myrelposts_shortcode');


/* Define the custom box */

// WP 3.0+
// add_action('add_meta_boxes', 'myrelposts_add_custom_box');

// backwards compatible
add_action('admin_init', 'myrelposts_add_custom_box', 1);

/* Do something with the data entered */
add_action('save_post', 'myrelposts_save_postdata');

/**
 * Adds a box to the main column on the Post and Page edit screens
 */
function myrelposts_add_custom_box() {
  add_meta_box(
    'myrelposts_sectionid',
    __( 'My Related Posts', 'myrelposts_textdomain' ),
    'myrelposts_inner_custom_box',
    'post'
  );
  add_meta_box(
    'myrelposts_sectionid',
    __( 'My Related Posts', 'myrelposts_textdomain' ), 
    'myrelposts_inner_custom_box',
    'page'
  );
}

/**
 * This function is called, when we open an edit or create post page.
 *
 * Prints the box content in the post edit and create page
 */
function myrelposts_inner_custom_box() {
  global $post;
  global $wpdb;
  global $myrelposts_tablename;
  global $myrelposts_meta_tag;

  // Use nonce for verification
  wp_nonce_field( plugin_basename(__FILE__), 'myrelposts_noncename' );
  
  // fetches the currently selected serie on the post, if any
  $selected = get_post_meta($post->ID, $myrelposts_meta_tag, true);
  
  // fetches the currently selected list.
  $sql = "select * from $myrelposts_tablename where meta = '".$selected."'";
  $listArray = $wpdb->get_results($sql, OBJECT);
  
  $currentList = null;
  if(sizeOf($listArray) >= 1) {
    $currentList = $listArray[0];
  }
  
  // Writes the label
  ?>
  <div style="font-size: 12px; margin: 5px 0; line-height: 18px;">
    

    <?php // if there is a current list, but it is NOT active
    if($currentList && $currentList->active == false) { ?>
      <span id="myrelposts_currentlist">
        <label style="vertical-align: baseline;">
          <?php echo __("This blog post is assigned to: ", 'myrelposts_textdomain' ); ?>
        </label>
        <span style="font-weight: bold;"><?php echo $currentList->title; ?></span>
        <span> | </span>
        <a href="#" id="myrelposts_reset" title="Removes the post from the inactive list">Reset</a>
      
        <script type="text/javascript">
          jQuery(document).ready(function() {
            // hides the select box filled with active lists
            jQuery('#myrelposts_selectbox').hide();
            // sets the related posts keep to 1, which means that 
            // a new (or empty) list should not be saved.
            jQuery('#myrelposts_keep').val(1);
          
            // when the reset link is clicked, the select box is show
            // and the "current" list is removed.
            jQuery('#myrelposts_reset').click(function() {
              // removes the currently selected inactive list from the GUI
              jQuery('#myrelposts_currentlist').remove();
              // shows the select box.
              jQuery('#myrelposts_selectbox').show();
              // makes sure that we can actually save the new post
              jQuery('#myrelposts_keep').val(0);
            
              return false;
            });
          });
        </script>
      </span>
    <?php } ?>

    <?php
    // fetches the data in the "related posts" table
    $sql = "select * from $myrelposts_tablename where active = 1 order by title ASC";
    // executes the query
    $series = $wpdb->get_results($sql, OBJECT);
    ?>
  
    <span id="myrelposts_selectbox">
      <label for="myrelposts_series_field" style="vertical-align: baseline;">
        <?php echo __("Assign to related post list: ", 'myrelposts_textdomain' ); ?>
      </label>
      <input type="hidden" name="myrelposts_keep" id="myrelposts_keep" value="0" />
      <select id="myrelposts_series_field" name="myrelposts_series_field">
        <option></option>
        <?php
          // runs through the data in the table, adding them as an option
          foreach($series as $s) {
            // compares the meta data with the current posts meta data
            $isSelected = (($selected == $s->meta)?'selected="selected"':"");
      
            echo '<option value="'.$s->meta.'" '.$isSelected.'>'.$s->title.'</option>';
          }
        ?>
      </select>
    </span>
  </div>
  <?php
}

/** 
 * This function is called, when a post is saved. It uses the current post id
 * to determine if it should save any "My Related Posts" data to the post.
 * 
 * When the post is saved, save our custom data
 * @param int $postId the post that is being saved.
 */
function myrelposts_save_postdata($postId) {
  global $myrelposts_meta_tag;
  
  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times

  if ( !wp_verify_nonce( $_POST['myrelposts_noncename'], plugin_basename(__FILE__) )) {
    return $postId;
  }

  // verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
  // to do anything
  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
    return $postId;

  
  // Check permissions
  if ( 'page' == $_POST['post_type'] ) {
    if ( !current_user_can( 'edit_page', $postId ) )
      return $postId;
  } else {
    if ( !current_user_can( 'edit_post', $postId ) )
      return $postId;
  }

  // OK, we're authenticated: we need to find and save the data
  $mydata = $_POST['myrelposts_series_field'];
  $keepCurrent = $_POST['myrelposts_keep'];
  
  // only save data, if the keepCurrent is set to false, else return
  if($keepCurrent == true) {
    return $postId;
  }
  
  // no data set, delete the post meta, if any!
  if($mydata == "") {
    delete_post_meta($postId, $myrelposts_meta_tag);
  }
  // we have data, add it to the post!
  else {
    // tries to add the meta data to the post.
    add_post_meta($postId, $myrelposts_meta_tag, $mydata, true) or
    update_post_meta($postId, $myrelposts_meta_tag, $mydata);
  }
  
  // Do something with $mydata 
  // probably using add_post_meta(), update_post_meta(), or 
  // a custom table (see Further Reading section below)

   return $mydata;
}
?>