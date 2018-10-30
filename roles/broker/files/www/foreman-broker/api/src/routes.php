<?php

$app->get('/', function ($request, $response) {
    $data = array('status' => 'Running API Version 1');
    return $response->withJson($data);
});

//SLACK =========================

$app->get('/slack/test', function ($request, $response, $args) {
	$slack_webhookurl = $this->get("slackapi");
	$slack_channel = $this->get("slackchannel");
	$slack_username = $this->get("slackusername");
        $data = "{\"username\": \"$slack_username\", \"text\": \"This is a notification test from Foreman Broker to Slack\", \"channel\": \"$slack_channel\"}";
	do_post_request($slack_webhookurl, $data);
	$result = array('result' => '0');
        return $this->response->withJson($result);
});

$app->get('/slack/send/{message}', function ($request, $response, $args) {
        $slack_webhookurl = $this->get("slackapi");
        $slack_channel = $this->get("slackchannel");
	$slack_username = $this->get("slackusername");
	$message = $args['message'];
        $data = "{\"username\": \"$slack_username\", \"text\": \"$message\", \"channel\": \"$slack_channel\"}";
        do_post_request($slack_webhookurl, $data);
        $result = array('result' => '0');
        return $this->response->withJson($result);
});

$app->get('/slack/send/{channel}/{message}', function ($request, $response, $args) {
        $slack_webhookurl = $this->get("slackapi");
        $slack_channel = $args['channel'];
	$slack_username = $this->get("slackusername");
        $message = $args['message'];
        $data = "{\"username\": \"$slack_username\", \"text\": \"$message\", \"channel\": \"$slack_channel\"}";
        do_post_request($slack_webhookurl, $data);
        $result = array('result' => '0');
        return $this->response->withJson($result);
});

//FOREMAN ================
$app->get('/foreman/hosts', function ($request, $response, $args) {
        $foremanserver = $this->get("foreman")['host'];
	$foremanuser = $this->get("foreman")['user'];
	$foremanpassword = $this->get("foreman")['pass'];
	$curl = curl_init();
        $url = "https://".$foremanserver."/api/v2/hosts";
	curl_setopt_array($curl, array(
	  CURLOPT_URL => $url,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_SSL_VERIFYHOST => 0,
	  CURLOPT_SSL_VERIFYPEER => 0,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_HTTPHEADER => array(
            "Authorization: Basic ". base64_encode($foremanuser.":".$foremanpassword),
	    "Cache-Control: no-cache"
	  ),
	));
	$response = curl_exec($curl);
	$err = curl_error($curl);
	$foreman_hosts = json_decode($response);

        $url = "https://".$foremanserver."/api/v2/discovered_hosts";
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_SSL_VERIFYHOST => 0,
          CURLOPT_SSL_VERIFYPEER => 0,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "Authorization: Basic ". base64_encode($foremanuser.":".$foremanpassword),
            "Cache-Control: no-cache"
          ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);

        //$result = array('1' => $foremanserver, '2' => $foremanuser, '3' => $foremanpassword);
        $foreman_discoveredhosts = json_decode($response);
        $result = array_merge($foreman_discoveredhosts->results,$foreman_hosts->results);
        return $this->response->withJson($result);
});


//CUSTOMERS ===============

$app->get('/customers', function ($request, $response, $args) {
        $sth = $this->db->prepare("SELECT * FROM customers");
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});

$app->get('/customers/delete/[{id}]', function ($request, $response, $args) {
        $sth = $this->db->prepare("delete FROM customers WHERE id=:id");
        $sth->bindParam("id", $args['id']);
        $sth->execute();
        $result = array('result' => '0');
        return $this->response->withJson($result);
});

$app->get('/customers/[{id}]', function ($request, $response, $args) {
	$sth = $this->db->prepare("SELECT * FROM customers WHERE id=:id");
        $sth->bindParam("id", $args['id']);
	$sth->execute();
	$result = $sth->fetchObject();
	return $this->response->withJson($result);
});


