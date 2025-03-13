    var file = "";
    var aktfile = "";
    var pickup = false;
    var tiny = false;
    var webdav = true;
    function picup() {
        //opener.document.getElementById("elm1").value="<a href='"+pfad+file+"'>"+file+"</a>";
        var label = $('#docname').val();
        var pos   = label.lastIndexOf('.');
        var ext   = label.substr(pos);
        if ( ['.jpg','.png','.jpeg'].indexOf(ext.toLowerCase()) >= 0 ) {
            text = "<img src='dokumente/"+$('#mandant').val()+aktfile+"' width='200'/>";
        } else {
            text = "<a href='dokumente/"+$('#mandant').val()+aktfile+"' target='_blank'>"+label+"</a>";
        }
        if ( tiny ) {
            opener.tinyMCE.activeEditor.execCommand('mceInsertContent', false,text);
        } else {
            var input = opener.document.getElementById("elm1");
            input.focus();
            /* für Internet Explorer */
            if(typeof document.selection != 'undefined') {
                /* Einfügen des Formatierungscodes */
                var range = document.selection.createRange();
                range.text = text;
                /* Anpassen der Cursorposition */
                range = document.selection.createRange();
                range.moveStart('character', text.length);      
                range.select();
            } else if(typeof input.selectionStart != 'undefined') {
            /* für neuere auf Gecko basierende Browser */
                var start = input.selectionStart;
                input.value =  input.value.substr(0, start) + text + input.value.substr(start);
                /* Anpassen der Cursorposition */
                var pos;
                pos = start + text.length;
                input.selectionStart = pos;
                input.selectionEnd = pos;
            } else {
            /* für die übrigen Browser */
            /* Abfrage der Einfügeposition */
                var pos;
                var re = new RegExp('^[0-9]{0,3}$');
                while(!re.test(pos)) {
                    pos = prompt("Einfügen an Position (0.." + input.value.length + "):", "0");
                }
                if(pos > input.value.length) {
                    pos = input.value.length;
                }
                /* Einfügen des Formatierungscodes */
                var insText = prompt("Bitte geben Sie den zu formatierenden Text ein:");
                input.value = input.value.substr(0, pos) + aTag + insText + eTag + input.value.substr(pos);
            }
        }
        self.close();
    }


    function mkDir() {
        seite = $('#seite').val();
        name  = $('#subdir').val();
        $.get('jqhelp/firmaserver.php?task=newDir&pfad='+pfadleft+'&newdir='+name,function(rc) {
                 if (rc == 'ok') {
                     dateibaum('left',pfadleft);
                     //newDir();
                 } else {
                     alert(rc);
                 }
             });
    }
    var downloadfile = "";
    var baseurl = "";
    var davurl  = '';
    var urlTgt = 'SendFile.php';
    var formName = 'someform';
    function download() {
        console.log(((webdav)?baseurl:davurl)+aktfile);
        var iFrameName = "downloadFileXferIframe";
        if ( $( "#" + iFrameName ).length == 0 ) {
            var iFrame = $( "<iframe name=\"" + iFrameName + "\" " +
                        "id=\"" + iFrameName + "\" " + "style=\"display: none\">" );
            $( "body" ).append( iFrame );
        }
        $( "#" + iFrameName ).on( "load", function() {
            var retVal= $( "#" + iFrameName )[0].contentWindow.document.body.innerHTML; 
            if ( retVal.length != 0 ) {
                errorHandler( retVal ); 
            }
        });
        $( "#" + formName ).attr( "action", urlTgt )
                .attr( "method", "post" )
                .attr( "enctype", "multipart/form-data" )
                .attr( "encoding", "multipart/form-data" )
                .attr( "target", iFrameName )
        $( '#DownLoadFile' ).val(((webdav)?baseurl:davurl)+aktfile);
        $( "#" + formName ).submit();  
    }
    function errorHandler( error ) {
        alert( error );
    }
    function saveAttribut() {
        name    = $('#docname').val();
        oldname = $('#docoldname').val();
        pfad    = $('#docpfad').val();
        komment = $('#docdescript').val();
        id      = $('#docid').val();
        $.ajax({
               url: "jqhelp/firmaserver.php",
               data: { 'task':'saveAttribut', 'name':name, 'oldname':oldname, 'pfad':pfad, 'komment':komment, 'id':id },
               dataType: 'json',
               success: function(data) { 
                   if ( data.rc > 0 ) {
                       editattribut();
                       dateibaum('left',pfadleft);
                       showFile('left',name);
                   } else {
                       alert('Error');
                   }
               }
        });
    }
    function movefile() {
        $.ajax({
               url: 'jqhelp/firmaserver.php?task=moveFile&file='+aktfile+'&pfadleft='+pfadleft,
               dataType: 'json',
               success: function(data) { 
                   if (data.rc == 0) { alert('Error'); }
                   else {
                       dateibaum('left',pfadleft);
                       dateibaum('right',pfadright);
                       aktfile = '';
                   } 
               }
        });

    }
    function filedelete() {
        id   = $('#docid').val();
        name = $('#docname').val();
        pfad = $('#docpfad').val();
        if (!id) id = 0;
        $.get('jqhelp/firmaserver.php?task=delFile&id='+id+'&pfad='+pfad+'&file='+name,function(data) { 
                 if (data == 'ok' ) {
                     dateibaum('left',pfadleft);
                     dateibaum('right',pfadright);
                     $('#fileDel').dialog('close');
                 } else {
                     alert(data);
                 }
              });
    }
    function deletefile() {
        if ( $('#fileDel').dialog( "isOpen" ) ) {
            $('#fileDel').dialog('close');
        } else {
            $('#fileDel').dialog('open');
            name = $('#docname').val();
            $('#delname').empty().append(name);
        }
    }
    function editattribut() {
        if ( $('#attribut').dialog( "isOpen" ) ) {
            $('#attribut').dialog('close');
        } else {
            $('#attribut').dialog('open');
        }
    }
    function newDir(seite) {
        if ( $('#newwindir').dialog( "isOpen" ) ) {
            $('#newwindir').dialog('close');
        } else {                            
            $('#newwindir').dialog('open');
            $('#subdir').focus();
            $('#seite').val(seite);
        }
    }
    function delDir(deldir) {
        var pos    = deldir.lastIndexOf('/');
        var newdir = deldir.substring(0,pos)
        var del    =  deldir.substring(pos+1);
        console.log("delDir: "+deldir)
        console.log("chDir: "+newdir)
        console.log("Del: "+del)
        $.ajax({
               url: "jqhelp/firmaserver.php",
               data: {'task':'delDir','del':del, 'pfad':newdir},
    	       //dataType: 'json',
    	       success: function(rc) { 
                   console.log("Del: "+rc);
                   dateibaum('left',newdir);
               }
        })
    }
    function newFile(seite) {
        if ( $('#uploadfr').dialog( "isOpen" ) ) {
            $('#uploadfr').dialog('close');
        } else {
            $('#uploadfr').dialog('open');
            $('#seite').val(seite);
            $('#uploadfr').show();
       }
    }
    var pfadleft = "";
    var pfadright = "";
    function showFile(seite,file) {
        console.log('Seite: '+seite+" File: "+file)
        $.ajax({
               url: "jqhelp/firmaserver.php",
               data: {'task':'showFile','id':seite,'file':file, 'pfad':(seite=="left")?pfadleft:pfadright},
    	       dataType: 'json',
    	       success: function(data){
                   aktfile = data.docpfad+"/"+data.docname;
                   $('#docname').val(data.docname);
                   $('#docoldname').val(data.docoldname);
                   $('#docpfad').val(data.docpfad);
                   $('#docid').val(data.docid);
                   $('#docdescript').empty().append(data.docdescript);
                   $('#fbright').empty().append(data.fbright);
                   showlinks();
                   if ( data.lock > 1 ) hidelinks(1);
                   if (pickup) $("#picupbut").show();
               }
        })
    }
    function lockFile() {
        id   = $('#docid').val();
        name = $('#docname').val();
        pfad = $('#docpfad').val();
        $.get('jqhelp/firmaserver.php?task=lockFile&id='+id+'&pfad='+pfad+'&file='+name,function(rc) { 
                 if (rc == 'lock' || rc == 'unlock') {
                     //dateibaum('left',pfadleft);
                     showFile('left',name);
                 } else {
                     alert(rc);
                 }
             });        
    }
    function hidelinks(lock) {
        if ( lock < 1 ) {
            $('#subdownload').hide();
            $('#lock').hide();
        }
        $('#subdelete').hide();
        $('#subedit').hide();
        $('#submove').hide();
        $('#picupbut').hide();
    }
    function showlinks() {
        $('#subfilebrowser').show();
        $('#subdownload').show();
        console.log('SL'+webdav);
        if ( webdav ) {
            $('#subdelete').show();
            $('#subedit').show();
            $('#lock').show();
            $('#submove').show();
        } else {
            $('#subdelete').hide();
            $('#subedit').hide();
            $('#submove').hide();
            $('#picupbut').hide();
            $('#lock').hide();
        }
    }
    function dateibaum(seite,start) {
        console.log('Seite: '+seite+" Start: "+start+"Webdav:"+webdav)
        if(seite=="left") { pfadleft=start; }
        else { 
            aktfile = '';
            pfadright=start; 
            hidelinks(0)
        };
        $( '#newwindir').dialog('close');
        $( "#uploadfr" ).dialog('close');
        $( "#fileDel" ).dialog('close');
        $( "#attribut" ).dialog('close');
        if ( start == '!' && webdav) {
            $( '#buttxt' ).empty().append('CRM');
            webdav = false;
        } else if ( start == '!' && !webdav) {
            $( '#buttxt' ).empty().append('WebDav');
            webdav = true;
            start = '/';
        }
        $.ajax({
               url: "jqhelp/firmaserver.php?task=showDir&id="+seite+"&dir="+start,
    	       dataType: 'json',
    	       success: function(data){
                   //console.log(JSON.stringify(data))
                   if (data.rc == 1) {
                       $('#path').empty().append(data.path);
                       $('#fb'+seite).empty().append(data.fb);
                       if ( data.count == 0 ) {
                           $( '#delDir' ).show();
                       } else {
                           $( '#delDir' ).hide();
                       }
                   }
               }
        });
        setTimeout("dateibaum('left',pfadleft)",100000) // 100sec
    }
    function showimage() {
             $('#fileimage').fancybox().trigger('click')
    };
    $(function(){
        $(".fancybox").fancybox({
				'transitionIn'	: 'elastic',
				'transitionOut'	: 'elastic',
                'autoDimensions': true});

         $('button')
          .button()
          .click( function(event) { 
                      event.preventDefault();  
                      link = this.getAttribute('name');
                      if ( link == 'close' ) return;
                      if ( link.substr(0,7) == 'onClick' ) {
                          eval ( link.substr(8) );
                      } else {
                          document.location.href = link; 
                      };
                  });
        $( "#newwindir" ).dialog({
                   autoOpen: false,
		   height: 250,
		   width:  300,
		   modal: true,
                   show: {
                      effect: "blind",
                      duration: 300
                   },
                   hide: {
                      effect: "blind",
                      duration: 300
                   }
        })
        $( "#uploadfr" ).dialog({
                   autoOpen: false,
		   height: 350,
		   width:  450,
		   modal: true,
                   show: {
                      effect: "blind",
                      duration: 300
                   },
                   hide: {
                      effect: "blind",
                      duration: 300
                   }
        })
        $( "#fileDel" ).dialog({
                   autoOpen: false,
		   height: 330,
		   width:  350,
		   modal: true,
                   show: {
                      effect: "blind",
                      duration: 300
                   },
                   hide: {
                      effect: "blind",
                      duration: 300
                   }
        })
        $( "#attribut" ).dialog({
                   autoOpen: false,
		   height: 350,
		   width:  550,
		   modal: true,
                   show: {
                      effect: "blind",
                      duration: 300
                   },
                   hide: {
                      effect: "blind",
                      duration: 300
                   }
        })
        $( "#newwindir" ).dialog();
        $( "#uploadfr" ).dialog();
        $( "#attribut" ).dialog();
        $( "#fileDel" ).dialog();
    });

