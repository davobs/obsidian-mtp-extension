<?php
/*
 
Plugin Name: Obsidian multitheme extension
 
Description: Automatically add CompaReview & Inspiration posts to multitheme plugin
 
Version: 1.0 

Author: Dav

*/

/* Add plugin to dashboard menu */

add_action('admin_menu', 'hmb_plugin');

function hmb_plugin()
{
    add_menu_page('Set secondary theme for WordPress if using Multitheme Plugin', 'Secondary theme', 'manage_options', 'hmb_plugin', 'hmb_init');
}

/* Add option to database (default CompaReview) */

add_option('hmb_option', 'CompaReview');

if (isset($_POST['submit'])) {
    $hmbOption = htmlentities($_POST['hmbSelection']);
    update_option('hmb_option', $hmbOption);
}


function hmb_init()
{ ?>
    <div class="notice" id="rewrite">
        <form action="" method="POST" style="display: flex;align-items: center;padding: 20px 0;gap: 10px;margin: 0;">
            <label for="hmbSelection">Chose a secondary theme:</label>
            <select name="hmbSelection" id="hmbSelection">
                <?php $themes = wp_get_themes();
                $hmbSelected = get_option('hmb_option');
                foreach ($themes as $theme) {
                    if ($hmbSelected == $theme->stylesheet) {
                        echo '<option name="' . $theme . '" selected id="' . $theme . '" value="' . $theme->stylesheet . '">' . $theme . '</option>';
                    } else {
                        echo '<option name="' . $theme . '" id="' . $theme . '" value="' . $theme->stylesheet . '">' . $theme . '</option>';
                    }
                } ?>
            </select>
            <input type="submit" name="submit" onclick="hmb_set_theme();" value="Select"></input>
        </form>
    </div>
<?php }


/* Fire hook for CompaReview if post has been created or updated */
add_action('save_post_compareview', 'hmbCR_on_post_action', 10, 3);

function hmbCR_on_post_action($post_ID, $post, $update)
{
    if ($post->post_status == 'auto-draft' || $post->post_status == 'draft') {
        return;
    } else {
        /* Get post name */
        $postName = $post->post_name;

        /* Get multitheme serialized array */
        global $wpdb;
        $results = $wpdb->get_results(
            "
        SELECT option_id, option_value
        FROM $wpdb->options
        WHERE option_name = 'jr_mt_settings'
        "
        );
        /* Database info */
        $databaseID = $results[0]->option_id;
        $hmbSerializedString = $results[0]->option_value;
        /* Unserialize data */
        $hmbUnserialized = @unserialize($hmbSerializedString);

        /* Extract post urls - get multitheme plugin aliases */
        $hmbPostUrls = $hmbUnserialized['url'];

        $hmbHostUrl = $hmbUnserialized['aliases'][0]['url'];
        $hmbHostUrlHost = $hmbUnserialized['aliases'][0]['prep']['host'];
        $hmbHostUrlPort = $hmbUnserialized['aliases'][0]['prep']['port'];

        $hmbWwwUrl = $hmbUnserialized['aliases'][1]['url'];
        $hmbWwwUrlHost = $hmbUnserialized['aliases'][1]['prep']['host'];
        $hmbWwwUrlPort = $hmbUnserialized['aliases'][1]['prep']['port'];


        /* Iterate trough mutlitheme post urls array */
        $getAllSlugs = array();
        foreach ($hmbPostUrls as $rel_url) {
            $getAllSlugs[] = $rel_url['rel_url'];
        }

        /* Check if post exists in all posts array if not create arr with post properties and push it to main array */
        if (in_array($postName, $getAllSlugs)) {
        } else {
            /* Check if post name is not empty so drafts are not counted in */
            if (!empty($postName)) {
                $createArr = array("url" => $hmbHostUrl . "/" . $postName . "/", "rel_url" => $postName, "theme" => "CompaReview", "prep" => array(array("host" => $hmbHostUrlHost, "path" => "/" . $postName, "port" => $hmbHostUrlPort, "query" => array()), array("host" => $hmbWwwUrlHost, "path" => "/" . $postName, "port" => $hmbWwwUrlPort, "query" => array())), "id" => $post_ID, "id_kw" => "p");
                array_push($hmbUnserialized['url'], $createArr);
            }
        }
        /* Serialize the data and update DB */
        $hmbSerialized = serialize($hmbUnserialized);
        $wpdb->update($wpdb->options, array('option_value' => $hmbSerialized), array('option_id' => $databaseID));
    }
}