$app->post('/customers/add', function ($request, $response, $args) {
	$data = $request->getParsedBody();
	$cust_name = filter_var($data['name'], FILTER_SANITIZE_STRING);
	$cust_addressline1 = filter_var($data['addressline1'], FILTER_SANITIZE_STRING);
	$cust_addressline2 = filter_var($data['addressline2'], FILTER_SANITIZE_STRING);
	$cust_addressline3 = filter_var($data['addressline3'], FILTER_SANITIZE_STRING);
	$cust_contact1 = filter_var($data['contact1'], FILTER_SANITIZE_STRING);
	$cust_contact1phone = filter_var($data['contact1phone'], FILTER_SANITIZE_STRING);
	$cust_contact2 = filter_var($data['contact2'], FILTER_SANITIZE_STRING);
	$cust_contact2phone = filter_var($data['contact2phone'], FILTER_SANITIZE_STRING);
	$sth = $this->db->prepare("insert into customers (name, addressline1, addressline2, addressline3, contact1, contact1phone, contact2, contact2phone) values (:name,:addressline1,:addressline2,:addressline3,:contact1,:contact1phone,:contact2,:contact2phone)");
	$sth->bindParam("name", $cust_name);
	$sth->bindParam("addressline1", $cust_addressline1);
	$sth->bindParam("addressline2", $cust_addressline2);
	$sth->bindParam("addressline3", $cust_addressline3);
	$sth->bindParam("contact1", $cust_contact1);
	$sth->bindParam("contact1phone", $cust_contact1phone);
	$sth->bindParam("contact2", $cust_contact2);
	$sth->bindParam("contact2phone", $cust_contact2phone);
	$sth->execute();
	$input['id'] = $this->db->lastInsertId();
	return $this->response->withJson($input);
});



//HOSTGROUPS =============
$app->get('/hostgroups', function ($request, $response, $args) {
        $sth = $this->db->prepare("select hostgroup.id, hostgroup.name, hostgroup.description, customers.id as customerid, customers.name as customername from hostgroup left join customers on hostgroup.customerid=customers.id;");
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});

$app->post('/hostgroups/add', function ($request, $response, $args) {
        $data = $request->getParsedBody();
        $hostgroup_name = filter_var($data['name'], FILTER_SANITIZE_STRING);
        $hostgroup_description = filter_var($data['description'], FILTER_SANITIZE_STRING);
        $hostgroup_customerid = filter_var($data['customerid'], FILTER_SANITIZE_STRING);
        $sth = $this->db->prepare("insert into hostgroup (name, description, customerid) values (:name,:description,:customerid)");
        $sth->bindParam("name", $hostgroup_name);
        $sth->bindParam("customerid", $hostgroup_customerid);
        $sth->bindParam("description", $hostgroup_description);
        $sth->execute();
        $input['id'] = $this->db->lastInsertId();
        return $this->response->withJson($input);
});

$app->get('/hostgroups/customer/{customerid}', function ($request, $response, $args) {
        $sth = $this->db->prepare("select hostgroup.id, hostgroup.name, hostgroup.description, customers.id as customerid, customers.name as customername from hostgroup left join customers on hostgroup.customerid=customers.id WHERE customers.id=:customerid;");
	$sth->bindParam("customerid", $args['customerid']);
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});

$app->get('/hostgroups/{id}', function ($request, $response, $args) {
        $sth = $this->db->prepare("select hostgroup.id, hostgroup.name, hostgroup.description, customers.id as customerid, customers.name as customername from hostgroup left join customers on hostgroup.customerid=customers.id WHERE hostgroup.id=:id;");
        $sth->bindParam("id", $args['id']);
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});


$app->get('/hostgroups/delete/{id}', function ($request, $response, $args) {
        $sth = $this->db->prepare("delete FROM hostgroup WHERE id=:id");
        $sth->bindParam("id", $args['id']);
        $sth->execute();
        $result = array('result' => '0');
        return $this->response->withJson($result);
});


$app->post('/hostgroups/addhost/{hostgroupid}/{hostid}', function ($request, $response, $args) {
        $sth = $this->db->prepare("update host set hostgroupid=:hostgroupid WHERE id=:hostid;");
        $sth->bindParam("hostgroupid", $args['hostgroupid']);
        $sth->bindParam("hostid", $args['hostid']);
        $sth->execute();
        $result = array('result' => '0');
        return $this->response->withJson($result);
});

$app->post('/hostgroups/deletehost/{hostgroupid}/{hostid}', function ($request, $response, $args) {
        $sth = $this->db->prepare("update host set hostgroupid=0 WHERE id=:hostid;");
        $sth->bindParam("hostid", $args['hostid']);
        $sth->execute();
        $result = array('result' => '0');
        return $this->response->withJson($result);
});


