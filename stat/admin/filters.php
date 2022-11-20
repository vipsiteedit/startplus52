<?php
$filter=$HTTP_GET_VARS["filter"];

if ($HTTP_GET_VARS["action"]=="create") {
	$title=mysql_escape_string(htmlspecialchars(StripSlashes($HTTP_GET_VARS["title"])));
	$excl=cnstats_sql_query("SELECT count(*) FROM cns_filters WHERE txt='-' AND title='".$title."'");
	if (mysql_num_rows($excl)!=0) {
		$excl=cnstats_sql_query("INSERT INTO cns_filters (txt,title) VALUES ('-','".$title."')");
		}
	header("Location: index.php?st=".$st."&stm=".$stm."&ftm=".$ftm."&filter=".$filter);
	exit;
	}

if ($HTTP_GET_VARS["action"]=="delf") {
	$r=cnstats_sql_query("SELECT title FROM cns_filters WHERE id='".intval($HTTP_GET_VARS["id"])."';");
	if (mysql_num_rows($r)==1) {
		cnstats_sql_query("DELETE FROM cns_filters WHERE title='".mysql_escape_string(mysql_result($r,0,0))."';");
		header("Location: index.php?st=".$st."&stm=".$stm."&ftm=".$ftm."&filter=".urlencode($filter));
		exit;
		}
	}

if ($HTTP_GET_VARS["action"]=="add") {
	$exclude=mysql_escape_string(htmlspecialchars(StripSlashes($HTTP_GET_VARS["exclude"])));
	$what=mysql_escape_string(htmlspecialchars(StripSlashes($HTTP_GET_VARS["what"])));
	$title=mysql_escape_string(urldecode($HTTP_GET_VARS["title"]));

	$type=intval($HTTP_GET_VARS["type"]);
	$logic=intval($HTTP_GET_VARS["logic"]);
	$excl=cnstats_sql_query("SELECT count(*) FROM cns_filters WHERE txt='".$what."|||".$logic."|||".$type."|||".$exclude."' AND title='".$title."'");
	if (mysql_result($excl,0,0)==0) {
		$excl=cnstats_sql_query("INSERT INTO cns_filters (txt,title) VALUES ('".$what."|||".$logic."|||".$type."|||".$exclude."','".$title."')");
		}
	header("Location: index.php?st=".$st."&stm=".$stm."&ftm=".$ftm."&title=".$HTTP_GET_VARS["title"]."&filter=".urlencode($filter));
	exit;
	}

if (isset($HTTP_GET_VARS["del"])) {
	$r=cnstats_sql_query("SELECT title FROM cns_filters WHERE id='".intval($HTTP_GET_VARS["del"])."';");
	if (mysql_num_rows($r)==1) {
		cnstats_sql_query("DELETE FROM cns_filters WHERE id='".intval($HTTP_GET_VARS["del"])."';");
		header("Location: index.php?st=".$st."&stm=".$stm."&ftm=".$ftm."&title=".mysql_result($r,0,0)."&filter=".urlencode($filter));
		exit;
		}
	}

$title=$HTTP_GET_VARS["title"];
if (empty($title)) {

	$titles=cnstats_sql_query("select title,id,count(*) as cnt FROM cns_filters GROUP BY title ORDER BY id");
	if (mysql_num_rows($titles)!=0) {
		print "<table width='".$TW."' cellspacing='1' border='0' cellpadding='3' bgcolor='#D4F3D7'>\n";
		print "<tr class=tbl0><td colspan=2 align=center><B>".$LANG["filterslist"]."</B></td></tr>";
		while ($a=mysql_fetch_array($titles)) {
			print "<tr class=tbl1><td width='95%'><a href='index.php?st=".$st."&amp;stm=".$stm."&amp;ftm=".$ftm."&amp;filter=".urlencode($filter)."&amp;title=".urlencode($a["title"])."'>".$a["title"]."</td>";
			print "<td align=right>".($a["cnt"]-1)."</td><td width=5%><a href='index.php?st=".$st."&amp;stm=".$stm."&amp;ftm=".$ftm."&amp;filter=".urlencode($filter)."&amp;nowrap=1&amp;action=delf&amp;id=".$a["id"]."'><img src='img/del.gif' alt='' width=19 height=14 border=0></a></td>";
			print "</tr>";
			}
		print "</table><br>";
		}

	print "<form action='index.php' method='GET' class='m0'>\n";
	print "<input type='hidden' name='st' value='filters'>\n";
	print "<input type='hidden' name='stm' value='".$stm."'>\n";
	print "<input type='hidden' name='ftm' value='".$ftm."'>\n";
	print "<input type='hidden' name='filter' value='".urlencode($filter)."'>\n";
	print "<input type='hidden' name='nowrap' value='1'>\n";
	print "<input type='hidden' name='action' value='create'>\n";
	print $TABLE;
	print "<tr class=tbl0><td colspan=2 align=center><B>".$LANG["createfilter"]."</B></td></tr>";
	print "<tr class=tbl1><td nowrap>".$LANG["filtertitle"].":</td><td width=100%><input style='width:100%' type=text name=title></td></tr>";
	print "<tr class=tbl2><td colspan=2 align=center><input type=submit value='".$LANG["create"]."'></td></tr>";
	print "</table>";
	print "</form>\n";
	}
