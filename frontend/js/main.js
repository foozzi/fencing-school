function number_format (number, decimals, dec_point, thousands_sep) {
  number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
  var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',
    toFixedFix = function (n, prec) {
      var k = Math.pow(10, prec);
      return '' + Math.round(n * k) / k;
    };
  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '').length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1).join('0');
  }
  return s.join(dec);
}

function redirect(link){
	location.href = link;
}

function showMessage(msg){
	alert(msg);
}

function showError(msg){
	if (typeof msg == 'object'){
		var arr = msg;
		msg = '';
		for (var i in arr) msg += arr[i]+"\n";
	}
	alert(msg);
}

function showNotify(msg, type, timeout){
	if (!type) type = 'msg';
	timeout = timeout ? timeout : 5000;
	var $notify = $('#notify-message');
	if (!$notify.size()) {
		$notify = $('<div id="notify-message"><div class="mask"></div><div class="message"></div></div>');
		$('body:first').prepend($notify);

		$notify.click(function(){
			$notify.fadeOut();
			return false;
		});
	}
	$notify.find('.message:first').removeClass('msg err warn').addClass(type).text(msg);
	$notify.fadeIn();
	$.timer(timeout, function(timer){
		timer.stop();
		if ($notify.is(':visible')) $notify.fadeOut();
	});
}


