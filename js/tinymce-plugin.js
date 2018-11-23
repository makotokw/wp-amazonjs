(function() {
	tinymce.PluginManager.add( 'amazonjs', function( editor, url ) {
		editor.addButton('amazonjs', {
			title: amazonjsAdmin.mce.buttonTitle,
			image: url + '/../images/amazon-icon.png',
			onclick: function () {
				editor.windowManager.open({
					title: amazonjsAdmin.mce.dialogTitle,
					url: amazonjsAdmin.mce.dialogUrl,
					width: $(window).width() * 0.9,
					height: $(window).height() * 0.9,
					id: 'amazonjs-insert-dialog'
				});
			}
		});
	});
})();
