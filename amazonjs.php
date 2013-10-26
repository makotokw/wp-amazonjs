<?php
/*
 Plugin Name: AmazonJS
 Plugin URI: http://wordpress.org/extend/plugins/amazonjs/
 Description: Easy to use interface to add an amazon product to your post and display it by using jQuery template.
 Author: makoto_kw
 Version: 0.4.2
 Author URI: http://makotokw.com
 Requires at least: 2.8
 Tested up to: 3.7
 License: GPLv2
 Text Domain: amazonjs
 Domain Path: /languages/
 */
__('Easy to use interface to add an amazon product to your post and display it by using jQuery template.');
/* 
 AmazonJS depends on
   jQuery tmpl
   PEAR Cache_Lite: Fabien MARTY <fab@php.net>
   PEAR Services_JSON: Michal Migurski <mike-json@teczno.com>
 */

require_once dirname(__FILE__) . '/Abstract.php';
require_once dirname(__FILE__) . '/lib/Cache/Lite.php';
require_once dirname(__FILE__) . '/lib/json.php';

class Amazonjs extends Amazonjs_Wordpress_Plugin_Abstract
{
	const VERSION = '0.4.2';
	const AWS_VERSION = '2011-08-01';
	const CACHE_LIFETIME = 86400;

	// jQuery tmpl requires jQuery 1.4.2 or later
	const JQ_URI = 'http://ajax.microsoft.com/ajax/jquery/jquery-1.4.2.min.js';
	const JQ_VERSION = '1.4.2';
	const JQ_TMPL_URI = 'http://ajax.microsoft.com/ajax/jquery.templates/beta1/jquery.tmpl.min.js';
	const JQ_TMPL_VERSION = 'beta1';

	public $media_type = 'amazonjs';
	public $countries;
	public $search_indexes;
	public $cache;
	public $cache_dir;
	public $display_items = array();
	public $simple_template;

