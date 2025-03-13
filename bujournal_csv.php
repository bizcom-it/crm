<?php
    require_once("inc/stdLib.php");
    $menu = $_SESSION['menu'];
    $head = mkHeader();
echo "<html>\n";
echo "<head><title></title>\n";
echo $menu['stylesheets'];
echo $menu['javascripts'];    
echo $head['CRMCSS'];
echo $head['THEME'];
?>    
    <script>
        $(document).ready(
            $(function () {
                $( "#von" ).datepicker($.datepicker.regional[ "de" ]);
                $( "#bis" ).datepicker($.datepicker.regional[ "de" ]);
            })
        );
    </script>
</head>
<body>
<?php
 echo $menu['pre_content'];
 echo $menu['start_content']; 

if ($_POST) {
    if (isset($_POST['von']) and $_POST['von'] != '') {
        $von = "AND ac.".$_POST['date']." >= '".$_POST['von']."' ";
    } else {
        $von = '';
    };
    if (isset($_POST['bis']) and $_POST['bis'] != '') {
        $bis = "AND ac.".$_POST['date']."<= '".$_POST['bis']."' ";
    } else {
        $bis = '';
    };
    $sql  = 'SELECT ac.acc_trans_id, g.id, \'gl\' AS type, FALSE AS invoice, g.reference, ac.taxkey, ';
    $sql .= 'c.link, g.description, ac.transdate, ac.gldate, ac.source, ac.trans_id, ac.amount, c.accno, ';
    $sql .= 'g.notes, t.chart_id, CASE WHEN (COALESCE(e.name, \'\') = \'\') THEN e.login ELSE e.name END AS employee ';
    $sql .= 'FROM gl g LEFT JOIN employee e ON (g.employee_id = e.id), acc_trans ac , chart c LEFT JOIN tax t ON (t.chart_id = c.id) ';
    $sql .= 'WHERE 1 = 1 '.$von.$bis.' AND (ac.chart_id = c.id) AND (g.id = ac.trans_id) ';
    $sql .= 'UNION SELECT ac.acc_trans_id, a.id, \'ar\' AS type, a.invoice, a.invnumber, ac.taxkey, ';
    $sql .= 'c.link, ct.name, ac.transdate, ac.gldate, ac.source, ac.trans_id, ac.amount, c.accno, ';
    $sql .= 'a.notes, t.chart_id, CASE WHEN (COALESCE(e.name, \'\') = \'\') THEN e.login ELSE e.name END AS employee ';
    $sql .= 'FROM ar a LEFT JOIN employee e ON (a.employee_id = e.id), acc_trans ac , customer ct, chart c LEFT JOIN tax t ON (t.chart_id=c.id) ';
    $sql .= 'WHERE 1 = 1 '.$von.$bis.' AND (ac.chart_id = c.id) AND (a.customer_id = ct.id) AND (a.id = ac.trans_id) ';
    $sql .= 'UNION SELECT ac.acc_trans_id, a.id, \'ap\' AS type, a.invoice, a.invnumber, ac.taxkey, ';
    $sql .= 'c.link, ct.name, ac.transdate, ac.gldate, ac.source, ac.trans_id, ac.amount, c.accno, ';
    $sql .= 'a.notes, t.chart_id, CASE WHEN (COALESCE(e.name, \'\') = \'\') THEN e.login ELSE e.name END AS employee ';
    $sql .= 'FROM ap a LEFT JOIN employee e ON (a.employee_id = e.id), acc_trans ac , vendor ct, chart c LEFT JOIN tax t ON (t.chart_id=c.id) ';
    $sql .= 'WHERE 1 = 1 '.$von.$bis.' AND (ac.chart_id = c.id) AND (a.vendor_id = ct.id) AND (a.id = ac.trans_id) ';
    if ( $_POST['sort'] == 'gldate' ) {
        $sql .= 'ORDER BY gldate ASC, id ASC, acc_trans_id ASC';
    } else if ( $_POST['sort'] == 'trans_id' ) {
        $sql .= 'ORDER BY trans_id ASC, acc_trans_id ASC';
    } else if ( $_POST['sort'] == 'transdate' ) {
        $sql .= 'ORDER BY transdate ASC,trans_id ASC';
    }
    $rs = $GLOBALS['db']->getAll($sql);
    if ($rs) {
        $f = fopen('tmp/buchungsdaten.csv','w');
        $head = "accid;id;type;invoice;reference;taxkey;link;description;transdate;gldate;source;trans_id;amount;accno;notes;chart_id;employee\n";
        fputs($f,$head);
        foreach ($rs as $row) {
            if ( $_POST['amount'] == ',' ) $row['amount'] = strtr($row['amount'],'.',',');
            fputcsv($f,$row,';','"');
        }
        fclose($f);
        //$out  = "<br><p><input type='reset' name='send' id='send' value='Buchungsdaten' onClick='download();'><br>";
        $out .= "<p><a href='tmp/buchungsdaten.csv' target='_blank'>Buchungsdaten</a></p>";
    } else { 
        $out = "Keine Daten für den Zeitraum.<br>";
    }
} ?>


<p class='listtop'>CSV-Export Buchungsdaten:</p>
<form name="bujourn" action="bujournal_csv.php" method="post">
Von <input type="text" silze="12" name="von" id="von"> 
Bis <input type="text" silze="12" name="bis" id="bis">
<input type='radio' name='date' value='gldate'>Buchungsdatum <input type='radio' name='date' value='transdate' checked>Rechnungsdatum<br>
Sortiert nach <input type='radio' name='sort' value='gldate' checked>Buchungsdatum 
              <input type='radio' name='sort' value='trans_id'>Transaktions-ID 
              <input type='radio' name='sort' value='transdate'>Transaktions-Datum
<br />
Zahlen mit <input type='radio' name='amount' value=',' checked>Komma <input type='radio' name='amount' value='.'>Punkt<br>
<input type="submit" name="ok" id="id" value="erzeugen">
<?php echo $out; ?>
</form>
<?php echo $menu['end_content']; ?>
</body>
</html>
