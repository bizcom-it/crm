-- @tag: AdressSync
-- @description: Marker ob Adresse synchronisiert werden soll

ALTER TABLE customer ADD COLUMN sync int;
ALTER TABLE customer ALTER COLUMN sync SET DEFAULT 0;
ALTER TABLE vendor ADD COLUMN sync int;
ALTER TABLE vendor ALTER COLUMN sync SET DEFAULT 0;
ALTER TABLE contacts ADD COLUMN cp_sync int;
ALTER TABLE contacts ALTER COLUMN cp_sync SET DEFAULT 0;