(function($){
	
	$.fn.outerHTML = function() {
		return $(this).clone().wrap('<div />').parent().html();
	}
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
				'stop' : function(event,ui){
					if (!url) return true;
					var items = new Array();
					$ul.find('> li').each(function(){
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
	
	/**
	 * The function returns object with all fields values in current container.
	 */
	$.fn.getFields = function(obj){
		var result = obj || {};
		this.each(function(){
			var $this = $(this);
			if (this.tagName=='LI') result['li-value-id'] = $this.attr('value');
			
			$this.find('input:text,input:password,input:radio:checked,input[type="hidden"],textarea,select').each(function(){
				if (!$(this).is(':disabled')) result[ $(this).attr('name') ] = $(this).val();
			});
			$this.find('input:checkbox:checked').each(function(){
				if ($(this).is(':disabled')) return;
				var size = $this.find('input[name="'+$(this).attr('name')+'"]').size();
				if (!result[ $(this).attr('name') ]){
					result[ $(this).attr('name') ] = size > 1 ? [ $(this).val() ] : $(this).val();
				} else {
					if (size > 1 && $(this).attr('name').substr(-2) == '[]')
						result[ $(this).attr('name') ].push($(this).val());
					else
						result[ $(this).attr('name') ] = $(this).val();
				}
			});
		});
		return result;
	}
	
	$.fn.serializeObject = function() {
		var arrayData, objectData;
		arrayData = this.serializeArray();
		objectData = {};

		$.each(arrayData, function() {
			var value;

			if (this.value != null) {
				value = this.value;
			} else {
				value = '';
			}

			if (objectData[this.name] != null) {
				if (!objectData[this.name].push) {
					objectData[this.name] = [objectData[this.name]];
				}

				objectData[this.name].push(value);
			} else {
				objectData[this.name] = value;
			}
		});

		return objectData;
	};	
	
	$.fn.formAuthPing = function(url){
		return this.each(function(){
			var values = {}, $inputs = $(this).find('input[type="text"], textarea, select');
			
			function pingServer(){
				$.post(url, {ajax : 1, ping : 'auth'}, function(res){
					
				}, 'json');
			}
			
			$inputs.each(function(){
				values[ $(this).attr('name') ] = $(this).val();
			});
			
			$.timer(60000, function(timer){
				var ping = false;
				$inputs.each(function(){
					if (values[ $(this).attr('name') ] != $(this).val()){
						ping = true;
						values[ $(this).attr('name') ] = $(this).val();
					}
				});
				if (ping) pingServer();
			});
		});
	}
	
	$.browserPrint = function(){
		var res = '';
		res += screen.width + 'x' + screen.height + "\n";
		res += (navigator.userAgent || '') + "\n";
		res += (navigator.vendor || '') + ' ' + (navigator.vendorSub || '') + "\n";
		res += (navigator.platform || '') + "\n";
		if (navigator.plugins) for (var k in navigator.plugins){
			res += (navigator.plugins[k].filename || '') + "; ";
			res += (navigator.plugins[k].name || '') + "; ";
			res += (navigator.plugins[k].version || '') + "\n";
		}
		if (navigator.mimeTypes) for (var k in navigator.mimeTypes){
			res += (navigator.mimeTypes[k].type || '') + "\n";
		}
		return res;
	}
	
	$.fn.pollBox = function(){
		return this.each(function(){
			var $box = $(this).parent(), url = $(this).attr('href');
			
			function loadBox(){
				$.post(url, {ajax : 1, print : $.browserPrint(), action : 'request'}, function(res){
					if (res.result){
						$box.html(res.html);
						initBox();
					}
				}, 'json');
			}
			
			function initBox(){
				$box.find('form').submit(function(){
					$.post(url, {ajax : 1, print : $.browserPrint(), action : 'post', value : $box.find('input[name="vote"]:checked').val()}, function(res){
						if (res.result){
							$box.html(res.html);
						}
					}, 'json');
					return false;
				});
			}
			
			loadBox();
			
		});
	}
	
	$.fn.jUniqueURL = function(o){
		var options = {
			'relatedEl'	: null,
			'url'		: null,
			'id'		: null,
			'type'		: null,
			'expr'		: /[^\w\_\-]/g
		};
		$.extend(options, o || {});
		
		return this.each(function(){
			if (!options.url || !options.type) return false;
			
			var $this = $(this);
			
			function buildLink(){
				var str = $(options.relatedEl).val().replace(/\s+$/g, '');
				str = str.replace(/\s/g, '-');
				str = str.replace(options.expr, '');
				$this.val('/'+str.toLowerCase()).change();
			}
			
			$this.change(function(){
				if ($this.val() == '') return;
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
	
	$.fn.jHintInput = function(o){
		
		return this.each(function(){
			var $this = $(this), value = $this.val();
			
			if (!$this.hasClass('ui-hint-input')) $this.addClass('ui-hint-input');
			
			function _showHint(){
				if ($this.val() == '' && value != ''){
					$this.val(value).addClass('ui-hint-input');
				}
			}
			
			function _hideHint(){
				if ($this.val() == value) $this.val('');
				$this.removeClass('ui-hint-input');
			}
			
			$this.bind('removeHint', function(){
				_hideHint();
				value = '';
			});
			
			$this.bind('hideHint', function(){
				_hideHint();
			});
			
			$this.bind('showHint', function(){
				_showHint();
			});
			
			$this.click(function(){
				_hideHint();
			});
			
			$this.focus(function(){
				_hideHint();
			});
			
			$this.blur(function(){
				_showHint();
			});
			
			$this.change(function(){
				_hideHint();
			});
			
			if ($this.hasClass('no-spaces')){
				$this.keyup(function(){
					$(this).val($(this).val().replace(' ', ''));
				});
			}
			
			$this.data('jHintInput', {
				val : function(){
					return $this.val() == value ? '' : $this.val();
				}
			});
			
		});
		
	}

	$.fn.showMessage = function(msg, o){
		alert(msg);
	}
	
	$.fn.showLoading = function(o){
		return this.each(function(){
			var $div = $(this), $load = $div.find('div.loading-box');
			
			if (!$load.size()){
				$load = $('<div class="loading-box"><div class="mask"></div><div class="icon"><div class="image"></div></div></div>');
				$load.appendTo($div);
				if ($div.css('position') != 'relative' && $div.css('position') != 'absolute'){
					$div.css('position', 'relative');
				}
			}
			var top = parseInt(($(window).height() - $load.find('.icon').outerHeight(true)) / 2);
			$load.find('.icon').css('top', top).css('position', 'fixed');
		});
	}
	
	$.fn.hideLoading = function(o){
		return this.each(function(){
			$(this).find('div.loading-box').remove();
		});
	}
	
	if (typeof $.tinymce != 'object') $.tinymce = {};
	
	var basic = {
			// Location of TinyMCE script
			script_url : '/tiny_mce/tiny_mce.js',
			// General options
			theme : "advanced",
			plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist,phpimage",
			// Theme options
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : true,
			// Example content CSS (should be your site CSS)
			content_css : "/css/content.css",
			// Drop lists for link/image/media/template dialogs
			template_external_list_url : "lists/template_list.js",
			external_link_list_url : "lists/link_list.js",
			external_image_list_url : "lists/image_list.js",
			media_external_list_url : "lists/media_list.js",
			convert_urls : false,
			file_browser_callback : "AjexFileManager.open"
		};

	$.tinymce.defaultOptions = {
		"basic"	: $.extend({}, basic, {
			// Theme options
			theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
			theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
			theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
			theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak"
		}),
		"simple" : $.extend({}, basic, {
			theme_advanced_buttons1	: "fullscreen,|,styleselect,formatselect,fontselect,fontsizeselect,bold,italic,underline,justifyleft,justifycenter,justifyright,justifyfull,|,pastetext,pasteword,|,bullist,numlist",
			theme_advanced_buttons2	: "link,unlink,anchor,image,cleanup,help,code",
			theme_advanced_buttons3	: "",
			theme_advanced_buttons4	: ""
		}),
		"links" : $.extend({}, basic, {
			theme_advanced_buttons1	: "bold,|,link,unlink,anchor,cleanup",
			theme_advanced_buttons2	: "",
			theme_advanced_buttons3	: "",
			theme_advanced_buttons4	: ""
		}),
		"text" : $.extend({}, basic, {
			theme_advanced_buttons1	: "justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontselect,fontsizeselect,|,code",
			theme_advanced_buttons2	: "bold,italic,underline,strikethrough,|,forecolor,backcolor,|,link,unlink,anchor,",
			theme_advanced_buttons3	: "",
			theme_advanced_buttons4	: ""
		}),
		
		"foo"	: {}
	};
	
	if (typeof AjexFileManager != 'undefined'){
		AjexFileManager.init({
		// РћР±СЏР·Р°С‚РµР»СЊРЅС‹Р№ РїР°СЂР°РјРµС‚СЂ
			returnTo: 'tinymce', // [ckeditor, tinymce, function] default=ckeditor

		// РћРїС†РёРѕРЅР°Р»СЊРЅРѕ
			//path: '/js/afm/', // РћРїСЂРµРґРµР»СЏРµС‚СЃСЏ Р°РІС‚РѕРјР°С‚РёС‡РµСЃРєРё, РЅРѕ РµСЃР»Рё РІРґСЂСѓРі РЅРµ СѓРґР°РµС‚СЃСЏ С‚Рѕ РјРѕР¶РЅРѕ РїСЂРѕРїРёСЃР°С‚СЊ
			//editor: '', // РћР±СЉРµРєС‚ CKEDitor'a, РЅСѓР¶РµРЅ С‚РѕР»СЊРєРѕ РґР»СЏ РЅРµРіРѕ
			//width: '', // РЁРёСЂРёРЅР° popup, default=1000
			//height: '', // Р’С‹СЃРѕС‚Р° popup, default=660
			//skin: '', // [dark, light], default=dark
			lang: 'ru', // РЇР·С‹Рє, СЃРµР№С‡Р°СЃ РµСЃС‚СЊ [ru, en], default=ru
			//connector: '', // default=php,
			contextmenu: true // [true, false], default=true
		});
	}
	
})(jQuery)