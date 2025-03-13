delete from parts where not id in (select parts_id from invoice union select parts_id from orderitems);
delete from partsgroup where id in (select id from partsgroup where exists (select null from partsgroup as tmppg where partsgroup.partsgroup=tmppg.partsgroup having partsgroup.id > min(tmppg.id) ) );
