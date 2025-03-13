<?php
require_once("inc/stdLib.php");
if ( isset($_GET['part_id']) ) {
  echo "Attribute für ID:".$_GET['part_id']."<br>";
  #$_GET['task']     = 'readMainGroup';
  #$_GET['part_id']  = $_GET['part_id'];
  #include 'jqhelp/filter.php';
?>
  <select id="group">
  </select>
  <script>
      function readMainGroup() {
          console.log('readMainGroup');
          $.ajax({
              url: 'crm/jqhelp/filter.php',
              dataType: 'json',
              data: { 
                  part_id: <?php echo $_GET['part_id']; ?>,
                  task:  'readMainGroup', 
              }
          }).done(function(json) {
             console.log( json['merkmale'] );
             console.log( 'ok1' );
             $( '#group' ).selectBoxIt({
                 //populate: $.parseJSON( json['merkmale'].trim() )
                 populate: json['merkmale'] 
             });
             console.log( 'ok2' );
             var selected = json['select'];
             console.log(selected);
             if ( selected > 0 ) {
                 showSubGroup( json['subgroup'] );
             }
          })
      };
      function showSubGroup( subgrp ) {
          console.log('showSubGroup');
          console.log( subgrp );
          $.each( subgrp, function(nr,sub) { //id,label,poslabel,poswert,wert) {
              console.log(nr,sub); //['gruppe']); //,id,label,poslabel,poswert,wert);
          });
      }

      $( document ).ready( function(){
          readMainGroup();
      });
  </script>

<?php
} else {
  echo "Artikel wurde noch nie gespeichert. Erst danach kann ein Filter eingerichtet werden";
};
echo "<br><br><b>Filter bitte extra speichern!</b><br>";
?>
