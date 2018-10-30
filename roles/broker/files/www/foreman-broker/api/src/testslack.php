<?php
$slack_webhookurl = "https://hooks.slack.com/services/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";
$data = '{"text": "This is a slackbot test"}';
do_post_request($slack_webhookurl, $data);

function do_post_request($url, $data, $optional_headers = null)
{
  $params = array('http' => array(
              'method' => 'POST',
              'content' => $data
            ));
  if ($optional_headers !== null) {
    $params['http']['header'] = $optional_headers;
  }
  $ctx = stream_context_create($params);
  $fp = @fopen($url, 'rb', false, $ctx);
  if (!$fp) {
    throw new Exception("Problem with $url, $php_errormsg");
  }
  $response = @stream_get_contents($fp);
  if ($response === false) {
    throw new Exception("Problem reading data from $url, $php_errormsg");
  }
  return $response;
}

