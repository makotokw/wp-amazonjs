=== Amazon JS ===
Contributors: makoto_kw
Tags: Amazon, books, post, media, affiliate, japanese
Requires at least: 3.3
Tested up to: 4.3.1
Stable tag: trunk
License: GPLv2 or later

Easy to add an Amazon product to your post and you can customize display it by using jQuery template.

== Description ==

AmazonJS displays Amazon products on your WordPress posts and pages. AmazonJS adds a search form to WordPress post form. Then you can search Amazon products by keyword, ASIN or URL, and add shortcode or html to your post from results of search.

AmazonJS uses `amazonjs' shortcode and jQuery template to display Amazon products. View the work data at runtime, Javascript allows various representations. AmazonJS has some template to Amazon products for each Product Group, it can how to display the different Amazon products, such as books and music.

* Requires WorPress 3.3 or later
* Requires your Amazon Product Advertising API
* Plugin Uses API cache by using Transient API
* Customize template by using jQuery template
* Supports Amazon domains ([US](http://www.amazon.com), [UK](http://www.amazon.co.uk), [Germany](http://www.amazon.de), [France](http://www.amazon.fr), [Japan](http://www.amazon.co.jp/), [Canada](http://www.amazon.ca), [China](http://www.amazon.cn), [Italy](http://www.amazon.it), [Spain](http://www.amazon.es))

= Using Amazon Product Advertising API =

AmazonJS requires [Amazon Product Advertising API](https://affiliate-program.amazon.com/gp/advertising/api/detail/main.html) 
in order to get Amazon product information from ASIN or keyword. 
Thus, you have to sign up Amazon Product Advertising API and specify your **Access Key** and **Secret Access Key**. 
And you must set your associate tags for Amazon Associates also.

= Shortcode =

AmazonJS adds a media link above an editor in the editing post page. The form that searches an Amazon product is shown when you click it. AmazonJS adds a simple html code (like WP-amazon) or an `amazonjs` shortcode from form.

AmazonJS supports the `amazonjs' shortcode. 

    ex) 
    [amazonjs asin="B00005ED8E" locale="JP" tmpl="Small" title="D・N・A"]

* *asin*: (required) ASIN (ProductID of Amazon) 
* *locale*: (required) `US`, `UK`, `DE`, `FR`, `JP`, `CA`, `CN`, `IT` or `ES`
* *tmpl*: (optional) `Small`. if tmpl is empty, apply a template via Product Group of Amazon Product Advertising API.
* *title*: (optional) It will be used for loading message.
* *imgsize*: (optional) Thumbnail imge size. `small`, `medium` or `large`.

= Display by javascript =

In first, AmazonJS converts <a/> tag from `amazonjs` short code in server side. Second, in window.load, `amazonjs.js` (it is added by plugin) will replace  <a/> tag with formatted html to display Amazon Product by using jQuery template.

= Link =

* GitHub Repository (lastest source code and old verisons): https://github.com/makotokw/wp-amazonjs
* Japanese article: http://blog.makotokw.com/portfolio/wordpress/amazonjs/

== Installation ==

1. Upload `amazonjs` to the `/wp-content/plugins/` directory
1. Activate the plugin through the `Plugins` menu in WordPress
1. Set your associate tags and your keys of the Product Advertising API through the  `Settubgs` > `Amazonjs` menu in WordPress

= Directory structure =

    /wp-content
      /plugins/amazonjs
        + /css/*
        + /images/*
        + /js/*
        + /languages/*
        + /lib/*
        - *.*

= Settings =

1. Access to /wp-admin/ and click Settings > AmazonJS on menu
1. Set your keys for Product Advertising API
1. Set your Amazon Associate Tag via Amazon Domain

== Frequently Asked Questions ==

== Screenshots ==

1. Click the gray Amazon icon to go to search form in editing entry page.

== Upgrade Notice ==

== Changelog ==

= 0.7.3 =

* Fixed to save settings by checkbox
* Fixed image url over https

= 0.7.2 =

* Fixed PHP Short Open Tag in PHP5.3 or earlier by @shield-9
* Improved timeout to request Product Advertising API
* Required WordPress 3.3

= 0.7.1 =

* Fixed to parse ItemID into https URL
* Fixed Japanese translation of search form
* Improved to display to display the error response of Product Advertising API
* Bundle the jquery.tmpl
* WordPress Cording Standard

= 0.7 =

* Supported click tracking by Google Analytics
* Added option to overwrite !important style of theme in v0.6.1

= 0.6.1 =

* Fixed to overwrite !important style of theme
* Fixed `imgsize`

= 0.6 =

* Added option to allow animation
* Supported `imgsize` attr of shortcode
* Fixed to display same ASIN for some countries
* Fixed style for smart phone and dark theme

= 0.5 =

* Added DVD template
* Optimized style for WordPress 3.8
* Used wp-ajax to search product in admin page
* Removed minify css and js to avoid to conflict W3 Total Cache

= 0.4.2 =

* Fixed to work in footer with jQuery
* Fixed to enqueue jQuery in admin page
* Fixed to find config file above ABSPATH
* Fixed and improved to display error message

= 0.4.1 =

* Added amazonjs.js script only when needed
* minify css and js

= 0.4 =

* Added option to display customer review, default is off
* Added option to display an Amazon official widget when disabled javascript in web browser, default is off
* Changed priority of wp_print_footer_scripts to execute before other plugin occurred error

= 0.3 =

* Used MediumImage in blog feed for the magazine view of feedly
* Add an Amazon product url to indicator if it has cache data
* Supported disable javascript to display amazon link widget
* Fixed to fetch more 10 products at once
* Fixed some php warnings

= 0.2.1 =

* Fixed to display image on the IE 8

= 0.2 =

* Fixed to use custom style on the child theme
* Improved to display an error message

= 0.1beta5 =

* Added ItemGroup Template for Amazon Kindle Book
* Fixed to display price

= 0.1beta4 =

* Allow to use Unsupported Search Index of Amazon Product Advertising API
* Amazon Product Advertising API Version 2011-08-01

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
* Amazon Product Advertising API Version 2010-11-01

= 0.1beta2 =

* Initial release