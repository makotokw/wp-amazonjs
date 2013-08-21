=== AmazonJS ===
Contributors: makoto_kw
Donate link: http://makotokw.com/
Tags: Amazon, books, post, media, affiliate, japanese
Requires at least: 2.8
Tested up to: 3.6
Stable tag: trunk

Easy to use interface to add an amazon product to your post and display it by using jQuery template.

== Description ==

AmazonJS is used to quickly add Amazon products to your WordPress posts and pages. The plugin added a search form to post form. You can search Amazon products by keyword, ASIN or URL.

The plugin use `amazonjs' shortcode and jQuery template to show Amazon products. View the work data at runtime, Javascript allows various representations. AmazonJS has some template to Amazon products for each group, it can how to display the different Amazon products, such as books and music.

* Requires WorPress 2.8 and PHP5
* Requires your Amazon Product Advertising API
* Plugin Uses file cache (Plugin does not touch database)
* Customize template by using jQuery template

= Using Product Advertising API =

This plugin uses [Amazon Product Advertising API](https://affiliate-program.amazon.com/gp/advertising/api/detail/main.html) 
in order to get product information from ASIN or keyword. 
Thus, you have to sign up Product Advertising API and specify your **Access Key** and **Secret Access Key**. 
And you must set your associate tags for Amazon Associates also.

= Shortcode =

This plugin adds a media link above an editor in the editing post page. The form that  searches an Amazon product is shown  when you click it. This plugin add a simple html code (like WP-amazon) or an amazonjs shortcode from form.

This plugin supports `amazonjs' shortcode. 

    ex) 
    [amazonjs asin="B00005ED8E" locale="JP" tmpl="Small" title="D・N・A"]

* *asin*: (required) ASIN (ProductID of Amazon) 
* *locale*: (required) `US`, `UK`, `DE`, `FR`, `JP`, `CA`, `CN`, `IT` or `ES`
* *tmpl*: (optional) `Small`. if tmpl is empty, apply a template via product group.
* *title*: (optional) It will be used for loading message.

= Display by javascript =

In first, this plugin outputs <a/> tags from `amazonjs` short code in server side. Second, in window.load, `amazonjs.js` (it is added by plugin) will replace  <a/> tag with formatted html by using jQuery template.

= Link =

* Repository: https://github.com/makotokw/wp-amazonjs
* Japanese article: http://blog.makotokw.com/portfolio/wordpress/amazonjs/

== Installation ==

1. Upload `amazonjs` to the `/wp-content/plugins/` directory
1. Create `/wp-content/cache/amazonjs/` directory and change mode it 0777
1. Activate the plugin through the `Plugins` menu in WordPress
1. Set your associate tags and your keys of the Product Advertising API through the  `Settubgs` > `Amazonjs` menu in WordPress

= Directory structure =

    /wp-content
      /cache
        + /amazonjs <- writable
      /plugins/amazonjs
        + /images/*
        + /languages/*
        + /lib/*
        - *.*

= Settings =

1. Access to /wp-admin/ and click Settings > AmazonJS on menu
1. Set your keys for Product Advertising API
1. Set your associate tag

== Frequently Asked Questions ==

== Screenshots ==

1. Click the gray Amazon icon to go to search form in editing entry page.

== Upgrade Notice ==

== Changelog ==

= 0.4 ==

* Added option to display customer review, default is off
* Added option to display official widget when disabled javascript in web browser, default is off
* Changed priority of wp_print_footer_scripts to execute before other plugin occured error

= 0.3 =

* Used MidiumImage in feed for the magazine view of feedly
* Add product url to indicator if it has cache data
* Supported disable javascript to display amazon link widget
* Fixed to fetch more 10 items at once
* Fixed some php warnings

= 0.2.1 =

* Fixed to display img on IE 8

= 0.2 =

* Fixed to use custom style on child theme
* Improved to display error message

= 0.1beta5 =

* Added ItemGroup Template for Kindle Book
* Fixed to display price

= 0.1beta4 =

* Allow to use Unsupported Search Index
* Product Advertising API Version 2011-08-01

= 0.1beta3d =

* Fixed to return json response with error log

= 0.1beta3c =

* Fixed to detect WP_CONTENT_DIR

= 0.1beta3b =

* Supported WordPress 3.3(beta3)
* Fixed to remove deprecated
* Fixed template

= 0.1beta3a =

* Supported to search by ASIN or URL
* Product Advertising API Version 2010-11-01

= 0.1beta2 =

* Initial release