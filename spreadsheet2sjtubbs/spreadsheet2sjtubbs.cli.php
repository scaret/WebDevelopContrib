<?php
header('Content-Type: text/plain');
date_default_timezone_set('PRC');
require_once 'config.inc.php';
require_once 'functions.php';

foreach($posts as $the_post)
{
    echo "============={$the_post['title']}=============\n";
    $data = get_spreadsheet_published_content_as_array($the_post['spreadsheet_publish_key']);
    if(!$data || count($data) == 0)
    {
      echo "Fail to fetch spreadsheet: {$the_post['title']}. Fuck GFW\n";
      continue;
    }
    else{
      echo "Fetch GDoc OK: {$the_post['title']}\n\n";
    }
    $data = pad_table($data);
    $rendered_table = render_table($data);
    $extra_info  =  "\n\n--\n本文档由Google Speadsheet生成于".date('Y-m-d H:i:s')."\n";
    $extra_info .= make_spreadsheet_url($the_post['spreadsheet_publish_key']);
    $extra_info .= "\n程序支持：WebDevelop板 https://github.com/scaret/WebDevelopContrib/tree/master/spreadsheet2sjtubbs";
    echo "Ready to update:\n$rendered_table\n$extra_info\n";
    update_post(
      SJTUBBS_USER,
      SJTUBBS_PASSWD,
      $the_post["board"],
      $the_post["filename"],
      $the_post["title"],
      $rendered_table.$extra_info
    );
}
