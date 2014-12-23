<?php 
/*
Plugin name: My social icons
Author: NAZ
Description: My Social icons will allow you to add social icons to your website.
Version: 1.0
*/

class SocialIcons{
	protected $icon_div_width;
	protected $icon_size;
	protected $icon_margin_left;
	protected $icon_margin_right;
	protected $icon_image;
	protected $icon_link;
	protected $table_name = "social_icons";
	
	public function __construct(){
		add_action('admin_menu', array($this,'social_menu_page')); // add main menu page
		add_action('admin_menu',array($this,'social_menu_subpage')); // add submenu page
		add_shortcode('SocialIcons',array($this,'shortcode'));     // add shortcode
		wp_register_style( 'social_plugin_css', plugin_dir_url( __FILE__ ).'css/style.css' );
		wp_enqueue_style('social_plugin_css');
	}
	
	public function social_menu_page(){
		add_menu_page( 'Social Icons', 'Social Links', 'manage_options', 'social-icons-admin', array($this,'social_icons'), '', 6 );
	}
	
	public function social_icons(){		
		global $wpdb;
		if(isset($_POST['submit-icon'])){
			$url = $_POST['icon-link'];
			$url = mysql_real_escape_string($url);
			
			$base_path = plugin_dir_path( __FILE__ );
			$tmp_name = $_FILES["icon-image"]["tmp_name"];
			$name = $_FILES["icon-image"]["name"];
			move_uploaded_file($tmp_name, plugin_dir_path( __FILE__ )."/images/$name");
			
			$sql  = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " ( ";
			$sql .= "id mediumint(9) NOT NULL AUTO_INCREMENT,";
			$sql .= "icon_image VARCHAR(200) NOT NULL,";
			$sql .= "icon_url VARCHAR(200) NOT NULL,";
			$sql .= "UNIQUE KEY id (id)";
			$sql .= ");";
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			
			$table_data = array( "icon_image"=>"$name", "icon_url" => "$url" );
			$data_format = array("%s","%s");
			//$wpdb->insert( $table, $data, $format );
			
			$wpdb->insert( $this->table_name, $table_data, $data_format );
		}
		?>
		<h1>Social Icons</h1>
		<form method="post" enctype="multipart/form-data">
		<table>
			<tr>
				<td>Icon Image :</td>
				<td><input type="file" name="icon-image" /></td>
			</tr>
			<tr>
				<td>Icon Link :</td>
				<td><input type="text" name="icon-link" /></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" name="submit-icon" value="Add"/></td>
			</tr>
		</table>
		</form>
		
		<?php 
		$qry = "select * from $this->table_name";
		$res = $wpdb->get_results($qry);
		?>
		<table border="1" cellpadding="5">
			<tr>	
				<th>Icon ID</th>
				<th>Icon Image</th>
				<th>Icon URL</th>
				<th>Delete Icon</th>
			</tr>
		<?php
		foreach($res as $result){
			/*echo "<pre>";
			print_r($result);
			echo "</pre>";*/
			?>
			<tr>	
				<td><?php echo $result->id; ?></td>
				<td><img src="<?php echo plugin_dir_url( __FILE__ )."images/".$result->icon_image; ?>" width="30" height="30"/></td>
				<td><?php echo $result->icon_url; ?></td>
				<td><?php echo $result->id; ?></td>
			</tr>
			<?php
			
		}
		?>
		</table>
		<?php
	}
	
	public function social_menu_subpage(){
		add_submenu_page( 'social-icons-admin', 'Settings', 'Icon Settings', 'manage_options', 'social-icons-settings', array($this,'social_icons_settings') ); 
	}
	
	public function social_icons_settings(){
		if(isset($_POST['submit-settings'])){
			$this->icon_div_width 		= $_POST["icon-div-width"];
			$this->icon_size 			= $_POST["icon-size"];
			$this->icon_margin_right 	= $_POST["icon-margin-right"];
			$this->icon_margin_left 	= $_POST["icon-margin-left"];
			
			update_option('icon_div_width', $this->icon_div_width );
			update_option('icon_size', $this->icon_size );
			update_option('icon_margin_right', $this->icon_margin_right );
			update_option('icon_margin_left', $this->icon_margin_left );
		}
		?>
		<h1>Social Icons Settings</h1>
		<form  method="post">
		<table>
		<tr>
			<td>Icon Div Width :</td>
			<td><input type="text" name="icon-div-width" value="<?php echo get_option('icon_div_width'); ?>" />px</td>
		</tr>
		<tr>
			<td>Icon Size :</td>
			<td><input type="text" name="icon-size" value="<?php echo get_option('icon_size'); ?>" />px</td>
		</tr>
		<tr>
			<td>Icon Margin Right</td>
			<td><input type="text" name="icon-margin-right" value="<?php echo get_option('icon_margin_right'); ?>" />px</td>
		</tr>
		<tr>
			<td>Icon Margin Left</td>
			<td><input type="text" name="icon-margin-left" value="<?php echo get_option('icon_margin_left'); ?>" />px</td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" name="submit-settings" /></td>
		</tr>
		</table>
		</form>
		<?php
	}
	
	public function shortcode(){
		global $wpdb;
		$div_width = get_option('icon_div_width');
		$icon_size = get_option('icon_size');
		$mar_right = get_option('icon_margin_right');
		$mar_left = get_option('icon_margin_left');
		//echo $this->table_name;
		$qry = "select * from $this->table_name";
		$res = $wpdb->get_results($qry);
		
		$output  = "<div id='naz-social-icons' style='min-width:".$div_width."px;'>";
		
		foreach($res as $result){ 
			$output .= "<div class='naz-social-icon-img' style='width:".$icon_size."px;height:".$icon_size."px;border:1px solid red;float:left;margin-right:".$mar_right."px;margin-left:".$mar_left."px;'>";
			$output .= "<a href='".$result->icon_url."' target='_blank'><img src='".plugin_dir_url( __FILE__ )."images/".$result->icon_image."' width='100%' height='100%' /></a>";
			$output .= "</div>";
		}
		
		$output .= "</div>";
		return $output;
	}
	
}

$social_icos = new SocialIcons();
?>