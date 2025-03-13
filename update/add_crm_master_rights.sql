-- @tag: add_crm_master_rights
-- @description: Rechte für CRM in die Datenbank migrieren
-- @depends: release_3_3_0
-- @locales: CRM
-- @locales: Searchmask
-- @locales: Add new Addresses
-- @locales: Sales: Opporunity,Catalog, Packlist, ebay
-- @locales: Other: Follow Up,E-Mail,Appointment
-- @locales: Documens
-- @locales: Finance
-- @locales: Parts: Edit, Warehouse
-- @locales: Service: Contract, Machine
-- @locales: Master: Client, Groups
-- @locales: Admin: Label, Category, Messages, Status, KnowHow write
-- @locales: User

INSERT INTO auth.master_rights (position, name, description, category) VALUES (20000, 'crm',                   'CRM', TRUE);
INSERT INTO auth.master_rights (position, name, description) VALUES (20001, 'crm_search',                      'Searchmask');
INSERT INTO auth.master_rights (position, name, description) VALUES (20002, 'crm_new',                         'Add new Addresses');
INSERT INTO auth.master_rights (position, name, description) VALUES (20003, 'crm_sales',                       'Sales: Opporunity,Catalog, Packlist, ebay');
INSERT INTO auth.master_rights (position, name, description) VALUES (20004, 'crm_other',                       'Other: Follow Up,E-Mail,Appointment');
INSERT INTO auth.master_rights (position, name, description) VALUES (20005, 'crm_document',                    'Documens');
INSERT INTO auth.master_rights (position, name, description) VALUES (20006, 'crm_finance',                     'Finance');
INSERT INTO auth.master_rights (position, name, description) VALUES (20007, 'crm_parts',                       'Parts: Edit, Warehouse');
INSERT INTO auth.master_rights (position, name, description) VALUES (20008, 'crm_service',                     'Service: Contract, Machine');
INSERT INTO auth.master_rights (position, name, description) VALUES (20009, 'crm_master',                      'Master: Client, Groups');
INSERT INTO auth.master_rights (position, name, description) VALUES (20010, 'crm_admin',                       'Admin: Label, Category, Messages, Status, KnowHow write');
INSERT INTO auth.master_rights (position, name, description) VALUES (20011, 'crm_user',                        'User');
