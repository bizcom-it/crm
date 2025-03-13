<?php
    require_once("../inc/stdLib.php"); 
    require_once("crmLib.php");  
    $restasks = array('deleteRessource','saveRessource');
    $task     = array_shift( $_POST );
    $newCat   = ( isSetVar($_POST['newCat']) )?$_POST['newCat']:false;
    $newColor = ( isSetVar($_POST['newColor']) )?$_POST['newColor']:false;
    if ( in_array($task, $restasks ) ) {
        $newRes   = ( isSetVar($_POST['newRes']) )?$_POST['newRes']:false;
        $delRes   = ( isSetVar($_POST['delRes']) )?$_POST['delRes']:false;   
    } else {
        $delCat   = ( isSetVar($_POST['delCat']) )?$_POST['delCat']:false;
    }
    switch( $task ){
        case "newCategory":
            $sql  = "INSERT INTO event_category ( label, color, cat_order ) VALUES ";
            $sql .= "( '$newCat', '$newColor', ( SELECT max( cat_order ) + 1 AS cat_order FROM event_category) )";
            $rc   = $GLOBALS['db']->query($sql); 
        break;
        case "getCategories":
            $sql = "SELECT id, label, TRIM( color ) AS color FROM event_category ORDER BY cat_order DESC";
            $rs  = $GLOBALS['db']->getJson( $sql );
            echo $rs;   
        break; 
        case "updateCategories":
            $data = array_shift( $_POST );
            $sql  = "WITH new_data (id, label, color, cat_order) AS ( VALUES ";
            foreach( $data as $key => $value ){
                $order = ( int ) ( $key / 2 );
                if ( $key % 2 ) $sql .= ", '".$value['value']."', ".$order." )";//\r\n
                else $sql .= ($key ? ',' :'' )."( ".substr($value['name'], 4).", '".$value['value']."'";
            }
            $sql .= " ) UPDATE event_category SET label = d.label, color = d.color, cat_order = d.cat_order FROM new_data d WHERE d.id = event_category.id";
            $rs = $GLOBALS['db']->getOne( $sql );
        break;
        case "deleteCategory":
            $sql="DELETE FROM event_category WHERE id = $delCat";
            $rc=$GLOBALS['db']->query($sql); 
        break;
        case "saveRessource":
            if ( $_POST['id'] > 0 ) {
                $sql  = "WITH new_data (id, ressource,  color, resorder) AS ( VALUES ( ";
                $sql .= $_POST['id'].",'".$_POST['ressource']."','".$_POST['color']."',".$_POST['resorder'].")";
                $sql .= " ) UPDATE ressourcen SET ressource = d.ressource, color = d.color, resorder = d.resorder ";
                $sql .= "FROM new_data d WHERE d.id = ressourcen.id";
                $rc   = $GLOBALS['db']->query($sql);
                echo $rc;
            } else {
                $sql  = "INSERT INTO ressourcen ( ressource, category,color, resorder ) VALUES ";
                $sql .= "( '".$_POST['ressource']."', ".$_POST['category']." , '".$_POST['color']."', ";
                $sql .= "( SELECT COALESCE(max(resorder)+1,1) AS cat_order FROM ressourcen WHERE category = ".$_POST['category'].") )";
                $GLOBALS['db']->begin();
                $rc   = $GLOBALS['db']->query($sql); 
                if ( $rc ) {
                    $sql = "SELECT max(id) as id FROM ressourcen";
                    $rs  = $GLOBALS['db']->getOne($sql);
                    $GLOBALS['db']->commit();
                    echo $rs['id'];
                } else {
                    $GLOBALS['db']->rollback();
                    echo false;
                };
            }
        break;
        case "getRessourcen":
            $sql  = "SELECT id, ressource, category,resorder, color FROM ressourcen ";
            if ( $_POST['category'] != '' ) $sql .= "WHERE category = ".$_POST['category'];
            $sql .= " ORDER BY category,resorder DESC";
            $rs  = $GLOBALS['db']->getJson( $sql );
            echo $rs;   
        break;
        case "deleteRessource":
            $sql = "DELETE FROM ressourcen WHERE id = $delRes";
            $rc  = $GLOBALS['db']->query($sql); 
        break;
        case "getResCategory":
            $sql = "SELECT id as value, category as text FROM ressourcen_category order by id";
            $rs  = $GLOBALS['db']->getJson($sql);
            echo $rs;
        break;
        case "saveResCat":
            if ( $_POST['id'] > 0 ) {
                $sql = "UPDATE ressourcen_category SET category = '".$_POST['name']."' WHERE id = ".$_POST['id'];
                $rc = $GLOBALS['db']->query($sql);
            } else {
                $sql = "INSERT INTO ressourcen_category (category) VALUES ('".$_POST['name']."')";
                $rc  = $GLOBALS['db']->query($sql);
                $sql = "SELECT max(id) as id FROM ressourcen_category";
                $rs  = $GLOBALS['db']->getOne($sql);
                $rc  = $rs['id'];
            }
            echo $rc;
        break;
     }
 ?>
