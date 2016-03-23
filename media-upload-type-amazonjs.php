<?php
require_once dirname( __FILE__ ) . '/amazonjs-aws-params.php';

/**
 * @var Amazonjs $amazonjs
 */
global $wpdb, $wp_query, $wp_locale, $type, $tab, $post_mime_types, $amazonjs;
add_filter( 'media_upload_tabs', array( $amazonjs, 'media_upload_tabs' ) );
media_upload_header();
$text_domain       = $amazonjs->text_domain;
$access_key_id     = $amazonjs->settings['accessKeyId'];
$secret_access_key = $amazonjs->settings['secretAccessKey'];
$error             = (empty( $access_key_id ) || empty( $secret_access_key ));

amazonjs_aws_params( $amazonjs );
?>
<div id="media_amazon">
	<?php if ( empty( $access_key_id ) || empty( $secret_access_key ) ) : ?>
		<div class="updated error">
			<p><?php printf( __( 'The Access Key ID or Secret Access Key is empty. Please specify it in <a href="%s" target="_blank">settings</a>.', $text_domain ), $amazonjs->option_page_url ); ?></p>
		</div>
	<?php endif ?>
	<form id="search_form" class="amazonjs_search_form" method="get" action="<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php">
		<input type="hidden" nama="tab" value="<?php echo esc_attr( $tab ); ?>"/>
		<input type="hidden" id="search_page" name="ItemPage" value="1"/>
		<?php if ( 'amazonjs_keyword' == $tab ) : ?>
			<fieldset>
				<select id="search_locale" name="CountryCode"></select>
				<select id="search_index" name="SearchIndex"></select>
			</fieldset>
			<input type="text" id="search_query" name="Keywords" placeholder="<?php _e( 'Input Keyword', $text_domain ) ?>"/>
		<?php elseif ( 'amazonjs_id' == $tab ) : ?>
			<fieldset>
				<select id="search_locale" name="CountryCode"></select>
			</fieldset>
			<input type="text" id="search_query" name="ID" placeholder="<?php _e( 'Input ASIN or URL', $text_domain ) ?>"/>
		<?php endif ?>
		<input type="submit" value="<?php _e( 'Search', $text_domain ) ?>" class="button button-large"/>
	</form>
	<div id="search_results">
		<div id="pager"></div>
		<ul id="items"></ul>
	</div>
</div>
<div id="overlay"></div>
<form id="template_form" onsubmit="return false" style="display: none;">
	<h2><?php _e( 'Select template', $text_domain ) ?></h2>

	<div id="select_template" class="select_template">
		<fieldset>
			<legend><?php _e( 'Simple Html', $text_domain ) ?></legend>
			<input type="radio" id="template_link" name="template" value="link" class="html"/>
			<label for="template_link"><?php _e( 'Title', $text_domain ) ?></label>
			<input type="radio" id="template_small_image" name="template" value="smallImage" class="html has_image"/>
			<label for="template_small_image" class="has_image"><?php _e( 'Small Image', $text_domain ) ?></label>
			<input type="radio" id="template_medium_image" name="template" value="mediumImage" class="html has_image"/>
			<label for="template_medium_image" class="has_image"><?php _e( 'Medium Image', $text_domain ) ?></label>
			<input type="radio" id="template_large_image" name="template" value="largeImage" class="html has_image"/>
			<label for="template_large_image" class="has_image"><?php _e( 'Large Image', $text_domain ) ?></label>
		</fieldset>
		<fieldset>
			<legend><?php _e( 'Dynamic Template', $text_domain ) ?></legend>
			<input type="radio" id="template_amazonjs" name="template" value="shortCode" class="shortcode"/>
			<label for="template_amazonjs"><?php _e( 'Default', $text_domain ) ?></label>
			<input type="radio" id="template_amazonjs_small" name="template" value="shortCodeSmall" class="shortcode"/>
			<label for="template_amazonjs_small"><?php _e( 'Small', $text_domain ) ?></label>
		</fieldset>
	</div>
	<h2><?php _e( 'Preview', $text_domain ) ?></h2>

	<div id="preview"></div>
	<textarea id="preview_code"></textarea>

	<div id="buttons" class="buttons">
		<input id="cancel" type="button" value="<?php _e( 'Cancel', $text_domain ) ?>" class="button"/>
		<input id="insert" type="submit" value="<?php _e( 'Insert', $text_domain ) ?>" class="button button-primary"/>
	</div>
