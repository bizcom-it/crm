-- @tag: EventRessourcen
-- @description: Table ressourcen for Calendar  

--Starttag
CREATE TABLE ressourcen_category(
    id      serial NOT NULL PRIMARY KEY,
    category    text 
);
CREATE TABLE ressourcen(
    id      serial NOT NULL PRIMARY KEY,
    ressource   text,
    category    integer,
    resorder integer NOT NULL DEFAULT 1,
    color    char(7)
);