//HOSTS ===================
$app->get('/hosts', function ($request, $response, $args) {
        $sth = $this->db->prepare("select host.*,hostgroup.name as hostgroupname from host left join hostgroup on host.hostgroupid=hostgroup.id;");
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});

$app->get('/hosts/assigned', function ($request, $response, $args) {
        $sth = $this->db->prepare("select host.*,hostgroup.name as hostgroupname from host left join hostgroup on host.hostgroupid=hostgroup.id where host.hostgroupid in (select id from hostgroup);");
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});

$app->get('/hosts/assigned/{hostgroupid}', function ($request, $response, $args) {
        $sth = $this->db->prepare("select host.*,hostgroup.name as hostgroupname from host left join hostgroup on host.hostgroupid=hostgroup.id where host.hostgroupid in (select id from hostgroup where host.hostgroupid=:hostgroupid);");
	$sth->bindParam("hostgroupid", $args['hostgroupid']);
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});

$app->get('/hosts/unassigned', function ($request, $response, $args) {
        $sth = $this->db->prepare("select host.*,hostgroup.name as hostgroupname from host left join hostgroup on host.hostgroupid=hostgroup.id where host.hostgroupid not in (select id from hostgroup);");
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});

$app->post('/hosts/add', function ($request, $response, $args) {
        $data = $request->getParsedBody();
        $host_name = filter_var($data['name'], FILTER_SANITIZE_STRING);
        $host_description = filter_var($data['description'], FILTER_SANITIZE_STRING);
        $host_hostgroupid = filter_var($data['hostgroupid'], FILTER_SANITIZE_STRING);
        //$sth = $this->db->prepare("insert into host (name, description, hostgroupid) values (:name,:description,:hostgroupid) where :name not in (select name from host)");
        $sth = $this->db->prepare("insert into host (name, description, hostgroupid) select :name,:description,:hostgroupid from dual where not exists (select 1 from host where name=:name)");
        $sth->bindParam("name", $host_name);
        $sth->bindParam("hostgroupid", $host_hostgroupid);
        $sth->bindParam("description", $host_description);
        $sth->execute();
        $input['id'] = $this->db->lastInsertId();
        return $this->response->withJson($input);
});

$app->get('/hosts/delete/{id}', function ($request, $response, $args) {
        $sth = $this->db->prepare("delete FROM host WHERE id=:id");
        $sth->bindParam("id", $args['id']);
        $sth->execute();
        $result = array('result' => '0');
        return $this->response->withJson($result);
});


//JOBS ===================

$app->get('/jobs', function ($request, $response, $args) {
        $sth = $this->db->prepare("select * from job order by name;");
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});

$app->post('/jobs/add', function ($request, $response, $args) {
        $data = $request->getParsedBody();
        $job_name = filter_var($data['name'], FILTER_SANITIZE_STRING);
        $job_description = filter_var($data['description'], FILTER_SANITIZE_STRING);
        $sth = $this->db->prepare("insert into job (name, description) values (:name,:description)");
        $sth->bindParam("name", $job_name);
        $sth->bindParam("description", $job_description);
        $sth->execute();
        $input['id'] = $this->db->lastInsertId();
        return $this->response->withJson($input);
});


$app->get('/jobs/status/{assignedjobid}', function ($request, $response, $args) {
        $sth = $this->db->prepare("select *,round((complete/total) * 100,0) as pctcomplete from (select customername, assignedjobid, statusid, jobname, jobdescription,  count(statusid) as total,ifnull((select count(statusid)  as total from vw_assignedtasks h where statusid=5 and assignedjobid=d.assignedjobid group by assignedjobid) ,0)  as complete from vw_assignedtasks d group by assignedjobid) as t where assignedjobid=:assignedjobid;");
        $sth->bindParam("assignedjobid", $args['assignedjobid']);
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});


$app->post('/jobs/assign', function ($request, $response, $args) {
        $data = $request->getParsedBody();
        $job_jobid = filter_var($data['jobid'], FILTER_SANITIZE_STRING);
        $job_groupid = filter_var($data['groupid'], FILTER_SANITIZE_STRING);
        $sth = $this->db->prepare("insert into assignedjobs (jobid, groupid, status, pctcomplete) values (:jobid, :groupid, 2, 0)");
        $sth->bindParam("jobid", $job_jobid);
        $sth->bindParam("groupid", $job_groupid);
        $sth->execute();
        $input['id'] = $this->db->lastInsertId();
        return $this->response->withJson($input);
});