/* Fire hook for Inspiration if post has been created or updated */
add_action('save_post_inspiration', 'hmbI_on_post_action', 10, 3);

function hmbI_on_post_action($post_ID, $post, $update)
{
    if ($post->post_status == 'auto-draft' || $post->post_status == 'draft') {
        return;
    } else {
        /* Get post name */
        $postName = $post->post_name;

        /* Get multitheme serialized array */
        global $wpdb;
        $results = $wpdb->get_results(
            "
        SELECT option_id, option_value
        FROM $wpdb->options
        WHERE option_name = 'jr_mt_settings'
        "
        );
        /* Database info */
        $databaseID = $results[0]->option_id;
        $hmbSerializedString = $results[0]->option_value;
        /* Unserialize data */
        $hmbUnserialized = @unserialize($hmbSerializedString);

        /* Extract post urls - get multitheme plugin aliases */
        $hmbPostUrls = $hmbUnserialized['url'];

        $hmbHostUrl = $hmbUnserialized['aliases'][0]['url'];
        $hmbHostUrlHost = $hmbUnserialized['aliases'][0]['prep']['host'];
        $hmbHostUrlPort = $hmbUnserialized['aliases'][0]['prep']['port'];

        $hmbWwwUrlHost = $hmbUnserialized['aliases'][1]['prep']['host'];
        $hmbWwwUrlPort = $hmbUnserialized['aliases'][1]['prep']['port'];


        /* Iterate trough mutlitheme post urls array */
        $getAllSlugs = array();
        foreach ($hmbPostUrls as $rel_url) {
            $getAllSlugs[] = $rel_url['rel_url'];
        }

        /* Check if post exists in all posts array if not create arr with post properties and push it to main array */
        if (in_array($postName, $getAllSlugs)) {
        } else {
            /* Check if post name is not empty */
            if (!empty($postName)) {
                $createArr = array("url" => $hmbHostUrl . "/" . $postName . "/", "rel_url" => $postName, "theme" => "CompaReview", "prep" => array(array("host" => $hmbHostUrlHost, "path" => "/" . $postName, "port" => $hmbHostUrlPort, "query" => array()), array("host" => $hmbWwwUrlHost, "path" => "/" . $postName, "port" => $hmbWwwUrlPort, "query" => array())), "id" => $post_ID, "id_kw" => "p");
                array_push($hmbUnserialized['url'], $createArr);
            }
        }
        /* Serialize the data and update DB */
        $hmbSerialized = serialize($hmbUnserialized);
        $wpdb->update($wpdb->options, array('option_value' => $hmbSerialized), array('option_id' => $databaseID));
    }
}
/* Fire hook for regular post if post has been created or updated */
add_action('save_post_post', 'hmbP_on_post_action', 10, 3);

function hmbP_on_post_action($post_ID, $post, $update)
{
    if ($post->post_status == 'auto-draft' || $post->post_status == 'draft') {
        return;
    } else {
        /* Get theme name from selection */
        $themeName = get_option('hmb_option');

        /* Get post name */
        $postName = $post->post_name;

        /* Get multitheme serialized array */
        global $wpdb;
        $results = $wpdb->get_results(
            "
        SELECT option_id, option_value
        FROM $wpdb->options
        WHERE option_name = 'jr_mt_settings'
        "
        );
        /* Database info */
        $databaseID = $results[0]->option_id;
        $hmbSerializedString = $results[0]->option_value;
        /* Unserialize data */
        $hmbUnserialized = @unserialize($hmbSerializedString);

        /* Extract post urls - get multitheme plugin aliases */
        $hmbPostUrls = $hmbUnserialized['url'];

        $hmbHostUrl = $hmbUnserialized['aliases'][0]['url'];
        $hmbHostUrlHost = $hmbUnserialized['aliases'][0]['prep']['host'];
        $hmbHostUrlPort = $hmbUnserialized['aliases'][0]['prep']['port'];

        $hmbWwwUrlHost = $hmbUnserialized['aliases'][1]['prep']['host'];
        $hmbWwwUrlPort = $hmbUnserialized['aliases'][1]['prep']['port'];


        /* Iterate trough mutlitheme post urls array */
        $getAllSlugs = array();
        foreach ($hmbPostUrls as $rel_url) {
            $getAllSlugs[] = $rel_url['rel_url'];
        }

        /* Check if post exists in all posts array if not create arr with post properties and push it to main array */
        if (in_array($postName, $getAllSlugs)) {
        } else {
            /* Check if post name is not empty */
            if (!empty($postName)) {
                $createArr = array("url" => $hmbHostUrl . "/" . $postName . "/", "rel_url" => $postName, "theme" => $themeName, "prep" => array(array("host" => $hmbHostUrlHost, "path" => "/" . $postName, "port" => $hmbHostUrlPort, "query" => array()), array("host" => $hmbWwwUrlHost, "path" => "/" . $postName, "port" => $hmbWwwUrlPort, "query" => array())), "id" => $post_ID, "id_kw" => "p");
                array_push($hmbUnserialized['url'], $createArr);
            }
        }
        /* Serialize the data and update DB */
        $hmbSerialized = serialize($hmbUnserialized);
        $wpdb->update($wpdb->options, array('option_value' => $hmbSerialized), array('option_id' => $databaseID));
    }
}

