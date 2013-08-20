(function ($) {
	if (!$) return;
	var isIE6 = ($.browser.msie && $.browser.version == '6.0');
	var resources = {};
	var resource = resources['en'] = {
		BookAuthor:'Author',
		BookPublicationDate:'PublicationDate',
		BookPublisher:'Publisher',
		NumberOfPagesValue:'${NumberOfPages} pages',
		ListPrice:'List Price',
		Price:'Price',
		PriceUsage:'Product prices and availability are accurate as of the date/time indicated and are subject to change. Any price and availability information displayed on [amazon.com or endless.com, as applicable] at the time of purchase will apply to the purchase of this product.',
		PublicationDate:'Publication Date',
		ReleaseDate:'Release Date',
		SalesRank:'SalesRank',
		SalesRankValue:'#${SalesRank}',
		RunningTime:'Run Time',
		RunningTimeValue:'${RunningTime} minutes',
		CustomerReviewTitle:'${Title} Customer Review',
		SeeCustomerReviews:'See Customer Reviews',
		PriceUpdatedat:'(at ${UpdatedDate})'
	};
	$.extend({
		amazonjs:{
			resource:resource,
			resources:resources,
			setLocale:function (locale) {
				var r = this.resources[locale];
				if (r) this.resource = r;
			},
			initTemplate:function () {
				var r = this.resource;
				var smallImageTemplate =
					[
						'{{if SmallImage}}',
						'<div class="amazonjs_image">',
						'<a href="${DetailPageURL}" title="${Title}" target="_blank">',
						'<img src="${SmallImage.src}" width="${SmallImage.width}" height="${SmallImage.height}" style="max-width:${SmallImage.width}px" alt="${Title}"/>',
						'</a>',
						'</div>',
						'{{/if}}'
					].join('');
				var mediumImageTemplate =
					[
						'{{if MediumImage}}',
						'<div class="amazonjs_image">',
						'<a href="${DetailPageURL}" title="${Title}" target="_blank">',
						'<img src="${MediumImage.src}" width="${MediumImage.width}" height="${MediumImage.height}" style="max-width:${MediumImage.width}px" alt="${Title}"/>',
						'</a>',
						'</div>',
						'{{/if}}'
					].join('');
				var largeImageTemplate =
					[
						'{{if LargeImage}}',
						'<div class="amazonjs_image">',
						'<a href="${DetailPageURL}" title="${Title}" target="_blank">',
						'<img src="${LargeImage.src}" width="${LargeImage.width}" height="${LargeImage.height}" style="max-width:${LargeImage.width}px" alt="${Title}"/>',
						'</a>',
						'</div>',
						'{{/if}}'
					].join('');
				var priceContentTemplate =
					[
						'{{if $item.isSale()}}',
						'<b>' + r.ListPrice + '</b><span class="amazonjs_listprice">${ListPrice.FormattedPrice}</span><br/>',
						'{{if OfferSummary.LowestNewPrice}}<b>' + r.Price + '</b>${OfferSummary.LowestNewPrice.FormattedPrice}{{/if}}',
						'<span>' + r.PriceUpdatedat + '</span>',
						'{{else}}',
						'<b>' + r.Price + '</b>${ListPrice.FormattedPrice}',
						'<span>' + r.PriceUpdatedat + '</span>',
						'{{/if}}'
					].join('');
				var priceTemplate = '{{if ListPrice}}<div class="amazonjs_price" title="' + r.PriceUsage + '">' + priceContentTemplate + '</div>{{/if}}';
				var priceLiTemplate = '{{if ListPrice}}<li class="amazonjs_price" title="' + r.PriceUsage + '">' + priceContentTemplate + '</li>{{/if}}';
				var reviewLinkTemplate = '<a href="${IFrameReviewURL}&TB_iframe=true&height=500&width=600" title="' + r.CustomerReviewTitle + '" target="_blank" class="amazonjs_review">' + r.SeeCustomerReviews + '</a>';

				this.partial = {
					smallImage:smallImageTemplate,
					mediumImage:mediumImageTemplate,
					largeImage:largeImageTemplate,
					price:priceTemplate
				};

				var defaultTemplates = {
					Small:[
						'<div class="amazonjs_item">',
						smallImageTemplate,
						'<div class="amazonjs_info" style="{{if SmallImage}}margin-left:${SmallImage.width}px;{{/if}}">',
						'<h4><a href="${DetailPageURL}" title="${Title}" target="_blank">${Title}</a></h4>',
						'<ul>',
						'{{if Creator}}<li>${Creator}</li>{{/if}}',
						'{{if Manufacturer}}<li>${Manufacturer}</li>{{/if}}',
						priceLiTemplate,
						'{{if PublicationDate}}<li><b>' + r.PublicationDate + '</b>${PublicationDate}</li>{{/if}}',
						'{{if SalesRank}}<li><b>' + r.SalesRank + '</b>' + r.SalesRankValue + '</li>{{/if}}',
						'</ul>',
						'</div>',
						reviewLinkTemplate,
						'<div class="amazonjs_footer"></div>',
						'</div>'
					],
					Music:[
						'<div class="amazonjs_item amazonjs_music">',
						mediumImageTemplate,
						'<div class="amazonjs_info" style="{{if MediumImage}}margin-left:${MediumImage.width}px;{{/if}}">',
						'<h4><a href="${DetailPageURL}" title="${Title}" target="_blank">${Title}</a></h4>',
						'<ul>',
						'{{if Artist}}<li>${Artist}</li>{{/if}}',
						'{{if Creator}}<li>${Creator}</li>{{/if}}',
						'{{if Label}}<li>${Label}</li>{{/if}}',
						priceLiTemplate,
						'{{if ReleaseDate}}<li><b>' + r.ReleaseDate + '</b>${ReleaseDate}</li>{{/if}}',
						'{{if SalesRank}}<li><b>' + r.SalesRank + '</b>' + r.SalesRankValue + '</li>{{/if}}',
						'{{if RunningTime}}<li><b>' + r.RunningTime + '</b>' + r.RunningTimeValue + '</li>{{/if}}',
						'</ul>',
						'</div>',
						reviewLinkTemplate,
						'<div class="amazonjs_footer"></div>',
						'</div>'
					],
					Book:[
						'<div class="amazonjs_item amazonjs_book">',
						mediumImageTemplate,
						'<div class="amazonjs_info" style="{{if MediumImage}}margin-left:${MediumImage.width}px;{{/if}}">',
						'<h4><a href="${DetailPageURL}" title="${Title}" target="_blank">${Title}</a></h4>',
						'<ul>',
						'<li><b>' + r.BookAuthor + '</b>${Author}</li>',
						priceLiTemplate,
						'<li><b>' + r.BookPublicationDate + '</b>${PublicationDate}</li>',
						'{{if SalesRank}}<li><b>' + r.SalesRank + '</b>' + r.SalesRankValue + '</li>{{/if}}',
						'<li><b>${Binding}</b>' + r.NumberOfPagesValue + '</li>',
						'<li><b>ISBN-10</b>${ISBN}</li>',
						'<li><b>ISBN-13</b>${EAN}</li>',
						'<li><b>' + r.BookPublisher + '</b>${Publisher}</li>',
						'</ul>',
						'</div>',
						reviewLinkTemplate,
						'<div class="amazonjs_footer"></div>',
						'</div>'
					],
					eBooks:[
						'<div class="amazonjs_item amazonjs_book">',
						mediumImageTemplate,
						'<div class="amazonjs_info" style="{{if MediumImage}}margin-left:${MediumImage.width}px;{{/if}}">',
						'<h4><a href="${DetailPageURL}" title="${Title}" target="_blank">${Title}</a></h4>',
						'<ul>',
						'<li><b>' + r.BookAuthor + '</b>${Author}</li>',
						priceLiTemplate,
						'<li><b>' + r.BookPublicationDate + '</b>${PublicationDate}</li>',
						'{{if SalesRank}}<li><b>' + r.SalesRank + '</b>' + r.SalesRankValue + '</li>{{/if}}',
						'<li><b>${Binding}</b>' + r.NumberOfPagesValue + '</li>',
						'<li><b>' + r.BookPublisher + '</b>${Publisher}</li>',
						'</ul>',
						'</div>',
						reviewLinkTemplate,
						'<div class="amazonjs_footer"></div>',
						'</div>'
					]
				};
				this.template(defaultTemplates);
				var me = this;
				$.each(this.addTemplateCallbacks, function (i, callback) {
					var t = callback.call(me, me.partial);
					if (t) me.template(t);
				});
			},
			addTemplateCallbacks:[],
			addTemplate:function (fn) {
				if (typeof(fn) == 'function') {
					this.addTemplateCallbacks.push(fn);
				}
			},
			template:function (templates) { // set up jQuery template
				$.each(templates, function (name, tmpl) {
					if (tmpl) {
						$.template('amazonjs' + name + 'Tpl', (typeof tmpl === 'string') ? tmpl : tmpl.join(''));
					}
				});
			},
			render:function (items) {
				var $items = [];
				this.initTemplate();
				$("a[rel='amazonjs']").each(function () {
					var classNames = $(this).attr('class').split(' '),
						c = classNames[0].split('_'),
						asin = c[1],
						countryCode = c[2],
						tmpl = (c.length > 3) ? c[3] : null,
						item = find(asin, countryCode);

					if (item) {
						var $item = $.amazonjs.tmpl(item, $.amazonjs.formatTmplName(tmpl));
						$(this).replaceWith($item.hide());
						var $review = $item.find('.amazonjs_review');
						if (!isIE6) {
							$item.css('position', 'relative');
						} else {
							$review.css({
								'float':'right',
								'position':'static',
								'marginRight':'32px'
							});
						}
						$review.click(function () {
							tb_show(this.title, this.href);
							this.blur();
							return false;
						});
						$items.push($item);
					} else {
						// add official link when amazon product is not fetched by using AWS API
						tmpl = 'Link' + countryCode.toUpperCase();
						item = {
							asins:asin,
							fc1:'000000',
							lc1:'0000FF',
							bc1:'000000',
							bg1:'FFFFFF',
							IS2:1,
							lt1:'_blank',
							f:'ifr',
							m:'amazon'
						};
						var $item = $.amazonjs.tmpl(item, $.amazonjs.formatTmplName(tmpl));
						$(this).replaceWith($item);
					}
				});
				fadeIn();
				function find(asin, countryCode) {
					for (var i = 0, length = items.length; i < length; i++) {
						if (items[i].ASIN == asin && items[i].CountryCode == countryCode) return items[i];
					}
				}

				function fadeIn() {
					if ($items.length > 0) {
						var $item = $items.shift();
						$item.fadeIn();
						setTimeout(fadeIn, 100);
					}
				}
			},
			formatTmplName:function (key) {
				return (key) ? 'amazonjs' + key + 'Tpl' : null;
			},
			formatNumber:function (val) {
				val += '';
				var x = val.split('.'),
					x1 = x[0],
					x2 = x.length > 1 ? '.' + x[1] : '',
					rgx = /(\d+)(\d{3})/;
				while (rgx.test(x1)) {
					x1 = x1.replace(rgx, '$1' + ',' + '$2');
				}
				return x1 + x2;
			},
			formatDateTime:function (timestamp) {
				var dt = new Date(timestamp * 1000);
				var Y = dt.getFullYear(),
					m = dt.getMonth() + 1,
					d = dt.getDate(),
					H = dt.getHours(),
					i = dt.getMinutes();
				if (m < 10) m = '0' + m;
				if (d < 10) d = '0' + d;
				if (H < 10) H = '0' + H;
				if (i < 10) i = '0' + i;
				return Y + '/' + m + '/' + d + ' ' + H + ':' + i;
			},
			getTemplate:function (item) {
				var defaultTmpl = this.formatTmplName('Small');
				if (item && item.ProductGroup) {
					var tmpl = this.formatTmplName(item.ProductGroup);
					return $.template[tmpl] || defaultTmpl;
				}
				return defaultTmpl;
			},
			prepareData:function (item) {
				item.PublicationDate = item.PublicationDate || item.ReleaseDate;
				item.Manufacturer = item.Manufacturer || item.Label;
				if (item.SalesRank) item.SalesRank = this.formatNumber(item.SalesRank);
				if (item.PublicationDate) item.PublicationDate = item.PublicationDate.replace(/-/g, '/');
				if (item.ReleaseDate) item.ReleaseDate = item.ReleaseDate.replace(/-/g, '/');
				if (item.Artist && item.Creator) {
					var Creator = [];
					var a = $.isArray(item.Artist) ? item.Artist : [item.Artist];
					var c = $.isArray(item.Creator) ? item.Creator : [item.Creator];
					$.each(c, function (i, value) {
						if ($.inArray(value, a) == -1) Creator.push(value);
					});
					item.Creator = (Creator.length) ? Creator : null;
				}
				item.UpdatedDate = this.formatDateTime(item.UpdatedAt);
				return item;
			},
			tmpl:function (item, tmpl) {
				item = this.prepareData(item);
				// overwrite ListPrice
				if (!item.ListPrice && item.OfferSummary) {
					item.ListPrice = item.OfferSummary.LowestNewPrice;
				}
				return $.tmpl($.template[tmpl] || this.getTemplate(item), item, {isSale:function () {
					if (this.data) {
						var lp = this.data.ListPrice, la = Number(lp.Amount),
							os = this.data.OfferSummary || {}, sp = os.LowestNewPrice || {}, sa = Number(sp.Amount);
						return (!isNaN(sa) && sa < la);
					}
					return false;
				}});
			}
		}
	});
})(jQuery);