$app->get('/jobs/addtask/{jobid}/{taskid}', function ($request, $response, $args) {
        $sth = $this->db->prepare("insert into jobs(jobid,taskid) values(:jobid,:taskid);");
        $sth->bindParam("jobid", $args['jobid']);
        $sth->bindParam("taskid", $args['taskid']);
        $sth->execute();
        $result = array('result' => '0');
        return $this->response->withJson($result);
});


$app->get('/jobs/deletetask/{jobid}/{taskid}', function ($request, $response, $args) {
        $sth = $this->db->prepare("delete from jobs where jobid=:jobid and taskid=:taskid;");
        $sth->bindParam("jobid", $args['jobid']);
        $sth->bindParam("taskid", $args['taskid']);
        $sth->execute();
        $result = array('result' => '0');
        return $this->response->withJson($result);
});


$app->get('/jobs/assigned/delete/{id}', function ($request, $response, $args) {
        $sth = $this->db->prepare("delete FROM assignedjobs WHERE id=:id");
        $sth->bindParam("id", $args['id']);
        $sth->execute();
        $result = array('result' => '0');
        return $this->response->withJson($result);
});

$app->get('/jobs/assigned', function ($request, $response, $args) {
        $sth = $this->db->prepare("select assignedjobs.id, customers.name as customername, jobid, job.name as jobname, job.description, groupid, hostgroup.name as hostgroupname,pctcomplete, notify, jobstatus.statusid, jobstatus.status from assignedjobs join jobstatus on assignedjobs.status=jobstatus.statusid join hostgroup on assignedjobs.groupid=hostgroup.id join job on assignedjobs.jobid = job.id join customers on customers.id=hostgroup.customerid order by customers.name, hostgroup.name;");
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});

$app->get('/jobs/status', function ($request, $response, $args) {
        $sth = $this->db->prepare("select *,(totaltasks - completedtasks + errortasks) as taskstatus from vw_job_task_status_rollup where totaltasks > 0;;");
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});

$app->get('/jobs/assigned/start/{jobid}', function ($request, $response, $args) {
        $sth = $this->db->prepare("update assignedjobs set status=3 where id=:jobid ;");
        $sth->bindParam("jobid", $args['jobid']);
        $sth->execute();
	//$sth = $this->db->prepare("insert into assignedtasks(taskid, statusid, hostid, assignedjobid) select taskid, 1, hostid, jobid from  vw_assigned_tasks_to_host where statusid=3 and concat(taskid,statusid,hostid,jobid) not in (select concat(taskid,statusid,hostid,assignedjobid) from assignedtasks);");
	$sth = $this->db->prepare("insert into assignedtasks(taskid, statusid, hostid, assignedjobid) select taskid, 1, hostid, jobid from  vw_assigned_tasks_to_host where statusid=3 and concat(taskid,hostid,jobid) not in (select concat(taskid,hostid,assignedjobid) from assignedtasks);");
	$sth->execute();
        $result = array('result' => '0');
        return $this->response->withJson($result);
});

$app->get('/jobs/assigned/stop/{jobid}', function ($request, $response, $args) {
        $sth = $this->db->prepare("update assignedjobs set status=2 where id=:jobid;");
        $sth->bindParam("jobid", $args['jobid']);
        $sth->execute();
        $sth = $this->db->prepare("update assignedtasks set statusid=2 where assignedjobid=:jobid;");
        $sth->bindParam("jobid", $args['jobid']);
        $sth->execute();
	$sth = $this->db->prepare("delete from assignedtasks where assignedjobid=:jobid and statusid=2;");
        $sth->bindParam("jobid", $args['jobid']);
	$sth->execute();
        $result = array('result' => '0');
        return $this->response->withJson($result);
});

$app->get('/jobs/assigned/completed/{jobid}', function ($request, $response, $args) {
        $sth = $this->db->prepare("update assignedjobs set status=5 where id=:jobid ;");
        $sth->bindParam("jobid", $args['jobid']);
        $sth->execute();
        $result = array('result' => '0');
        return $this->response->withJson($result);
});

$app->get('/jobs/assignedtasks', function ($request, $response, $args) {
        $sth = $this->db->prepare("select task.id as taskid, task.name, task.description, jobs.id as jobid from task join jobs on task.id=jobs.taskid order by jobs.id;");
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});

$app->get('/jobs/assignedtasks/{jobid}', function ($request, $response, $args) {
	$sth = $this->db->prepare("select jobs.jobid as jobid, jobs.taskid as taskid, task.name, task.description from jobs join task on jobs.taskid=task.id where jobid=:jobid order by jobs.id;");
	$sth->bindParam("jobid", $args['jobid']);
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});

