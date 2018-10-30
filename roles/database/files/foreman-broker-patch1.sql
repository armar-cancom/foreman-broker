alter table results add column jobid int;
alter table results add column jobstart datetime;
alter table results add column jobname varchar(100);
drop view vw_results;
create view vw_results as select results.*,resultstate.state from results join resultstate on results.resultstateid=resultstate.stateid;
