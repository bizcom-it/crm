-- @tag: erpnotes
-- @description: Fehlende follow_ups - Einträge erstellen

-- @php: *
$sql  = 'SELECT n.id,n.created_by,n.trans_id,n.itime,n.subject,c.name as customer, v.name as vendor from notes n ';
$sql .= 'left join follow_ups u on n.id = note_id ';
$sql .= 'left join customer c on c.id=n.trans_id ';
$sql .= 'left join vendor v on v.id=n.trans_id ';
$sql .= "where u.id is null and n.trans_module = 'ct'";
$sf   = $GLOBALS['db']->getAll($sql);
$sqlu = "insert into follow_ups (follow_up_date,created_for_user,done,note_id,created_by) values ('%s',%d,'t',%d,%d)";
$sqll = "insert into follow_up_links (follow_up_id,trans_id,trans_type,trans_info) values (%d,%d,'%s','%s')";
$ask  = 'select id from follow_ups where note_id = ';
if ($sf) {
  foreach ($sf as $r) {
    echo $r['subject']."<br>";
    $rc = $GLOBALS['db']->query(sprintf($sqlu,substr($r['itime'],0,10),$r['created_by'],$r['id'],$r['created_by']));
    $rs = $GLOBALS['db']->getOne($ask.$r['id']);
    if ( $r['customer'] != '' ) { $type = 'customer'; $name = $r['customer']; }
    else { $type = 'vendor'; $name = $r['vendor']; };
    $rc = $GLOBALS['db']->query(sprintf($sqll,$rs['id'],$r['trans_id'],$type,$name));
  };
}
-- @exec: *