//RESULTS ===================
$app->post('/results/add/{hostname}', function ($request, $response, $args) {
        $data = $request->getParsedBody();
        $result_result = filter_var($data['result'], FILTER_SANITIZE_STRING);
        $result_taskname = filter_var($data['taskname'], FILTER_SANITIZE_STRING);
        $result_description = filter_var($data['description'], FILTER_SANITIZE_STRING);
        $result_resultstateid = filter_var($data['resultstateid'], FILTER_SANITIZE_STRING);
        $result_jobid = filter_var($data['jobid'], FILTER_SANITIZE_STRING);
        $result_jobstart = filter_var($data['jobstart'], FILTER_SANITIZE_STRING);
        $result_jobname = filter_var($data['jobname'], FILTER_SANITIZE_STRING);
        $sth = $this->db->prepare("insert into results (hostname, taskname, result, description, resultstateid, timestamp, jobid, jobstart, jobname) values (:hostname,:taskname,:result,:description,:resultstateid, now(),:jobid,:jobstart,:jobname)");
	$sth->bindParam("hostname", $args['hostname']);
        $sth->bindParam("result", $result_result);
        $sth->bindParam("taskname", $result_taskname);
        $sth->bindParam("description", $result_description);
        $sth->bindParam("resultstateid", $result_resultstateid);
        $sth->bindParam("jobid", $result_jobid);
        $sth->bindParam("jobstart", $result_jobstart);
        $sth->bindParam("jobname", $result_jobname);
        $sth->execute();
        $input['id'] = $this->db->lastInsertId();
        return $this->response->withJson($input);
});

$app->get('/results', function ($request, $response, $args) {
        $sth = $this->db->prepare("select * from vw_results;");
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});

$app->get('/results/{hostname}', function ($request, $response, $args) {
        $sth = $this->db->prepare("select * from vw_results where hostname=:hostname;");
	$sth->bindParam("hostname", $args['hostname']);
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});


//TASKS ===================

$app->get('/tasks', function ($request, $response, $args) {
        $sth = $this->db->prepare("select * from task order by name;");
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});

$app->get('/tasks/delete/{taskid}', function ($request, $response, $args) {
        $sth = $this->db->prepare("delete from task where id=:taskid;");
	$sth->bindParam("taskid", $args['taskid']);
        $sth->execute();
        $result = array('result' => '0');
	return $this->response->withJson($result);
});


$app->post('/tasks/add', function ($request, $response, $args) {
        $data = $request->getParsedBody();
        $task_name = filter_var($data['name'], FILTER_SANITIZE_STRING);
        $task_description = filter_var($data['description'], FILTER_SANITIZE_STRING);
	$task_foremantemplate = filter_var($data['foremantemplate'], FILTER_SANITIZE_STRING);
	$task_isreportable = filter_var($data['isreportable'], FILTER_SANITIZE_STRING);
	$task_notify = filter_var($data['notify'], FILTER_SANITIZE_STRING);
	$task_executeremote = filter_var($data['executeremote'], FILTER_SANITIZE_STRING);
        $sth = $this->db->prepare("insert into task (name, description, foremantemplate, isreportable, notify, executeremote) values (:name,:description,:foremantemplate,:isreportable,:notify,:executeremote)");
        $sth->bindParam("name", $task_name);
        $sth->bindParam("description", $task_description);
        $sth->bindParam("foremantemplate", $task_foremantemplate);
        $sth->bindParam("isreportable", $task_isreportable);
        $sth->bindParam("notify", $task_notify);
        $sth->bindParam("executeremote", $task_executeremote);
        $sth->execute();
        $input['id'] = $this->db->lastInsertId();
        return $this->response->withJson($input);
});



$app->get('/tasks/assigned', function ($request, $response, $args) {
        $sth = $this->db->prepare("select * from vw_assigned_tasks_to_host order by customername, hostname, name;");
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});

$app->get('/mytasks/foreman', function ($request, $response, $args) {
        $sth = $this->db->prepare("select * from (select * from vw_assignedtasks where statusid=1 order by id) t group by hostname having executeremote=1;"); 
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});

$app->get('/mytasks/{hostname}', function ($request, $response, $args) {
        $sth = $this->db->prepare("select * from vw_assignedtasks where hostname=:hostname;");
	$sth->bindParam("hostname", $args['hostname']);
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});

