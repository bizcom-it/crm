-- @tag: add_shop_master_rights
-- @description: Rechte für Shopsysteme  in die Datenbank migrieren
-- @depends: release_3_3_0
-- @locales: Shop
-- @locales: Import/Export
-- @locales: Configuration

INSERT INTO auth.master_rights (position, name, description, category) VALUES (300, 'shop',                   'Shop', TRUE);
INSERT INTO auth.master_rights (position, name, description) VALUES (301, 'shop_imexport',                    'Import/Export');
INSERT INTO auth.master_rights (position, name, description) VALUES (302, 'shop_admin',                       'Configuration');
