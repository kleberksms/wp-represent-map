<?php
/**
 * Options page
 * 
 * @since 1.0.0
 */


/**
 * Management for options
 * 
 * @since 1.0.0
 */
function manage_options_for_wp_represent_map()
{
    $errors = '';
    $upload = new Upload();
        
    $wp_upload_dir = wp_upload_dir();
    $upload->setBasePath( $wp_upload_dir['basedir'] . '/map-icons' );
    $upload->appendAllowedType('image/png');
    
    
    if (isset($_POST)) {
        if (isset($_POST['_wp_represent_map_default_city'])) {

            $option_data = array(
                '_wp_represent_map_default_city' => $_POST['_wp_represent_map_default_city'],
                '_wp_represent_map_default_lat_lng' => $_POST['_wp_represent_map_default_lat_lng'],
            );

            if (update_option('wp-represent-map', $option_data)) {
                echo '<br /><div class="update-nag">' . __('Options saved with success', 'wp-represent-map') . '</div>';
            } else {
                echo '<br /><div class="update-nag">' . __('No changes made', 'wp-represent-map') . '</div>';
            }
        }
        
        
        if ( isset($_FILES) && !empty($_FILES) ) {
            $filename = filter_input(INPUT_POST, 'map_type', FILTER_SANITIZE_STRING);
            $_FILES['pin']['name'] = $filename;
            
            $upload->prepareUpload( $_FILES['pin'] )->flush();
            $errors = $upload->getErrors();
            
            if ( empty($errors) ) {
                echo '<script>'
                        . 'alert("'.__('Pin uploaded with success', 'wp-represent-map').'");'
                        . 'window.location.href="'. admin_url() .'options-general.php?page=wp-represent-map/wp-represent-map.php&tab=markers"'
                        . '</script>';
            } 
            
        }
    }
    
    if ( isset($_GET['delete']) && !empty($_GET['delete']) ) {
        $delete = base64_decode($_GET['delete']);
        $upload->removeFile( $delete . '.png' );
        
        $removeErrors = $upload->getErrors();
        if ( empty($errors) ) {
            $errors = $removeErrors;
        } else {
            array_push($errors, $removeErrors);
        }
        
        if( empty($removeErrors) ) {
            echo '<script>'
                        . 'alert("'.__('Pin removed with success', 'wp-represent-map').'");'
                        . 'window.location.href="'. admin_url() .'options-general.php?page=wp-represent-map/wp-represent-map.php&tab=markers"'
                        . '</script>';
        }
    }

    if( !empty($errors) ) {
        $errors = implode('<br />', $errors);
    }
    
    $options_values = get_option('wp-represent-map');
    ?>

<style>
    #markers{
        display: none;
    }
    
    table.options th, table.options td{
        border: #ccc solid 1px;
        text-align: center;
    }
    table.options th{
        height: 30px;
        background-color: #eee;
    }
    .options {
        width: 70% !important;
    } 