$app->get('/mytasks/{hostname}/total', function ($request, $response, $args) {
        $sth = $this->db->prepare("select count(1) as total from vw_assignedtasks where hostname=:hostname;");
        $sth->bindParam("hostname", $args['hostname']);
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});

$app->get('/mytasks/{hostname}/next', function ($request, $response, $args) {
        $sth = $this->db->prepare("select * from vw_assignedtasks where hostname=:hostname and statusid in (1,3) order by id limit 1;");
        $sth->bindParam("hostname", $args['hostname']);
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});

$app->get('/mytasks/{hostname}/completetask/{assignedtaskid}', function ($request, $response, $args) {
        $sth = $this->db->prepare("update assignedtasks set statusid=5 where id=:assignedtaskid;");
        $sth->bindParam("assignedtaskid", $args['assignedtaskid']);
        $sth->execute();
        $result = array('result' => '0');
        return $this->response->withJson($result);
});  

$app->get('/mytasks/{hostname}/starttask/{assignedtaskid}', function ($request, $response, $args) {
        $sth = $this->db->prepare("update assignedtasks set statusid=3 where id=:assignedtaskid;");
        $sth->bindParam("assignedtaskid", $args['assignedtaskid']);
        $sth->execute();
        $result = array('result' => '0');
        return $this->response->withJson($result);
}); 

$app->get('/tasks/assigned/runningjobs', function ($request, $response, $args) {
        $sth = $this->db->prepare("select * from vw_assignedtasks order by customername, hostname, taskid;");
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});

$app->get('/tasks/{taskid}', function ($request, $response, $args) {
        $sth = $this->db->prepare("select * from task where id=:taskid;");
	$sth->bindParam("taskid", $args['taskid']);
        $sth->execute();
        $result = $sth->fetchAll();
        return $this->response->withJson($result);
});

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");

    return $response;
});

$app->post('/tasks/update', function ($request, $response, $args) {
        $data = $request->getParsedBody();
	$task_id = filter_var($data['id'], FILTER_SANITIZE_STRING);
        $task_name = filter_var($data['name'], FILTER_SANITIZE_STRING);
        $task_description = filter_var($data['description'], FILTER_SANITIZE_STRING);
        $task_foremantemplate = filter_var($data['foremantemplate'], FILTER_SANITIZE_STRING);
        $task_executeremote = filter_var($data['executeremote'], FILTER_SANITIZE_STRING);
        $sth = $this->db->prepare("update task set name=:name, description=:description, foremantemplate=:foremantemplate, executeremote=:executeremote where id=:taskid;");
        $sth->bindParam("name", $task_name);
        $sth->bindParam("taskid", $task_id);
        $sth->bindParam("description", $task_description);
        $sth->bindParam("foremantemplate", $task_foremantemplate);
        $sth->bindParam("executeremote", $task_executeremote);
        $sth->execute();
        $result = array('result' => '0');
        return $this->response->withJson($result);
});

$app->get('/bye/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $data = array('name' => $name);
    $response->withJson($data);

    return $response;
});


//EXAMPLES===========================================================
//GET RECORDS
$app->get('/todos', function ($request, $response, $args) {
	$sth = $this->db->prepare("SELECT * FROM todo");
	$sth->execute();
	$todos = $sth->fetchAll();
        return $this->response->withJson($todos);
});

//GET RECORD BY ID
$app->get('/todo/[{id}]', function ($request, $response, $args) {
$sth = $this->db->prepare("SELECT * FROM products WHERE id=:id");
       $sth->bindParam("id", $args['id']);
$sth->execute();
$todos = $sth->fetchObject();
    return $this->response->withJson($todos);
});

//ADD RECORD
$app->get('/todo/add/[{id}]', function ($request, $response, $args) {
$sth = $this->db->prepare("insert into todo (task) values (:task)");
$sth->bindParam("task", $args['id']);
$sth->execute();
$input['id'] = $this->db->lastInsertId();
    return $this->response->withJson($input);
});


//DELETE RECORD
// DELETE a todo with given id
$app->get('/todo/delete/[{id}]', function ($request, $response, $args) {
 $sth = $this->db->prepare("DELETE FROM todo WHERE id=:id");
        $sth->bindParam("id", $args['id']);
 $sth->execute();
 $todos = array('status' => 'Record Deleted');;
     return $this->response->withJson($todos);
 });




