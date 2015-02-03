drop table Execution;
drop table Result;
drop table Step;

create table Execution (
 	id bigint NOT NULL  auto_increment,
	test_plan_name char(100) NOT NULL,
	device_build_num char(30) NOT NULL,
	device_serial_num char(50) NOT NULL,
	device_hardware_ver char(30), 
	run_timestamp timestamp default 0, 
	corid char(10) NOT NULL,
	primary key (id)
	);

create table Result (
	id bigint NOT NULL auto_increment,
	execution_id bigint NOT NULL, 
	test_case_name char(100) NOT NULL,
	group_type_1 char(100),
	group_type_2 char(100),
	test_result char(1) NOT NULL,
	exec_type char(10),
	exec_time  time,
	primary key (id)
	);
create table Step (
	id bigint NOT NULL auto_increment,
	result_id bigint NOT NULL,
	task char(100) NOT NULL,
	step_result char(1) NOT NULL,
	dev_log_name char(100) NOT NULL,
	test_log_name char(100) NOT NULL,
	primary key (id)
	);
