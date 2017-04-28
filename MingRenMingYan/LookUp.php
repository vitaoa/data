<?php
$title = '新华字典'; //查询名称，不必修改
$is_cache = 1; //缓存设置 1打开 0关闭

$dbhost = 'localhost'; //MYSQL服务器【需配置】
$dbname = '911cha'; //数据库名称【需配置】
$dbuser = 'root'; //数据库用户名【需配置】
$dbpw = 'softblue'; //数据库密码【需配置】
$tbname = 'zi'; //数据表名【需配置】

function getlink($query){
	global $dbhost,$dbuser,$dbpw,$dbname;
	$link = mysql_connect($dbhost, $dbuser, $dbpw)
		or die('无法连接: ' . mysql_error());

	mysql_query("set character set 'utf8'");
	mysql_select_db($dbname) or die('不能连接数据库！');
	mysql_query("SET NAMES UTF8");

	// 执行 SQL 查询
	$result = mysql_query($query) or die('查询失败: ' . mysql_error());
	return $result;
}

$bpa = "丨亅丿乛一乙乚丶八勹匕冫卜厂刀刂儿二匚阝丷几卩冂力冖凵人亻入十厶亠匸讠廴又艹屮彳巛川辶寸大飞干工弓廾广己彐彑巾口马门宀女犭山彡尸饣士扌氵纟巳土囗兀夕小忄幺弋尢夂子贝比灬长车歹斗厄方风父戈卝户火旡见斤耂毛木肀牛牜爿片攴攵气欠犬日氏礻手殳水瓦尣王韦文毋心牙爻曰月爫支止爪白癶歺甘瓜禾钅立龙矛皿母目疒鸟皮生石矢示罒田玄穴疋业衤用玉耒艸臣虫而耳缶艮虍臼米齐肉色舌覀页先行血羊聿至舟衣竹自羽糸糹貝采镸車辰赤辵豆谷見角克里卤麦身豕辛言邑酉豸走足青靑雨齿長非阜金釒隶門靣飠鱼隹風革骨鬼韭面首韋香頁音髟鬯鬥高鬲馬黄鹵鹿麻麥鳥魚鼎黑黽黍黹鼓鼠鼻齊齒龍龠"; //部首数组

if($_POST['q']){ //搜索
	$q = htmlspecialchars(trim($_POST['q']));
}elseif($_GET['id']){
	$id = $_GET['id'];
}elseif($_GET['list']){
	$lst = intval($_GET['list']);
}

function getR($q){ //搜索
	global $bpa,$tbname;
	if(preg_match("/^[A-Za-z]+$/",$q)){ //如果搜的拼音
		$sql = "select id,zi from ".$tbname." where py = '".strtolower($q)."' limit 150";
	}else{
		$sql = "select id,zi from ".$tbname." where zi like '%".$q."%' limit 150";
	}
	$result = getlink($sql);

	while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$res[] = $line;
	}
	if(count($res)==1) header("location: ?id=".$res[0]['id']);
	return $res;
}

function getArr($num,$t=0){ //获取缓存的数据
	if($t==0){
		$furl = "cache\\".floor(($num-1)/1000)."\\";
	}else{
		$furl = 'cache\\';
	}
	$filename = $furl.$num.".txt"; //缓存文件名

	if(file_exists($filename)){
		return unserialize(@file_get_contents($filename));
	}else{
		return false;
	}
}

function cacheArr($num,$arr,$t=0){ //将数据存下来
	if($t==0){
		$furl = "cache\\".floor(($num-1)/1000)."\\";
	}else{
		$furl = 'cache\\';
	}
	$filename = $furl.$num.".txt"; //缓存文件名

	if(!file_exists($furl)){ //创建文件夹
		if(!file_exists("cache\\") && $t==0){
			mkdir("cache\\", 0777);
		}
		mkdir($furl, 0777);
	}

	$t=serialize($arr);
	$fp = @fopen($filename,"w");
	@fwrite($fp,$t);
	@fclose($fp);
}