else {
	print "&nbsp;<a href='index.php?st=".$st."&amp;stm=".$stm."&amp;ftm=".$ftm."&amp;filter=".urlencode($filter)."'> &laquo; ".$LANG["returntofilterslist"]."</a><br><br>";


	print "<form action='index.php' class='m0'><table width='100%' cellspacing=1 cellpadding=5 bgcolor='#D4F3D7' border=0>\n";
	print "<tr class='tbl0'><td colspan=2><B>&nbsp;".$LANG["conditions"]." &quot;".$title."&quot; </b></td><td width=19>&nbsp;</td></tr>\n";
	$title=$HTTP_GET_VARS["title"];
	if (!empty($title)) {
	$excl_count=0;
	$excl=cnstats_sql_query("select txt,id FROM cns_filters WHERE title='".mysql_escape_string($title)."' ORDER BY id");

		$cnt=0;
		for ($i=0;$i<mysql_num_rows($excl);$i++) {
			$a=mysql_result($excl,$i,0);
			if ($a!="-") {
				if ($class!="tbl1") $class="tbl1"; else $class="tbl2";
				$e=explode("|||",$a);
				print "<tr class='".$class."'>\n<td width='50%'><B>";
				if ($cnt!=0) if ($e[1]==0) print $LANG["and"]; else print $LANG["or"];
				print "</B> ".$LANG["field"]." &quot;".$LANG["exclusionw"][$e[0]]."&quot; ".$LANG["exclusions"][$e[2]]."</td>\n<td style=\"width:50%\">".$e[3]."</td>\n<td width=19><a href='index.php?st=filters&amp;stm=".$stm."&amp;ftm=".$ftm."&amp;filter=".urlencode($filter)."&amp;del=".mysql_result($excl,$i,1)."&amp;nowrap=1'><img src='img/del.gif' alt='' width=19 height=14 border=0></a></td>\n</tr>\n";
				$cnt++;
				}
			}
		}

	print "</table><br>";

	print "<table width='".$TW."' cellspacing='1' border='0' cellpadding='3' bgcolor='#D4F3D7'>\n";
	print "<tr><td colspan=3 align=center>".$LANG["filternewcondition"]."<B></B></td></tr>";
	print "<tr class=\"tbl2\"><td>";

	if ($cnt!=0) {
		print "<SELECT name=logic>";
		print "<OPTION value=0>".$LANG["and"]." ".$LANG["field"];
		print "<OPTION value=1>".$LANG["or"]." ".$LANG["field"];
		print "</SELECT><br>";
		}

	print "<SELECT name=what style=\"width:150px;\">";
	for ($i=1;$i<5;$i++) print "<OPTION value=\"".$i."\">".$LANG["exclusionw"][$i]."\n";
	print "</SELECT><br>";

	print "<SELECT name=type style=\"width:150px;\">";
	for ($i=1;$i<8;$i++) print "<OPTION value=\"".$i."\">".$LANG["exclusions"][$i]."\n";
	print "</SELECT>";
	print "</td><td width=\"100%\" valign=\"top\"><input type=text style='width:100%' name='exclude' value=''></td><td width=19 valign=top><input vspace=2 type=image src='img/add.gif' alt='' style='width:19px;height:14px;margin:0px;'></td></tr>\n";
	print "</table>\n";
	print "<input type='hidden' name='title' value='".urlencode($title)."'>\n";
	print "<input type='hidden' name='st' value='filters'>\n";
	print "<input type='hidden' name='stm' value='".$stm."'>\n";
	print "<input type='hidden' name='ftm' value='".$ftm."'>\n";
	print "<input type='hidden' name='filter' value='".$filter."'>\n";
	print "<input type='hidden' name='nowrap' value='1'>\n";
	print "<input type='hidden' name='action' value='add'>\n";
	print "</form>\n";
	}
$NOFILTER=1;
?>
