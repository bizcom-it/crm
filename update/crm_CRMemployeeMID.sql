-- @tag: CRMemployeeMID
-- @description: Mandantenid je User
-- @require: CRMemployee
-- @check: *

ALTER TABLE crmemployee DROP COLUMN ceid;
ALTER TABLE crmemployee ADD COLUMN manid int4;

-- @php: *
$rc = $GLOBALS['db']->begin();
$rc = $GLOBALS['db']->query('UPDATE crmemployee SET manid = '.$_SESSION['manid']);
$GLOBALS['db']->commit();
return true;
-- @exec: *