function listzidian($id){ //列表
	global $bpa,$tbname,$is_cache;
	if($is_cache==1){
		$c = getArr($id,1);
		if($c!=false) return $c;
	}

	$pos = substr($bpa,$id*3-3,3);

	if($pos==""){
		$sql = "select id,zi,bihua from ".$tbname." where bushou='难检字' or bushou='' order by bushou";
		$res['type'] = "难检字";
	}else{
		$sql = "select id,zi,bihua from ".$tbname." where bushou='".$pos."' order by bushou";
		$res['type'] = $pos;
	}
	$result = getlink($sql);

	while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$res[] = $line;
	}
	if($is_cache==1){
		cacheArr($id,$res,1);
	}
	return $res;
}

function zidian($id){ //某个ID
	global $is_cache,$tbname;
	if($is_cache==1){
		$c = getArr($id);
		if($c!=false) return $c;
	}

	$sql = "select * from ".$tbname." where id=".$id." limit 1";
	$result = getlink($sql);

	$line = mysql_fetch_array($result, MYSQL_ASSOC);
	if(!isset($line['zi'])) return false;

	$nsql = "select id,zi from ".$tbname." where id=".($id-1)." or id=".($id+1);
	$nresult = getlink($nsql);
	if($id==1){
		$line[1] = null;
		$line[2] = mysql_fetch_array($nresult, MYSQL_ASSOC);
	}elseif($id==31715){
		$line[1] = mysql_fetch_array($nresult, MYSQL_ASSOC);
		$line[2] = null;
	}else{
		$line[1] = mysql_fetch_array($nresult, MYSQL_ASSOC);
		$line[2] = mysql_fetch_array($nresult, MYSQL_ASSOC);
	}
	
	if($is_cache==1){
		cacheArr($id,$line);
	}
	return $line;
}

