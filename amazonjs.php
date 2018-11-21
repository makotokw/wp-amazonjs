<?php
/*
 Plugin Name: AmazonJS
 Plugin URI: http://wordpress.org/extend/plugins/amazonjs/
 Description: Easy to use interface to add an amazon product to your post and display it by using jQuery template.
 Author: makoto_kw
 Version: 0.8
 Author URI: http://makotokw.com
 Requires at least: 3.3
 Tested up to: 4.9.8
 License: GPLv2
 Text Domain: amazonjs
 Domain Path: /languages/
 */
/*
 AmazonJS depends on
   jQuery tmpl
   PEAR Services_JSON: Michal Migurski <mike-json@teczno.com>
 */

// TODO: Fixed NoSilencedErrors.Discouraged
// @codingStandardsIgnoreStart Generic.PHP.NoSilencedErrors.Discouraged

require_once dirname( __FILE__ ) . '/lib/json.php';

class Amazonjs
{
	const VERSION        = '0.8';
	const AWS_VERSION    = '2013-08-01';
	const CACHE_LIFETIME = 86400;

	public $title;
	public $url;
	public $option_page_url;
	public $plugin_rel_file;
	public $option_page_name;
	public $option_name;
	public $setting_sections;
	public $setting_fields;
	public $default_settings;
	public $settings;
	public $text_domain;

	public $media_type = 'amazonjs';
	public $countries;
	public $search_indexes;
	public $display_items = array();
	public $simple_template;

	function __construct() {
		$path                   = __FILE__;
		$dir                    = dirname( $path );
		$slug                   = basename( $dir );
		$this->title            = 'AmazonJS';
		$this->plugin_rel_file  = basename( $dir ) . DIRECTORY_SEPARATOR . basename( $path );
		$this->option_page_name = basename( $dir );
		$this->option_name      = preg_replace( '/[\-\.]/', '_', $this->option_page_name ) . '_settings';
		$this->url              = plugins_url( '', $path );
		$this->option_page_url  = admin_url() . 'options-general.php?page=' . $this->option_page_name;
		$this->text_domain      = $slug;
		load_plugin_textdomain( $this->text_domain, false, dirname( $this->plugin_rel_file ) . '/languages' );

		$this->countries = array(
			'US' => array(
				'label'              => __( 'United States', $this->text_domain ),
				'domain'             => 'Amazon.com',
				'baseUri'            => 'https://webservices.amazon.com',
				'linkTemplate'       => '<iframe src="https://rcm.amazon.com/e/cm?t=${t}&o=1&p=8&l=as1&asins=${asins}&fc1=${fc1}&IS2=${IS2}&lt1=${lt1}&m=amazon&lc1=${lc1}&bc1=${bc1}&bg1=${bg1}&f=ifr" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>',
				'associateTagSuffix' => '-20',
			),
			'UK' => array(
				'label'              => __( 'United Kingdom', $this->text_domain ),
				'domain'             => 'Amazon.co.uk',
				'baseUri'            => 'https://webservices.amazon.co.uk',
				'linkTemplate'       => '<iframe src="https://rcm-uk.amazon.co.uk/e/cm?t=${t}&o=2&p=8&l=as1&asins=${asins}&fc1=${fc1}&IS2=${IS2}&lt1=${lt1}&m=amazon&lc1=${lc1}&bc1=${bc1}&bg1=${bg1}&f=ifr" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>',
				'associateTagSuffix' => '-21',
			),
			'DE' => array(
				'label'              => __( 'Deutschland', $this->text_domain ),
				'domain'             => 'Amazon.de',
				'baseUri'            => 'https://webservices.amazon.de',
				'linkTemplate'       => '<iframe src="https://rcm-de.amazon.de/e/cm?t=${t}&o=3&p=8&l=as1&asins=${asins}&fc1=${fc1}&IS2=${IS2}&lt1=${lt1}&m=amazon&lc1=${lc1}&bc1=${bc1}&bg1=${bg1}&f=ifr" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>',
				'associateTagSuffix' => '04-21',
			),
			'FR' => array(
				'label'              => __( 'France', $this->text_domain ),
				'domain'             => 'Amazon.fr',
				'baseUri'            => 'https://webservices.amazon.fr',
				'linkTemplate'       => '<iframe src="https://rcm-fr.amazon.fr/e/cm?t=${t}&o=8&p=8&l=as1&asins=${asins}&fc1=${fc1}&IS2=${IS2}&lt1=${lt1}&m=amazon&lc1=${lc1}&bc1=${bc1}&bg1=${bg1}&f=ifr" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>',
				'associateTagSuffix' => '09-21',
			),
			'JP' => array(
				'label'              => __( 'Japan', $this->text_domain ),
				'domain'             => 'Amazon.co.jp',
				'baseUri'            => 'https://webservices.amazon.co.jp',
				'linkTemplate'       => '<iframe src="https://rcm-jp.amazon.co.jp/e/cm?t=${t}&o=9&p=8&l=as1&asins=${asins}&fc1=${fc1}&IS2=${IS2}&lt1=${lt1}&m=amazon&lc1=${lc1}&bc1=${bc1}&bg1=${bg1}&f=ifr" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>',
				'associateTagSuffix' => '-22',
			),
			'CA' => array(
				'label'              => __( 'Canada', $this->text_domain ),
				'domain'             => 'Amazon.ca',
				'baseUri'            => 'https://webservices.amazon.ca',
				'linkTemplate'       => '<iframe src="https://rcm-ca.amazon.ca/e/cm?t=${t}&o=15&p=8&l=as1&asins=${asins}&fc1=${fc1}&IS2=${IS2}&lt1=${lt1}&m=amazon&lc1=${lc1}&bc1=${bc1}&bg1=${bg1}&f=ifr" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>',
				'associateTagSuffix' => '0c-20',
			),
			'CN' => array(
				'label'              => __( 'China', $this->text_domain ),
				'domain'             => 'Amazon.cn',
				'baseUri'            => 'https://webservices.amazon.cn',
				'linkTemplate'       => '<iframe src="https://rcm-cn.amazon.cn/e/cm?t=${t}&o=28&p=8&l=as1&asins=${asins}&fc1=${fc1}&IS2=${IS2}&lt1=${lt1}&m=amazon&lc1=${lc1}&bc1=${bc1}&bg1=${bg1}&f=ifr" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>',
				'associateTagSuffix' => '-23',
			),
			'IT' => array(
				'label'              => __( 'Italia', $this->text_domain ),
				'domain'             => 'Amazon.it',
				'baseUri'            => 'https://webservices.amazon.it',
				'linkTemplate'       => '<iframe src="https://rcm-it.amazon.it/e/cm?t=${t}&o=29&p=8&l=as1&asins=${asins}&fc1=${fc1}&IS2=${IS2}&lt1=${lt1}&m=amazon&lc1=${lc1}&bc1=${bc1}&bg1=${bg1}&f=ifr" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>',
				'associateTagSuffix' => '-21',
			),
			'ES' => array(
				'label'              => __( 'España', $this->text_domain ),
				'domain'             => 'Amazon.es',
				'baseUri'            => 'https://webservices.amazon.es',
				'linkTemplate'       => '<iframe src="https://rcm-es.amazon.es/e/cm?t=${t}&o=30&p=8&l=as1&asins=${asins}&fc1=${fc1}&IS2=${IS2}&lt1=${lt1}&m=amazon&lc1=${lc1}&bc1=${bc1}&bg1=${bg1}&f=ifr" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>',
				'associateTagSuffix' => '-21',
			),
		);
	}

