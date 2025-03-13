-- @tag: CalendarUpd
-- @description: Datenübernahme von  termine 
-- @require: Calendar

--Starttag
INSERT INTO event_category ( label, color, cat_order) SELECT catname, '#'||ccolor,sorder FROM termincat;
INSERT INTO events (title, description, location, start, "end", repeat, repeat_factor, repeat_quantity, repeat_end, uid, category, visibility,"allDay",job,done) ( SELECT cause AS title,c_cause AS description, location, ( SELECT CASE WHEN ( startZeit IS NULL OR startZeit = '' ) THEN start ELSE starttag + startZeit::INTERVAL END AS start ),( SELECT CASE WHEN ( stopZeit IS NULL OR stopZeit = '' ) THEN stop ELSE starttag + stopZeit::INTERVAL END AS stop ), ( SELECT CASE WHEN repeat=1   THEN 'day' WHEN repeat=2   THEN 'day' WHEN repeat=7   THEN 'week' WHEN repeat=14  THEN 'week' WHEN repeat=30  THEN 'month' WHEN repeat=365 THEN 'year' ELSE 'day' END ) AS repeat, ( SELECT CASE WHEN repeat=1   THEN 1 WHEN repeat=2   THEN 2 WHEN repeat=7   THEN 1 WHEN repeat=14  THEN 2 WHEN repeat=30  THEN 1 WHEN repeat=365 THEN 1 ELSE 0 END ) AS repeat_factor, ( SELECT CASE WHEN count(idx) = 0 THEN 0 ELSE count(idx)-1 END FROM termdate WHERE termid=termine.id) AS repeat_quantity, ( start + ( ( SELECT CASE WHEN repeat=1   THEN 1 WHEN repeat=2   THEN 2 WHEN repeat=7   THEN 1 WHEN repeat=14  THEN 2 WHEN repeat=30  THEN 1 WHEN repeat=365 THEN 1 END )||( SELECT CASE WHEN repeat=1   THEN 'day' WHEN repeat=2   THEN 'day' WHEN repeat=7   THEN 'week' WHEN repeat=14  THEN 'week' WHEN repeat=30  THEN 'month' WHEN repeat=365 THEN 'year' ELSE 'DAY' END ) )::INterVAL ) AS repeat_end, ( SELECT (CASE WHEN member is not null THEN member ELSE uid end) AS uid), kategorie + 1 AS category,  ( CASE WHEN privat=true THEN 2 ELSE 0 END ) AS visibility,'f','f','f' FROM termine left join terminmember tm on termin=id where (tm.tabelle = 'E' or tm.tabelle is null) );


UPDATE events SET "end" = start WHERE "end" < start;
UPDATE events SET repeat = 'day' WHERE repeat IS NULL;
UPDATE events SET repeat_factor = 0 WHERE repeat_factor IS NULL;
UPDATE events SET repeat_quantity = 0 WHERE repeat_quantity IS NULL;

--ALTER TABLE events ADD  FOREIGN KEY( category ) REFERENCES event_category( id );