if($q){ //搜索
	$zidianArr = getR($q);
	if(count($zidianArr)==1) header("location: ?id=".$zidianArr[0]['id']);
	$cha_title = $q." 的汉字搜索结果 - ".$title;
	$zidianStatus = 1;
}elseif(isset($lst)){ //列表
	if($lst<1) header("location: ./");
	$zidianArr = listzidian($lst);
	$cha_title = "偏旁部首为“".$zidianArr['type']."”的汉字 - ".$title;
	$zidianStatus = 2;
}elseif(isset($id)){ //某ID
	$zidianArr = zidian($id);
	if(!isset($zidianArr['zi'])) header("location: ./");
	$cha_title = $zidianArr['zi']." - ".$title;
	$zidianStatus = 3;
}else{
	$zidianStatus = 0;
	$cha_title = $title;
}
?><html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=$cha_title?></title>
<style>
*{margin:0;padding:0;}
body{font-size:12px;font-family: Geneva, Arial, Helvetica, sans-serif;}
a img {border:0;}
.red{color:#f00;}
.center{text-align:center;}
a,a:visited{color:#0353ce;}
table {font-size:12px;margin:0 auto;}
	table th {font-size:12px;font-weight:bold;background-color:#f7f7f7;line-height:200%;padding: 0 5px;}
	table th {font-size:12px;font-weight:bold;background:#EDF7FF;padding: 0 5px;color:#014198;line-height:200%;}
#footer{line-height:150%;text-align:center;color:#9c9c9c;padding: 8px 0;}
	#footer a,#footer a:visited{color:#9c9c9c;}
.ttitle{text-decoration:none;font-size:24px; line-height:150%;font-weight:bold;}
p{padding: 10px;}
.cboth{clear:both;font-size:0px;}
a.lan,a.lan:visited{color:#999;}
#alltools{background-color:#fff;border:1px #B2D0EA solid;padding:9px;clear:both;width:758px!important;width:778px;margin:10px auto 0 auto;}
ul.all{padding:0;margin:0;margin:0 0 8px 0!important;width:100%;}
	ul.all li{list-style-type:none;float:left;padding:0 0 0 12px;line-height:24px;height:24px;overflow:hidden;width:113px!important;width:125px;text-align:left;}
h1.zi{background:#fff url(zibg.gif) 2px 2px no-repeat;font-size:48px;text-align:center;width:60px;height:70px;padding:0;*padding:5px 0 0 0;color:#000;}
h1{font-size:16px;padding:10px 8px;color:#014198;}
strong a{text-decoration:none;color:#000;}
</style>
</head>

<body>
<div align="center">
<table cellspacing="0" cellpadding="0" width="778" border="0"><tr><td align="left" style="padding:10px 0"><a href="http://zidian.911cha.com/" class="ttitle">新华字典</a></td></tr></table>
<table width="778" cellpadding="2" cellspacing="0" style="border:1px solid #B2D0EA;" id="top"><tr><td style="background:#EDF7FF;padding:0 5px;color:#014198;" height="26" valign="middle" colspan="5"><a href="http://www.911cha.com/">实用查询</a> &gt; <a href="./">新华字典</a></td></tr><tr><td align="center" valign="middle" height="60"><form action="./" method="post" name="f1"><input name="q" id="q" type="text" size="18" delay="0" value="" style="width:300px;height:22px;font-size:16px;font-family: Geneva, Arial, Helvetica, sans-serif;" onmouseover="this.focus();" /> <input type="submit" value=" 查询 " /><br />查找汉字<span style="color:blue">卐</span>，直接输入<span style="color:blue">卐</span>，或其拼音<span style="color:blue">wan</span>即可</form></td></tr></table><br />
<? if($zidianStatus==0){ ?>
<table width="778" cellpadding="2" cellspacing="0" style="border:1px solid #B2D0EA;"><tr><td style="background:#EDF7FF;padding:0 5px;color:#014198;" height="26" valign="middle" colspan="5">新华字典</td></tr><tr><td style="padding:8px;font-size:14px;"><p>　　本新华字典收录汉字2万多个，提供汉字的拼音、偏旁部首、五笔、笔画、汉字的解释。</p><p>　　汉字是汉语书写的最基本单元，其使用最晚始于商代，历经甲骨文、大篆、小篆、隶书、楷书（草书、行书）诸般书体变化。秦始皇统一中国，李斯整理小篆，“书同文”的历史从此开始。</p><p>　　三千余年来，汉字的书写方式变化不大，使得后人得以阅读古文而不生窒碍。但近代西方文明进入东亚之后，整个汉字文化圈的各个国家纷纷掀起了学习西方的思潮，其中，放弃使用汉字是这场运动的一个重要方面。这些运动的立论以为：跟西方拼音文字相比，汉字是繁琐笨拙的。许多使用汉字国家即进行了不同程度的汉字简化，甚至还有完全拼音化的尝试。日文假名的拉丁转写方案以及汉语多种拼音方案的出现都是基于这种思想。中国大陆将汉字笔划参考行书草书加以省简，于1956年1月28日审订通过《简化字总表》，在中国及新加坡使用至今。台湾则一直使用繁体中文。</p>
<p style="line-height:200%">笔画一：<a href="?list=1">丨</a> <a href="?list=2">亅</a> <a href="?list=3">丿</a> <a href="?list=4">乛</a> <a href="?list=5">一</a> <a href="?list=6">乙</a> <a href="?list=7">乚</a> <a href="?list=8">丶</a><br />
笔画二：<a href="?list=9">八</a> <a href="?list=10">勹</a> <a href="?list=11">匕</a> <a href="?list=12">冫</a> <a href="?list=13">卜</a> <a href="?list=14">厂</a> <a href="?list=15">刀</a> <a href="?list=16">刂</a> <a href="?list=17">儿</a> <a href="?list=18">二</a> <a href="?list=19">匚</a> <a href="?list=20">阝</a> <a href="?list=21">丷</a> <a href="?list=22">几</a> <a href="?list=23">卩</a> <a href="?list=24">冂</a> <a href="?list=25">力</a> <a href="?list=26">冖</a> <a href="?list=27">凵</a> <a href="?list=28">人</a> <a href="?list=29">亻</a> <a href="?list=30">入</a> <a href="?list=31">十</a> <a href="?list=32">厶</a> <a href="?list=33">亠</a> <a href="?list=34">匸</a> <a href="?list=35">讠</a> <a href="?list=36">廴</a> <a href="?list=37">又</a><br />
笔画三：<a href="?list=38">艹</a> <a href="?list=39">屮</a> <a href="?list=40">彳</a> <a href="?list=41">巛</a> <a href="?list=42">川</a> <a href="?list=43">辶</a> <a href="?list=44">寸</a> <a href="?list=45">大</a> <a href="?list=46">飞</a> <a href="?list=47">干</a> <a href="?list=48">工</a> <a href="?list=49">弓</a> <a href="?list=50">廾</a> <a href="?list=51">广</a> <a href="?list=52">己</a> <a href="?list=53">彐</a> <a href="?list=54">彑</a> <a href="?list=55">巾</a> <a href="?list=56">口</a> <a href="?list=57">马</a> <a href="?list=58">门</a> <a href="?list=59">宀</a> <a href="?list=60">女</a> <a href="?list=61">犭</a> <a href="?list=62">山</a> <a href="?list=63">彡</a> <a href="?list=64">尸</a> <a href="?list=65">饣</a> <a href="?list=66">士</a> <a href="?list=67">扌</a> <a href="?list=68">氵</a> <a href="?list=69">纟</a> <a href="?list=70">巳</a> <a href="?list=71">土</a> <a href="?list=72">囗</a> <a href="?list=73">兀</a> <a href="?list=74">夕</a> <a href="?list=75">小</a> <a href="?list=76">忄</a> <a href="?list=77">幺</a> <a href="?list=78">弋</a> <a href="?list=79">尢</a> <a href="?list=80">夂</a> <a href="?list=81">子</a><br />
笔画四：<a href="?list=82">贝</a> <a href="?list=83">比</a> <a href="?list=84">灬</a> <a href="?list=85">长</a> <a href="?list=86">车</a> <a href="?list=87">歹</a> <a href="?list=88">斗</a> <a href="?list=89">厄</a> <a href="?list=90">方</a> <a href="?list=91">风</a> <a href="?list=92">父</a> <a href="?list=93">戈</a> <a href="?list=94">卝</a> <a href="?list=95">户</a> <a href="?list=96">火</a> <a href="?list=97">旡</a> <a href="?list=98">见</a> <a href="?list=99">斤</a> <a href="?list=100">耂</a> <a href="?list=101">毛</a> <a href="?list=102">木</a> <a href="?list=103">肀</a> <a href="?list=104">牛</a> <a href="?list=105">牜</a> <a href="?list=106">爿</a> <a href="?list=107">片</a> <a href="?list=108">攴</a> <a href="?list=109">攵</a> <a href="?list=110">气</a> <a href="?list=111">欠</a> <a href="?list=112">犬</a> <a href="?list=113">日</a> <a href="?list=114">氏</a> <a href="?list=115">礻</a> <a href="?list=116">手</a> <a href="?list=117">殳</a> <a href="?list=118">水</a> <a href="?list=119">瓦</a> <a href="?list=120">尣</a> <a href="?list=121">王</a> <a href="?list=122">韦</a> <a href="?list=123">文</a> <a href="?list=124">毋</a> <a href="?list=125">心</a> <a href="?list=126">牙</a> <a href="?list=127">爻</a> <a href="?list=128">曰</a> <a href="?list=129">月</a> <a href="?list=130">爫</a> <a href="?list=131">支</a> <a href="?list=132">止</a> <a href="?list=133">爪</a><br />
笔画五：<a href="?list=134">白</a> <a href="?list=135">癶</a> <a href="?list=136">歺</a> <a href="?list=137">甘</a> <a href="?list=138">瓜</a> <a href="?list=139">禾</a> <a href="?list=140">钅</a> <a href="?list=141">立</a> <a href="?list=142">龙</a> <a href="?list=143">矛</a> <a href="?list=144">皿</a> <a href="?list=145">母</a> <a href="?list=146">目</a> <a href="?list=147">疒</a> <a href="?list=148">鸟</a> <a href="?list=149">皮</a> <a href="?list=150">生</a> <a href="?list=151">石</a> <a href="?list=152">矢</a> <a href="?list=153">示</a> <a href="?list=154">罒</a> <a href="?list=155">田</a> <a href="?list=156">玄</a> <a href="?list=157">穴</a> <a href="?list=158">疋</a> <a href="?list=159">业</a> <a href="?list=160">衤</a> <a href="?list=161">用</a> <a href="?list=162">玉</a><br />
笔画六：<a href="?list=163">耒</a> <a href="?list=164">艸</a> <a href="?list=165">臣</a> <a href="?list=166">虫</a> <a href="?list=167">而</a> <a href="?list=168">耳</a> <a href="?list=169">缶</a> <a href="?list=170">艮</a> <a href="?list=171">虍</a> <a href="?list=172">臼</a> <a href="?list=173">米</a> <a href="?list=174">齐</a> <a href="?list=175">肉</a> <a href="?list=176">色</a> <a href="?list=177">舌</a> <a href="?list=178">覀</a> <a href="?list=179">页</a> <a href="?list=180">先</a> <a href="?list=181">行</a> <a href="?list=182">血</a> <a href="?list=183">羊</a> <a href="?list=184">聿</a> <a href="?list=185">至</a> <a href="?list=186">舟</a> <a href="?list=187">衣</a> <a href="?list=188">竹</a> <a href="?list=189">自</a> <a href="?list=190">羽</a> <a href="?list=191">糸</a> <a href="?list=192">糹</a><br />
笔画七：<a href="?list=193">貝</a> <a href="?list=194">采</a> <a href="?list=195">镸</a> <a href="?list=196">車</a> <a href="?list=197">辰</a> <a href="?list=198">赤</a> <a href="?list=199">辵</a> <a href="?list=200">豆</a> <a href="?list=201">谷</a> <a href="?list=202">見</a> <a href="?list=203">角</a> <a href="?list=204">克</a> <a href="?list=205">里</a> <a href="?list=206">卤</a> <a href="?list=207">麦</a> <a href="?list=208">身</a> <a href="?list=209">豕</a> <a href="?list=210">辛</a> <a href="?list=211">言</a> <a href="?list=212">邑</a> <a href="?list=213">酉</a> <a href="?list=214">豸</a> <a href="?list=215">走</a> <a href="?list=216">足</a><br />
笔画八：<a href="?list=217">青</a> <a href="?list=218">靑</a> <a href="?list=219">雨</a> <a href="?list=220">齿</a> <a href="?list=221">長</a> <a href="?list=222">非</a> <a href="?list=223">阜</a> <a href="?list=224">金</a> <a href="?list=225">釒</a> <a href="?list=226">隶</a> <a href="?list=227">門</a> <a href="?list=228">靣</a> <a href="?list=229">飠</a> <a href="?list=230">鱼</a> <a href="?list=231">隹</a><br />
笔画九：<a href="?list=232">風</a> <a href="?list=233">革</a> <a href="?list=234">骨</a> <a href="?list=235">鬼</a> <a href="?list=236">韭</a> <a href="?list=237">面</a> <a href="?list=238">首</a> <a href="?list=239">韋</a> <a href="?list=240">香</a> <a href="?list=241">頁</a> <a href="?list=242">音</a><br />
笔画十：<a href="?list=243">髟</a> <a href="?list=244">鬯</a> <a href="?list=245">鬥</a> <a href="?list=246">高</a> <a href="?list=247">鬲</a> <a href="?list=248">馬</a><br />
笔画十一：<a href="?list=249">黄</a> <a href="?list=250">鹵</a> <a href="?list=251">鹿</a> <a href="?list=252">麻</a> <a href="?list=253">麥</a> <a href="?list=254">鳥</a> <a href="?list=255">魚</a><br />
笔画十二：<a href="?list=256">鼎</a> <a href="?list=257">黑</a> <a href="?list=258">黽</a> <a href="?list=259">黍</a> <a href="?list=260">黹</a><br />
笔画十三：<a href="?list=261">鼓</a> <a href="?list=262">鼠</a><br />
笔画十四：<a href="?list=263">鼻</a> <a href="?list=264">齊</a><br />
笔画十五：<a href="?list=265">齒</a> <a href="?list=266">龍</a> <a href="?list=267">龠</a><br />
其他：<a href="?list=268">难检字</a></p>
</td></tr></table>
<? }elseif($zidianStatus==1){ //搜索 ?>
<table width="778" cellpadding="2" cellspacing="0" style="border:1px solid #B2D0EA;"><tr><td style="background:#EDF7FF;padding:0 5px;color:#014198;" height="26" valign="middle" colspan="5"><a href="./">新华字典</a> &gt; “<?=$q?>”的汉字搜索结果</td></tr><tr><td style="padding:8px;font-size:14px;">
<? if(count($zidianArr)==0){
		if(preg_match("/^[\x{4e00}-\x{9fa5}]+$/u",$q)){ //全为中文
			echo '<p class="f14">你输入了一个以上汉字，请输入一个汉字后查询！</p>';
			echo '<p class="f14">或者：</p>';
			echo '<p class="f14">　　在 <a href="http://cidian.911cha.com/" class="f14" target="_blank">汉语词典</a> 里查找“<a href="http://cidian.911cha.com/q_'.urlencode($q).'" class="f14" target="_blank">'.$q.'</a>”的解释</p>';
			echo '<p class="f14">　　在 <a href="http://chengyu.911cha.com/" class="f14" target="_blank">成语词典</a> 里查找“<a href="http://chengyu.911cha.com/q_'.urlencode($q).'" class="f14" target="_blank">'.$q.'</a>”的解释</p>';
			echo '<p class="f14">　　在 <a href="http://baike.911cha.com/" class="f14" target="_blank">百科全书</a> 里查找“<a href="http://chengyu.911cha.com/q_'.urlencode($q).'" class="f14" target="_blank">'.$q.'</a>”的解释</p>';
		}else{
			echo '<p class="pink f14">你查询的不全为汉字，请剔除掉非汉字字符后再进行查询！<a href="./" class="f14">回'.$title.'首页</a></p>';
		}
}else{
	if(count($zidianArr)>=150) echo '<p class="f14 pink">返回结果数过多，仅列出前150个，请输入更准确的关键字进行搜索！</p><hr size="1" noshade="noshade" />';
	echo '<p style="line-height:200%">';
	for($i=0;$i<count($zidianArr);$i++){
		echo '<a href="?id='.$zidianArr[$i]['id'].'" target="_blank">'.$zidianArr[$i]['zi'].'</a> ';
	}
	echo '</p>';
}
?>
</td></tr></table>
<? }elseif($zidianStatus==2){ //列表 ?>
<table width="778" cellpadding="2" cellspacing="0" style="border:1px solid #B2D0EA;"><tr><td style="background:#EDF7FF;padding:0 5px;color:#014198;" height="26" valign="middle" colspan="5"><a href="./">新华字典</a> &gt; 偏旁部首为“<?=$zidianArr['type']?>”的汉字</td></tr><tr><td style="padding:8px;font-size:14px;"><h1>偏旁部首为“<?=$zidianArr['type']?>”的汉字</h1><?
	$bb = 0;
	for($i=0;$i<count($zidianArr)-1;$i++){
		if($zidianArr[$i]['bihua']>$bb){
			if($i>0) echo '</p>';
			echo '<p class="f14 b">笔画数'.$zidianArr[$i]['bihua'].'：';
			$bb = $zidianArr[$i]['bihua'];
		}
		echo '<a href="?id='.$zidianArr[$i]['id'].'" target="_blank" class="f14">'.$zidianArr[$i]['zi'].'</a> ';
	}
?>
</td></tr></table>
<? }elseif($zidianStatus==3){ //某个ID ?>
<table width="778" cellpadding="2" cellspacing="0" style="border:1px solid #B2D0EA;"><tr><td style="background:#EDF7FF;padding:0 5px;color:#014198;" height="26" valign="middle" colspan="5"><a href="./">新华字典</a> &gt; <?
	$pos = strpos($bpa,$zidianArr['bushou']);
	if ($pos === false){
		echo '<a href="?list=268">部首: 难检字</a>  &gt; ';
	}else{
		$pos=$pos/3+1;
		echo '<a href="?list='.$pos.'">部首: '.$zidianArr['bushou'].'</a>  &gt; ';
	}
?><?=$zidianArr['zi']?></td></tr><tr><td style="padding:8px;font-size:14px;word-break:break-all;"><h1 class="zi"><?=$zidianArr['zi']?></h1><?
	if($zidianArr['pinyin']){ echo '<p><strong><a href="http://pinyin.911cha.com/" target="_blank">拼音</a></strong> '.$zidianArr['pinyin'].'</p>'; }
	if($pos === false){
		echo '<p><strong>部首</strong> <a href="?list=268" class="f14">难检字</a></p>';
	}else{
		echo '<p><strong>部首</strong> <a href="?list='.$pos.'" class="f14">'.$zidianArr['bushou'].'</a></p>';
	}
	if($zidianArr['wubi']){ echo '<p><strong><a href="http://wubi.911cha.com/" target="_blank">五笔</a></strong> '.$zidianArr['wubi'].'</p>'; }
	if($zidianArr['bihua']){ echo '<p><strong><a href="http://bihua.911cha.com/" target="_blank">笔画</a></strong> '.$zidianArr['bihua'].'</p>'; }
	if($zidianArr['lizi']){ echo '<p><strong>例子</strong> '.$zidianArr['lizi'].'</p>'; }
	if($zidianArr['jijie']){ echo '<hr size="1" noshade="noshade" /><p><strong>基本解释</strong><br />'.$zidianArr['jijie'].'</p>'; }
	if($zidianArr['xiangjie']){ echo '<hr size="1" noshade="noshade" /><p><strong>详细解释</strong><br />'.$zidianArr['xiangjie'].'</p>'; }
	?>
</td></tr></table>
<? } ?>
<div id="alltools"><ul class="all"><li><a href="http://shouji.911cha.com/" target="_blank">手机号码归属地查询</a></li><li><a href="http://youbian.911cha.com/" target="_blank">邮编查询</a></li><li><a href="http://dream.911cha.com/" target="_blank">周公解梦大全</a></li><li><a href="http://nongli.911cha.com/" target="_blank">黄道吉日查询</a></li><li><a href="http://miyu.911cha.com/" target="_blank">中华谜语大全</a></li><li><a href="http://jx.911cha.com/" target="_blank">数字吉凶预测</a></li><li><a href="http://shici.911cha.com/" target="_blank">诗词大全</a></li><li><a href="http://naojin.911cha.com/" target="_blank">脑筋急转弯</a></li><li><a href="http://pianfang.911cha.com/" target="_blank">民间偏方大全</a></li><li><a href="http://suoxie.911cha.com/" target="_blank">英文缩写大全</a></li><li><a href="http://raokouling.911cha.com/" target="_blank">绕口令大全</a></li><li><a href="http://huoche.911cha.com/" target="_blank">列车时刻表</a></li><li><a href="http://xiehouyu.911cha.com/" target="_blank">歇后语大全</a></li><li><a href="http://chengyu.911cha.com/" target="_blank">成语词典</a></li><li><a href="http://shengxiao.911cha.com/" target="_blank">十二生肖属相查询</a></li><li><a href="http://zidian.911cha.com/" target="_blank">新华字典</a></li><li><a href="http://baike.911cha.com/" target="_blank">百科全书</a></li><li><a href="http://process.911cha.com/" target="_blank">进程查询</a></li><li><a href="http://whois.911cha.com/" target="_blank">域名WHOIS查询</a></li><li><a href="http://today.911cha.com/" target="_blank">历史上的今天</a></li><li><a href="http://cidian.911cha.com/" target="_blank">汉语词典</a></li><li><a href="http://ip.911cha.com/" target="_blank">IP地址查询</a></li><li><a href="http://birth.911cha.com/" target="_blank">解密生日</a></li><li><a href="http://xing.911cha.com/" target="_blank">百家姓</a></li><li><a href="http://taiwanpc.911cha.com/" target="_blank">台湾邮编查询</a></li><li><a href="http://idcard.911cha.com/" target="_blank">身份证号码验证</a></li><li><a href="http://yingyang.911cha.com/" target="_blank">食物营养成分查询</a></li><li><a href="http://bihua.911cha.com/" target="_blank">笔画数查询</a></li><li><a href="http://mingfang.911cha.com/" target="_blank">中草药名方大全</a></li><li><a href="http://yanyu.911cha.com/" target="_blank">民间谚语</a></li><li><a href="http://anquanqi.911cha.com/" target="_blank">女性安全期自测</a></li><li><a href="http://ipwhois.911cha.com/" target="_blank">IPWHOIS查询</a></li><li><a href="http://zhiwen.911cha.com/" target="_blank">指纹运势查询</a></li><li><a href="http://zhoupu.911cha.com/" target="_blank">粥谱大全</a></li><li><a href="http://wubi.911cha.com/" target="_blank">五笔编码查询</a></li><li><a href="http://country.911cha.com/" target="_blank">国家和地区</a></li><li><a href="http://yanfang.911cha.com/" target="_blank">中草药民间验方</a></li><li><a href="http://mingyan.911cha.com/" target="_blank">名人名言名句大全</a></li><li><a href="http://pr.911cha.com/" target="_blank">GooglePR值查询</a></li><li><a href="http://wannianli.911cha.com/" target="_blank">万年历</a></li><li><a href="http://jiufang.911cha.com/" target="_blank">酒方大全</a></li><li><a href="http://jisuanqi.911cha.com/" target="_blank">科学计算器</a></li><li><a href="http://zhongcaoyao.911cha.com/" target="_blank">中草药大全</a></li><li><a href="http://flag.911cha.com/" target="_blank">升降旗时间</a></li><li><a href="http://npo.911cha.com/" target="_blank">全国社会性组织</a></li><li><a href="http://nianling.911cha.com/" target="_blank">外星年龄</a></li><li><a href="http://jianfan.911cha.com/" target="_blank">汉字简体繁体转换</a></li><li><a href="http://daxue.911cha.com/" target="_blank">大学查询</a></li><li><a href="http://bencao.911cha.com/" target="_blank">中华本草</a></li><li><a href="http://lukuang.911cha.com/" target="_blank">实时交通路况</a></li><li><a href="http://mima.911cha.com/" target="_blank">密码强度检测</a></li><li><a href="http://zhongyi.911cha.com/" target="_blank">中医名词辞典</a></li><li><a href="http://tizhong.911cha.com/" target="_blank">外星体重</a></li><li><a href="http://morsecode.911cha.com/" target="_blank">摩尔斯电码</a></li><li><a href="http://shicha.911cha.com/" target="_blank">世界时差查询</a></li><li><a href="http://guwen.911cha.com/" target="_blank">竖排古文</a></li><li><a href="http://reverseip.911cha.com/" target="_blank">同IP站点查询</a></li><li><a href="http://xianxing.911cha.com/" target="_blank">车牌尾号限行查询</a></li><li><a href="http://pinyin.911cha.com/" target="_blank">汉字拼音查询</a></li><li><a href="http://ip2country.911cha.com/" target="_blank">IP所在国家查询</a></li><li><a href="http://huilv.911cha.com/" target="_blank">货币汇率查询</a></li><li><a href="http://ditie.911cha.com/" target="_blank">地铁线路图</a></li><li><a href="http://airportcode.911cha.com/" target="_blank">机场三字码查询</a></li><li><a href="http://yuce.911cha.com/" target="_blank">预测吉凶</a></li><li><a href="http://weizhang.911cha.com/" target="_blank">车辆违章查询</a></li><li><a href="http://nannv.911cha.com/" target="_blank">生男生女预测</a></li><li><a href="http://quhao.911cha.com/" target="_blank">国内长途电话区号</a></li><li><a href="http://ascii.911cha.com/" target="_blank">ASCII码对照表</a></li><li><a href="http://ditu.911cha.com/" target="_blank">中国电子地图</a></li><li><a href="http://bianma.911cha.com/" target="_blank">在线编码解码</a></li><li><a href="http://danci.911cha.com/" target="_blank">单词在线翻译</a></li><li><a href="http://pi.911cha.com/" target="_blank">百万圆周率</a></li><li><a href="http://dianma.911cha.com/" target="_blank">中文电码查询</a></li><li><a href="http://chebiao.911cha.com/" target="_blank">汽车车标大全</a></li><li><a href="http://zhengma.911cha.com/" target="_blank">郑码编码查询</a></li><li><a href="http://cangjie.911cha.com/" target="_blank">仓颉编码查询</a></li><li><a href="http://sijiao.911cha.com/" target="_blank">四角号码在线查询</a></li></ul><div class="cboth"></div></div>
</div>
<div id="footer">&copy; 2009 <a href="http://www.911cha.com/" title="911查实用查询">911查</a></div>
<div style="display:none"><script src='http://w.cnzz.com/c.php?id=30019168' language='JavaScript' charset='gb2312'></script></div>
</body>
</html>