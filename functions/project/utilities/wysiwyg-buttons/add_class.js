(function () {
    tinymce.PluginManager.add('terra_add_class', function (editor) {
        editor.addButton('terra_add_class', {
            title: 'Add Class',
            icon: 'code',
            onclick: function () {
                editor.focus();
                var selection = editor.selection.getContent();
                if (!selection) return;

                editor.windowManager.open({
                    title: 'Add CSS Class',
                    body: [
                        {
                            type: 'textbox',
                            name: 'cssClass',
                            label: 'Class name',
                            value: '',
                        },
                    ],
                    onsubmit: function (e) {
                        var cls = e.data.cssClass.trim();
                        if (!cls) return;

                        editor.undoManager.transact(function () {
                            editor.selection.setContent(
                                '<span class="' + cls + '">' + editor.selection.getContent() + '</span>'
                            );
                        });
                    },
                });
            },
        });
    });
})();
