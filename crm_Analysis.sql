-- @tag: Analysis
-- @description: Berichte über Umsätze

--Starttag
CREATE TABLE analysistyp(
    id      serial NOT NULL PRIMARY KEY,
    label   text,
    bericht text,
    lang    char(3),
    sort    INT DEFAULT 1,
    felder  text
);
INSERT INTO analysistyp (label,bericht,sort,lang) VALUES ('Tagesumsätze','T1',1,'de');
INSERT INTO analysistyp (label,bericht,sort,lang,felder) VALUES ('Monatsumsätze','M1',10,'de','Umsatz:netamount');
INSERT INTO analysistyp (label,bericht,sort,lang) VALUES ('Umsatz je Kunde','U1',20,'de');
INSERT INTO analysistyp (label,bericht,sort,lang) VALUES ('Umsatz je Kunde / Monat','U2',21,'de');
INSERT INTO analysistyp (label,bericht,sort,lang) VALUES ('Umsatz je Tag','U3',22,'de');
INSERT INTO analysistyp (label,bericht,sort,lang,felder) VALUES ('Umsatz nach Wochentag','U5',23,'de','Artikelnummer:p.partnumber');
INSERT INTO analysistyp (label,bericht,sort,lang) VALUES ('Verkaufte Artikel','U4',24,'de');
INSERT INTO analysistyp (label,bericht,sort,lang,felder) VALUES ('Kasse: Umsatz je Tag','K1',40,'de','Kasse:c.customernumber');
INSERT INTO analysistyp (label,bericht,sort,lang,felder) VALUES ('Kasse: Umsatz je Stunde','K2',41,'de','Kasse:c.customernumber');
INSERT INTO analysistyp (label,bericht,sort,lang,felder) VALUES ('Kasse: Menge/Umsatz Artikel','K3','41','de','Kasse:c.customernumber');
INSERT INTO analysistyp (label,bericht,sort,lang,felder) VALUES ('Kassenumsatz nach Wochentag','K4',42,'de','Kasse:c.customernumber');
