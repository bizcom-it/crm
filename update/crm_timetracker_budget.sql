-- @tag: timetracker_budget
-- @description: Budget erfassen
-- @require: timetracker

ALTER TABLE timetrack ADD COLUMN budget numeric(15,5);

