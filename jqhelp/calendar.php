<?php
    $lf = "\n";
    require_once("../inc/stdLib.php"); 
    require_once("crmLib.php"); 
    $task           = '';
    $startGet       = '';
    $endGet         = '';
    $repeat_end_GET = '';
    $repeat_end_sql = '';
    $repeat_end     = 'Invalid date';
    $where          = '';
    $myuid          = '';


    if ( isset($_POST) AND isset($_POST['task']) ) {
        $task  = $_POST['task'];
    } else if ( isset($_GET) AND isset($_GET['task']) ) {
        $task = $_GET['task'];
    };
    //ToDo Funktion AjaxSql schreiben. Diese werten $_POST oder $_GET aus, erster Parameter ist Tabelle, zeiter P ist task (insert, select, update) folgende sind die serialisierten Daten 
    if( !$task ) $task = 'getEvents';
    if ( isset($_GET) ) {
        $startGet       = isset($_GET['start'])?$_GET['start']:'';
        $endGet         = isset($_GET['end'])?$_GET['end']:'';
        if ( isset($_GET['repeat_end'] ) ) {
             $repeat_end_GET = $_GET['repeat_end'] == 'Invalid date'? 'NULL' : $_GET['repeat_end'];
        } else {
            $repeat_end_GET = 'NULL';
        };
        $where          = isset($_GET['where'])?$_GET['where']:'';
        $myuid          = isset($_GET['myuid'])?$_GET['myuid']:$_SESSION['loginCRM'];
    }
    if ( isset($_POST) ) foreach( $_POST as $key => $value ){
        $$key = htmlspecialchars($value);
    }
    $repeat_end_sql = $repeat_end == 'Invalid date' ? 'NULL' : "'$repeat_end'::TIMESTAMP"; 
    switch( $task ){
        case "isManager":
            echo isManager();
        break;    
        case "initCal":
            $usr = getUserEmployee(array('feature_ac_delay','feature_ac_minlength','termbegin','termend','termseq','countrycode','caltype'));
            if ( $usr['caltype'] == '' ) $usr['caltype'] = 'agendaWeek';
            $usr['loginCRM'] = $_SESSION['loginCRM'];
            $usr['countrycode'] = $_SESSION['countrycode'];
            $sql  = "SELECT count(*) as cnt FROM gruppenname n LEFT JOIN grpusr u ON n.grpid=u.grpid ";
            $sql .= "WHERE n.rechte = 'r' AND u.usrid = ".$_SESSION['loginCRM'];
            $rs = $GLOBALS['db']->getOne($sql);
            $usr['manager'] = $rs['cnt'] > 0;
            $grps = array();
            array_unshift( $grps, array( 'value' => '0', 'text' => 'Benutzer' ) );
            array_unshift( $grps, array( 'value' => '-1', 'text' => 'Alle' ) );
            $sql = "SELECT * from gruppenname ORDER BY grpname";
            $rs = $GLOBALS['db']->getAll($sql);
            if ( $rs ) foreach ( $rs as $row ) array_push( $grps,  array( 'value' => $row['grpid'], 'text' => $row['grpname']) );
            $usr['grps'] = $grps;
            echo json_encode( $usr );
        break;
        case "newEvent":
            $sql = "INSERT INTO events ( start,\"end\", title, description, \"allDay\", uid, visibility, category, prio, job, color, done, location, cust_vend_pers, repeat, repeat_factor, repeat_quantity, repeat_end ) VALUES ( '$start', '$end','$title','$description', $allDay, $uid, $visibility, $category, $prio, '$job', '$color', '$done', '$location', '$cust_vend_pers', '$repeat', '$repeat_factor', '$repeat_quantity', $repeat_end_sql )";
            $GLOBALS['db']->begin();
            $rc = $GLOBALS['db']->query( $sql ); 
            if ( $cust_vend_pers>0 && $rc ) {
                $termid = $GLOBALS['db']->getOne('SELECT max(id) as id FROM events'); // Das sollte dieses Event sein
                $GLOBALS['db']->commit();
                $tid = mknewTelCall();
                $sql = "UPDATE telcall SET cause='$title',caller_id=".substr($cust_vend_pers,1).",calldate='$start',termin_id=".$termid['id'];
                $sql.= ",c_long='$description',employee='".$_SESSION["loginCRM"]."',kontakt='X',bezug=0 where id=$tid";
                $rc  = $GLOBALS['db']->query($sql);
            } else {
                if ( $cust_vend_pers>0 ) { $GLOBALS['db']->rollback(); }
                else { $GLOBALS['db']->commit(); };
            }
        break;
        case "updateEvent":
            $sql = "UPDATE events SET title = '$title', start = '$start', \"end\" = '$end', description = '$description', \"allDay\" = $allDay, uid = '$uid', visibility = '$visibility', category = '$category', prio = '$prio', job = '$job', color = '$color', done = '$done', location = '$location', cust_vend_pers = '$cust_vend_pers', repeat = '$repeat', repeat_factor = '$repeat_factor', repeat_quantity = '$repeat_quantity', repeat_end = $repeat_end_sql  WHERE id = $id";
            $rc = $GLOBALS['db']->query( $sql );
            if ( ($cust_vend_pers<>'' ||  $cust_vend_pers_old<>'') && $rc ) {
                $sql = 'SELECT id,bezug FROM telcall WHERE termin_id='.$id;
                $rs  = $GLOBALS['db']->getOne($sql); //sollte nur einer sein. 
                if ( $cust_vend_pers_old<>'' && $cust_vend_pers_old == $cust_vend_pers ) {   //gleiche  Bezugsadresse
                    if (!$rs) {
                      $tid = mknewTelCall();
                      $bezug = 0;
                    } else {
                      $tid = $rs['id'];
                      $bezug = $rs['bezug'];
                    }
                } else if ( $cust_vend_pers_old<>'' && $cust_vend_pers == '' ) {             //keine    Bezugsadresse mehr
                    if ( $rs) { $bezug = $rs['id']; } else { $bezug = 0; };
                    $tid = mknewTelCall();
                    $title = 'vom Termin entfernt';
                    $cust_vend_pers = $cust_vend_pers_old;
                } else if ( $cust_vend_pers_old=='' && $cust_vend_pers <> '' ) {             //erstmals Bezugsadresse
                    $tid = mknewTelCall();
                    $bezug = 0;
                }
                $sql = "UPDATE telcall SET cause='$title',caller_id=".substr($cust_vend_pers,1).",calldate='$start',termin_id=".$id;
                $sql.= ",c_long='$description',employee='".$_SESSION["loginCRM"]."',kontakt='X',bezug=$bezug where id=$tid";
                $rc  = $GLOBALS['db']->query($sql);
            } else {
                if ( $cust_vend_pers>0 )  $GLOBALS['db']->rollback();
            }
        break;
        case "updateTimestamp":
            $sql = "UPDATE events SET  start = '$start', \"end\" = '$end', \"allDay\" = $allDay WHERE id = $id";
            $rc = $GLOBALS['db']->query( $sql );   
        break;
        case "deleteEvent":
            $sql = "DELETE FROM events WHERE id = $id";
            $rc = $GLOBALS['db']->query( $sql );   
        break;
        case "getEvents":
            $grp = getGrp($_SESSION["loginCRM"]);
            if ( $grp ) { $visible = " or visibility in $grp"; }
            else { $visible = ''; };
            $sql = "SELECT * FROM ( WITH RECURSIVE r(repeat_quantity) AS ( SELECT repeat_quantity::int,id,title,description,location,uid,prio,category,\"allDay\" AS \"allDay\",color,job,done,job_planned_end,cust_vend_pers,repeat_factor,repeat,repeat_end,start,\"end\",visibility FROM events UNION ALL SELECT repeat_quantity-1,   id,title,description,location,uid,prio,category,\"allDay\",color,job,done,job_planned_end,cust_vend_pers,repeat_factor,repeat,repeat_end ,(start::timestamp+((repeat_quantity-1)*(repeat_factor||repeat)::interval)) AS start,(\"end\"::timestamp+((repeat_quantity-1)*(repeat_factor||repeat)::interval)) AS \"end\",visibility FROM r WHERE repeat_quantity >1) SELECT * FROM r ORDER BY id ASC, repeat_quantity DESC) alle_termine where start >= '$startGet' AND start <= '$endGet' AND $where ( CASE WHEN visibility = 0 THEN uid = $myuid ELSE TRUE END $visible ) AND visibility != -2";
            $rs = $GLOBALS['db']->getAll($sql);
            if ( $rs ) for ($i=0; $i<count($rs); $i++) {
                $rs[$i]['allDay'] = ($rs[$i]['allDay']=='f')?false:true;
                $rs[$i]['textColor'] = sprintf('#%06X',(16777215-hexdec($rs[$i]['color'])));
            };
            echo json_encode($rs);
        break; 
        case "getUsers":
            $sql = "SELECT id AS value, CASE WHEN name='' or name IS null THEN login ELSE name END AS text FROM employee WHERE deleted = FALSE "; //login
            //$sql = "SELECT id AS value, COALESCE(name,login) as text FROM employee WHERE deleted = FALSE "; //login
            $rs = $GLOBALS['db']->getJson( $sql );    
            echo $rs;  
        break;
        case "getCategory":
            $sql = "SELECT id AS value, label AS text FROM event_category ORDER BY cat_order";                                                       
            $rs = $GLOBALS['db']->getJson( $sql );
            echo $rs;  
        break;
        case "ressource":
            $sql = "SELECT * FROM ( WITH RECURSIVE r(repeat_quantity) AS ( SELECT repeat_quantity::int,id,title,description,location,uid,prio,category,\"allDay\" AS \"allDay\",color,job,done,job_planned_end,cust_vend_pers,repeat_factor,repeat,repeat_end,start,\"end\",visibility FROM events UNION ALL SELECT repeat_quantity-1,   id,title,description,location,uid,prio,category,\"allDay\",color,job,done,job_planned_end,cust_vend_pers,repeat_factor,repeat,repeat_end ,(start::timestamp+((repeat_quantity-1)*(repeat_factor||repeat)::interval)) AS start,(\"end\"::timestamp+((repeat_quantity-1)*(repeat_factor||repeat)::interval)) AS \"end\",visibility FROM r WHERE repeat_quantity >1) SELECT * FROM r ORDER BY id ASC, repeat_quantity DESC) alle_termine where start >= '$startGet' AND start <= '$endGet' AND visibility = -2";
            $rs = $GLOBALS['db']->getAll($sql);
            if ( $rs ) for ($i=0; $i<count($rs); $i++) {
                $rs[$i]['allDay'] = ($rs[$i]['allDay']=='f')?false:true;
                $rs[$i]['textColor'] = sprintf('#%06X',(16777215-hexdec($rs[$i]['color'])));
            }
            echo json_encode($rs);
     }    
?>		
