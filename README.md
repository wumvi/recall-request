```
create table tasks
(
	id integer not null
		primary key autoincrement,
	url text no,
	method text not null,
	data text not null,
	try int default 0
);

```