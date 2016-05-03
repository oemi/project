
<html><head>
<title>Гостевая книга</title>
</head>
<body>
<div align="center"><h1>Гостевая книга</h1></div>

<?php

//---------- Настройки GB ----------//
$file_gb = "/var/www/homework7/gb1.dat"; // файл где хранятся записи GB
$max_rec = 100; // максимальное количество записей в файле
$rec_page = 10; // количество записей выводимых на одной странице
//----------------------------------//

// Проверка введённых данных //
function test() {
global $HTTP_POST_VARS;

 if (!isset($HTTP_POST_VARS['msg_from'],$HTTP_POST_VARS['msg_mail'],$HTTP_POST_VARS['msg_url'],$HTTP_POST_VARS['msg_message'])) {
  echo "<p align=\"center\">Ошибка при передачи параметров к скрипту! Обратитесь к администратору сайта.</p>\n";
  return(false);
 }
 if (trim($HTTP_POST_VARS['msg_from'])=="") {
  echo "<p align=\"center\">Вы не ввели своё имя. Повторите ввод.</p>\n";
  return(false);
 }

 if ($HTTP_POST_VARS['msg_mail'])<>"" && filter_var($HTTP_POST_VARS['msg_mail'], FILTER_VALIDATE_EMAIL) === false) {
  echo "<p align=\"center\">Вы неправильно ввели e-mail. Повторите ввод.</p>\n";
  return(false);
  }

 if (trim($HTTP_POST_VARS['msg_message'])=="") {
  echo "<p align=\"center\">Вы не ввели сообщение. Повторите ввод.</p>\n";
  return(false);
 }
 return(true);
} // test()