	function __construct()
	{
		$file = __FILE__;
		parent::__construct($file);
		$this->title = 'AmazonJS';
		$this->use_option_page = true;

		$this->countries = array(
			'US' => array(
				'label' => __('United States', $this->textdomain),
				'domain' => 'Amazon.com',
				'baseUri' => 'http://webservices.amazon.com',
				'linkTemplate' => '<iframe src="http://rcm.amazon.com/e/cm?t=${t}&o=1&p=8&l=as1&asins=${asins}&fc1=${fc1}&IS2=${IS2}&lt1=${lt1}&m=amazon&lc1=${lc1}&bc1=${bc1}&bg1=${bg1}&f=ifr" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>',
				'associateTagSufix' => '-20'
			),
			'UK' => array(
				'label' => __('United Kingdom', $this->textdomain),
				'domain' => 'Amazon.co.uk',
				'baseUri' => 'http://webservices.amazon.co.uk',
				'linkTemplate' => '<iframe src="http://rcm-uk.amazon.co.uk/e/cm?t=${t}&o=2&p=8&l=as1&asins=${asins}&fc1=${fc1}&IS2=${IS2}&lt1=${lt1}&m=amazon&lc1=${lc1}&bc1=${bc1}&bg1=${bg1}&f=ifr" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>',
				'associateTagSufix' => '-21'
			),
			'DE' => array(
				'label' => __('Deutschland', $this->textdomain),
				'domain' => 'Amazon.de',
				'baseUri' => 'http://webservices.amazon.de',
				'linkTemplate' => '<iframe src="http://rcm-de.amazon.de/e/cm?t=${t}&o=3&p=8&l=as1&asins=${asins}&fc1=${fc1}&IS2=${IS2}&lt1=${lt1}&m=amazon&lc1=${lc1}&bc1=${bc1}&bg1=${bg1}&f=ifr" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>',
				'associateTagSufix' => '04-21'
			),
			'FR' => array(
				'label' => __('France', $this->textdomain),
				'domain' => 'Amazon.fr',
				'baseUri' => 'http://webservices.amazon.fr',
				'linkTemplate' => '<iframe src="http://rcm-fr.amazon.fr/e/cm?t=${t}&o=8&p=8&l=as1&asins=${asins}&fc1=${fc1}&IS2=${IS2}&lt1=${lt1}&m=amazon&lc1=${lc1}&bc1=${bc1}&bg1=${bg1}&f=ifr" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>',
				'associateTagSufix' => '09-21'
			),
			'JP' => array(
				'label' => __('Japan', $this->textdomain),
				'domain' => 'Amazon.co.jp',
				'baseUri' => 'http://webservices.amazon.co.jp',
				'linkTemplate' => '<iframe src="http://rcm-jp.amazon.co.jp/e/cm?t=${t}&o=9&p=8&l=as1&asins=${asins}&fc1=${fc1}&IS2=${IS2}&lt1=${lt1}&m=amazon&lc1=${lc1}&bc1=${bc1}&bg1=${bg1}&f=ifr" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>',
				'associateTagSufix' => '-22'
			),
			'CA' => array(
				'label' => __('Canada', $this->textdomain),
				'domain' => 'Amazon.ca',
				'baseUri' => 'http://webservices.amazon.ca',
				'linkTemplate' => '<iframe src="http://rcm-ca.amazon.ca/e/cm?t=${t}&o=15&p=8&l=as1&asins=${asins}&fc1=${fc1}&IS2=${IS2}&lt1=${lt1}&m=amazon&lc1=${lc1}&bc1=${bc1}&bg1=${bg1}&f=ifr" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>',
				'associateTagSufix' => '0c-20'
			),
			'CN' => array(
				'label' => __('China', $this->textdomain),
				'domain' => 'Amazon.cn',
				'baseUri' => 'http://webservices.amazon.cn',
				'linkTemplate' => '<iframe src="http://rcm-cn.amazon.cn/e/cm?t=${t}&o=28&p=8&l=as1&asins=${asins}&fc1=${fc1}&IS2=${IS2}&lt1=${lt1}&m=amazon&lc1=${lc1}&bc1=${bc1}&bg1=${bg1}&f=ifr" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>',
				'associateTagSufix' => '-23'
			),
			'IT' => array(
				'label' => __('Italia', $this->textdomain),
				'domain' => 'Amazon.it',
				'baseUri' => 'http://webservices.amazon.it',
				'linkTemplate' => '<iframe src="http://rcm-it.amazon.it/e/cm?t=${t}&o=29&p=8&l=as1&asins=${asins}&fc1=${fc1}&IS2=${IS2}&lt1=${lt1}&m=amazon&lc1=${lc1}&bc1=${bc1}&bg1=${bg1}&f=ifr" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>',
				'associateTagSufix' => '-21'
			),
			'ES' => array(
				'label' => __('España', $this->textdomain),
				'domain' => 'Amazon.es',
				'baseUri' => 'http://webservices.amazon.es',
				'linkTemplate' => '<iframe src="http://rcm-es.amazon.es/e/cm?t=${t}&o=30&p=8&l=as1&asins=${asins}&fc1=${fc1}&IS2=${IS2}&lt1=${lt1}&m=amazon&lc1=${lc1}&bc1=${bc1}&bg1=${bg1}&f=ifr" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>',
				'associateTagSufix' => '-21'
			),
		);

		//$this->cache_dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR;
		$wp_content_dir = (defined('WP_CONTENT_DIR') && file_exists(WP_CONTENT_DIR)) ? WP_CONTENT_DIR : ABSPATH . 'wp-content';
		$this->cache_dir = $wp_content_dir . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'amazonjs' . DIRECTORY_SEPARATOR;
		if (!@is_dir($this->cache_dir)) {
			@mkdir($this->cache_dir);
		}
		$this->cache = new Amazonjs_Cache_Lite(
			array(
				'cacheDir' => $this->cache_dir,
				'lifeTime' => self::CACHE_LIFETIME,
				'automaticSerialization' => true,
			)
		);
	}

	function clean()
	{
		$this->delete_settings();
		$this->cache->clean();
		@rmdir($this->cache_dir);
	}

