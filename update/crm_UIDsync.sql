-- @tag: UIDsync
-- @description: eindeutige UID für die Synchronisation

ALTER TABLE customer ADD COLUMN uid text;
ALTER TABLE vendor ADD COLUMN uid text;
ALTER TABLE contacts ADD COLUMN uid text;