/* Fire on secondary theme submit */
if (isset($_POST['submit'])) {

    /* Get theme name from selection and convert to lowercase unless CR or MB */
    $themeName = get_option('hmb_option');

    /* Get multitheme serialized array */
    global $wpdb;
    $results = $wpdb->get_results(
        "
         SELECT option_id, option_value
         FROM $wpdb->options
         WHERE option_name = 'jr_mt_settings'
         "
    );
    /* Database info */
    $databaseID = $results[0]->option_id;
    $hmbSerializedString = $results[0]->option_value;
    /* Unserialize data */
    $hmbUnserialized = @unserialize($hmbSerializedString);

    /* Extract post urls - get multitheme plugin aliases */
    $hmbPostUrls = $hmbUnserialized['url'];

    $hmbHostUrl = $hmbUnserialized['aliases'][0]['url'];
    $hmbHostUrlHost = $hmbUnserialized['aliases'][0]['prep']['host'];
    $hmbHostUrlPort = $hmbUnserialized['aliases'][0]['prep']['port'];

    $hmbWwwUrlHost = $hmbUnserialized['aliases'][1]['prep']['host'];
    $hmbWwwUrlPort = $hmbUnserialized['aliases'][1]['prep']['port'];


    /* Iterate trough mutlitheme post urls array */
    $getAllSlugs = array();
    foreach ($hmbPostUrls as $rel_url) {
        $getAllSlugs[] = $rel_url['rel_url'];
    }

    /* Get all posts with post_type="post" */
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish'
    );

    $get_posts = get_posts($args);

    /* Iterate trough posts from query */
    foreach ($get_posts as $post) {
        /* Post data*/
        $postName = $post->post_name;
        $post_ID = $post->ID;
        /* Check if post exists in all posts array, if exsists iterate trough array, find matching post name & post key then replace current theme value with selec theme value. If not create arr with post properties and push it to main array */
        if (in_array($postName, $getAllSlugs)) {
            foreach ($hmbPostUrls as $key => $post) {
                foreach ($post as $p => $k) {
                    if ($p == 'rel_url' && $k == $postName) {
                        $hmbUnserialized['url'][$key]['theme'] = $themeName;
                    }
                }
            }
        } else {
            if (!empty($postName)) {
                $createArr = array("url" => $hmbHostUrl . "/" . $postName . "/", "rel_url" => $postName, "theme" => $themeName, "prep" => array(array("host" => $hmbHostUrlHost, "path" => "/" . $postName, "port" => $hmbHostUrlPort, "query" => array()), array("host" => $hmbWwwUrlHost, "path" => "/" . $postName, "port" => $hmbWwwUrlPort, "query" => array())), "id" => $post_ID, "id_kw" => "p");
                array_push($hmbUnserialized['url'], $createArr);
            }
        }
    }

    /* Serialize the data and update DB */
    $hmbSerialized = serialize($hmbUnserialized);
    $wpdb->update($wpdb->options, array('option_value' => $hmbSerialized), array('option_id' => $databaseID));
}

// add_action('delete_post', 'hmb_on_delete_action', 10, 3);
// function hmb_on_delete_action($post)
// {
//     echo 'DAvidddd';
//     var_dump($post);
// }
?>