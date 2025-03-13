-- @tag: uniqe1
-- @description: IDs müssen eindeutig sein

ALTER TABLE wissencontent ADD UNIQUE (id);
ALTER TABLE wissencategorie ADD UNIQUE (id);
ALTER TABLE telcallhistory ADD UNIQUE (id);
ALTER TABLE postit ADD UNIQUE (id);
ALTER TABLE opportunity ADD UNIQUE (id);
ALTER TABLE opport_status ADD UNIQUE (id);
ALTER TABLE maschine ADD UNIQUE (id);
ALTER TABLE mailvorlage ADD UNIQUE (id);
ALTER TABLE telcallhistory ADD UNIQUE (id);
ALTER TABLE labels  ADD UNIQUE (id);
ALTER TABLE labeltxt ADD UNIQUE (id);
ALTER TABLE crm ADD UNIQUE (id);
