-- @tag: reisekosten
-- @description: Fahrtenbuch und Reisekostenabrechnung

CREATE TABLE fahrtenbuch (
    id         serial NOT NULL PRIMARY KEY,
    datum      date,
    startzeit  time without time zone,
    stopzeit   time without time zone,
    reisegrund text,
    startkm    int,
    stopkm     int,
    fahrer     int,
    fahrzeug   text
);