</style>

    <div class="wrap">
        <div id="icon-options-general" class="icon32"><br></div>
        <h2><?php echo __('Wp Represent Map Settings', 'wp-represent-map'); ?></h2>

        <?php if ( !empty($errors) ) : ?>
            <br />
            <div class="update-nag">
                <?php echo $errors; ?>
            </div>
        <?php endif; ?>
            
        <div class="page-content">
            <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
		<a href="#" id="positioning-click" class="nav-tab nav-tab-active">
                    <?php echo __('Default coordenates', 'wp-represent-map'); ?>
                </a>
                <a href="#" id="markers-click" class="nav-tab ">
                    <?php echo __('Markers', 'wp-represent-map'); ?>
                </a>
            </h2>
        
            <div id="positioning">
                <form name="form" action="" method="post">
                    <p><?php echo __('Change your location and another stuffs', 'wp-represent-map'); ?></p>

                    <h3><?php echo __('Settings', 'wp-represent-map'); ?></h3>
                    <table class="form-table permalink-structure" style="width: 40%; float: left;">
                        <tbody>
                            <tr>
                                <th>
                                    <label>
                                        <?php echo __('Default City', 'wp-represent-map'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" name="_wp_represent_map_default_city" value="<?php echo @$options_values['_wp_represent_map_default_city']; ?>">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label>
                                        <?php echo __('Default Lat Lng', 'wp-represent-map'); ?>
                                        &nbsp;
                                        <a href="#" onclick="return false" title="<?php echo __('Lat and Long is need to determine the center of the map on default screen', 'wp-represent-map'); ?>">
                                            <strong>?</strong>
                                        </a>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" name="_wp_represent_map_default_lat_lng" value="<?php echo @$options_values['_wp_represent_map_default_lat_lng']; ?>">&nbsp;
                                    <a href="#" title="<?php echo __('How I discover Lat Lng?', 'wp-represent-map'); ?>" id="ShowTipLatLng">
                                        <img src="../wp-content/plugins/wp-represent-map/assets/img/info.png">
                                    </a>
                                </td>
                            </tr>
                            <tr id="TipLatLng" style="display:none;">
                                <td colspan="2">
                                    <div class="update-nag" style="border-radius: 5px; padding:10px;">
                                        <?php echo __('Go at http://maps.google.com.br and follow these steps <br />1: type your location, browse to center map where you want<br />2: at the options click in a chain icon, browse in the link has open at his side, <br />copy the values like the step 3', 'wp-represent-map'); ?>
                                        <br />
                                        <img src="../wp-content/plugins/wp-represent-map/assets/img/map-lat-lng.png">
                                    </div>
                                </td>
                            </tr>

                        </tbody>
                    </table>

                    <br clear="all">
                    <p class="submit">
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save Changes', 'wp-represent-map'); ?>">
                    </p>  
                </form>
                </div>
                
            <div id="markers">
                
                <?php 
                    $icons = array();
                    $base_uri = get_bloginfo('url');
                    
                    $path = opendir('../wp-content/uploads/map-icons');
                    while( $file = readdir( $path ) ) {
                        if ( '.' != $file && '..' != $file ) {
                            $icons[$file] = $file;
                        } 
                    }
                ?>
                
                <form action="" name="markers" method="post" enctype="multipart/form-data">
                    <h3><?php echo __('Create or update a pin', 'wp-represent-map'); ?></h3>
                    
                    <?php echo __('Link to: ', 'wp-represent-map'); ?>&nbsp;
                    <select name="map_type">
                        <option value="default.png"><?php echo __('Default', 'wp-represent-map'); ?></option>
                        <?php 
                            if ( $terms = get_terms('represent_map_type') ) : ?>
                                <?php foreach( $terms as $t ) : ?>
                                    <option value="<?php echo $t->slug; ?>.png"><?php echo $t->name; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                    </select>
                    <input type="file" name="pin" >&nbsp;
                    <input type="submit" class="submit button-primary" value="<?php echo __('Save Changes', 'wp-represent-map'); ?>">
                </form>
                <h4><?php echo __('Info:', 'wp-represent-map'); ?></h4>
                <?php echo __('Image type: ', 'wp-represent-map'); ?><b>PNG</b><br />
                <?php echo __('Max width: ', 'wp-represent-map'); ?><b>31px</b><br />
                <?php echo __('Max height: ', 'wp-represent-map'); ?><b>42px</b><br />
                <hr>
                
                <h3>
                    <?php echo __('Current markers', 'wp-represent-map'); ?>
                </h3>
                
                <table class="options" cellspacing="0">
                    <thead>
                        <tr>
                            <th>
                                <?php echo __('Name', 'wp-represent-map'); ?>
                            </th>
                            <th>
                                <?php echo __('Icon', 'wp-represent-map'); ?>
                            </th>
                            <th>
                                <?php echo __('Actions', 'wp-represent-map'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo __('Default Marker', 'wp-represent-map'); ?></td>
                            <td>
                                <img src="<?php echo $base_uri; ?>/wp-content/uploads/map-icons/default.png" >
                            </td>
                            <td>---</td>
                        </tr>
                <?php 
                    if ( !empty($terms) ) : ?>
                        <?php foreach( $terms as $t ) : ?>
                            <tr>
                                <td><?php echo $t->name; ?></td>
                                <td>
                                    <?php if ( array_key_exists($t->slug . '.png', $icons) 
                                            && file_exists('../wp-content/uploads/map-icons/' . $icons[$t->slug . '.png'] ) ) : ?>
                                        <img src="<?php echo $base_uri; ?>/wp-content/uploads/map-icons/<?php echo $icons[$t->slug . '.png']; ?>" >
                                    <?php else : ?>
                                        <?php echo __('Not pin yet', 'wp-represent-map'); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a 
                                        href="<?php echo admin_url(); ?>/options-general.php?page=wp-represent-map/wp-represent-map.php&tab=markers&delete=<?php echo base64_encode($t->slug); ?>" class="delete">
                                            <?php echo __('Delete', 'wp-represent-map'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                </table>
            </div>
            
            </div>
    </div>

<script>
    jQuery(document).ready(function($) {
        $("#ShowTipLatLng").click(function() {
            $("#TipLatLng").toggle("slow");
            return false;
        });
        
        $("#positioning-click").bind("click", function(){
           $("#markers").hide();
           $("#markers-click").removeClass("nav-tab-active");
           $("#positioning-click").addClass("nav-tab-active");
           $("#positioning").show();
           return false;
        });
        $("#markers-click").bind("click", function(){
           $("#positioning").hide();
           $("#positioning-click").removeClass("nav-tab-active");
           $("#markers-click").addClass("nav-tab-active");
           $("#markers").show();
           return false;
        });
        $(".delete").bind("click", function(){
           return confirm("<?php echo __('Are you sure you want to delete the item icon?', 'wp-represent-map')?>");
        });
        
        
        $(".submit").bind("click", function(){
            return confirm("<?php echo __('This will override the current pin if exists. Do you wish continue?', 'wp-represent-map'); ?>");
        });
        
        <?php if ( isset($_GET['tab']) && 'markers' == $_GET['tab'] ) : ?>
            $("#positioning").hide();
            $("#positioning-click").removeClass("nav-tab-active");
            $("#markers-click").addClass("nav-tab-active");
            $("#markers").show();
        <?php endif; ?>
    });
</script>
    <?php
}
