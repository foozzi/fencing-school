// orderPosition
(function($){

	/**
	 * The function sends by POST positions for current list items to server.
	 */
	$.fn.orderPosition = function(url, o){
		var defaults = {
			data : {},
			onStop : null
		};
		var options = $.extend(defaults, o || {});
		return this.each(function(){
			var $ul = $(this), values = new Array();
			
			$ul.find('> li').each(function(){
				values[values.length] = $(this).attr('value');
			});
			
			$ul.sortable({
				stop : function(event,ui){
					if (!url) return true;
					var items = new Array();
					$ul.find('> li, > tr').each(function(){
						items[items.length] = $(this).attr('value');
					});
					// Check if sorting is changed
					if (items.join() == values.join()) return false;
					values = items;
					var postData = $.extend(options.data || {}, {ajax : 1, 'attachMethod' : 'none', 'items':items} );
					//$ul.parent().showLoading({'style' : 'new'});
					$.post(url, postData, function(res){
						//$ul.parent().hideLoading();
						//$ul.parent().jQueryUI();
						if (typeof options.onStop == 'function') options.onStop.call(this);
					}, 'json');
					return true;
				}
			});
		});
	};

})(jQuery);

// jForm, jAjaxForm, jFixedForm
(function($){
	
	var rules = {
		"Address"	: /^.{4,}$/,
		"Date"		: /^\d{2}\.\d{2}\.\d{4}$/,
		"Email"		: /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/,
		"Empty"		: function(value){
			if (this.tagName == 'SELECT') return $(this).find('> option[value!=]').size() == 0;
			return /^\s*$/.test(value);
		},
		"Int"		: /^\d+$/,
		"Login"		: /^[a-z0-9\|\-_]{3,30}$/i,
		"Name"		: /^[^\d]{2,}$/i,
		"Password"	: /^\S{4,}$/i,
		"Phone"		: /^[\d- \(\)]{7,}$/i,
		"Selected"	: function(value){
			if (this.tagName == 'INPUT'	&& $(this).attr('type') == 'checkbox') return $(this).is(':checked');
			if (this.tagName == 'SELECT') return $(this).val() != '0' && $(this).val() != '';
			return false;
		},
		"Slug"		: /^[\w\d\-_\#\?\.\@\&=;,]+$/i,
		"Link"		: /^[\w\d\-_\/\#\?\.\@\&=;,]+$/i,
		"Text"		: /\S{4,}/,
		"Title"		: /\S{2,}/,
		"Any"		: /\S+/,
		"Username"	: /^[a-z0-9\|\.\-_]{4,30}$/i,
		"Website"	: /^[\w\-_\d]{1,}[\w\-_\d\.]*\.[\w]{2,5}$/,
		"Year"		: function(value){ return parseInt(value) > 1900 && parseInt(value) < 2100 }
	};
	
	var convertion = {
		"Slug"		: function(value){
			return value.toLowerCase().replace(/\s/g, '-').replace(/[^\w\d\-_\#\?\.\@\&=;,]/gi, '').replace(/\-$/g, '').replace(/^\-/g, '');
		}
	};
	
	/**
	 * rel="validate(Phone|Empty)", rel="validate(Password)", rel="validate(Phone|Email)"
	 * rel="convert(Slug,#name)", rel="convert(Translit,input[name=Title])"
	 * rel="confirm(#password)", rel="confirm(input[name=Password])"
	 * rel="validate(Password);confirm(input[name=Password])"
	 */
	$.fn.jForm = function(o){
		var options = {
			testUrl		: null,
			testConvertFrom : null,
			visibleOnly : false,
			scroll : false,
			scrollPadding : 0,
			onChange	: function() {},
			onSubmit	: function() { return true; },
			fly : false
		};
		
		$.extend(options, o || {});
		
		return this.each(function(){
			if (this.tagName != 'FORM') return;
			
			var $form = $(this), $val = [], $conv = [], $conf = [];

			function goTop(){
				var top = null;
				if (options.scroll === true)
					top = $form.offset().top;
				else if (typeof options.scroll == 'number')
					top = options.scroll;
				else if (options.scroll === 'field' || options.scroll === 'input'){
					var o = $form.find(':input.error:visible:first').offset();
					top = o ? o.top : 0;
				}
				else if (options.scroll !== false)
					top = $(options.scroll).offset().top;
				if (top !== null) $('html, body').animate({scrollTop : top - options.scrollPadding + 'px'}, 'slow');
			}
			
			function testUnique(el){
				var $this = $(el), $span = $this.parents('.js-row:first').find('.js-test');
				$span.hide();
				if (options.fly) $span.addClass('flying');
				if ($this.is('.error') || !options.testUrl) return;
				$.get(options.testUrl, {"ajax" : 1, "field" : $this.attr('name'), "value" : $this.val()}, function(res){
					$span.removeClass('test-avail test-error test-busy');
					if (res.avail){
						$span.addClass('test-avail');
						if ($this.attr('reason') == 'busy') $this.attr('reason', '');
					} else {
						$span.addClass('test-busy');
						$this.addClass('error').attr('reason', 'test');
					}
					if (res.text) $span.text(res.text);
					$span.show();
				}, "json");
			}
			
			function validationTest(){
				if ($(this).is('[reason="confirm"]')) return;
				var unique = false, ok = false, arr = $(this).data('validationRules') || [];
				for (var i = 0; i<arr.length; i++){
					if (arr[i] == 'unique') {
						unique = true;
						continue;
					}
					if (rules[arr[i]]){
						var rule = rules[arr[i]];
						if (rule instanceof RegExp){
							ok = ok | rule.test($(this).val());
						} else {
						//if (typeof rule == 'function'){ // does not work in chrome
							ok = ok | rule.call(this, $(this).val());
						}
					} else if (arr[i].substr(0,8) == 'ReferTo=') {
						ok = $(this).parents('form:first').find(':input[name="'+arr[i].substr(8)+'"]').val() == $(this).val();
					}
				}
				var $span = $(this).parents('.js-row:first').find('.js-error');
				if (ok){
					$(this).removeClass('error').attr('reason', '');
					$span.removeClass('error');
					if (!options.fly) $span.hide();
				} else {
					$(this).addClass('error').attr('reason', 'error');
					$span.addClass('error');
					if (!options.fly) $span.show();
				}
				if (unique) testUnique(this);
				
				$form.removeClass('js-failed');
				if ($form.find(':input.error').size() > 0) $form.addClass('js-failed');
				options.onChange.call(this);
			}
			
			function convertionDo(){
				var arr = $(this).data('convertionRule'), $el = $(arr[1]);
				if (!convertion[arr[0]]) return;
				var value = convertion[arr[0]].call(this, $(this).val());
				$el.each(function(){
					if (this.tagName == 'INPUT' || this.tagName == 'TEXTAREA') $(this).val(value); else $(this).text(value);
				});
			}
			
			function confirmDo(){
				var field = $(this).data('confirmField');
				if (!field || $(this).is('[reason="error"]')) return;
				var $el = field.substr(0, 1) == '#' ? $(field) : $form.find(field);
				if ($el.size() == 0) return;
				var $span = $(this).parents('.js-row:first').find('.js-confirm');
				if ($el.val() == $(this).val()){
					$(this).removeClass('error').attr('reason', '');
					$span.removeClass('error');
					if (!options.fly) $span.hide();
				} else {
					$(this).addClass('error').attr('reason', 'confirm');
					$span = $span.addClass('error');
					if (!options.fly) $span.show();
				}
			}
			
			function checkStatus(){
				$form.removeClass('js-failed');
				var v = options.visibleOnly ? ':visible' : '';
				if ($form.find(':input.error'+v).size() > 0){
					goTop();
					$form.addClass('js-failed');
					return false;
				}
				return true;
			}

			$form.find(':input').each(function(){
				if (!$(this).attr('rel')) return;
				var arr = $(this).attr('rel').split(';');
				for (var i = 0; i < arr.length; i++){
					if (/^validate\(.+\)$/.test(arr[i])){
						var str = arr[i].replace('validate(', '').replace(')', '');
						$(this).data('validationRules', str.split('|'));
						$val.push(this);
					} else if (/^convert\(.+\)$/.test(arr[i])){
						var str = arr[i].replace('convert(', '').replace(')', ''), arr = str.split(',');
						if (arr.length != 2) continue;
						var $el = arr[1].substr(0, 1) == '#' ? $(arr[1]) : $form.find(arr[1]);
						$(this).data('convertionRule', [arr[0], $el]);
						$conv.push(this);
					} else if (/^confirm\(.+\)$/.test(arr[i])){
						var str = arr[i].replace('confirm(', '').replace(')', '');
						$(this).data('confirmField', str);
						$conf.push(this);
					}
				}
			});
			
			$.each($val, function(i, el){
				$(el).bind('change', validationTest);
				if (options.fly){
					var $row = $(el).parents('.js-row:first');
					$(el).hover(function(){
						var $span = $row.find('.js-'+$(this).attr('reason'));
						if (!$span.is('.flying')) $span.addClass('flying').show();
						if ($(this).hasClass('error')) $row.addClass('hover');
					}, function(){
						var $span = $row.find($(this).attr('reason') == 'error' ? '.js-error' : '.js-test');
						if ($(this).hasClass('error')) $row.removeClass('hover');
					});
				}
			});
			
			$.each($conv, function(i, el){
				$(el).bind('change', convertionDo);
			});
			
			$.each($conf, function(i, el){
				$(el).bind('change', confirmDo);
			});
			
			if ($form.find(':submit').size() > 1 && $form.find(':submit.default').size() == 1){
				$form.find('input').keypress(function(e){
					if (e.which && e.which == 13){
						$form.find(':submit.default').click();
						return false;
					}
					return true;
				});
			}
			
			$form.submit(function(){
				$.each($val, function(i, el){
					validationTest.call(el);
				});
				$.each($conv, function(i, el){
					convertionDo.call(el);
				});
				$.each($conf, function(i, el){
					confirmDo.call(el);
				});
				
				var result = true;
				
				checkStatus();
				result = options.onSubmit.call(this);
				if (!checkStatus()) result = false;

				return result;
			});

			$form.find(':input.first').focus();

		});
	}
	
	$.fn.jAjaxForm = function(o){

		var options = {
			onClose : function() {},
			onMessageClose : function() {},
			onResult : function() {},
			startSubmit : function() {},
			completeSubmit : function() {},
			popup : false,
			timeout : 5000
		};
		$.extend(options, o || {});
		
		return this.each(function(){
			
			var $form = $(this);

			function processMsg(msg, type){
				if (options.popup){
					if (type == 'message') showMessage(msg); else showError(msg);
					return null;
				}
				if (typeof msg == 'object') msg = msg.join("<br />");
				return $('<blockquote class="'+type+'" />').html(msg).appendTo($form.find('div.response').show());
			}

			function processResult(res){
				var timeout = res.timeout || options.timeout;
				options.onResult.call($form.get(0), res);
				if (res.result){
					if (res.msg) {
						if (options.popup){
							showMessage(res.msg);
						} else {
							$form.find('fieldset').slideUp('fast');
							processMsg(res.msg, 'message');
						}
					}
					if (res.html){
						$(res.html).appendTo($form.find('div.response'));
						$form.find('fieldset').slideUp('fast');
					}
					if (res.url){
						$.timer(timeout, function(timer){
							timer.stop();
							redirect(res.url);
						});
					}
				} else if (res.msg){
					var $b = processMsg(res.msg, 'error');
					if ($b){
						$.timer(timeout, function(timer){
							timer.stop();
							$b.slideUp(function(){
								$b.remove();
								options.onMessageClose.call($form.get(0), false);
							})
						});
					}
				}
				if (res.callback){
					if (res.callback == 'close') {
						$form.find('fieldset').hide();
						if (timeout) $.timer(timeout, function(timer){
							timer.stop();
							options.onClose.call($form.get(0), res);
						}); else {
							options.onClose.call($form.get(0), res);
						}
					}
				}
			}

			if ($form.attr('enctype') && $form.attr('enctype').toLowerCase() == 'multipart/form-data'){
				// uploading files through iframe
				var target = $form.attr('target') || ($form.attr('id')+'-frame');
				var $frame = $('#'+target);
				if (!$frame.size()){
					$frame = $('<iframe frameborder="0" width="0" height="0" style="display: none" />')
						.attr('src', $form.attr('action')).attr('name', target);
					$frame.prependTo($('body:first'));
					$form.attr('target', target);
				}

				$frame.load(function(){
					var html = $frame.contents().find('body:first').html();
					if (html.substr(0,1) == '{') {
						var res = eval("(" + html + ")");
						options.completeSubmit.call($form.get(0), res);
						processResult(res);
					}
				});

				if (!$form.is('.multipart-form-data')) $form.addClass('multipart-form-data');
			}

			$form.jForm(o).submit(function(){
				if ($form.is('.js-failed')) return false;
				if ($form.is('.multipart-form-data')){
					options.startSubmit.call(this);
					return true;
				}
				
				options.startSubmit.call(this);
				var self = this;
				$.post($form.attr('action'), $form.getFields({"ajax" : 1, "submit" : 1}), function(res){
					options.completeSubmit.call(self, res);
					processResult(res);
				}, "json");
				return false;
			});

		});
	}

	$.fn.jFixedForm = function(o){

		var options = {
			height : null
		};
		$.extend(options, o || {});

		return this.each(function(){
			var $form = $(this), $div = $form.find('fieldset'), $btns = $form.find('div.form-buttons');

			var m = options.height || 0;
			$form.find('ul.tabs').each(function(){
				var $items = $(this).next('div.items');
				if (m == 0) {
					$items.find('> div').each(function(){
						if (m < $(this).height()) m = $(this).height();
					});
				}
				if (m) $items.find('> div').height(m);
			});
			if ($form.is('.ajax-form')) $form.jAjaxForm(options); else $form.jForm();
			if ($btns.width() < $form.width()){
				$div.width($form.width() - $btns.outerWidth(true));
				$btns.height(m || $form.height());
				if (!$form.is('.fixed-form')) $form.addClass('fixed-form');
			}

			$btns.find('.cancel').click(function(){
				redirect($(this).attr('href'));
				return false;
			})

			$(document).keyup(function(e){
				if (e.which == 27){
					$btns.find('.cancel').click();
				}
			})
		});

	}
	
})(jQuery);

// jFilterList, jInfinityList, jStratightList
(function($){

	$.fn.jFilterList = function(o){
		var options = {
			'wrapper'	: null,
			'orderby'	: 'Id desc',
			'posUrl'	: null,
			'txtDelete'	: 'Delete?',
			onDelete 	: function() { return true; },
			onInit		: function() {},
			onMove		: function() {}
		};
		$.extend(options, o || {});
		
		return this.each(function(){
			var $list = $(this), $wrap = null, orderby = options.orderby;
			
			if (typeof options.wrapper == 'object' || options.wrapper.substr(0, 1) == '#')
				$wrap = $(options.wrapper);
			else
				$wrap = $list.parents(options.wrapper+':first');
			
			if (!$wrap || $wrap.size() == 0) $wrap = $('body');
			
			$list.find('a.orderby').click(function(){
				var arr = $(this).attr('href').split('#'), value = arr[1], form = false;
				value += '-'+($(this).is('.orderby-asc') ? 'desc' : 'asc')
				
				$wrap.find('form.filter-form:first').each(function(){
					$(this).append('<input type="hidden" name="sort" value="'+value+'" />');
					$(this).submit();
					form = true;
				});
				if (!form){
					$(this).attr('href', '?sort='+value);
					return true;
				}
				return false;
			});
			
			var arr = orderby.split(' ');
			$list.find('a.orderby[href$=#'+arr[0]+']').each(function(){
				$(this).addClass('orderby-'+(arr[1] && arr[1] == 'desc' ? 'desc' : 'asc'));
			});
			
			$wrap.find('form.filter-form select').change(function(){
				$(this).parents('form:first').submit();
			});
			
			$list.delegate('a[href$="#delete"]', 'click', function(){
				if (!confirm(options.txtDelete)) return false;
				var $this = $(this), $li = $this.parents('li:first');
				if (!$li.size()) $li = $this.parents('tr:first');
				$.post($(this).attr('href'), {ajax : 1}, function(res){
					if (res.result) {
						if (options.onDelete.call($this.get(0), res) !== false){
							$li.remove();
						}
					}
					if (res.msg) alert(res.msg);
				}, 'json');
				return false;
			});
	
			if (options.posUrl && $.fn.orderPosition){
				$list.find('.ui-sortable').orderPosition(options.posUrl, {onStop : options.onMove});
			}
	
			$wrap.find('form.filter-form').each(function(){
				options.onInit.call(this);
			});
		
		});
	}	

	$.fn.jInfinityList = function(o){

		var options = {
			offset : 0,
			rowsLimit : 50,
			url : null,
			data : {},

			getPostData : function() { return {}; },
			onInit : function() {},
			onLoad : function() {}
		};

		$.extend(options, o || {});

		return this.each(function(){
			var $list = $(this), offset = options.offset, stopScrolling = false;

			function rawLoad(data, clear){
				if ($list.is('.list-loading')) return false;

				data = data || {};
				clear = clear || false;
				if (clear) offset = 0;
				$.extend(data, options.data, {offset : offset, limit : options.rowsLimit}, options.getPostData.call($list.get(0)));
				$list.addClass('list-loading');
				$.post(options.url, data, function(res){
					if (res.result){
						if (clear) $list.html('');
						offset = res.offset ? res.offset : (offset + options.rowsLimit);
						if (res.rows && res.rows < offset) stopScrolling = true;
						$list.append(res.html);
						options.onLoad.call($list.get(0));
					}
					$list.removeClass('list-loading');
				}, "json");
			}

			var load = function(data){
				stopScrolling = false
				rawLoad(data);
			}

			var reload = function(data){
				stopScrolling = false
				rawLoad(data, true);
			}

			$list.data('jInfinityList', {
				load : load,
				reload : reload
			});

			$(window).scroll(function(){
				if ($(window).height() + $(window).scrollTop() >= $list.offset().top + $list.height() - 100){
					if (!stopScrolling) load();
				}
			});

			options.onInit.call($list.get(0));
		});
	};
	
	$.fn.jStraightList = function(o){
		var options = {
			rows	: 4,
			limit	: 20,
			lightbox : false
		};
		$.extend(options, o || {});
		
		return this.each(function(){
			var $wrap = $(this), $list = $wrap.find('div.js-items'), $pages = $wrap.find('div.js-pages'),
				limit = options.limit, rows = options.rows;
			
			function initImages(){
				if (!options.lightbox) return;
				if ($.ui.rlightbox){
					if ($.ui.rlightbox.global.sets.a && $.ui.rlightbox.global.sets.a.length > 0){
						$.ui.rlightbox.global.sets = {};
					}
					$list.find('a.lightbox').rlightbox({loop : true, preventSameUrl : true});
				} else if ($.fn.prettyPhoto) {
					$list.find('a[rel^="prettyPhoto"]').prettyPhoto();
				}
			}

			function initPages(){
				$pages.find('a').unbind('click').click(function(){
					go(this);
					return false;
				});
			}

			function drawPages(){
				var size = parseInt($list.find('#list-size').text()), $ul = $pages.find('ul'), 
					current = $($ul.find('> li')).index($ul.find('> li.active')) + 1,
					pages = Math.ceil(size / limit);

				if ($pages.find('li').size() != pages){
					$ul.html('');
					for (var i = 0; i < pages; i++){
						var p = i + 1;
						$('<li value="'+p+'"><a href="?page='+p+'#'+p+'">'+p+'</a></li>').appendTo($ul);
					}

					$ul.find('> li.active').removeClass('active');
					$ul.find('> li:eq('+(current - 1)+')').addClass('active');
					if (pages == 1) $pages.hide(); else $pages.show();

					initPages();
				}
				if (current > pages){
					while (current > pages) current--;
					$ul.find('> li.active').removeClass('active');
					$ul.find('> li:eq('+(current - 1)+')').addClass('active');
				}
			}

			function initWidth(force){
				force = force || false;
				var cols = Math.floor($list.width() / $list.find('.item:first').width());
				if (limit == cols * rows && !force) return;
				limit = cols * rows;
				drawPages();
				$pages.find('li.active a').click();
			}

			// public function
			var go = function(el, extra){
				extra = extra || {};
				var data = {};
				$.extend(data, extra, {"ajax" : 1, "limit" : limit});
				var url = '', $el = null;
				if (typeof el == 'object'){
					$el = $(el);
					url = $el.is('form') ? $el.attr('action') : $el.attr('href');
				} else {
					url = el;
				}
				$.get(url, data, function(html){
					$list.html(html);
					if ($el){
						if ($el.is('form')){
							$pages.find('li.active').removeClass('active');
							$pages.find('li:first').addClass('active');
						} else {
							$el.parents('ul:first').find('li.active').removeClass('active');
							$el.parents('li:first').addClass('active');
						}
					}
					initImages();
					drawPages();
				}, 'html');
			}
			
			$wrap.data('jStraightList', {
				"go" : go
			});
			
			initPages();	
			initImages();
			initWidth(true);

			var windowResizing = false;

			$(window).resize(function(){
				if (windowResizing) return;
				windowResizing = true;
				setTimeout(function(){
					windowResizing = false;
					initWidth();
				}, 500);
			});

		});
	}

})(jQuery);

// jDialog, jFixedScroll, jSmallTable, jTabs
(function($){

	$.fn.jDialog = function(o){

		var action = null, 
			options = {
				fixed : true,
				hideScroll : false,
				calcLeft : true,
				onInit : function() {},
				onShow : function() {}
			};
		if (typeof o == 'object') $.extend(options, o || {}); else action = o;

		function show(el){
			var options = $(el).data('jDialogOptions') || options;
			var $box = $(el).show(), $body = $box.find('> .dialog-body'), $mask = $box.find('> .dialog-mask'),
				left = parseInt(($(window).width() - $body.outerWidth(true)) / 2), 
				top = parseInt(($(window).height() - $body.outerHeight(true)) / 2);
			$box.css('position', 'absolute').css('left', 0).css('top', 0).css('width', '100%').css('height', $(document).height());
			if (!options.fixed) top += $(window).scrollTop();
			$body.css('top', top);
			if (options.fixed) $body.css('position', 'fixed');
			if (options.calcLeft) $body.css('left', left);

			if (options.hideScroll) $('body').css('overflow', 'hidden');
			options.onShow.call(el);
		}

		function hide(el){
			var options = $(el).data('jDialogOptions');
			$(el).hide();
			if (options.hideScroll) $('body').css('overflow', 'auto');
		}

		function destroy(el){
			var options = $(el).data('jDialogOptions');
			$(el).remove();
			if (options.hideScroll) $('body').css('overflow', 'auto');
		}

		return this.each(function(){

			switch (action){
				case 'show':
					show(this);
					return true;

				case 'hide':
					hide(this);
					return true;

				case 'destroy':
					destroy(this);
					return true;
			}
			$(this).data('jDialogOptions', options);
			options.onInit.call(this);
		});
	}

	var fixedScrollId = 0;

	$.fn.jFixedScroll = function(o){

		var options = {
			calcTop : false,
			dependent : false,
			onInit : function() {},
			onShow : function() {},
			onHide : function() {}
		}
		$.extend(options, o || {});

		return this.each(function(){
			var $div = $(this), offset = $(this).offset(), isTable = $div.get(0).tagName == 'TABLE';

			if (isTable && $div.find('> thead').size() == 0) return true;
			$div.data('fixedScrollId', ++fixedScrollId);

			var $fix = getClone(true); // make clone

			offset.y = 0;
			if (options.calcTop){
				var top = 0;
				for (var i = 1; i < $div.data('fixedScrollId'); i++) top += $('#fixed-scroll'+i).outerHeight();
				offset.y = top;
				offset.top -= top;
				$fix.css('top', offset.y + 'px');
			}

			function getClone(hidden){
				hidden = hidden || false;
				var $fix = $('#fixed-scroll'+$div.data('fixedScrollId'));
				if ($fix.size() == 0){
					$fix = $div.clone(true);
					if (isTable) $fix.find('> tbody, > tfoot').remove();
					$fix.attr('id', 'fixed-scroll'+$div.data('fixedScrollId')).width($div.width())
						.css('position', 'fixed').css('left', offset.left + 'px').prependTo($('body:first'));
					if (hidden) $fix.hide();

					if (options.dependent){
						$fix.find(':input').change(function(){
							$div.find(':input[name="'+$(this).attr('name')+'"]').val($(this).val()).trigger('change');
						});
						$div.find(':input').change(function(){
							$fix.find(':input[name="'+$(this).attr('name')+'"]').val($(this).val());
						});
					}

					options.onInit.call($fix.get(0), $div.get(0));
				}
				return $fix;
			}

			function showFixed(){
				var $clone = getClone();			
					$clone.css('left', $div.offset().left + 'px') // fixing left position (FF infinity list)
					.show();
				options.onShow.call($clone.get(0));
			}

			function hideFixed(){
				var $clone = getClone();
				$clone.hide();
				options.onHide.call($clone.get(0));
			}

			$(window).scroll(function(){
				if ($(window).scrollTop() > offset.top){
					showFixed();
				} else {
					hideFixed();
				}
			});
		});
	};

	$.fn.jSmallTable = function(o){
		
		var options = {
			sortable	: true,
			hideOnDelete : false,
			checkboxImg : null,
			buttonAddRow : null,

			onRowAdd : function(tr) { return $(tr).clone(); },
			onRowAdded : function() {}
		};
		$.extend(options, o || {});
		
		return this.each(function(){
			var $table = $(this);

			$table.find('> tbody').sortable();

			function addRow(){
				var $tr = $table.find('> tbody > tr:last'), req = true;
				$tr.find(':input.required').each(function(){
					if ($(this).val() == ''){
						req = false;
						return false;
					}
				});
				if ($tr.next('tr').size() == 0 && req){
					var $n = options.onRowAdd.call(this, $tr.get(0));
					$n.find('input').val('');
					$n.addClass('new-row').appendTo($table.find('> tbody'));
					$table.find('> tbody tr:last :input:first').focus();
					initRows();

					options.onRowAdded.call($n.get(0), $table.get(0));
				}
			}

			var initRows = function(){
				$table.find(':input:last').unbind('keydown').keydown(function(e){
					if (e.keyCode == 9 && !e.shiftKey){
						addRow();
					}
				});

				$table.find('input[name*="Delete"]').unbind('change').change(function(){
					if ($table.find('> tbody tr').size() > 1) {
						$(this).parents('tr:first').each(function(){
							if (options.hideOnDelete && !$(this).is('.new-row')) $(this).hide(); else $(this).remove();
						});
						initRows();
					}
				});
				
				if (options.checkboxImg){
					$table.find('input[name*="Delete"]').each(function(){
						var $p = $(this).parent();
						if (!$p.is('.icon-wrap')){
							$(this).wrap('<a class="icon-wrap" href="#delete-row" />');
							$p = $(this).parent();
							$('<img src="'+options.checkboxImg+'" alt="" />').appendTo($p);
							$p.find('input').hide();
						}
					});
					
					$table.find('a[href$="#delete-row"]').unbind('click').click(function(){
						$(this).parent().find('input').attr('checked', true).change();
						return false;
					});
				}
			}

			initRows();

			var fixHelper = function(e, ui) {
				ui.children().each(function() {
					$(this).width($(this).width());
				});
				return ui;
			};		

			if (options.sortable) $table.find('> tbody').sortable({
				helper : fixHelper
			});

			if (options.buttonAddRow) $(options.buttonAddRow).click(function(){
				addRow();
				return false;
			});

		})
	}

	$.fn.jTabs = function(o){

		var options = {
			currentClass : 'current',
			prefix : '',
			onChange : function(){}
		};
		$.extend(options, o || {});

		return this.each(function(){
			var $ul = $(this);

			$ul.find('li a').click(function(){
				$ul.find('li.'+options.currentClass).removeClass(options.currentClass);
				$ul.find('li a').each(function(){
					var a = $(this).attr('href').split('#');
					if (a[1]) $('#'+options.prefix+a[1]).hide();
				});
				var a = $(this).attr('href').split('#'), tab = '#'+options.prefix+a[1];
				if (a[1]) $(tab).show().find(':input:first').focus();
				$(this).parent().addClass(options.currentClass);

				options.onChange.call($(tab).get(0), this);
			});

			if (location.hash){
				var $a = $ul.find('li a[href$="'+location.hash+'"]');
				if ($a.size()) $a.click(); else $ul.find('li:first a').click();
			} else {
				$ul.find('li:first a').click();
			}
		});
	}

})(jQuery);

// jUniqueURL
(function($){

	$.fn.jUniqueURL = function(o){
		var options = {
			'affix'		: '',
			'relatedEl'	: null,
			'url'		: null,
			'id'		: null,
			'type'		: null,
			'transFrom'	: "РђР‘Р’Р“Р”Р•РЃР–Р—РР™РљР›РњРќРћРџР РЎРўРЈР¤РҐР¦Р§РЁР©РЄР«Р¬Р­Р®РЇР°Р±РІРіРґРµС‘Р¶Р·РёР№РєР»РјРЅРѕРїСЂСЃС‚СѓС„С…С†С‡С€С‰СЉС‹СЊСЌСЋСЏ",
			'transTo'	: ['A','B','V','G','D','E','JO','ZH','Z','I','J','K','L','M','N','O','P','R','S','T','U','F','H','C','CH','SH','SHH','','I','','E','YU','YA',
							'a','b','v','g','d','e','jo','zh','z','i','j','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','shh','','i','','e','yu','ya'],
			'expr'		: /[^\w\_\-]/g
		};
		$.extend(options, o || {});
		
		return this.each(function(){
			//if (!options.url || !options.type) return false;
			
			var $this = $(this);
			
			function translit(str, from, to){
				var res = ''; 
				for(var i = 0; i < str.length; i++) { 
					var char = str.charAt(i), index = from.indexOf(char); 
					if (index >= 0) res += to[index]; else res += char;
				} 
				return res; 				
			}
			
			function buildLink(){
				var str = $(options.relatedEl).val().replace(/\s+$/g, '');
				if (options.transFrom) str = translit(str, options.transFrom, options.transTo);
				str = str.replace(/\s/g, '-');
				str = str.replace(options.expr, '');
				$this.val('/'+str.toLowerCase()+options.affix).change();
			}
			
			$this.change(function(){
				if ($this.val() == '' || !options.url) return;
				$.post(options.url, {"ajax" : 1, "type" : options.type, "id" : options.id, "url" : $this.val()}, function(res){
					$this.parent().find('span').hide();
					if (res.status == 'avail' && options.id == 0 || res.status != 'avail'){
						$this.parent().find('span.'+res.status).show();
					}
				}, "json");
			});
			
			$(options.relatedEl).change(function(){
				if ($(this).val() != '' && $this.val() == '') buildLink();
			}).change();
			
			return true;
		});
	}
	

})(jQuery);