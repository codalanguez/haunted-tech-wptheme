#!/usr/bin/env bash
set -u
WP_ROOT="$(dirname "$(dirname "$(dirname "$1")")")"
echo 'Host load and process summary'
uptime
ps -eo comm,pcpu,pmem,etime --sort=-pcpu | head -12
for candidate in php8.4 php8.3 php8.2 php8.1 php8.0 php; do
  if command -v "$candidate" >/dev/null 2>&1 && "$candidate" -r 'exit(PHP_VERSION_ID >= 80000 ? 0 : 1);'; then PHP_BIN="$candidate"; break; fi
done
export WP_ROOT
echo 'WordPress bootstrap and sanitized plugin diagnostics'
"$PHP_BIN" <<'PHP'
<?php
$t=microtime(true);
require getenv('WP_ROOT').'/wp-load.php';
echo 'Bootstrap seconds: '.round(microtime(true)-$t,2)."\n";
echo 'Active plugin slugs: '.json_encode(get_option('active_plugins'))."\n";
$page=get_page_by_path('mechape',OBJECT,'page');
echo 'MechApe published: '.($page && $page->post_status==='publish'?'yes':'no')."\n";
echo 'Release content present: '.(strpos($page->post_content,'release-updates')!==false?'yes':'no')."\n";
echo 'Cache drop-in: '.(file_exists(getenv('WP_ROOT').'/wp-content/advanced-cache.php')?'present':'none')."\n";
foreach (['/wp-content/debug.log','/error_log','/../logs/error.log','/../logs/php-error.log'] as $relative) {
 $file=getenv('WP_ROOT').$relative;
 if(!is_readable($file))continue;
 echo 'Found readable error log: '.$relative."\n";
 $f=fopen($file,'r');fseek($f,max(0,filesize($file)-64000));$tail=stream_get_contents($f);fclose($f);
 foreach (['Fatal error','Allowed memory size','Maximum execution time','upstream timed out','pm.max_children','PHP Warning'] as $needle) echo $needle.': '.substr_count($tail,$needle)."\n";
 preg_match_all('~wp-content/(?:plugins|themes)/([a-zA-Z0-9_-]+)/~',$tail,$m);echo 'Components in error tail: '.json_encode(array_values(array_unique($m[1])))."\n";
}
PHP
echo 'Dynamic versus static response timings'
for route in '/mechape/' '/wp-json/' '/wp-login.php' '/wp-content/themes/haunted-tech-wptheme-main/assets/mechape-2.0.1-chat.jpg'; do
  printf '%s ' "$route"
  curl -sS -o /dev/null --max-time 12 -w 'status=%{http_code} total=%{time_total}\n' "https://codalanguez.com$route" || true
done
echo 'Web process counts'
ps -eo comm= | sort | uniq -c | grep -E 'php|nginx|apache|mysql' || true
echo 'Readable log filenames'
find /var/log -maxdepth 2 -type f -readable -name '*log*' 2>/dev/null | head -25
find "$(dirname "$WP_ROOT")" -maxdepth 2 -type f -readable -name '*log*' 2>/dev/null | sed "s|$(dirname "$WP_ROOT")|SITE_PARENT|g" | head -25
echo 'PHP service state'
systemctl list-units --type=service --no-pager --plain 2>/dev/null | grep -E 'php|nginx|apache' || true
echo 'Web server log error categories'
"$PHP_BIN" <<'PHP'
<?php
$paths=array_merge(glob('/var/log/*log')?:[],glob('/var/log/nginx/*log')?:[],glob('/var/log/apache2/*log')?:[]);
foreach($paths as $file){
 if(!is_readable($file)||!is_file($file))continue;
 $f=fopen($file,'r');fseek($f,max(0,filesize($file)-128000));$tail=stream_get_contents($f);fclose($f);
 $counts=[];foreach(['upstream timed out','connect() failed','Connection refused','No such file','pm.max_children','server reached','Fatal error','Allowed memory size','Maximum execution time',' 502 ',' 504 '] as $needle){$count=substr_count($tail,$needle);if($count)$counts[$needle]=$count;}
 echo basename($file).': '.json_encode($counts)."\n";
}
PHP
