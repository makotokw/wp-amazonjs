<?php
class Amazonjs_Wordpress_Plugin_Abstract
{
	var $title;
	var $url;
	var $basename;
	var $filename;
	var $dir;
	var $slug;
	var $use_option_page = false;
	var $option_name;
	var $option_url;
	var $setting_sections;
	var $setting_fileds;
	var $deafult_settings;
	var $settings;
	var $textdomain;
	
	function __construct($path) {
		$this->basename = plugin_basename($path);
		$filePaths = explode(DIRECTORY_SEPARATOR, $path);
		$this->filename = end($filePaths);
		$this->dir = dirname($path);
		$this->slug = basename($this->dir);
		$this->option_name = preg_replace('/[\-\.]/','_',$this->slug).'_settings';
		$this->url = plugins_url('', $path);
		$this->textdomain = str_replace('.php', '', $this->filename);
		load_plugin_textdomain($this->textdomain, false, $this->slug.'/languages');
	}
	
	function wp_url() {
		return (function_exists('site_url')) ? site_url() : get_bloginfo('wpurl');
	}

	function init() {
		global $wp_version;
		$this->init_settings();
		if ($this->use_option_page) {
			$this->option_url = $this->wp_url().'/wp-admin/options-general.php?page='.$this->basename;
			if (is_admin()) {
				add_action('admin_menu', array($this, 'admin_menu'));
				add_action('admin_init', array($this, 'admin_init'));
				if (version_compare($wp_version, '2.7alpha', '<' )) {
					add_action('plugin_action_links', array($this, 'plugin_action_links'), 10, 2);
				} else {
					if (version_compare( $wp_version, '2.8alpha', '>' )) {
						add_action('plugin_row_meta', array($this, 'plugin_row_meta'), 10, 2);
					}
				}
			}
		}
	}
	
	function admin_menu() {
		$this->add_options_page($this->title,$this->title);
	}
	
	function admin_init() {
		$page = $this->slug;
		register_setting($this->option_name, $this->option_name, array($this,'validate_settings'));
		if ($this->setting_sections) {
			foreach ($this->setting_sections as $key => $section) {
				add_settings_section($page.'_'.$key, $section['label'], array($this, $section['add']), $page);
			}
		}		
		foreach ($this->setting_fileds as $key => $field) {
			$label = ($field['type']=='checkbox') ? '' : $field['label'];
			add_settings_field(
				$this->option_name.'_'.$key, 
				$label,
				array($this,'add_settings_field_'.$key),
				$page,
				$page.'_'.$field['section']
				// , array($key, $field) // not work wordpress 2.9.0 #11143
				);
		}
	}
	
	function plugin_action_links($links, $file){
		if ($file == $this->basename) {
			$link = sprintf( '<a href="options-general.php?page=%s">%s</a>', $this->basename, __('Settings') );
			$links = array_merge(array($link), $links); // before other links
		}
		return $links;
	}
	
	function plugin_row_meta($links, $file) {
		if ($file == $this->basename) {
			array_unshift(
				$links,
				sprintf( '<a href="options-general.php?page=%s">%s</a>', $this->basename, __('Settings') )
			);
		}
		return $links;
	}
	
	function add_options_page($page_title, $menu_title) {
		if (function_exists('add_options_page')) {
			add_options_page(
				__($this->title,$this->textdomain), 
				__($this->title,$this->textdomain),
				'manage_options', 
				$this->basename, 
				array($this,'options_page')
			);
		}
	}
	
	function init_settings() {
		$this->default_settings = array();
		if (is_array($this->setting_fileds)) {
			foreach ($this->setting_fileds as $key => $field) {
				$this->default_settings[$key] = @$field['defaults'];
			}
		}
		//delete_option($this->option_name);
		$this->settings = wp_parse_args((array)get_option($this->option_name), $this->default_settings);
	}
	
	function delete_settings() {
		delete_option($this->option_name);
	}
	
	function default_settings() {
		
	}
	
	function validate_settings($settings) {
		foreach ($this->setting_fileds as $key => $field) {
			if ($field['type']=='checkbox') {
				$settings[$key] = (@$settings[$key] == 'on');
			}
		}
		return $settings;
	}
	
	function add_no_section() {}
	
	function add_settings_field($key, $field) {
		$id = $this->option_name.'_'.$key;
		$name = $this->option_name."[{$key}]";
		$value = $this->settings[$key];
		if (isset($field['html'])) {
			echo $field['html'];
		} else {
			switch ($field['type']) {
				case 'checkbox':
					echo "<input id='{$id}' name='{$name}' type='checkbox' ".checked(true,$value,false)."/>";
					echo "<label for='{$id}'>".$field['label']."</label>";
					break;
				case 'radio':
					foreach ($field['options'] as $v => $content) {
						echo "<input name='{$name}' type='radio' ".checked($v,$value,false)." value='{$v}'>{$content}</input>";
					}
					break;
				case 'select':
					echo "<select id='{$id}' name='{$name}' value='{$value}'>";
					foreach ($field['options'] as $option => $name) {
						echo "<option value='{$option}' ".selected($option,$value,false).">{$name}</option>";
					}
					echo "</select>";
					break;
				case 'text':
				default:
					$size = @$field['size'];
					$placeholder = @$field['placeholder'];
					if ($size<=0) $size = 40;
					echo "<input id='{$id}' name='{$name}' size='{$size}' type='text' value='{$value}' ".self::placeholder($placeholder)."/>";
					break;
			}
		}
		if (@$field['description']) {
			echo '<p class="description">' . $field['description'] . '</p>';
		}
	}
	
	static function placeholder($placeholder) {
		if ($placeholder) return 'placeholder="'.$placeholder.'"';
		return '';
	}
	
	function options_page() {
		$page = $this->slug;
?>
<div class="wrap">
<?php screen_icon(); ?>
<h2><?php echo $this->title ?></h2>
<?php $this->options_page_header();?>
<form action="options.php" method="post">
<?php settings_fields($this->option_name); ?>
<?php do_settings_sections($page); ?>
<?php submit_button(); ?>
</form>
<?php $this->options_page_footer();?>
</div>
<?php
	}
	
	function options_page_header() {
	}
	function options_page_footer() {
	}
}