	function init() {
		$this->init_settings();

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		}
		add_shortcode( 'amazonjs', array( $this, 'shortcode' ) );
		if ( ! is_admin() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
			add_action( 'wp_footer', array( $this, 'wp_enqueue_scripts_for_footer' ), 1 );
		}
	}

	function admin_init() {
		add_action( 'media_buttons', array( $this, 'media_buttons' ), 20 );
		add_action( 'media_upload_amazonjs', array( $this, 'media_upload_amazonjs' ) );
		add_action( 'media_upload_amazonjs_keyword', array( $this, 'media_upload_amazonjs_keyword' ) );
		add_action( 'media_upload_amazonjs_id', array( $this, 'media_upload_amazonjs_id' ) );
		add_action( 'wp_ajax_amazonjs_search', array( $this, 'ajax_amazonjs_search' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		$page = $this->option_page_name;
		register_setting( $this->option_name, $this->option_name, array( $this, 'validate_settings' ) );
		if ( $this->setting_sections ) {
			foreach ( $this->setting_sections as $key => $section ) {
				add_settings_section( $page . '_' . $key, $section['label'], array( $this, $section['add'] ), $page );
			}
		}
		foreach ( $this->setting_fields as $key => $field ) {
			$label = ('checkbox' == $field['type']) ? '' : $field['label'];
			add_settings_field(
				$this->option_name . '_' . $key,
				$label,
				array( $this, 'add_settings_field' ),
				$page,
				$page . '_' . $field['section'],
				array( $key, $field )
			);
		}
	}

	function admin_print_styles() {
		global $wp_version;
		// use dashicon
		if ( version_compare( $wp_version, '3.8', '>=' ) ) {
			wp_enqueue_style( 'amazonjs-options', $this->url . '/css/amazonjs-options.css', array(), self::VERSION );
		}
	}

	function wp_enqueue_styles() {
		if ( $this->settings['displayCustomerReview'] ) {
			wp_enqueue_style( 'thickbox' );
		}

		if ( $this->settings['overrideThemeCss'] ) {
			wp_enqueue_style( 'amazonjs', $this->url . '/css/amazonjs-force.css', array(), self::VERSION );
		} else {
			wp_enqueue_style( 'amazonjs', $this->url . '/css/amazonjs.css', array(), self::VERSION );
		}
		if ( $this->settings['customCss'] ) {
			wp_enqueue_style( 'amazonjs-custom', get_stylesheet_directory_uri() . '/amazonjs.css' );
		}
	}

	function wp_enqueue_scripts() {
		wp_register_script( 'jquery-tmpl', $this->url . '/components/js/jquery-tmpl/jquery.tmpl.min.js', array( 'jquery' ), '1.0.0pre', true );

		$depends = array( 'jquery-tmpl' );
		if ( $this->settings['displayCustomerReview'] ) {
			$depends[] = 'thickbox';
		}
		wp_register_script( 'amazonjs', $this->url . '/js/amazonjs.js', $depends, self::VERSION, true );
		if ( $this->settings['customJs'] ) {
			wp_register_script( 'amazonjs-custom', get_stylesheet_directory_uri() . '/amazonjs.js', array( 'amazonjs' ), self::VERSION, true );
		}
	}

	function wp_enqueue_scripts_for_footer() {
		$country_codes = array();
		$items         = array();
		foreach ( $this->display_items as $country_code => $sub_items ) {
			foreach ( $this->fetch_items( $country_code, $sub_items ) as $asin => $item ) {
				$items[ $country_code . ':' . $asin ] = $item;
			}
			$country_codes[] = $country_code;
		}

		if ( count( $items ) == 0 ) {
			return;
		}

		$this->enqueue_amazonjs_scripts( $items, $country_codes );
	}

	function enqueue_amazonjs_scripts( $items = array(), $country_codes = array() ) {
		$wpurl = get_bloginfo( 'wpurl' );

		$region = array();
		foreach ( $this->countries as $code => $value ) {
			if ( in_array( $code, $country_codes ) ) {
				foreach ( array( 'linkTemplate' ) as $attr ) {
					$region[ 'Link' . $code ] = $this->tmpl( $value[ $attr ], array( 't' => $this->settings[ 'associateTag' . $code ] ) );
				}
			}
		}

		$amazonVars = array(
			'thickboxUrl'             => $wpurl . '/wp-includes/js/thickbox/',
			'regionTemplate'          => $region,
			'resource'                => array(
				'BookAuthor'          => __( 'Author', $this->text_domain ),
				'BookPublicationDate' => __( 'PublicationDate', $this->text_domain ),
				'BookPublisher'       => __( 'Publisher', $this->text_domain ),
				'NumberOfPagesValue'  => __( '${NumberOfPages} pages', $this->text_domain ),
				'ListPrice'           => __( 'List Price', $this->text_domain ),
				'Price'               => __( 'Price', $this->text_domain ),
				'PriceUsage'          => __( 'Product prices and availability are accurate as of the date/time indicated and are subject to change. Any price and availability information displayed on [amazon.com or endless.com, as applicable] at the time of purchase will apply to the purchase of this product.', $this->text_domain ),
				'PublicationDate'     => __( 'Publication Date', $this->text_domain ),
				'ReleaseDate'         => __( 'Release Date', $this->text_domain ),
				'SalesRank'           => __( 'SalesRank', $this->text_domain ),
				'SalesRankValue'      => __( '#${SalesRank}', $this->text_domain ),
				'RunningTime'         => __( 'Run Time', $this->text_domain ),
				'RunningTimeValue'    => __( '${RunningTime} minutes', $this->text_domain ),
				'CustomerReviewTitle' => __( '${Title} Customer Review', $this->text_domain ),
				'SeeCustomerReviews'  => __( 'See Customer Reviews', $this->text_domain ),
				'PriceUpdatedat'      => __( '(at ${UpdatedDate})', $this->text_domain ),
			),
			'isCustomerReviewEnabled' => ($this->settings['displayCustomerReview']) ? true : false,
			'isTrackEventEnabled'     => ($this->settings['useTrackEvent']) ? true : false,
			'isFadeInEnabled'         => ($this->settings['useAnimation']) ? true : false,
			'items'                   => array_values( $items ),

		);
		wp_localize_script( 'amazonjs', 'amazonjsVars', $amazonVars );

		wp_enqueue_script( 'amazonjs' );
		if ( $this->settings['customJs'] ) {
			wp_enqueue_script( 'amazonjs-custom' );
		}
	}

	function init_settings() {
		// section
		$this->setting_sections = array(
			'api'        => array(
				'label' => __( 'Product Advertising API settings', $this->text_domain ),
				'add'   => 'add_api_setting_section',
			),
			'associate'  => array(
				'label' => __( 'Amazon Associates settings', $this->text_domain ),
				'add'   => 'add_associate_setting_section',
			),
			'appearance' => array(
				'label' => __( 'Appearance settings', $this->text_domain ),
				'add'   => 'add_appearance_setting_section',
			),
			'analytics'  => array(
				'label' => __( 'Analytics settings', $this->text_domain ),
				'add'   => 'add_analytics_setting_section',
			),
			'customize'  => array(
				'label' => __( 'Customize', $this->text_domain ),
				'add'   => 'add_customize_setting_section',
			),
		);
		// filed
		$template_url         = get_bloginfo( 'template_url' );
		$this->setting_fields = array(
			'accessKeyId'               => array(
				'label'   => __( 'Access Key ID', $this->text_domain ),
				'type'    => 'text',
				'size'    => 60,
				'section' => 'api',
			),
			'secretAccessKey'           => array(
				'label'   => __( 'Secret Access Key', $this->text_domain ),
				'type'    => 'text',
				'size'    => 60,
				'section' => 'api',
			),
			'displayCustomerReview'     => array(
				'label'       => __( 'Display customer review', $this->text_domain ),
				'type'        => 'checkbox',
				'section'     => 'appearance',
				'description' => __( "AmazonJS will display customer review by using WordPress's Thickbox.", $this->text_domain ),
			),
			'useAnimation'              => array(
				'label'   => __( 'Use fadeIn animation', $this->text_domain ),
				'type'    => 'checkbox',
				'section' => 'appearance',
			),
			'overrideThemeCss'          => array(
				'label'       => __( 'Override style of theme', $this->text_domain ),
				'type'        => 'checkbox',
				'section'     => 'appearance',
				'description' => __( 'If set to true, AmazonJS will override the style of the theme by using <code>!important</code> declaration.', $this->text_domain ),
			),
			'useShortItemUrl'     => array(
				'label'       => __( 'Use short item url', $this->text_domain ),
				'type'        => 'checkbox',
				'section'     => 'appearance',
				'description' => __( "AmazonJS will remove a product title from an item url.", $this->text_domain ),
			),
			'supportDisabledJavascript' => array(
				'label'       => __( 'Uf set to true, display an official widget instead javascript code', $this->text_domain ),
				'type'        => 'checkbox',
				'section'     => 'appearance',
				'description' => __( 'If set to true, AmazonJS will output html by using <code>&lt;script type=&quot;text/javascript&quot;&gt;document.write(&quot;{$indicator_html}&quot;)&lt;/script&gt;&lt;noscript&gt;{$link_html}&lt;/noscript&gt;</code>.', $this->text_domain ),
			),
			'useTrackEvent'             => array(
				'label'       => __( 'Click Tracking by using Google Analytics', $this->text_domain ),
				'type'        => 'checkbox',
				'section'     => 'analytics',
				'description' => __( 'If set to true, AmazonJS will call <code>_gaq.push(["_trackEvent", "AmazonJS", "Click", "ASIN TITLE"])</code> or <code>ga("send", "event", "AmazonJS", "Click", "ASIN TITLE")</code>.', $this->text_domain ),
			),
			'customCss'                 => array(
				'label'       => __( 'Use Custom Css', $this->text_domain ),
				'type'        => 'checkbox',
				'section'     => 'customize',
				'description' => '(' . $template_url . '/amazonjs.css)',
			),
			'customJs'                  => array(
				'label'       => __( 'Use Custom Javascript', $this->text_domain ),
				'type'        => 'checkbox',
				'section'     => 'customize',
				'description' => '(' . $template_url . '/amazonjs.js)',
			),
		);
		foreach ( $this->countries as $key => $value ) {
			$this->setting_fields[ 'associateTag' . $key ] = array(
				'label'       => __( $value['domain'], $this->text_domain ),
				'type'        => 'text',
				'size'        => 30,
				'placeholder' => 'associatetag' . $value['associateTagSuffix'],
				'section'     => 'associate',
			);
		}

		$this->default_settings = array();
		if ( is_array( $this->setting_fields ) ) {
			foreach ( $this->setting_fields as $key => $field ) {
				$this->default_settings[ $key ] = @$field['defaults'];
			}
		}
		//delete_option($this->option_name);
		$this->settings = wp_parse_args( (array) get_option( $this->option_name ), $this->default_settings );
	}

	function delete_settings() {
		delete_option( $this->option_name );
	}

	function validate_settings( $settings ) {

		foreach ( $this->setting_fields as $key => $field ) {
			if ( 'checkbox' == $field['type'] ) {
				$settings[ $key ] = ( 'on' == @$settings[ $key ] || '1' == @$settings[ $key ] );
			}
		}

		foreach ( array( 'accessKeyId', 'secretAccessKey' ) as $key ) {
			$settings[ $key ] = trim( $settings[ $key ] );
		}

		foreach ( $this->countries as $country_code => $value ) {
			$key            = 'associateTag' . $country_code;
			$settings[ $key ] = trim( $settings[ $key ] );
		}

		return $settings;
	}

	function admin_menu() {
		if ( function_exists( 'add_options_page' ) ) {
			$page_hook_suffix = add_options_page(
				__( $this->title, $this->text_domain ),
				__( $this->title, $this->text_domain ),
				'manage_options',
				$this->option_page_name,
				array( $this, 'options_page' )
			);
			add_action( 'admin_print_styles-' . $page_hook_suffix, array( $this, 'admin_print_styles' ) );
		}
	}

	function get_amazon_official_link( $asin, $country_code ) {
		$tmpl = $this->countries[ $country_code ]['linkTemplate'];
		$item = array(
			't'     => $this->settings[ 'associateTag' . $country_code ],
			'asins' => $asin,
			'fc1'   => '000000',
			'lc1'   => '0000FF',
			'bc1'   => '000000',
			'bg1'   => 'FFFFFF',
			'IS2'   => 1,
			'lt1'   => '_blank',
			'f'     => 'ifr',
			'm'     => 'amazon',
		);
		return $this->tmpl( $tmpl, $item );
	}

	function shortcode( $atts, /** @noinspection PhpUnusedParameterInspection */ $content ) {
		/**
		 * @var string $asin
		 * @var string $tmpl
		 * @var string $locale
		 * @var string $title
		 * @var string $imgsize
		 */
		$defaults = array( 'asin' => '', 'tmpl' => '', 'locale' => $this->default_country_code(), 'title' => '', 'imgsize' => '' );
		extract( shortcode_atts( $defaults, $atts ) );
		if ( empty($asin) ) {
			return '';
		}
		$country_code  = strtoupper( $locale );
		$imgsize = strtolower( $imgsize );
		if ( is_feed() ) {
			// use static html for rss reader
			if ( $ai = $this->get_item( $country_code, $asin ) ) {
				$aimg = $ai['SmallImage'];
				if ( array_key_exists( 'MediumImage', $ai ) ) {
					$aimg = $ai['MediumImage'];
				}
				return <<<EOF
<a href="{$ai['DetailPageURL']}" title="{$ai['Title']}" target="_blank">
<img src="{$aimg['src']}" width="{$aimg['width']}" height="{$aimg['height']}" alt="{$ai['Title']}"/>
{$ai['Title']}
</a>
EOF;
			}
			return $this->get_amazon_official_link( $asin, $country_code );
		}
		if ( ! isset($this->display_items[ $country_code ]) ) {
			$this->display_items[ $country_code ] = array();
		}
		$item = (array_key_exists( $asin, $this->display_items[ $country_code ] ))
			? $this->display_items[ $country_code ][ $asin ]
			: $this->display_items[ $country_code ][ $asin ] =  $this->get_cached_item( $country_code, $asin );
		$url  = '#';
		if ( is_array( $item ) && array_key_exists( 'DetailPageURL', $item ) ) {
			$url = $item['DetailPageURL'];
		}
		$indicator_html = <<<EOF
<div data-role="amazonjs" data-asin="{$asin}" data-locale="{$country_code}" data-tmpl="${tmpl}" data-img-size="${imgsize}" class="asin_{$asin}_{$country_code}_${tmpl} amazonjs_item"><div class="amazonjs_indicator"><span class="amazonjs_indicator_img"></span><a class="amazonjs_indicator_title" href="{$url}">{$title}</a><span class="amazonjs_indicator_footer"></span></div></div>
EOF;

		$indicator_html = trim( $indicator_html );
		if ( ! $this->settings['supportDisabledJavascript'] ) {
			return $indicator_html;
		}
		$indicator_html = addslashes( $indicator_html );
		$link_html      = $this->get_amazon_official_link( $asin, $country_code );

		return <<<EOF
<script type="text/javascript">document.write("{$indicator_html}")</script><noscript>{$link_html}</noscript>
EOF;
	}

	/**
	 * Gets default country code by get_locale
	 * @return string
	 */
	function default_country_code() {
		switch ( get_locale() ) {
			case 'en_CA':
				return 'CA';
			case 'de_DE':
				return 'DE';
			case 'fr_FR':
				return 'FR';
			case 'ja':
				return 'JP';
			case 'en_GB':
				return 'UK';
			case 'zh_CN':
				return 'CN';
			case 'it_IT':
				return 'IT';
			case 'es_ES':
				return 'ES';
		}
		return 'US';
	}

	function delete_cache() {
		global $wpdb;

		if ( !empty( $wpdb ) && $wpdb instanceof wpdb ) {
			$flag = $wpdb->suppress_errors;
			$wpdb->suppress_errors( true );
			$wpdb->query("DELETE FROM $wpdb->options o WHERE o.option_name LIKE '\_site\_transient\_amazonjs\_%'");
			$wpdb->suppress_errors( $flag );
			return true;
		}
		return false;
	}

	function get_cached_item( $country_code, $asin ) {
		if ( $cached_item = get_site_transient( "amazonjs_{$country_code}_{$asin}" ) ) {
			$this->fix_item( $cached_item );
			return $cached_item;
		}
		return null;
	}

	function get_item( $country_code, $asin ) {
		if ( $cached_item = $this->get_cached_item( $country_code, $asin ) ) {
			return $cached_item;
		}
		$items = $this->fetch_items( $country_code, array( $asin => false ) );
		return @$items[ $asin ];
	}

	/**
	 * @param string $country_code
	 * @param array $items
	 * @return array
	 */
	function fetch_items( $country_code, $items ) {
		$now     = time();
		$item_ids = array();
		foreach ( $items as $asin => $item ) {
			if ( ! $item && $item['UpdatedAt'] + 86400 < $now ) {
				$item_ids[] = $asin;
			}
		}
		while ( count( $item_ids ) ) {
			// fetch via 10 products
			// ItemLookup ItemId: Must be a valid item ID. For more than one ID, use a comma-separated list of up to ten IDs.
			$itemid  = implode( ',', array_splice( $item_ids, 0, 10 ) );
			$results = $this->itemlookup( $country_code, $itemid );
			if ( $results && $results['success'] ) {
				foreach ( $results['items'] as $item ) {
					$items[ $item['ASIN'] ] = $item;
					set_site_transient("amazonjs_{$country_code}_{$item['ASIN']}", $item, self::CACHE_LIFETIME);
				}
			}
		}
		return $items;
	}

	function tmpl( $tmpl, $item ) {
		$s = $tmpl;
		foreach ( $item as $key => $value ) {
			$s = str_replace( '${' . $key . '}', $value, $s );
		}
		return $s;
	}

	function plugin_row_meta( $links, $file ) {
		if ( $file == $this->plugin_rel_file ) {
			array_push(
				$links,
				sprintf( '<a href="%s">%s</a>', $this->option_page_url, __( 'Settings' ) )
			);
			array_push(
				$links,
				sprintf( '<a href="https://github.com/makotokw/wp-amazonjs/releases" target="_blank">%s</a>', __( 'Old Releases', $this->text_domain ) )
			);
		}
		return $links;
	}

	function add_api_setting_section() {
		?>
		<p><?php _e( 'This plugin uses the Amazon Product Advertising API in order to get product infomation. Thus, you must use your Access Key ID &amp; Secret Access Key.', $this->text_domain ); ?></p>
		<p><?php _e( 'You can sign up the Amazon Product Advertising API from <a href="https://affiliate-program.amazon.com/gp/advertising/api/detail/main.html" target="_blank">here</a>. Please review the <a href="http://affiliate-program.amazon.com/gp/advertising/api/detail/agreement.html" target="_blank">Product Advertising API License Agreement</a> for details.', $this->text_domain ) ?></p>
	<?php
	}

	function add_associate_setting_section() {
		?>
		<p><?php _e( 'Amazon has an affiliate program called Amazon Associates. To apply for the Associates Program, visit the <a href="https://affiliate-program.amazon.com/" target="_blank">Amazon Associates website</a> for details.', $this->text_domain ); ?></p>
		<p><?php _e( 'Associate Tag has been a <strong>required and verified</strong> input parameter in all requests to the Amazon Product Advertising API since 11/1/2011.', $this->text_domain ) ?></p>
	<?php
	}

	function add_appearance_setting_section() {
	}

	function add_analytics_setting_section() {
	}

	function add_customize_setting_section() {
	}

	function add_settings_field( $args = array() ) {
		// not work wordpress 2.9.0 #11143
		if ( empty($args) ) {
			return;
		}
		list ($key, $field) = $args;
		$id    = $this->option_name . '_' . $key;
		$name  = $this->option_name . "[{$key}]";
		$value = $this->settings[ $key ];
		if ( isset($field['html']) ) {
			echo '' . $field['html'] . '';
		} else {
			switch ( $field['type'] ) {
				case 'checkbox':
					?>
					<input id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" type="checkbox" <?php checked( true, $value ); ?> value="1" />
					<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
					<?php
					break;
				case 'radio':
					$index = 1;
					foreach ( $field['options'] as $v => $content ) {
						$input_element_id = $id . '_' . $index;
						?>
						<input id="<?php echo esc_attr( $input_element_id ); ?>" name="<?php echo esc_attr( $name ); ?>" type="radio" <?php checked( $v, $value ); ?> value="<?php echo esc_attr( $v ); ?>" />
						<label for="<?php echo esc_attr( $input_element_id ); ?>"><?php echo esc_html( $content ); ?></label>
						<?php
						$index++;
					}
					break;
				case 'select':
					?>
					<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>">
					<?php foreach ( $field['options'] as $option => $name ) : ?>
						<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $option, $value ); ?>><?php echo esc_html( $name ); ?></option>
					<?php endforeach ?>
					</select>
					<?php
					break;
				case 'text':
				default:
					$size        = @$field['size'];
					$placeholder = @$field['placeholder'];
					if ( $size <= 0 ) {
						$size = 40;
					}
					if ( ! is_string( $placeholder ) ) {
						$placeholder = '';
					}
					?>
					<input id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" type="text" size="<?php echo esc_attr( $size ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>"/>
					<?php
					break;
			}
		}
		if ( @$field['description'] ) {
			echo '<p class="description">' . $field['description'] . '</p>';
		}
	}

	/**
	 * @return bool
	 */
	function is_page_amazonjs_options() {
		global $pagenow;
		return ( 'options-general.php' == $pagenow && isset($_GET['page']) && $this->option_page_name == $_GET['page'] );
	}

	function admin_notices() {
		// https://wordpress.org/support/topic/how-to-use-settings-api-and-print-custom-validation-errors?replies=3
		if ( $this->is_page_amazonjs_options() ) {

			if ( (isset($_GET['updated']) && 'true' == $_GET['updated']) || (isset($_GET['settings-updated']) && 'true' == $_GET['settings-updated']) ) {
				// Validate keys
				if ( !empty($this->settings['accessKeyId']) && !empty($this->settings['secretAccessKey']) ) {
					$results = $this->itemsearch( $this->default_country_code(), null, 'WordPress' );
					if ( is_array( $results ) && isset($results['error_code']) ) {
						switch ( $results['error_code'] ) {
							case 'InvalidClientTokenId':
								add_settings_error( 'general', 'settings_updated', __( 'The Access Key ID may be invalid', $this->text_domain )  . ' (' . $results['error_code'] . ')', 'error' );
								break;
							case 'SignatureDoesNotMatch':
								add_settings_error( 'general', 'settings_updated', __( 'The Secret Access Key may be invalid', $this->text_domain ). ' (' . $results['error_code'] . ')', 'error' );
								break;
						}
					}
				}
			}

			if ( isset($_POST['amazonjs_delete_cache']) ) {
				if ( $this->delete_cache() ) {
					add_settings_error( 'general', 'settings_updated', __( 'The Caches are deleted', $this->text_domain ), 'updated' );
				}
			}
		}
	}

	function media_buttons() {
		global $post_ID, $temp_ID;
		$iframe_ID  = (int) ( 0 == $post_ID ? $temp_ID : $post_ID );
		$iframe_src = 'media-upload.php?post_id=' . $iframe_ID . '&amp;type=' . $this->media_type . '&amp;tab=' . $this->media_type . '_keyword';
		$label      = __( 'Add Amazon Link', $this->text_domain );
		?>
		<a href="<?php echo esc_attr( $iframe_src . '&amp;TB_iframe=true' ); ?>" id="add_amazon" class="button thickbox" title="<?php echo esc_attr( $label ); ?>"><img src="<?php echo esc_attr( $this->url . '/images/amazon-icon.png' ); ?>" alt="<?php echo esc_attr( $label ); ?>"/></a>
	<?php
	}

	function media_upload_init() {
		add_action( 'admin_print_styles', array( $this, 'wp_enqueue_styles' ) );

		$this->wp_enqueue_scripts();
		wp_enqueue_style( 'amazonjs-media-upload', $this->url . '/css/media-upload-type-amazonjs.css', array( 'amazonjs' ), self::VERSION );

		$this->enqueue_amazonjs_scripts();
	}

	function media_upload_amazonjs() {
		$this->media_upload_init();
		wp_iframe( 'media_upload_type_amazonjs' );
	}

	function media_upload_amazonjs_keyword() {
		$this->media_upload_init();
		wp_iframe( 'media_upload_type_amazonjs_keyword' );
	}

	function media_upload_amazonjs_id() {
		$this->media_upload_init();
		wp_iframe( 'media_upload_type_amazonjs_id' );
	}

	function media_upload_tabs( /** @noinspection PhpUnusedParameterInspection */$tabs ) {
		return array(
			$this->media_type . '_keyword' => __( 'Keyword Search', $this->text_domain ),
			$this->media_type . '_id'      => __( 'Search by ASIN/URL', $this->text_domain ),
		);
	}

	function options_page() {
		?>
		<div class="wrap wrap-amazonjs">
			<h2><?php echo esc_html( $this->title ); ?></h2>
			<?php $this->options_page_header(); ?>
			<!--suppress HtmlUnknownTarget -->
			<form action="options.php" method="post">
				<?php settings_fields( $this->option_name ); ?>
				<?php do_settings_sections( $this->option_page_name ); ?>
				<?php submit_button(); ?>
			</form>

			<form action="<?php echo $_SERVER["REQUEST_URI"] ?>" method="post">
				<input type="hidden" name="action" value="amazonjs_delete_cache"/>
				<?php submit_button( __( 'Delete Cache', $this->text_domain ), 'secondary', 'amazonjs_delete_cache' ); ?>
			</form>
		</div>
	<?php
	}

	function options_page_header() {
		?>
		<?php if ( ! function_exists( 'simplexml_load_string' ) ) : ?>
			<div class="error">
				<p><?php printf( __( 'Error! "simplexml_load_string" function is not found. %s requires PHP 5 and SimpleXML extension.', $this->text_domain ), $this->title ); ?></p>
			</div>
		<?php endif ?>
	<?php
	}

	// amazon api
	function itemlookup( $countryCode, $itemId ) {
		$options              = array();
		$options['ItemId']    = $itemId;
		$options['Operation'] = 'ItemLookup';
		return $this->amazon_get( $countryCode, $options );
	}

	// amazon api
	function itemsearch( $countryCode, $searchIndex, $keywords, $itemPage = 0 ) {
		$options = array();
		if ( $itemPage > 0 ) {
			$options['ItemPage'] = $itemPage;
		}
		$options['Keywords']  = $keywords;
		$options['Operation'] = 'ItemSearch';
		if ( $searchIndex ) {
			$options['SearchIndex'] = $searchIndex;
		}
		return $this->amazon_get( $countryCode, $options );
	}

	/**
	 * parse ASIN from URL
	 * @param string $url
	 * @return bool|string
	 */
	static function parse_asin( $url ) {
		if ( preg_match( '/^https?:\/\/.+\.amazon\.([^\/]+).+\/(dp|gp\/product|ASIN)\/([^\/]+)/', $url, $matches ) ) {
			return $matches[3];
		}
		return null;
	}

	function ajax_amazonjs_search() {
		$itemId = null;

		// from http get
		$itemPage    = @$_GET['ItemPage'];
		$id          = @$_GET['ID'];
		$keywords    = @$_GET['Keywords'];
		$searchIndex = @$_GET['SearchIndex'];
		$countryCode = @$_GET['CountryCode'];

		if ( ! empty($id) ) {
			if ( preg_match( '/^https?:\/\//', $id ) ) {
				if ( $asin = self::parse_asin( $id ) ) {
					$itemId = $asin;
				} else {
					// url string as query keyword
					$keywords = $id;
				}
			} else {
				$itemId = $id;
			}
		} else if ( ! empty($keywords) ) {
			if ( preg_match( '/^https?:\/\//', $keywords ) ) {
				if ( $asin = self::parse_asin( $keywords ) ) {
					$itemId = $asin;
				}
			}
		}
		$amazonjs = new Amazonjs();
		$amazonjs->init();
		if ( isset($itemId) ) {
			$result = $amazonjs->itemlookup( $countryCode, $itemId );
			die(json_encode( $result ));
		} else {
			$result = $amazonjs->itemsearch( $countryCode, $searchIndex, $keywords, $itemPage );
			die(json_encode( $result ));
		}
	}

	function amazon_get( $countryCode, $options ) {
		$baseUri         = $this->countries[ $countryCode ]['baseUri'];
		$accessKeyId     = @trim( $this->settings['accessKeyId'] );
		$secretAccessKey = @trim( $this->settings['secretAccessKey'] );
		$associateTag    = @$this->settings[ 'associateTag' . $countryCode ];

		// validate request
		if ( empty($countryCode) || (empty($options['ItemId']) && empty($options['Keywords'])) || (empty($accessKeyId) || empty($secretAccessKey)) ) {
			$message = __( 'Invalid Request Parameters', $this->text_domain );
			return compact( 'success', 'message' );
		}

		$options['AWSAccessKeyId'] = $accessKeyId;
		if ( ! empty($associateTag) ) {
			$options['AssociateTag'] = @trim( $associateTag );
		}
		$options['ResponseGroup'] = 'ItemAttributes,Small,Images,OfferSummary,SalesRank,Reviews';
		$options['Service']       = 'AWSECommerceService';
		$options['Timestamp']     = gmdate( 'Y-m-d\TH:i:s\Z' );
		$options['Version']       = self::AWS_VERSION;
		ksort( $options );
		$params = array();
		foreach ( $options as $k => $v ) {
			$params[] = $k . '=' . self::urlencode_rfc3986( $v );
		}
		$query = implode( '&', $params );
		$urlInfo = parse_url( $baseUri );
		$signature = sprintf( "GET\n%s\n/onca/xml\n%s", $urlInfo['host'], $query );
		$signature = base64_encode( hash_hmac( 'sha256', $signature, $secretAccessKey, true ) );
		unset($params, $urlInfo);

		$url = sprintf( '%s/onca/xml?%s&Signature=%s', $baseUri, $query, self::urlencode_rfc3986( $signature ) );

		$response = wp_remote_request(
			$url,
			array(
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			$error  = '';
			$errors = $response->get_error_messages();
			if ( is_array( $errors ) ) {
				$error = implode( '<br/>', $errors );
			}
			$message = sprintf( __( 'Network Error: %s', $this->text_domain ), $error );
			return compact( 'success', 'message' );
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty($body) ) {
			$message = sprintf( __( 'Empty Response from %s', $this->text_domain ), $url );
			return compact( 'success', 'message' );
		}

		$fetchedAt = time();

		$success = false;
		/* @var $xml stdClass */
		$xml = @simplexml_load_string( $body );
		if ( WP_DEBUG ) {
			if ( ! $xml ) {
				error_log( 'amazonjs: cannot parse xml: ' . $body );
			}
		}

		if ( $xml ) {
			if ( 'True' == (string) @$xml->Items->Request->IsValid ) {
				$success   = true;
				$items     = array();
				$operation = $options['Operation'];
				if ( 'ItemSearch' == $operation ) {
					$os                 = array(); // OpenSearch
					$request            = $xml->Items->Request->ItemSearchRequest;
					$resultMap          = self::to_array( $xml->Items->SearchResultsMap );
					$itemsParPage       = 10;
					$startPage          = ($request->ItemPage) ? (int) $request->ItemPage : 1;
					$os['itemsPerPage'] = $itemsParPage;
					$os['startIndex']   = ($startPage - 1) * $itemsParPage + 1;
					$os['Query']        = array( 'searchTerms' => (string) $request->Keywords, 'startPage' => $startPage );
				}
				$os['totalResults'] = (int) $xml->Items->TotalResults;
				$os['totalPages']   = (int) $xml->Items->TotalPages;

				foreach ( $xml->Items->Item as $item ) {
					$r                  = self::to_array( $item->ItemAttributes );
					$r['ASIN']          = trim( (string) $item->ASIN );
					$r['DetailPageURL'] = trim( (string) $item->DetailPageURL );
					$r['SalesRank']     = (int) $item->SalesRank;
					if ( $reviews = $item->CustomerReviews ) {
						$r['IFrameReviewURL'] = (string) $reviews->IFrameURL;
					}
					$r['OfferSummary'] = self::to_array( $item->OfferSummary );
					$r['SmallImage']   = self::image_element( $item->SmallImage );
					$r['MediumImage']  = self::image_element( $item->MediumImage );
					$r['LargeImage']   = self::image_element( $item->LargeImage );
					$r['CountryCode']  = $countryCode;
					$r['UpdatedAt']    = $fetchedAt;

					$this->fix_item( $r );
					$items[]           = $r;
				}
				if ( 'ItemLookup' == $operation ) {
					if ( 0 == $os['totalResults'] ) {
						$os['totalResults'] = count( $items );
					}
					if ( 0 == $os['totalPages'] ) {
						$os['totalPages'] = 1;
					}
				}
			} else {
				if ( $error = @$xml->Items->Request->Errors->Error ) {
					$message = __( 'Amazon Product Advertising API Error', $this->text_domain );
					$error_code    = (string) @$error->Code;
					$error_message = (string) @$error->Message;
				} elseif ( $error = @$xml->Error ) {
					$message = __( 'Amazon Product Advertising API Error', $this->text_domain );
					$error_code    = (string) @$error->Code;
					$error_message = (string) @$error->Message;
				} else {
					$message    = __( 'Cannot Parse Amazon Product Advertising API Response' );
					$error_body = (string) $body;
				}
			}
		} else {
			$message = __( 'Invalid Response', $this->text_domain );
		}
		return compact( 'success', 'operation', 'os', 'items', 'resultMap', 'message', 'error_code' , 'error_message', 'error_body' );
	}

	static function to_array( $element ) {
		$orgElement = $element;
		if ( is_object( $element ) && 'SimpleXMLElement' == get_class( $element ) ) {
			$element = get_object_vars( $element );
		}
		if ( is_array( $element ) ) {
			$result = array();
			if ( count( $element ) <= 0 ) {
				return trim( strval( $orgElement ) );
			}
			foreach ( $element as $key => $value ) {
				if ( is_string( $key ) && '@attributes' == $key ) {
					continue;
				}
				$result[ $key ] = self::to_array( $value );
			}
			return $result;
		} else {
			return trim( strval( $element ) );
		}
	}

	static function image_element( $element ) {
		if ( $element ) {
			$src    = trim( (string) @$element->URL );
			$width  = (int) @$element->Width;
			$height = (int) @$element->Height;
			return compact( 'src', 'width', 'height' );
		}
		return null;
	}

	/**
	 * @param array $item
	 */
	function fix_item( &$item ) {
		$item['DetailPageURL'] = self::to_ssl_detail_url( $item['DetailPageURL'] );
		if ( $this->settings['useShortItemUrl'] ) {
			$item['DetailPageURL'] = self::trim_title_from_detail_url( $item['DetailPageURL'] );
		}
		if ( isset( $item['IFrameReviewURL'] ) ) {
			$item['IFrameReviewURL'] = self::to_ssl_detail_url( $item['IFrameReviewURL'] );
		}
		foreach ( array('SmallImage', 'MediumImage', 'LargeImage') as $imageKey ) {
			if ( isset( $item[$imageKey] ) ) {
				$item[$imageKey]['src'] = self::to_ssl_image_url( $item[$imageKey]['src'] );
			}
		}
	}

	static function trim_title_from_detail_url( $url ) {
		return preg_replace('/(\.amazon\.[^\/]+)\/([^\/]+)\/dp\//', '$1/dp/', $url);
	}

	static function to_ssl_detail_url( $url ) {
		return preg_replace('/^http:\/\//', 'https://', $url);
	}

	static function to_ssl_image_url( $url ) {
		return preg_replace('/^http:\/\/ecx\./', 'https://images-na.ssl-', $url);
	}

	static function urlencode_rfc3986( $string ) {
		return str_replace( '%7E', '~', rawurlencode( $string ) );
	}
}

function media_upload_type_amazonjs() {
	include dirname( __FILE__ ) . '/media-upload-type-amazonjs.php';
}

function media_upload_type_amazonjs_keyword() {
	include dirname( __FILE__ ) . '/media-upload-type-amazonjs.php';
}

function media_upload_type_amazonjs_id() {
	include dirname( __FILE__ ) . '/media-upload-type-amazonjs.php';
}

function amazonjs_init() {
	global $amazonjs;
	$amazonjs = new Amazonjs();
	$amazonjs->init();
}

function amazonjs_uninstall() {
	$amazonjs = new Amazonjs();
	$amazonjs->delete_settings();
	$amazonjs->delete_cache();
	unset($amazonjs);
}

add_action( 'init', 'amazonjs_init' );
if ( function_exists( 'register_uninstall_hook' ) ) {
	register_uninstall_hook( __FILE__, 'amazonjs_uninstall' );
}