</form>
<script type="text/javascript">
<!--
(function ($) {
	$(document).ready(function () {
		var $insert = $('#insert'),
			$cancel = $('#cancel'),
			$form = $('#search_form'),
			$results = $('#search_results'),
			$searchLocale = $('#search_locale'),
			$searchIndex = $('#search_index'),
			$searchPage = $('#search_page'),
			$searchQuery = $('#search_query'),
			$templateForm = $('#template_form'),
			$overlay = $('#overlay'),
			loading = false,
			selectedItem,
			defaultLocale = '<?php echo esc_js( $amazonjs->default_country_code() ); ?>',
			countries = <?php echo json_encode( $amazonjs->countries );?>,
			searchIndexes = <?php echo json_encode( $amazonjs->search_indexes );?>
			;

		$.each(countries, function (key, value) {
			$searchLocale.append('<option value="' + key + '">' + value.label + '</option>')
		});
		function loadSearchIndex(locale) {
			$searchIndex.empty();

			var unsupportedOptions = [];
			$.each(searchIndexes, function (key, value) {
				if (value[locale]) {
					$searchIndex.append(
							'<option value="' + key + '">' + value.label + ' (' + key + ')' + '</option>'
					);
				} else {
					unsupportedOptions.push('<option value="' + key + '">!' + value.label + ' (' + key + ')' + '</option>')
				}
			});
			$searchIndex.append(unsupportedOptions.join(''));
		}

		$searchLocale.change(function () {
			loadSearchIndex($(this).val());
		});
		$searchLocale.val(defaultLocale).change();
		$searchIndex.change(function () {
			$searchPage.val(1)
		});
		$searchQuery.change(function () {
			$searchPage.val(1)
		});
		$cancel.click(function () {
			hideTemplateForm();
		});

		$.amazonjs.initTemplate();
		$.template('amazonjsSearchIndexHeaderTpl', '<?php _e( '<h3 class="searchindex"><a href="#" rel="${IndexName}">${Label}</a> (${Results} hits)</h3>', $text_domain )?>');
		$.template('amazonjsSearchPagerTpl', '<?php _e( '<div class="searchpager">{{if prev}}<button class="button prev">Prev</button>{{/if}}${startIndex} - ${endIndex} / ${totalResults}{{if next}}<button class="button next">Next</button>{{/if}}</div>', $text_domain )?>');
		$.template('amazonjsSearchItemTpl',
			[
				'<li id="asin_${ASIN}" class="amazonjs_searchitem">',
				'<a href="${DetailPageURL}" title="${Title}" target="_blank">',
				'{{if SmallImage}}',
				'<img src="${SmallImage.src}" width="${SmallImage.width}" height="${SmallImage.height}" alt="${Title}"/>',
				'{{else}}',
				'<img src="<?php echo esc_attr( $amazonjs->url ); ?>/images/noimage-small.jpg" alt="${Title}"/>',
				'{{/if}}',
				'</a>',
				'<h4><a href="${DetailPageURL}" title="${Title}" target="_blank">${Title}</a></h4><br/>',
				'{{if ListPrice}}&nbsp;ListPrice: ${ListPrice.FormattedPrice}<br/>{{/if}}',
				'{{if OfferSummary.LowestNewPrice}}&nbsp;Price: ${OfferSummary.LowestNewPrice.FormattedPrice}<br/>{{/if}}',
				'{{if PublicationDate}}&nbsp;${PublicationDate}<br/>{{/if}}',
				'{{if SalesRank}}&nbsp;Rank: ${SalesRank}<br/>{{/if}}',
				'<button id="btn_${ASIN}" class="button select"><?php _e( 'Select', $text_domain )?></button>',
				'<div class="amazonjs_footer"></div>',
				'</li>'
			].join(''));

		$form.submit(function () {
			try {
				if (!loading) {
					request();
				}
			} catch (e) {
			}
			return false;
		});

		function insertToEditor(text) {
			var window = top, editor;
			if (typeof window.tinyMCE != 'undefined' && (editor = window.tinyMCE.activeEditor) && !editor.isHidden()) {
				editor.focus();
				if (window.tinymce.isIE) {
					editor.selection.moveToBookmark(window.tinymce.EditorManager.activeEditor.windowManager.bookmark);
				}
				editor.execCommand('mceInsertContent', false, text);
			} else if (typeof window.edInsertContent == 'function') {
				top.edInsertContent(top.edCanvas, text);
			} else {
				$canvas = window.jQuery(top.edCanvas);
				$canvas.val($canvas.val() + text);
			}
		}

		function close() {
			top.tb_remove();
		}

		function request(params) {
			params = params || {};
			params['action'] = 'amazonjs_search';
			$.each([$searchLocale, $searchIndex, $searchPage, $searchQuery], function (i, $field) {
				params[$field.attr('name')] = $field.val();
			});
			$.ajax({
				url: $form.attr('action'),
				dataType: 'json',
				data: params,
				beforeSend: function (xhr) {
					loading = true;
					$results.html('<span class="indicator"></span>');
					$form.find('input,select,button').attr('disabled', 'disabled');
				},
				success: function (data, textStatus, jqXHR) {
					onLoaded(data, params);
				},
				error: function (jqXHR, textStatus, errorThrown) {
					var responseText = (jqXHR && jqXHR.responseText) ? jqXHR.responseText : '';
					var message = textStatus;
					if (typeof(responseText) == 'string' && responseText.length > 0) {
						message = responseText;
					}
					else if (typeof(errorThrown) == 'string') {
						message = errorThrown;
					}
					else if (errorThrown && errorThrown.message) {
						message = errorThrown.message
					}
					onLoaded({
						success: false,
						message: message
					});
				}
			});
		}

		function onLoaded(data, params) {
			loading = false;
			$results.empty();
			$form.find('input,select,button').attr('disabled', null);
			if (!data || !data.items || data.items.length == 0) {
				if (data && data.success) {
					var $u = $('<div/>').addClass('error').html('<p>No Items</p>');
					$results.append($u);
				} else {
					var msg = (data) ? (data.message || 'Error') : 'Amazonjs Search Error';
					var $e = $('<div/>').addClass('error').html('<p>' + msg + '</p>');
					if (data.error_code) {
						var $errorCode = $('<strong/>').html(data.error_code);
						$('<p/>').append($errorCode).appendTo($e);
					}
					if (data.error_message) {
						$e.append($('<p/>').html(data.error_message));
					}
					if (data.error_body) {
						$e.append($('<pre/>').addClass('error_body').text(data.error_body));
					}
					if (data && data.ob) {
						$e.append($('<div/>').html(data.ob));
					}
					$results.append($e);
				}
				return;
			}
			var items = data.items, length = items.length;

			function find(asin) {
				for (var i = 0; i < length; i++) {
					if (items[i].ASIN == asin) return items[i];
				}
			}

			if (params.SearchIndex == 'Blended') {
				$.each(data.resultMap.SearchIndex, function (i, searchIndex) {
					var subItems = [];
					var asins = ('string' == typeof(searchIndex.ASIN)) ? [searchIndex.ASIN] : searchIndex.ASIN;
					$.each(asins, function (i, asin) {
						subItems.push(find(asin));
					});
					searchIndex.Label = (searchIndexes[searchIndex.IndexName]) ? searchIndexes[searchIndex.IndexName].label : searchIndex.IndexName;
					$results.append($.tmpl("amazonjsSearchIndexHeaderTpl", searchIndex));
					var $ul = $('<ul/>');
					$ul.append($.tmpl("amazonjsSearchItemTpl", subItems));
					$results.append($ul);
				});
			} else {
				var os = data.os;
				if (data.operation == 'ItemSearch') {
					os.prev = (os.Query.startPage > 1);
					os.next = (os.Query.startPage < os.totalPages);
					os.endIndex = Math.min(os.startIndex + os.itemsPerPage - 1, os.totalResults);
					var $pager = $.tmpl("amazonjsSearchPagerTpl", os);
					$results.append($pager);
				}
				var $ul = $('<ul/>');
				$ul.append($.tmpl("amazonjsSearchItemTpl", items));
				$results.append($ul);
				if ($pager) {
					$results.append($pager.clone());
				}
			}
			$results.find('.searchindex > a').click(function () {
				var index = $(this).attr('rel');
				$searchIndex.val(index);
				$form.submit();
				return false;
			});
			$results.find('.prev').click(function () {
				var page = parseInt($searchPage.val());
				if (page > 1) {
					$searchPage.val(page - 1);
					$form.submit();
				}
				return false;
			});
			$results.find('.next').click(function () {
				var page = parseInt($searchPage.val());
				$searchPage.val(page + 1);
				$form.submit();
				return false;
			});
			$results.find('.select').click(function () {
				var asin = $(this).attr('id').split('_')[1];
				showTemplateForm(selectedItem = find(asin));
				return false;
			});
		}

		var $container = $('#media_amazon'),
			$preview = $('#preview'),
			$previewCode = $('#preview_code'),
			$imageTemplateSelect = $('#select_template input.has_image'),
			$imageTemplateSelectLabel = $('#select_template label.has_image'),
			$templateSelect = $('#select_template input').change(function () {
				var shortcode = $(this).hasClass('shortcode'),
					html = $(this).hasClass('html');
				if (html) {
					var tplName = $(this).val() + 'AmazonItem';
					var $item = $.tmpl(tplName, selectedItem);
					$preview.empty().append($item);
					$previewCode.val($preview.html());
				} else { // shortcode
					var val = $(this).val();
					var tplName = 'shortCodeAmazonItem';
					selectedItem.EscapeTitle = selectedItem.Title.replace(/\[|\]/g, '');
					selectedItem.Tmpl = (val == 'shortCode') ? null : val.replace('shortCode', '');
					var $shortCode = $.tmpl(tplName, selectedItem);
					var $item = $.amazonjs.tmpl(selectedItem, $.amazonjs.formatTmplName(selectedItem.Tmpl));
					$preview.empty().append($item);
					$previewCode.val($shortCode[0].textContent);
					//console.log($item[0]);
				}
				$insert.attr({disabled: null});
			});

		// display template
		{
			$.template('linkAmazonItem', '<a href="${DetailPageURL}" title="${Title}" target="_blank">${Title}</a>');
			$.template('smallImageAmazonItem',
				[
					'<a href="${DetailPageURL}" title="${Title}" target="_blank">',
					'{{if SmallImage}}',
					'<img src="${SmallImage.src}" width="${SmallImage.width}" height="${SmallImage.height}" alt="${Title}"/>',
					'{{/if}}',
					'</a>'
				].join(''));
			$.template('mediumImageAmazonItem',
				[
					'<a href="${DetailPageURL}" title="${Title}" target="_blank">',
					'{{if MediumImage}}',
					'<img src="${MediumImage.src}" width="${MediumImage.width}" height="${MediumImage.height}" alt="${Title}"/>',
					'{{/if}}',
					'</a>'
				].join(''));
			$.template('largeImageAmazonItem',
				[
					'<a href="${DetailPageURL}" title="${Title}" target="_blank">',
					'{{if LargeImage}}',
					'<img src="${LargeImage.src}" width="${LargeImage.width}" height="${LargeImage.height}" alt="${Title}"/>',
					'{{/if}}',
					'</a>'
				].join(''));
			$.template('shortCodeAmazonItem', '[amazonjs asin="${ASIN}" locale="${CountryCode}"{{if Tmpl}} tmpl="${Tmpl}"{{/if}} title="${EscapeTitle}"]');
		}

		$insert.click(function () {
			insertToEditor($previewCode.val());
			//hideTemplateForm();
			close();
		});

		// template form
		function showTemplateForm(item) {
			$imageTemplateSelect.attr({disabled: (item.SmallImage) ? null : 'disabled'});
			if (item.SmallImage) {
				$imageTemplateSelectLabel.removeClass('disabled');
			} else {
				$imageTemplateSelectLabel.addClass('disabled');
			}
			$overlay.height($container.height() + 40).show();
			$preview.empty();
			$previewCode.val('');
			$templateSelect.attr('checked', null);
			$insert.attr({disabled: 'disabled'});
			$templateForm.show();
			var top = ($(window).height() / 2 - $templateForm.height() / 2) + $(window).scrollTop();
			$templateForm.css('top', top < 0 ? 0 : top);
			$('#template_amazonjs').attr('checked', 'checked').change();
		}

		function hideTemplateForm() {
			$overlay.hide();
			$templateForm.hide();
		}
	});
})(jQuery);
//-->
</script>
