-- @tag: katalog_saveMask
-- @description: Suchmaske für Katalog speichern

CREATE TABLE katmask (
    maskid integer DEFAULT nextval('crmid'::text) NOT NULL,
    maskname text,
    mask text
);

ALTER TABLE  katmask ADD  primary key (maskid);

