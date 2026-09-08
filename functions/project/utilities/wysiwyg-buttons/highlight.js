(function () {
    tinymce.PluginManager.add('terra_highlight', function (editor) {
        editor.addButton('terra_highlight', {
            title: 'Highlight',
            icon: 'backcolor',
            onclick: function () {
                var selection = editor.selection.getContent();
                if (selection) {
                    editor.selection.setContent('<mark>' + selection + '</mark>');
                }
            },
        });
    });
})();