// Функция добавления записи //
function add() {
global $max_rec;
global $file_gb;
global $HTTP_POST_VARS;

 $recs = @file($file_gb) or $recs = array();

 $from = $HTTP_POST_VARS['msg_from'];
 $mail = $HTTP_POST_VARS['msg_mail'];
 $url =  $HTTP_POST_VARS['msg_url'];
 $message = $HTTP_POST_VARS['msg_message'];

 $from = str_replace ("|", "&brvbar;", $from);
 $mail = str_replace ("|","&brvbar;",$mail);
 $url =  str_replace ("|","&brvbar;",$url);
 if (strlen($message)>1000)  $message=substr($message,0,1000);
 $message = htmlspecialchars($message, ENT_QUOTES);
 $message = str_replace("|","&brvbar;",$message);
 $message = trim($message);
$message = ereg_replace ("
", "<br>", $message);

 array_unshift ($recs,"$from|$mail|$url|".date("d M Y, H:i")."|$message\n");
 if (count($recs)>$max_rec) $recs=array_slice($recs,0,$max_rec);
 $count = count($recs);
 $f = fopen ($file_gb, "w");
 for ($i=0; $i<$count; $i++) {
  fwrite($f,$recs[$i]);
 }
 fclose($f);
} // add()

// Функция вывода записей //
function view() {
global $file_gb;
global $HTTP_GET_VARS;
global $rec_page;

 if (file_exists($file_gb)) {
  $messages = file($file_gb);
  $count = count($messages);
  if ($count>$rec_page) { nav_page(ceil($count/$rec_page),(isset($HTTP_GET_VARS['page']) ? $HTTP_GET_VARS['page']: 1),"gb.php?page="); echo "<br>"; }
  $num_page=1;
  if (isset($HTTP_GET_VARS['page'])) {
   if (($HTTP_GET_VARS['page']>0) and ($HTTP_GET_VARS['page']<=ceil($count/$rec_page))) $num_page=$HTTP_GET_VARS['page'];
  }
  for ( $i=($num_page-1)*$rec_page; $i<=(($num_page*$rec_page<$count) ? $num_page*$rec_page-1: $count-1); $i++) {
   $tmp = explode("|",$messages[$i]);
   echo "<table class=\"text_info\" border=\"0\" width=\"100%\">\n";
   echo "<tr class=\"sel_p\">\n";
   echo "<td>\n";
   echo "<b>Îò:</b><br>";
   if ($tmp[2]<>"") echo "<b>URL:</b><br>";
   echo "<b>Äàòà:</b>\n";
   echo "</td>\n";
   echo "<td class=\"text_info_sel\" width=\"100%\">\n";
   echo $tmp[0];
   if ($tmp[1]<>"") { echo " | <a href=\"mailto:".$tmp[1]."\">".$tmp[1]."</a>"; }
   echo "<br>";
   if ($tmp[2]<>"") echo "<a href=\"http://".$tmp[2]."\">".$tmp[2]."</a><br>";
   echo $tmp[3]."\n";
   echo "</td>\n</tr>\n";
   echo "<td colspan=\"2\"><br>\n";
   echo $tmp[4];
   echo "</td>\n</tr>\n</table>\n";
   echo "<br>";
  } // for
  if ($count>$rec_page) { nav_page(ceil($count/$rec_page),(isset($HTTP_GET_VARS['page']) ? $HTTP_GET_VARS['page']: 1),"gb.php?page="); }
  echo "<br>";
 } else {
  echo "<center>Записей нет. Вы можете быть первым ;)</center><br>\n";
 }
} // view()

// Функция вывода навигации по страницам //
function nav_page(
                  $count,    // Общее кол-во страниц
                  $num_page, // Номер текущей страницы
                  $url       // URL для ссылки на страницу (к нему добавляется номер страницы)
                 ) {

$page_nav = 3; // сколько страниц выводить одновременно

 $begin_loop=1; // начальное значение в цикле
 $end_loop=$count; // конечное значение в цикле
 echo "<div align=\"center\">[ Страницы ($count):";
 if ($num_page>$count or $num_page<1) $num_page=1; // Проверка на корректность номера текущей страницы


 if ($num_page>$page_nav) {
  echo "  <a href=\"$url".($page_nav*(floor($num_page/$page_nav)-($num_page%$page_nav==0 ? 1: 0)))."\">(".($page_nav*(floor($num_page/$page_nav)-1-($num_page%$page_nav==0 ? 1: 0))+1)."-".($page_nav*(floor($num_page/$page_nav)-($num_page%$page_nav==0 ? 1: 0))).")</a> ...";
  $begin_loop=$page_nav*(floor($num_page/$page_nav)-($num_page%$page_nav==0 ? 1: 0))+1;
 }
 if ($count>$page_nav*(floor($num_page/$page_nav)-($num_page%$page_nav==0 ? 1: 0)+1)) { $end_loop=$page_nav*ceil($num_page/$page_nav); }
 for ($i = $begin_loop; $i <= $end_loop;  $i++) {
  if ($i==$num_page) echo "  <b>$i</b>";
     else echo "  <a href=\"$url$i\">$i</a>";
 } // for
 if ($count>$page_nav*(floor($num_page/$page_nav)-($num_page%$page_nav==0 ? 1: 0)+1)) {
  echo "  ... <a href=\"$url".($page_nav*ceil($num_page/$page_nav)+1)."\">(".($page_nav*ceil($num_page/$page_nav)+1);
  if ($page_nav*ceil($num_page/$page_nav)+1<$count) {
   echo "-".($count<=$page_nav*(ceil($num_page/$page_nav)+1) ? $count: $page_nav*(ceil($num_page/$page_nav)+1));
  }
  echo ")</a>";
 }
 echo "  ]</div>\n";
} // nav_page()

if (isset($HTTP_POST_VARS['msg_submit'])) { if (test()) add(); }
view();

?>

<form action="gb.php" method="post">
<table width="500" cellpadding="2" cellspacing="0" style="border: 1px solid rgb(0, 75, 151);" bgcolor="#d7ecff">
<tbody><tr>
<td align="right">
* Имя:
</td>
<td align="left">
<input type="text" name="msg_from" maxlength="40" size="30">
</td>
</tr>
<tr>
<td align="right">
E-Mail:
</td>
<td align="left">
<input type="text" name="msg_mail" maxlength="40" size="30">
</td>
</tr>
<tr>
<td align="right">
** URL:
</td>
<td align="left">
<input type="text" name="msg_url" maxlength="40" size="30">
</td>
</tr>
<tr>
<td align="right">
* Сообщение:
</td>
<td align="left">
<textarea cols="45" rows="7" name="msg_message"></textarea>
</td>
</tr>
<tr>
<td align="center" colspan="2">
<input type="submit" name="msg_submit" value="Добавить">
<input type="reset">
</td>
</tr>
<tr>
<td align="center" colspan="2">
* Поля обязательные для заполнения<br>
** url вводить без http://
</td>
</tr>
</tbody></table>
</form>
</body>
</html>