	function init()
	{
		parent::init();
		add_shortcode('amazonjs', array($this, 'shortcode'));
		if (!is_admin()) {
			add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_styles'));
			add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));
			add_action('wp_footer', array($this, 'wp_enqueue_scripts_for_footer'), 1);
		}
	}

	function wp_enqueue_styles()
	{
		if ($this->settings['displayCustomerReview']) {
			wp_enqueue_style('thickbox');
		}
		if (WP_DEBUG) {
			wp_enqueue_style('amazonjs', $this->url . '/css/amazonjs.css', array(), self::VERSION);
		} else {
			wp_enqueue_style('amazonjs', $this->url . '/css/amazonjs.min.css', array(), self::VERSION);
		}
		if ($this->settings['customCss']) {
			wp_enqueue_style('amazonjs-custom', get_stylesheet_directory_uri() . '/amazonjs.css');
		}
	}

	function wp_enqueue_scripts()
	{
		$v = get_bloginfo('version');
		if (version_compare($v, '3.0', '<')) {
			wp_deregister_script('jquery');
			wp_register_script('jquery', self::JQ_URI, array(), self::JQ_VERSION);
		}
		wp_register_script('jqeury-tmpl', self::JQ_TMPL_URI, array('jquery'), self::JQ_TMPL_VERSION, true);

		$depends =  array('jqeury-tmpl');
		if ($this->settings['displayCustomerReview']) {
			$depends[] = 'thickbox';
		}
		if (WP_DEBUG) {
			wp_register_script('amazonjs', $this->url . '/js/amazonjs.js', $depends, self::VERSION, true);
		} else {
			wp_register_script('amazonjs', $this->url . '/js/amazonjs.min.js', $depends, self::VERSION, true);
		}
		if ($this->settings['customJs']) {
			wp_register_script('amazonjs-custom', get_stylesheet_directory_uri() . '/amazonjs.js', array('amazonjs'), self::VERSION, true);
		}
	}

	function wp_enqueue_scripts_for_footer()
	{
		$items = array();
		foreach ($this->display_items as $contry_code => $sub_items) {
			$items = array_merge($items, $this->fetch_items($contry_code, $sub_items));
		}
		if (count($items) == 0) {
			return;
		}

		$this->enqueue_amazonjs_scripts($items);
	}

	function enqueue_amazonjs_scripts($items = array())
	{
		$wpurl = get_bloginfo('wpurl');

		$region = array();
		foreach ($this->countries as $code => $value) {
			foreach (array('linkTemplate') as $attr) {
				$region['Link' . $code] = $this->tmpl($value[$attr], array('t' => $this->settings['associateTag' . $code]));
			}
		}

		$amazonVars = array(
			'thickboxUrl' => $wpurl . '/wp-includes/js/thickbox/',
			'regionTempalte' => $region,
			'resource' => array(
				'BookAuthor' => __('Author', $this->textdomain),
				'BookPublicationDate' => __('PublicationDate', $this->textdomain),
				'BookPublisher' => __('Publisher', $this->textdomain),
				'NumberOfPagesValue' => __('${NumberOfPages} pages', $this->textdomain),
				'ListPrice' => __('List Price', $this->textdomain),
				'Price' => __('Price', $this->textdomain),
				'PriceUsage' => __('Product prices and availability are accurate as of the date/time indicated and are subject to change. Any price and availability information displayed on [amazon.com or endless.com, as applicable] at the time of purchase will apply to the purchase of this product.', $this->textdomain),
				'PublicationDate' => __('Publication Date', $this->textdomain),
				'ReleaseDate' => __('Release Date', $this->textdomain),
				'SalesRank' => __('SalesRank', $this->textdomain),
				'SalesRankValue' => __('#${SalesRank}', $this->textdomain),
				'RunningTime' => __('Run Time', $this->textdomain),
				'RunningTimeValue' => __('${RunningTime} minutes', $this->textdomain),
				'CustomerReviewTitle' => __('${Title} Customer Review', $this->textdomain),
				'SeeCustomerReviews' => __('See Customer Reviews', $this->textdomain),
				'PriceUpdatedat' => __('(at ${UpdatedDate})', $this->textdomain),
			),
			'isCustomerReviewEnabled' => ($this->settings['displayCustomerReview']) ? 'true' : 'false',
			'items' => array_values($items),

		);
		wp_localize_script('amazonjs', 'amazonjsVars', $amazonVars);

		wp_enqueue_script('amazonjs');
		if ($this->settings['customJs']) {
			wp_enqueue_script('amazonjs-custom');
		}
	}

	function init_settings()
	{
		// section
		$this->setting_sections = array(
			'api' => array(
				'label' => __('Product Advertising API settings', $this->textdomain),
				'add' => 'add_api_section'),
			'associate' => array(
				'label' => __('Amazon Associates settings', $this->textdomain),
				'add' => 'add_associate_section'),
			'appearance' => array(
				'label' => __('Appearance settings', $this->textdomain),
				'add' => 'add_appearance_section'),
			'customize' => array(
				'label' => __('Customize', $this->textdomain),
				'add' => 'add_customize_section'),
		);
		// filed
		$template_url = get_bloginfo('template_url');
		$this->setting_fileds = array(
			'accessKeyId' => array(
				'label' => __('Access Key ID', $this->textdomain),
				'type' => 'text',
				'size' => 60,
				'section' => 'api',
			),
			'secretAccessKey' => array(
				'label' => __('Secret Access Key', $this->textdomain),
				'type' => 'text',
				'size' => 60,
				'section' => 'api',
			),
			'displayCustomerReview' => array(
				'label' => __('Display customer review', $this->textdomain),
				'type' => 'checkbox',
				'section' => 'appearance',
				'description' => __("AmazonJS will display customer review by using WordPress's Thickbox.", $this->textdomain)
			),
			'supportDisabledJavascript' => array(
				'label' => __('Display official widget when disabled javascript in web browser', $this->textdomain),
				'type' => 'checkbox',
				'section' => 'appearance',
				'description' => __('If set to true, AmazonJS will output html by document.write.', $this->textdomain),
			),
			'customCss' => array(
				'label' => __('Use Custom Css', $this->textdomain),
				'type' => 'checkbox',
				'section' => 'customize',
				'description' => '(' . $template_url . '/amazonjs.css)'
			),
			'customJs' => array(
				'label' => __('Use Custom Javascript', $this->textdomain),
				'type' => 'checkbox',
				'section' => 'customize',
				'description' => '(' . $template_url . '/amazonjs.js)'
			),
		);
		foreach ($this->countries as $key => $value) {
			$this->setting_fileds['associateTag' . $key] = array(
				'label' => __($value['domain'], $this->textdomain),
				'type' => 'text',
				'size' => 30,
				'placeholder' => 'associatetag' . $value['associateTagSufix'],
				'section' => 'associate',
			);
		}
		parent::init_settings();
	}

	function admin_menu()
	{
		$menu_title = '<img width="12" height="12" src="' . $this->url . '/images/amazon-icon.png" alt="' . $this->title . '"/>&nbsp;';
		$menu_title .= $this->title;
		$this->add_options_page($this->title, $menu_title);
	}

	function get_amazon_official_link($asin, $locale)
	{
		$tmpl = $this->countries[$locale]['linkTemplate'];
		$item = array(
			't' => $this->settings['associateTag' . $locale],
			'asins' => $asin,
			'fc1' => '000000',
			'lc1' => '0000FF',
			'bc1' => '000000',
			'bg1' => 'FFFFFF',
			'IS2' => 1,
			'lt1' => '_blank',
			'f' => 'ifr',
			'm' => 'amazon'
		);
		return $this->tmpl($tmpl, $item);
	}

	function shortcode($atts, $content = null)
	{
		/**
		 * @var string $asin
		 * @var string $tmpl
		 * @var string $locale
		 * @var string $title
		 */
		$defaults = array('asin' => '', 'tmpl' => '', 'locale' => $this->default_country_code(), 'title' => '');
		extract(shortcode_atts($defaults, $atts));
		if (empty($asin)) {
			return '';
		}
		$locale = strtoupper($locale);
		if (is_feed()) {
			// use static html for rss reader
			if ($ai = $this->get_item($locale, $asin)) {
				$aimg = $ai['SmallImage'];
				if (array_key_exists('MediumImage', $ai)) {
					$aimg = $ai['MediumImage'];
				}
				return <<<EOF
<a href="{$ai['DetailPageURL']}" title="{$ai['Title']}" target="_blank">
<img src="{$aimg['src']}" width="{$aimg['width']}" height="{$aimg['height']}" alt="{$ai['Title']}"/>
{$ai['Title']}
</a>
EOF;
			}
			return $this->get_amazon_official_link($asin, $locale);
		}
		if (!isset($this->display_items[$locale])) {
			$this->display_items[$locale] = array();
		}
		$item = (array_key_exists($asin, $this->display_items[$locale]))
			? $this->display_items[$locale][$asin]
			: $this->display_items[$locale][$asin] = $this->cache->get($asin, $locale);
		$url = '#';
		if (is_array($item) && array_key_exists('DetailPageURL', $item)) {
			$url = $item['DetailPageURL'];
		}
		$indicator_html = <<<EOF
<a href="{$url}" class="asin_{$asin}_{$locale}_${tmpl} amazonjs_item" rel="amazonjs"><span class="amazonjs_indicator">{$title}</span></a>
EOF;

		$indicator_html = trim($indicator_html);
		if (!$this->settings['supportDisabledJavascript']) {
			return $indicator_html;
		}
		$indicator_html = addslashes($indicator_html);
		$link_html = $this->get_amazon_official_link($asin, $locale, true);
		return <<<EOF
<script type="text/javascript">document.write("{$indicator_html}")</script><noscript>{$link_html}</noscript>
EOF;
	}

	/**
	 * Gets default country code by WPLANG
	 * @return string
	 */
	function default_country_code()
	{
		// https://codex.wordpress.org/WordPress_in_Your_Language
		switch (WPLANG) {
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

	function get_item($country_code, $asin)
	{
		if ($ai = $this->cache->get($asin, $country_code)) {
			return $ai;
		}
		$items = $this->fetch_items($country_code, array($asin => false));
		return @$items[$asin];
	}

	/**
	 * @param string $country_code
	 * @param array $items
	 * @return array
	 */
	function fetch_items($country_code, $items)
	{
		$now = time();
		$itemids = array();
		foreach ($items as $asin => $item) {
			if (!$item && $item['UpdatedAt'] + 86400 < $now) {
				$itemids[] = $asin;
			}
		}
		while (count($itemids)) {
			// fetch via 10 products
			// ItemLookup ItemId: Must be a valid item ID. For more than one ID, use a comma-separated list of up to ten IDs.
			$itemid = implode(',', array_splice($itemids, 0, 10));
			$results = $this->itemlookup($country_code, $itemid);
			if ($results && $results['success']) {
				foreach ($results['items'] as $item) {
					$items[$item['ASIN']] = $item;
					$this->cache->save($item, $item['ASIN'], $country_code);
				}
			}
		}
		return $items;
	}

	function tmpl($tmpl, $item)
	{
		$s = $tmpl;
		foreach ($item as $key => $value) {
			$s = str_replace('${' . $key . '}', $value, $s);
		}
		return $s;
	}

	function add_api_section()
	{
		?>
	<p><?php _e('This plugin uses the Amazon Product Advertising API in order to get product infomation. Thus, you must use your Access Key ID &amp; Secret Access Key.', $this->textdomain); ?></p>
	<p><?php _e('You can sign up the Amazon Product Advertising API from <a href="https://affiliate-program.amazon.com/gp/advertising/api/detail/main.html" target="_blank">here</a>. Please review the <a href="http://affiliate-program.amazon.com/gp/advertising/api/detail/agreement.html" target="_blank">Product Advertising API License Agreement</a> for details.', $this->textdomain)?></p>
	<?php
	}

	function add_associate_section()
	{
		?>
	<p><?php _e('Amazon has an affiliate program called Amazon Associates. To apply for the Associates Program, visit the <a href="https://affiliate-program.amazon.com/" target="_blank">Amazon Associates website</a> for details.', $this->textdomain); ?></p>
	<p><?php _e('Associate Tag has been a <strong>required and verified</strong> input parameter in all requests to the Amazon Product Advertising API since 11/1/2011.', $this->textdomain)?></p>
	<?php
	}

	function add_appearance_section()
	{
	}

	function add_customize_section()
	{
	}

	function add_settings_field_accessKeyId()
	{
		$this->add_settings_field('accessKeyId', $this->setting_fileds['accessKeyId']);
	}

	function add_settings_field_secretAccessKey()
	{
		$this->add_settings_field('secretAccessKey', $this->setting_fileds['secretAccessKey']);
	}

	function add_settings_field_associateTagUS()
	{
		$this->add_settings_field('associateTagUS', $this->setting_fileds['associateTagUS']);
	}

	function add_settings_field_associateTagUK()
	{
		$this->add_settings_field('associateTagUK', $this->setting_fileds['associateTagUK']);
	}

	function add_settings_field_associateTagDE()
	{
		$this->add_settings_field('associateTagDE', $this->setting_fileds['associateTagDE']);
	}

	function add_settings_field_associateTagJP()
	{
		$this->add_settings_field('associateTagJP', $this->setting_fileds['associateTagJP']);
	}

	function add_settings_field_associateTagCA()
	{
		$this->add_settings_field('associateTagCA', $this->setting_fileds['associateTagCA']);
	}

	function add_settings_field_associateTagFR()
	{
		$this->add_settings_field('associateTagFR', $this->setting_fileds['associateTagFR']);
	}

	function add_settings_field_associateTagCN()
	{
		$this->add_settings_field('associateTagCN', $this->setting_fileds['associateTagCN']);
	}

	function add_settings_field_associateTagES()
	{
		$this->add_settings_field('associateTagES', $this->setting_fileds['associateTagES']);
	}

	function add_settings_field_associateTagIT()
	{
		$this->add_settings_field('associateTagIT', $this->setting_fileds['associateTagIT']);
	}

	function add_settings_field_customCss()
	{
		$this->add_settings_field('customCss', $this->setting_fileds['customCss']);
	}

	function add_settings_field_customJs()
	{
		$this->add_settings_field('customJs', $this->setting_fileds['customJs']);
	}

	function add_settings_field_displayCustomerReview()
	{
		$this->add_settings_field('displayCustomerReview', $this->setting_fileds['displayCustomerReview']);
	}

	function add_settings_field_supportDisabledJavascript()
	{
		$this->add_settings_field('supportDisabledJavascript', $this->setting_fileds['supportDisabledJavascript']);
	}

	function admin_init()
	{
		add_action('media_buttons', array($this, 'media_buttons'), 20);
		add_action('media_upload_amazonjs', array($this, 'media_upload_amazonjs'));
		add_action('media_upload_amazonjs_keyword', array($this, 'media_upload_amazonjs_keyword'));
		add_action('media_upload_amazonjs_id', array($this, 'media_upload_amazonjs_id'));
		parent::admin_init();
	}

	function media_buttons()
	{
		global $post_ID, $temp_ID;
		$iframe_ID = (int)(0 == $post_ID ? $temp_ID : $post_ID);
		$iframe_src = 'media-upload.php?post_id=' . $iframe_ID . '&amp;type=' . $this->media_type . '&amp;tab=' . $this->media_type . '_keyword';
		$label = __('Add Amazon Link', $this->textdomain);

		echo <<<EOF
<a href="{$iframe_src}&amp;TB_iframe=true" id="add_amazon" class="thickbox" title="{$label}"><img src="{$this->url}/images/amazon-icon.png" alt="{$label}"/></a>
EOF;
	}

	function media_upload_init()
	{
		add_action('admin_print_styles', array($this, 'wp_enqueue_styles'));

		$this->wp_enqueue_scripts();
		$this->enqueue_amazonjs_scripts();
	}

	function media_upload_amazonjs()
	{
		$this->media_upload_init();
		wp_iframe('media_upload_type_amazonjs');
	}

	function media_upload_amazonjs_keyword()
	{
		$this->media_upload_init();
		wp_iframe('media_upload_type_amazonjs_keyword');
	}

	function media_upload_amazonjs_id()
	{
		$this->media_upload_init();
		wp_iframe('media_upload_type_amazonjs_id');
	}

	function media_upload_tabs($tabs)
	{
		return array(
			$this->media_type . '_keyword' => __('Keyword Search', $this->textdomain),
			$this->media_type . '_id' => __('Search by ASIN/URL', $this->textdomain)
		);
	}

	function options_page_header()
	{
		$cache_dir_exists = @is_dir($this->cache_dir);
?>
	<?php if (!function_exists('simplexml_load_string')): ?>
	<div class="error">
		<p><?php echo sprintf(__('Error! "simplexml_load_string" function is not found. %s requires PHP 5 and SimpleXML extension.', $this->textdomain), $this->title)?></p>
	</div>
	<?php endif ?>
	<?php if (!$cache_dir_exists || !is_writable($this->cache_dir)): ?>
	<div class="error">
		<p><?php echo sprintf(__('Warning! Cache Directory "%s" is not writable', $this->textdomain), $this->cache_dir)?></p>
	</div>
	<?php endif ?>
	<?php
	}

	// amazon api
	function itemlookup($countryCode, $itemId)
	{
		$options = array();
		$options['ItemId'] = $itemId;
		$options['Operation'] = 'ItemLookup';
		return $this->amazon_get($countryCode, $options);
	}

	function itemsearch($countryCode, $searchIndex, $keywords, $itemPage = 0)
	{
		$options = array();
		if ($itemPage > 0) $options['ItemPage'] = $itemPage;
		$options['Keywords'] = $keywords;
		$options['Operation'] = 'ItemSearch';
		if ($searchIndex) $options['SearchIndex'] = $searchIndex;
		return $this->amazon_get($countryCode, $options);
	}

	function amazon_get($countryCode, $options)
	{
		$baseUri = $this->countries[$countryCode]['baseUri'];
		$accessKeyId = $this->settings['accessKeyId'];
		$secretAccessKey = $this->settings['secretAccessKey'];
		$associateTag = @$this->settings['associateTag' . $countryCode];

		// validate request
		if (empty($countryCode) || (empty($options['ItemId']) && empty($options['Keywords'])) || (empty($accessKeyId) || empty($secretAccessKey))) {
			$message = __('Invalid Request Parameters', $this->textdomain);
			return compact('success', 'message');
		}

		$options['AWSAccessKeyId'] = $accessKeyId;
		if (!empty($associateTag)) $options['AssociateTag'] = $associateTag;
		$options['ResponseGroup'] = 'ItemAttributes,Small,Images,OfferSummary,SalesRank,Reviews';
		$options['Service'] = 'AWSECommerceService';
		$options['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
		$options['Version'] = self::AWS_VERSION;
		ksort($options);
		$params = array();
		foreach ($options as $k => $v) $params[] = $k . '=' . self::urlencode_rfc3986($v);
		$query = implode("&", $params);
		unset($params);
		$signature = sprintf("GET\n%s\n/onca/xml\n%s", str_replace('http://', '', $baseUri), $query);
		$signature = base64_encode(hash_hmac('sha256', $signature, $secretAccessKey, true));

		$url = sprintf('%s/onca/xml?%s&Signature=%s', $baseUri, $query, self::urlencode_rfc3986($signature));

		$response = wp_remote_request($url);
		if (is_wp_error($response)) {
			$error = '';
			$errors = $response->get_error_messages();
			if (is_array($errors)) {
				$error = implode('<br/>', $errors);
			}
			$message = sprintf(__('Network Error: %s', $this->textdomain), $error);
			return compact('success', 'message');
		}

		$body = wp_remote_retrieve_body($response);
		if (empty($body)) {
			$message = sprintf(__('Empty Response from %s', $this->textdomain), $url);
			return compact('success', 'message');
		}

		//$string = file_get_contents($this->dir.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'blend.xml');
		$fetchedAt = time();

		$success = false;
		$xml = @simplexml_load_string($body);
		if (WP_DEBUG) {
			if (!$xml) {
				error_log('amazonjs: cannot parse xml: ' . $body);
			}
		}

		if ($xml) {
			if ('True' == (string)@$xml->Items->Request->IsValid) {
				$success = true;
				$items = array();
				$operation = $options['Operation'];
				if ($operation == 'ItemSearch') {
					$os = array(); // opensearch
					$request = $xml->Items->Request->ItemSearchRequest;
					$resultMap = self::toArray($xml->Items->SearchResultsMap);
					$itemsParPage = 10;
					$startPage = ($request->ItemPage) ? (int)$request->ItemPage : 1;
					$os['itemsPerPage'] = $itemsParPage;
					$os['startIndex'] = ($startPage - 1) * $itemsParPage + 1;
					$os['Query'] = array('searchTerms' => (string)$request->Keywords, 'startPage' => $startPage);
				}
				$os['totalResults'] = (int)$xml->Items->TotalResults;
				$os['totalPages'] = (int)$xml->Items->TotalPages;

				foreach ($xml->Items->Item as $item) {
					$r = self::toArray($item->ItemAttributes);
					$r['ASIN'] = trim((string)$item->ASIN);
					$r['DetailPageURL'] = trim((string)$item->DetailPageURL);
					$r['SalesRank'] = (int)$item->SalesRank;
					if ($reviews = $item->CustomerReviews) {
						$r['IFrameReviewURL'] = (string)$reviews->IFrameURL;
					}
					$r['OfferSummary'] = self::toArray($item->OfferSummary);
					$r['SmallImage'] = self::imageElement($item->SmallImage);
					$r['MediumImage'] = self::imageElement($item->MediumImage);
					$r['LargeImage'] = self::imageElement($item->LargeImage);
					$r['CountryCode'] = $countryCode;
					$r['UpdatedAt'] = $fetchedAt;
					$items[] = $r;
				}
				if ($operation == 'ItemLookup') {
					if ($os['totalResults'] == 0) $os['totalResults'] = count($items);
					if ($os['totalPages'] == 0) $os['totalPages'] = 1;
				}
			} else {
				if ($error = @$xml->Items->Request->Errors->Error) {
					$message = sprintf('Amazon API Retuns Error: Code=%s, Message=%s', $error->Code, $error->Message);
				} else {
					$message = __('Cannot Parse Response from Amazon API', $this->textdomain);
				}
			}
		} else {
			$message = __('Invalid Response', $this->textdomain);
		}
		return compact('success', 'operation', 'os', 'items', 'resultMap', 'message');
	}

	static function toArray($element)
	{
		$orgElement = $element;
		if (is_object($element) && get_class($element) == 'SimpleXMLElement') {
			$element = get_object_vars($element);
		}
		if (is_array($element)) {
			$result = array();
			if (count($element) <= 0) {
				return trim(strval($orgElement));
			}
			foreach ($element as $key => $value) {
				if (is_string($key) && $key == '@attributes') {
					continue;
				}
				$result[$key] = self::toArray($value);
			}
			return $result;
		} else {
			return trim(strval($element));
		}
	}

	static function imageElement($element)
	{
		if ($element) {
			$src = trim((string)@$element->URL);
			$width = (int)@$element->Width;
			$height = (int)@$element->Height;
			return compact('src', 'width', 'height');
		}
		return null;
	}

	static function urlencode_rfc3986($string)
	{
		return str_replace('%7E', '~', rawurlencode($string));
	}
}

function media_upload_type_amazonjs()
{
	include dirname(__FILE__) . '/media-upload-type-amazonjs.php';
}

function media_upload_type_amazonjs_keyword()
{
	include dirname(__FILE__) . '/media-upload-type-amazonjs.php';
}

function media_upload_type_amazonjs_id()
{
	include dirname(__FILE__) . '/media-upload-type-amazonjs.php';
}

function amazonjs_init()
{
	global $amazonjs;
	$amazonjs = new Amazonjs();
	$amazonjs->init();
}

function amazonjs_uninstall()
{
	$amazonjs = new Amazonjs();
	$amazonjs->clean();
	unset($amazonjs);
}

add_action('init', 'amazonjs_init');
if (function_exists('register_uninstall_hook')) {
	register_uninstall_hook(__FILE__, 'amazonjs_uninstall');
}
