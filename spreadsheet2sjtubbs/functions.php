<?php
function get_spreadsheet_published_content_as_array($key){
    $url = make_spreadsheet_url($key)."&output=csv";
    $content = file_get_contents($url);
    $data = array();
    foreach(split("\n", $content) as $line){
      $data[] = str_getcsv($line);
    }
    return $data;
}

function make_spreadsheet_url($key){
    $url = 
    "https://docs.google.com/spreadsheet/pub".
    "?key=".$key.
    "&single=true".
    "&gid=0";
    return $url;
}

function strlen_chinese($str){
  $a = strlen($str);
  $b = mb_strlen($str, 'UTF8');
  return ($a + $b)/2;
}

function pad_table($data){
  $data_out = array();
  $collens = get_collens_of_table($data);
  foreach($data as $lineIndex =>$row){
    foreach($row as $colIndex =>$grid){
      $grid_plain = plain_the_link($grid);
      $paddings = $collens[$colIndex] - strlen_chinese($grid_plain);
      $data_out[$lineIndex][$colIndex] = $grid . str_repeat(" ", $paddings);
    }
  }
  return $data_out;
}

function get_collens_of_table($data){
  $collens = array();
  foreach($data as $rowIndex =>$row){
    foreach($row as $colIndex => $grid){
      $grid = plain_the_link($grid);
      $gridlen = strlen_chinese($grid);
      if(!isset($collens[$colIndex]) || $gridlen > $collens[$colIndex]){
        $collens[$colIndex] = $gridlen;
      }
    }
  }
  foreach($collens as $index=>$collen)
  {
    if($collen % 2 == 1){
      $collens[$index] += 1;
    }
  }
  return $collens;
}

function plain_the_link($str){
  $pattern = '/\[\[[^\|]*\|([^\|]*)]]/';
  $out = preg_replace($pattern, '$1', $str);
  return $out;
}

function render_table($data){
  $collens = get_collens_of_table($data);
  $tablewidth = count($collens) *2 +2 + array_sum($collens);

  $out = "";
  
  $header_lines = array();
  foreach($collens as $collen){
    $header_lines[] = str_repeat("─", $collen/2);
  }
  $head_line = "┎".join("┬", $header_lines)."┒\n";
  $body_line = "┃".join("┼", $header_lines)."┨\n";
  $foot_line  = "┖".join("┴", $header_lines) ."┚\n";

  $out .= $head_line;
  $rendered_lines = array();
  foreach($data as $line){
    $rendered_lines[] = "┃".join("│", $line)."┃\n";
  }
  $out .= join($body_line, $rendered_lines);
  $out .= $foot_line;

  return $out;
}

function update_post($userid, $pw, $board, $filename, $title, $body){
// prepare sjtubbs session
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_USERAGENT, 'yoursunny-spreadsheet2sjtubbs/20140104');
curl_setopt($ch, CURLOPT_COOKIEFILE, '');

  // sign in sjtubbs
curl_setopt($ch, CURLOPT_URL, 'https://bbs.sjtu.edu.cn/bbslogin');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, sprintf('id=%s&pw=%s&submit=login',
  urlencode($userid), urlencode($pw)));
curl_exec($ch);
 
// edit post
curl_setopt($ch, CURLOPT_URL, 'https://bbs.sjtu.edu.cn/bbsedit');
curl_setopt($ch, CURLOPT_POST, 1);
if($title){
  curl_setopt($ch, CURLOPT_POSTFIELDS, sprintf('title=%s&text=%s&type=1&board=%s&file=%s',
  urlencode(iconv('utf8','gb2312//TRANSLIT//IGNORE',$title)),
  urlencode(iconv('utf8','gb2312//TRANSLIT//IGNORE',$body)),
  urlencode($board), urlencode($filename)));
}
else{
  curl_setopt($ch, CURLOPT_POSTFIELDS, sprintf('text=%s&type=1&board=%s&file=%s',
  urlencode(iconv('utf8','gb2312//TRANSLIT//IGNORE',$body)),
  urlencode($board), urlencode($filename)));
}
curl_exec($ch);
curl_close($ch);
}
