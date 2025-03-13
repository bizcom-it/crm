-- @tag: Calendar
-- @description: Table events, event_category for Calendar imported from termine 

--Starttag
CREATE TABLE event_category( 
    id      serial NOT NULL PRIMARY KEY,
    label   text,
    cat_order INT DEFAULT 1,
    color 	 char(7)
);
INSERT INTO event_category (label,color) VALUES ('','');
CREATE TABLE events(
    id              serial NOT NULL PRIMARY KEY,
    title 		    text,
    "start" 	    timestamp without time zone,
    "end" 		    timestamp without time zone,
    repeat		    char(16),
    repeat_factor  	smallint,
    repeat_quantity	smallint,
    repeat_end	    timestamp without time zone,
    description     text,
    location        text,
    uid             int,
    prio            smallint,
    category 	    smallint,
    "allDay"        BOOLEAN,
    visibility	    smallint,
    color 		    character(7),
    job             boolean,
    done            boolean,
    job_planned_end timestamp without time zone,
    cust_vend_pers  text
);
INSERT INTO schema_info (tag,login) VALUES ('EventCategory','install');
