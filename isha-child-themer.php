<?php
/**
 * Plugin Name: Isha Child Themer
 * Author: Sharjeel Ahmad
 * Author URI: https://www.github.com/leo8545
 */

if(!defined("ABSPATH")) die();

define("ISHA_CT_DIR", plugin_dir_path(__FILE__));
define("ISHA_CT_URI", plugin_dir_url(__FILE__));
define("ISHA_CT_VERSION", "1.0.0");

final class IshaChildThemer
{

	private $currentTheme;

	private $childThemeDir;

	public function __construct()
	{
		$this->currentTheme = wp_get_theme();
		$this->childThemeDir = "";
		$this->defineAdminHooks();
		$this->definePublicHooks();
	}

	public function definePublicHooks()
	{
	
	}

	public function defineAdminHooks()
	{
		// create menu
		add_action("admin_menu", [$this, "adminMenu"]);

		// register settings
		add_action("admin_init", [$this, "adminRegisterSettings"]);
	}

	public function adminMenu()
	{
		add_menu_page(
			__("Isha Child Themer", "ishact"),
			__("Isha Child Themer", "ishact"),
			"manage_options",
			"isha_child_themer",
			[$this, "adminMenuCallback"]
		);
	}

	public function adminMenuCallback()
	{
		?>
		<div class="isha_ct_wrapper">
			<h1><?php _e("Isha Child Themer", "ishact") ?></h1>
			<form action="options.php" method="post">
				<?php 
					settings_fields("isha_child_themer");
					do_settings_sections("isha_child_themer");
					submit_button();
					$this->createChildThemeDirectory();
				?>
			</form>
		</div>
		<?php
	}

	public function adminRegisterSettings()
	{
		add_settings_section("ishact_section", "Create child theme", function() {}, "isha_child_themer");
		add_settings_field("isha_child_themer[parent_theme]", "Choose theme:", [$this, "adminFieldParentTheme"], "isha_child_themer", "ishact_section");
		add_settings_field("isha_child_themer[child_theme_name]", "Name of child theme:", [$this, "adminFieldThemeName"], "isha_child_themer", "ishact_section");
		register_setting("isha_child_themer", "isha_child_themer");
	}

	public function adminFieldParentTheme()
	{
		?>
		<select name="isha_child_themer[parent_theme]" id="ishact_parenttheme">
			<?php foreach($this->getThemeNames() as $template => $name): ?>
			<option value="<?php echo $template ?>" <?php selected(get_option("isha_child_themer")['parent_theme'], $template) ?>><?php echo $name ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}
	
	public function adminFieldThemeName()
	{
		?>
		<input type="text" name="isha_child_themer[child_theme_name]" value="<?php echo $this->getOption('child_theme_name') ?>">
		<?php
	}

	public function getThemeNames()
	{
		$themes[$this->currentTheme->get_template()] = $this->currentTheme->display("Name");
		foreach( wp_get_themes() as $theme ) {
			if($theme === $this->currentTheme) continue;
			$themes[$theme->get_template()] = $theme->display("Name");
		}
		return $themes;
	}

	public function getOption($optionName)
	{
		return (@get_option("isha_child_themer") ? get_option("isha_child_themer")[$optionName] : ""); 
	}

	public function createChildThemeDirectory()
	{
		$themeName = strtolower( preg_replace("/[^a-zA-Z]/", "", $this->getOption("child_theme_name")) );
		$template = $this->getOption("parent_theme");
		if(!is_dir( get_theme_root() . "/" . $themeName )) {
			$this->childThemeDir = get_theme_root() . "/" . $themeName;
			mkdir($this->childThemeDir);
			file_put_contents($this->childThemeDir . "/style.css", "/** Theme Name: " . $this->getOption('child_theme_name') . " \nTemplate: $template */");
			file_put_contents($this->childThemeDir . "/functions.php", "<?php add_action('wp_enqueue_scripts', function(){\nwp_enqueue_style('parent-theme', get_template_dir_uri() . '/style.css');\nwp_enqueue_style('child-theme', get_stylesheet_dir_uri() . '/style.css');})");
		}
	}
}

new IshaChildThemer;