tinyMCE.init({
                theme                   : "modern",
                mode                    : "exact",
                elements                : "elm1",
                plugins: ["table emoticons textcolor textcolor colorpicker hr anchor pagebreak code insertdatetime link image"],
                toolbar1: "bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect formatselect fontselect fontsizeselect | code",
                toolbar2: "bullist numlist | outdent indent blockquote | undo redo | link unlink image media | insertdatetime preview | forecolor backcolor | hr removeformat | subscript superscript | table | localpic",

                menubar: false,
                toolbar_items_size: 'small',
                setup: function (editor) {
                     editor.addButton('localpic', {
                       text: 'Local Pics',
                       icon: false,
                       onclick: function () {
                               filesearch();
                       }
                     });
                },
        });
