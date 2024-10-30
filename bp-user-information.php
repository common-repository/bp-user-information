<?php
/*
Plugin Name: BP User Information
Description: Plugin to show user information in members page
Author: Beatriz Lima
Version: 1.0
*/
//add actions to call options page functions
add_action( 'admin_menu', 'bp_ui_add_admin_menu' );
add_action( 'admin_init', 'bp_ui_settings_init' );

//set options page
function bp_ui_add_admin_menu(  ) { 
	add_options_page( 'User Information', 'User Information', 'manage_options', 'user_information', 'bp_ui_options_page' );
}

//register settings and add fields
function bp_ui_settings_init(  ) { 
	register_setting( 'pluginPage', 'userinfo_settings' );

	add_settings_section(
		'userinfo_pluginPage_section', 
		__( '', 'wordpress' ), 
		'bp_ui_settings_section_callback', 
		'pluginPage'
	);
  
  global $wpdb;
  $table_name = $wpdb->prefix . 'bp_xprofile_fields';
  $fields = $wpdb->get_col( "SELECT name FROM $table_name" );
  $i=1;
  foreach ( $fields as $field )
  {
    $field_order = $wpdb->get_col( "SELECT field_order FROM $table_name WHERE name='$field'");
    
		//field to save which fields will appear in members-loop
    add_settings_field( 
      $field, 
      __( $field, 'wordpress' ),  
      'pluginPage', 
      'userinfo_pluginPage_section' 
    );

		//field to save which order the fields will appear
    add_settings_field( 
      $i, 
      __( $i, 'wordpress' ),
      'pluginPage', 
      'userinfo_pluginPage_section' 
    );
    $i++;
  }
}

//render options page with form to choose fields to show in members page
function bp_ui_render() { 
	$options = get_option( 'userinfo_settings' );
  global $wpdb;
  $table_name = $wpdb->prefix . 'bp_xprofile_fields';
  $fields = $wpdb->get_col( "SELECT name FROM $table_name" );
  $i=1;
  $size=bp_ui_size_of();
	?>
	<table class="form-table">
		<tr>
			<th>Fields</th>
			<th>Show/Hide</th>
			<th>Order</th>
		</tr>
	<?php
  foreach ( $fields as $field )
  {
    $types = $wpdb->get_col( "SELECT type FROM $table_name WHERE name='$field'");
    if($types[0] != "option"){
	?>
    <tr valign="top">
      <td scope="row"><?php echo $field; ?></td>
      <td>
        <input type='checkbox' name='userinfo_settings[<?php echo $field; ?>]' <?php checked( $options[$field], 1 ); ?> value='1'>
      </td>
      <td>
        <select name='userinfo_settings[<?php echo $i; ?>]'>
          <option></option>
          <?php for($x=1;$x<=$size;$x++){ ?>
                   <option value='<?php echo $x; ?>' <?php selected( $options[$i], $x ); ?>><?php echo $x; ?></option>
          <?php } ?>
        </select>
      </td>
    </tr>
  <?php
   }
   $i++;
  }
	?>
	</table>
	<?php
}

function bp_ui_settings_section_callback(  ) { 
	echo __( 'Select the fields you wanna show in members page', 'wordpress' );
}

//function to call options page functions
function bp_ui_options_page(  ) { 
	?>
	<form action='options.php' method='post'>
		<h2>User Information</h2>
		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
    bp_ui_render();
		submit_button();
		?>
	</form>
	<?php
}

//function to get the number os fields in xprofilefields, not including option types
function bp_ui_size_of(){
  global $bp; 
  global $wpdb;
  $table_name = $wpdb->prefix . 'bp_xprofile_fields';
  $fields = $wpdb->get_col( "SELECT name FROM $table_name" );
  $options = get_option( 'userinfo_settings' );
  foreach ( $fields as $field )
  {
   $types = $wpdb->get_col( "SELECT type FROM $table_name WHERE name='$field'");
   if ($types[0]!="option"){
    $size++;
   }
  }
  return $size;
}

//function called in members-loop to show the user fields selected in options page
function bp_ui_show_user_data()
{
  global $bp; 
  global $wpdb;
  $table_name = $wpdb->prefix . 'bp_xprofile_fields';
  $fields = $wpdb->get_col( "SELECT name FROM $table_name" );
  $options = get_option( 'userinfo_settings' );
  $size = bp_ui_size_of();
  $i=1;
  $y=1;
	
  for($total=1; $total<=$size; $total++){
    foreach ( $fields as $field ){
     if ($options[$field]==1 && $options[$i]==$y){
      $i=1;
      $y++;
      $field_name = xprofile_get_field_data( $field, bp_get_member_user_id() );
      $types = $wpdb->get_col( "SELECT type FROM $table_name WHERE name='$field'");
      if($types[0] == "checkbox" || $types[0] == "multiselectbox"){
        $field_name = implode(',', $field_name);
        echo "<span class='campo'><br>$field: $field_name</span>";
      }else{
       echo "<span class='campo'><br>$field: $field_name</span>";
      }
     break 1;
     }
     $i++;
    }
  }
}

//add action to be called in members-loop
add_action( 'bp_ui_show_user_data', 'bp_ui_show_user_data' );
?>