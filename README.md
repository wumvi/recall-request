```mysql
create database recall_request;

CREATE USER 'service'@'%' IDENTIFIED BY 'service';
GRANT ALL PRIVILEGES ON *.* TO 'service'@'%';
FLUSH PRIVILEGES;
```

```mysql
create table recall_request
(
	id int auto_increment,
	url varchar(255) not null,
	method varchar(4) not null,
	data varchar(255) not null,
	attempt int default 0 not null,
	last_error text null,
	constraint recall_request_id_uindex
		unique (id),
	constraint recall_request_pk
		unique (url, method, data)
)
comment 'Хранит данные для повторных вызовов';

alter table recall_request
	add primary key (id);

```

```mysql
create definer = service@`%` procedure recall_add_record(IN p_url varchar(255), IN p_method varchar(4), IN p_data varchar(255))
begin
    INSERT IGNORE into recall_request (url, method, data) values (p_url, p_method, p_data);
end;

create definer = service@`%` procedure recall_delete_record(IN p_id int)
begin
    delete from recall_request where id = p_id;
end;

create definer = service@`%` procedure recall_get_records()
begin
    select id, url, method, data from recall_request;
end;

create definer = service@`%` procedure recall_set_error_to_record(IN p_id int, IN p_error text)
begin
    update recall_request
    set attempt = attempt + 1, last_error = p_error
    where id = p_id;
end;

```