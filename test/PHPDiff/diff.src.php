<html><head>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1"><title>diff example</title></head>

<body>
<h1>PHP Diff Example</h1>
<h3>Implementation of DIFF in pure-php</h3>
Simply copy-paste the code to your editor:<br>
The Code is free to use.<br>
<a href="http://www.holomind.de/phpnet/diff2.src.php">also check version 2 of this script !</a><br>
<a href="http://www.holomind.de/phpnet/diff.php">Here you can see the file in action</a>
<br>
<hr>
<code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/**
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Diff&nbsp;implemented&nbsp;in&nbsp;pure&nbsp;php,&nbsp;written&nbsp;from&nbsp;scratch.
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Copyright&nbsp;(C)&nbsp;2003&nbsp;&nbsp;Daniel&nbsp;Unterberger&nbsp;&lt;diff.phpnet@holomind.de&gt;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This&nbsp;program&nbsp;is&nbsp;free&nbsp;software;&nbsp;you&nbsp;can&nbsp;redistribute&nbsp;it&nbsp;and/or
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;modify&nbsp;it&nbsp;under&nbsp;the&nbsp;terms&nbsp;of&nbsp;the&nbsp;GNU&nbsp;General&nbsp;Public&nbsp;License
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;as&nbsp;published&nbsp;by&nbsp;the&nbsp;Free&nbsp;Software&nbsp;Foundation;&nbsp;either&nbsp;version&nbsp;2
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;of&nbsp;the&nbsp;License,&nbsp;or&nbsp;(at&nbsp;your&nbsp;option)&nbsp;any&nbsp;later&nbsp;version.
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This&nbsp;program&nbsp;is&nbsp;distributed&nbsp;in&nbsp;the&nbsp;hope&nbsp;that&nbsp;it&nbsp;will&nbsp;be&nbsp;useful,
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;but&nbsp;WITHOUT&nbsp;ANY&nbsp;WARRANTY;&nbsp;without&nbsp;even&nbsp;the&nbsp;implied&nbsp;warranty&nbsp;of
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MERCHANTABILITY&nbsp;or&nbsp;FITNESS&nbsp;FOR&nbsp;A&nbsp;PARTICULAR&nbsp;PURPOSE.&nbsp;&nbsp;See&nbsp;the
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;GNU&nbsp;General&nbsp;Public&nbsp;License&nbsp;for&nbsp;more&nbsp;details.
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You&nbsp;should&nbsp;have&nbsp;received&nbsp;a&nbsp;copy&nbsp;of&nbsp;the&nbsp;GNU&nbsp;General&nbsp;Public&nbsp;License
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;along&nbsp;with&nbsp;this&nbsp;program;&nbsp;if&nbsp;not,&nbsp;write&nbsp;to&nbsp;the&nbsp;Free&nbsp;Software
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Foundation,&nbsp;Inc.,&nbsp;59&nbsp;Temple&nbsp;Place&nbsp;-&nbsp;Suite&nbsp;330,&nbsp;Boston,&nbsp;MA&nbsp;&nbsp;02111-1307,&nbsp;USA.
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;http://www.gnu.org/licenses/gpl.html
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;About:
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;I&nbsp;searched&nbsp;a&nbsp;function&nbsp;to&nbsp;compare&nbsp;arrays&nbsp;and&nbsp;the&nbsp;array_diff()
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;was&nbsp;not&nbsp;specific&nbsp;enough.&nbsp;It&nbsp;ignores&nbsp;the&nbsp;order&nbsp;of&nbsp;the&nbsp;array-values.
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;So&nbsp;I&nbsp;reimplemented&nbsp;the&nbsp;diff-function&nbsp;which&nbsp;is&nbsp;found&nbsp;on&nbsp;unix-systems
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;but&nbsp;this&nbsp;you&nbsp;can&nbsp;use&nbsp;directly&nbsp;in&nbsp;your&nbsp;code&nbsp;and&nbsp;adopt&nbsp;for&nbsp;your&nbsp;needs.
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Simply&nbsp;adopt&nbsp;the&nbsp;formatline-function.&nbsp;with&nbsp;the&nbsp;third-parameter&nbsp;of&nbsp;arr_diff()
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;you&nbsp;can&nbsp;hide&nbsp;matching&nbsp;lines.&nbsp;Hope&nbsp;someone&nbsp;has&nbsp;use&nbsp;for&nbsp;this.
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Contact:&nbsp;d.u.diff@holomind.de&nbsp;&lt;daniel&nbsp;unterberger&gt;
<br>&nbsp;&nbsp;&nbsp;&nbsp;**/
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">arr_diff</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">$f1&nbsp;</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$f2&nbsp;</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$show_equal&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0&nbsp;</span><span style="color: #007700">)
<br>&nbsp;&nbsp;&nbsp;&nbsp;{
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$c1&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0&nbsp;</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;current&nbsp;line&nbsp;of&nbsp;left
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$c2&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0&nbsp;</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;current&nbsp;line&nbsp;of&nbsp;right
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$max1&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">count</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">$f1&nbsp;</span><span style="color: #007700">)&nbsp;;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;maximal&nbsp;lines&nbsp;of&nbsp;left
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$max2&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">count</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">$f2&nbsp;</span><span style="color: #007700">)&nbsp;;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;maximal&nbsp;lines&nbsp;of&nbsp;right
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$outcount&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;output&nbsp;counter
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$hit1&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">""&nbsp;</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;hit&nbsp;in&nbsp;left
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$hit2&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">""&nbsp;</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;hit&nbsp;in&nbsp;right
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">while&nbsp;(&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$c1&nbsp;</span><span style="color: #007700">&lt;&nbsp;</span><span style="color: #0000BB">$max1&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;have&nbsp;next&nbsp;line&nbsp;in&nbsp;left
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">and&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$c2&nbsp;</span><span style="color: #007700">&lt;&nbsp;</span><span style="color: #0000BB">$max2&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;have&nbsp;next&nbsp;line&nbsp;in&nbsp;right
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">and&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(</span><span style="color: #0000BB">$stop</span><span style="color: #007700">++)&nbsp;&lt;&nbsp;</span><span style="color: #0000BB">1000&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;don-t&nbsp;have&nbsp;more&nbsp;then&nbsp;1000&nbsp;(&nbsp;loop-stopper&nbsp;)
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">and&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$outcount&nbsp;</span><span style="color: #007700">&lt;&nbsp;</span><span style="color: #0000BB">20&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;output&nbsp;count&nbsp;is&nbsp;less&nbsp;then&nbsp;20
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">)
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/**
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;is&nbsp;the&nbsp;trimmed&nbsp;line&nbsp;of&nbsp;the&nbsp;current&nbsp;left&nbsp;and&nbsp;current&nbsp;right&nbsp;line
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;the&nbsp;same&nbsp;?&nbsp;then&nbsp;this&nbsp;is&nbsp;a&nbsp;hit&nbsp;(no&nbsp;difference)
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*/&nbsp;&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">if&nbsp;(&nbsp;</span><span style="color: #0000BB">trim</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">$f1</span><span style="color: #007700">[</span><span style="color: #0000BB">$c1</span><span style="color: #007700">]&nbsp;)&nbsp;==&nbsp;</span><span style="color: #0000BB">trim&nbsp;</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">$f2</span><span style="color: #007700">[</span><span style="color: #0000BB">$c2</span><span style="color: #007700">])&nbsp;&nbsp;)&nbsp;&nbsp;&nbsp;&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/**
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;add&nbsp;to&nbsp;output-string,&nbsp;if&nbsp;"show_equal"&nbsp;is&nbsp;enabled
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*/
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$out&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">.=&nbsp;(</span><span style="color: #0000BB">$show_equal</span><span style="color: #007700">==</span><span style="color: #0000BB">1</span><span style="color: #007700">)&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;?&nbsp;&nbsp;</span><span style="color: #0000BB">formatline&nbsp;</span><span style="color: #007700">(&nbsp;(</span><span style="color: #0000BB">$c1</span><span style="color: #007700">)&nbsp;,&nbsp;(</span><span style="color: #0000BB">$c2</span><span style="color: #007700">),&nbsp;</span><span style="color: #DD0000">"="</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$f1</span><span style="color: #007700">[&nbsp;</span><span style="color: #0000BB">$c1&nbsp;</span><span style="color: #007700">]&nbsp;)&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;</span><span style="color: #DD0000">""&nbsp;</span><span style="color: #007700">;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/**
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;increase&nbsp;the&nbsp;out-putcounter,&nbsp;if&nbsp;"show_equal"&nbsp;is&nbsp;enabled
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;this&nbsp;ist&nbsp;more&nbsp;for&nbsp;demonstration&nbsp;purpose
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*/
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">if&nbsp;(&nbsp;</span><span style="color: #0000BB">$show_equal&nbsp;</span><span style="color: #007700">==&nbsp;</span><span style="color: #0000BB">1&nbsp;</span><span style="color: #007700">)&nbsp;&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$outcount</span><span style="color: #007700">++&nbsp;;&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/**
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;move&nbsp;the&nbsp;current-pointer&nbsp;in&nbsp;the&nbsp;left&nbsp;and&nbsp;right&nbsp;side
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*/
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$c1&nbsp;</span><span style="color: #007700">++;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$c2&nbsp;</span><span style="color: #007700">++;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/**
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;the&nbsp;current&nbsp;lines&nbsp;are&nbsp;different&nbsp;so&nbsp;we&nbsp;search&nbsp;in&nbsp;parallel
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;on&nbsp;each&nbsp;side&nbsp;for&nbsp;the&nbsp;next&nbsp;matching&nbsp;pair,&nbsp;we&nbsp;walk&nbsp;on&nbsp;both&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;sided&nbsp;at&nbsp;the&nbsp;same&nbsp;time&nbsp;comparing&nbsp;with&nbsp;the&nbsp;current-lines
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;this&nbsp;should&nbsp;be&nbsp;most&nbsp;probable&nbsp;to&nbsp;find&nbsp;the&nbsp;next&nbsp;matching&nbsp;pair
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;we&nbsp;only&nbsp;search&nbsp;in&nbsp;a&nbsp;distance&nbsp;of&nbsp;10&nbsp;lines,&nbsp;because&nbsp;then&nbsp;it
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;is&nbsp;not&nbsp;the&nbsp;same&nbsp;function&nbsp;most&nbsp;of&nbsp;the&nbsp;time.&nbsp;other&nbsp;algos
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;would&nbsp;be&nbsp;very&nbsp;complicated,&nbsp;to&nbsp;detect&nbsp;'real'&nbsp;block&nbsp;movements.
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*/
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">else
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$b&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">""&nbsp;</span><span style="color: #007700">;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$s1&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0&nbsp;&nbsp;</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;search&nbsp;on&nbsp;left
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$s2&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0&nbsp;&nbsp;</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;search&nbsp;on&nbsp;right
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$found&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0&nbsp;&nbsp;</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;flag,&nbsp;found&nbsp;a&nbsp;matching&nbsp;pair
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$b1&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">""&nbsp;</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$b2&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">""&nbsp;</span><span style="color: #007700">;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$fstop&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0&nbsp;&nbsp;</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;distance&nbsp;of&nbsp;maximum&nbsp;search
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#fast&nbsp;search&nbsp;in&nbsp;on&nbsp;both&nbsp;sides&nbsp;for&nbsp;next&nbsp;match.
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">while&nbsp;(&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$found&nbsp;</span><span style="color: #007700">==&nbsp;</span><span style="color: #0000BB">0&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;search&nbsp;until&nbsp;we&nbsp;find&nbsp;a&nbsp;pair
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">and&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(&nbsp;</span><span style="color: #0000BB">$c1&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #0000BB">$s1&nbsp;</span><span style="color: #007700">&lt;=&nbsp;</span><span style="color: #0000BB">$max1&nbsp;</span><span style="color: #007700">)&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;and&nbsp;we&nbsp;are&nbsp;inside&nbsp;of&nbsp;the&nbsp;left&nbsp;lines
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">and&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(&nbsp;</span><span style="color: #0000BB">$c2&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #0000BB">$s2&nbsp;</span><span style="color: #007700">&lt;=&nbsp;</span><span style="color: #0000BB">$max2&nbsp;</span><span style="color: #007700">)&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;and&nbsp;we&nbsp;are&nbsp;inside&nbsp;of&nbsp;the&nbsp;right&nbsp;lines
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">and&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$fstop</span><span style="color: #007700">++&nbsp;&nbsp;&lt;&nbsp;</span><span style="color: #0000BB">10&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;and&nbsp;the&nbsp;distance&nbsp;is&nbsp;lower&nbsp;than&nbsp;10&nbsp;lines
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">)
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/**
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;test&nbsp;the&nbsp;left&nbsp;side&nbsp;for&nbsp;a&nbsp;hit
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;comparing&nbsp;current&nbsp;line&nbsp;with&nbsp;the&nbsp;searching&nbsp;line&nbsp;on&nbsp;the&nbsp;left
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;b1&nbsp;is&nbsp;a&nbsp;buffer,&nbsp;which&nbsp;collects&nbsp;the&nbsp;line&nbsp;which&nbsp;not&nbsp;match,&nbsp;to&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;show&nbsp;the&nbsp;differences&nbsp;later,&nbsp;if&nbsp;one&nbsp;line&nbsp;hits,&nbsp;this&nbsp;buffer&nbsp;will
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;be&nbsp;used,&nbsp;else&nbsp;it&nbsp;will&nbsp;be&nbsp;discarded&nbsp;later
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*/
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#hit
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">if&nbsp;(&nbsp;</span><span style="color: #0000BB">trim</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">$f1</span><span style="color: #007700">[</span><span style="color: #0000BB">$c1</span><span style="color: #007700">+</span><span style="color: #0000BB">$s1</span><span style="color: #007700">]&nbsp;)&nbsp;==&nbsp;</span><span style="color: #0000BB">trim</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">$f2</span><span style="color: #007700">[</span><span style="color: #0000BB">$c2</span><span style="color: #007700">]&nbsp;)&nbsp;&nbsp;)
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$found&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;set&nbsp;flag&nbsp;to&nbsp;stop&nbsp;further&nbsp;search
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$s2&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;reset&nbsp;right&nbsp;side&nbsp;search-pointer
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$c2</span><span style="color: #007700">--&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;move&nbsp;back&nbsp;the&nbsp;current&nbsp;right,&nbsp;so&nbsp;next&nbsp;loop&nbsp;hits
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$b&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$b1&nbsp;</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;set&nbsp;b=output&nbsp;(b)uffer
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">}
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#no&nbsp;hit:&nbsp;move&nbsp;on
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">else
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/**
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;prevent&nbsp;finding&nbsp;a&nbsp;line&nbsp;again,&nbsp;which&nbsp;would&nbsp;show&nbsp;wrong&nbsp;results
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;add&nbsp;the&nbsp;current&nbsp;line&nbsp;to&nbsp;leftbuffer,&nbsp;if&nbsp;this&nbsp;will&nbsp;be&nbsp;the&nbsp;hit
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*/
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">if&nbsp;(&nbsp;</span><span style="color: #0000BB">$hit1</span><span style="color: #007700">[&nbsp;(</span><span style="color: #0000BB">$c1&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #0000BB">$s1</span><span style="color: #007700">)&nbsp;.&nbsp;</span><span style="color: #DD0000">"_"&nbsp;</span><span style="color: #007700">.&nbsp;(</span><span style="color: #0000BB">$c2</span><span style="color: #007700">)&nbsp;]&nbsp;!=&nbsp;</span><span style="color: #0000BB">1&nbsp;</span><span style="color: #007700">)
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{&nbsp;&nbsp;&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/**
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;add&nbsp;current&nbsp;search-line&nbsp;to&nbsp;diffence-buffer
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*/
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$b1&nbsp;&nbsp;</span><span style="color: #007700">.=&nbsp;</span><span style="color: #0000BB">formatline</span><span style="color: #007700">(&nbsp;(</span><span style="color: #0000BB">$c1&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #0000BB">$s1</span><span style="color: #007700">)&nbsp;,&nbsp;(</span><span style="color: #0000BB">$c2</span><span style="color: #007700">),&nbsp;</span><span style="color: #DD0000">"-"</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$f1</span><span style="color: #007700">[&nbsp;</span><span style="color: #0000BB">$c1</span><span style="color: #007700">+</span><span style="color: #0000BB">$s1&nbsp;</span><span style="color: #007700">]&nbsp;);
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/**
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;mark&nbsp;this&nbsp;line&nbsp;as&nbsp;'searched'&nbsp;to&nbsp;prevent&nbsp;doubles.&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*/
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$hit1</span><span style="color: #007700">[&nbsp;(</span><span style="color: #0000BB">$c1&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #0000BB">$s1</span><span style="color: #007700">)&nbsp;.&nbsp;</span><span style="color: #DD0000">"_"&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">$c2&nbsp;</span><span style="color: #007700">]&nbsp;=&nbsp;</span><span style="color: #0000BB">1&nbsp;</span><span style="color: #007700">;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}
<br>
<br>
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/**
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;test&nbsp;the&nbsp;right&nbsp;side&nbsp;for&nbsp;a&nbsp;hit
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;comparing&nbsp;current&nbsp;line&nbsp;with&nbsp;the&nbsp;searching&nbsp;line&nbsp;on&nbsp;the&nbsp;right
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*/
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">if&nbsp;(&nbsp;</span><span style="color: #0000BB">trim&nbsp;</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">$f1</span><span style="color: #007700">[</span><span style="color: #0000BB">$c1</span><span style="color: #007700">]&nbsp;)&nbsp;==&nbsp;</span><span style="color: #0000BB">trim&nbsp;</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">$f2</span><span style="color: #007700">[</span><span style="color: #0000BB">$c2</span><span style="color: #007700">+</span><span style="color: #0000BB">$s2</span><span style="color: #007700">])&nbsp;&nbsp;)
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$found&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;flag&nbsp;to&nbsp;stop&nbsp;search
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$s1&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;reset&nbsp;pointer&nbsp;for&nbsp;search
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$c1</span><span style="color: #007700">--&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;move&nbsp;current&nbsp;line&nbsp;back,&nbsp;so&nbsp;we&nbsp;hit&nbsp;next&nbsp;loop
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$b&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$b2&nbsp;</span><span style="color: #007700">;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;get&nbsp;the&nbsp;buffered&nbsp;difference
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">}
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;else
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{&nbsp;&nbsp;&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/**
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;prevent&nbsp;to&nbsp;find&nbsp;line&nbsp;again
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*/
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">if&nbsp;(&nbsp;</span><span style="color: #0000BB">$hit2</span><span style="color: #007700">[&nbsp;(</span><span style="color: #0000BB">$c1</span><span style="color: #007700">)&nbsp;.&nbsp;</span><span style="color: #DD0000">"_"&nbsp;</span><span style="color: #007700">.&nbsp;(&nbsp;</span><span style="color: #0000BB">$c2&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #0000BB">$s2</span><span style="color: #007700">)&nbsp;]&nbsp;!=&nbsp;</span><span style="color: #0000BB">1&nbsp;</span><span style="color: #007700">)
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/**
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;add&nbsp;current&nbsp;searchline&nbsp;to&nbsp;buffer
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*/
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$b2&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">.=&nbsp;</span><span style="color: #0000BB">formatline&nbsp;</span><span style="color: #007700">(&nbsp;(</span><span style="color: #0000BB">$c1</span><span style="color: #007700">)&nbsp;,&nbsp;(</span><span style="color: #0000BB">$c2&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #0000BB">$s2</span><span style="color: #007700">),&nbsp;</span><span style="color: #DD0000">"+"</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$f2</span><span style="color: #007700">[&nbsp;</span><span style="color: #0000BB">$c2</span><span style="color: #007700">+</span><span style="color: #0000BB">$s2&nbsp;</span><span style="color: #007700">]&nbsp;);
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/**
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;mark&nbsp;current&nbsp;line&nbsp;to&nbsp;prevent&nbsp;double-hits
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*/
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$hit2</span><span style="color: #007700">[&nbsp;(</span><span style="color: #0000BB">$c1</span><span style="color: #007700">)&nbsp;.&nbsp;</span><span style="color: #DD0000">"_"&nbsp;</span><span style="color: #007700">.&nbsp;(</span><span style="color: #0000BB">$c2&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #0000BB">$s2</span><span style="color: #007700">)&nbsp;]&nbsp;=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/**
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;search&nbsp;in&nbsp;bigger&nbsp;distance
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;increase&nbsp;the&nbsp;search-pointers&nbsp;(satelites)&nbsp;and&nbsp;try&nbsp;again
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*/
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$s1</span><span style="color: #007700">++&nbsp;;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;increase&nbsp;left&nbsp;&nbsp;search-pointer
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$s2</span><span style="color: #007700">++&nbsp;;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#&nbsp;increase&nbsp;right&nbsp;search-pointer&nbsp;&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">}
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/**
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;add&nbsp;line&nbsp;as&nbsp;different&nbsp;on&nbsp;both&nbsp;arrays&nbsp;(no&nbsp;match&nbsp;found)
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*/
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">if&nbsp;(&nbsp;</span><span style="color: #0000BB">$found&nbsp;</span><span style="color: #007700">==&nbsp;</span><span style="color: #0000BB">0&nbsp;</span><span style="color: #007700">)
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$b&nbsp;&nbsp;</span><span style="color: #007700">.=&nbsp;</span><span style="color: #0000BB">formatline&nbsp;</span><span style="color: #007700">(&nbsp;(</span><span style="color: #0000BB">$c1</span><span style="color: #007700">)&nbsp;,&nbsp;(</span><span style="color: #0000BB">$c2</span><span style="color: #007700">),&nbsp;</span><span style="color: #DD0000">"-"</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$f1</span><span style="color: #007700">[&nbsp;</span><span style="color: #0000BB">$c1&nbsp;</span><span style="color: #007700">]&nbsp;);
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$b&nbsp;&nbsp;</span><span style="color: #007700">.=&nbsp;</span><span style="color: #0000BB">formatline&nbsp;</span><span style="color: #007700">(&nbsp;(</span><span style="color: #0000BB">$c1</span><span style="color: #007700">)&nbsp;,&nbsp;(</span><span style="color: #0000BB">$c2</span><span style="color: #007700">),&nbsp;</span><span style="color: #DD0000">"+"</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$f2</span><span style="color: #007700">[&nbsp;</span><span style="color: #0000BB">$c2&nbsp;</span><span style="color: #007700">]&nbsp;);
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">/**&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;add&nbsp;current&nbsp;buffer&nbsp;to&nbsp;outputstring
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*/
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$out&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">.=&nbsp;</span><span style="color: #0000BB">$b</span><span style="color: #007700">;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$outcount</span><span style="color: #007700">++&nbsp;;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#increase&nbsp;outcounter
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$c1</span><span style="color: #007700">++&nbsp;&nbsp;;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#move&nbsp;currentline&nbsp;forward
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$c2</span><span style="color: #007700">++&nbsp;&nbsp;;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#move&nbsp;currentline&nbsp;forward
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/**
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;comment&nbsp;the&nbsp;lines&nbsp;are&nbsp;tested&nbsp;quite&nbsp;fast,&nbsp;because&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;the&nbsp;current&nbsp;line&nbsp;always&nbsp;moves&nbsp;forward
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*/
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">}&nbsp;</span><span style="color: #FF8000">/*endif*/
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">}</span><span style="color: #FF8000">/*endwhile*/
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">return&nbsp;</span><span style="color: #0000BB">$out</span><span style="color: #007700">;
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;}</span><span style="color: #FF8000">/*end&nbsp;func*/
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;/**
<br>&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;callback&nbsp;function&nbsp;to&nbsp;format&nbsp;the&nbsp;diffence-lines&nbsp;with&nbsp;your&nbsp;'style'
<br>&nbsp;&nbsp;&nbsp;&nbsp;*/
<br>&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">formatline</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">$nr1</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$nr2</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$stat</span><span style="color: #007700">,&nbsp;&amp;</span><span style="color: #0000BB">$value&nbsp;</span><span style="color: #007700">)&nbsp;&nbsp;</span><span style="color: #FF8000">#change&nbsp;to&nbsp;$value&nbsp;if&nbsp;problems
<br>&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">{
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if&nbsp;(&nbsp;</span><span style="color: #0000BB">trim</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">$value&nbsp;</span><span style="color: #007700">)&nbsp;==&nbsp;</span><span style="color: #DD0000">""&nbsp;</span><span style="color: #007700">)
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #DD0000">""</span><span style="color: #007700">;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;switch&nbsp;(&nbsp;</span><span style="color: #0000BB">$stat&nbsp;</span><span style="color: #007700">)
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;case&nbsp;</span><span style="color: #DD0000">"="</span><span style="color: #007700">:
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">$nr1</span><span style="color: #007700">.&nbsp;</span><span style="color: #DD0000">"&nbsp;:&nbsp;$nr2&nbsp;:&nbsp;=&nbsp;"</span><span style="color: #007700">.</span><span style="color: #0000BB">htmlentities</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">$value&nbsp;</span><span style="color: #007700">)&nbsp;&nbsp;.</span><span style="color: #DD0000">"&lt;br&gt;"</span><span style="color: #007700">;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;break;
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;case&nbsp;</span><span style="color: #DD0000">"+"</span><span style="color: #007700">:
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">$nr1</span><span style="color: #007700">.&nbsp;</span><span style="color: #DD0000">"&nbsp;:&nbsp;$nr2&nbsp;:&nbsp;+&nbsp;&lt;font&nbsp;color='blue'&nbsp;&gt;"</span><span style="color: #007700">.</span><span style="color: #0000BB">htmlentities</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">$value&nbsp;</span><span style="color: #007700">)&nbsp;&nbsp;.</span><span style="color: #DD0000">"&lt;/font&gt;&lt;br&gt;"</span><span style="color: #007700">;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;break;
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;case&nbsp;</span><span style="color: #DD0000">"-"</span><span style="color: #007700">:
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">$nr1</span><span style="color: #007700">.&nbsp;</span><span style="color: #DD0000">"&nbsp;:&nbsp;$nr2&nbsp;:&nbsp;-&nbsp;&lt;font&nbsp;color='red'&nbsp;&gt;"</span><span style="color: #007700">.</span><span style="color: #0000BB">htmlentities</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">$value&nbsp;</span><span style="color: #007700">)&nbsp;&nbsp;.</span><span style="color: #DD0000">"&lt;/font&gt;&lt;br&gt;"</span><span style="color: #007700">;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;break;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;}
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;
<br></span><span style="color: #0000BB">?&gt;</span>&lt;html&gt;
<br>&lt;head&gt;&lt;title&gt;php&nbsp;diff&nbsp;example&lt;/title&gt;&lt;/head&gt;
<br>
<br>&lt;body&gt;
<br>&lt;h1&gt;php&nbsp;diff&nbsp;example&lt;/h1&gt;
<br>&lt;h2&gt;UPDATE!&lt;/h2&gt;
<br>
<br>Nils&nbsp;Kappmeier&nbsp;has&nbsp;made&nbsp;a&nbsp;new&nbsp;implementaion&nbsp;of&nbsp;this&nbsp;script.&nbsp;&lt;br&nbsp;/&gt;
<br>Less&nbsp;Buggs&nbsp;and&nbsp;Comments&nbsp;but&nbsp;faster&nbsp;;)
<br>
<br>so&nbsp;dont&nbsp;forget&nbsp;to&nbsp;check:&nbsp;&lt;a&nbsp;href="./diff2.php"&gt;diff-version2&lt;/a&gt;.&nbsp;&lt;br&nbsp;/&gt;
<br>
<br>The&nbsp;script&nbsp;can&nbsp;also&nbsp;be&nbsp;found&nbsp;in&nbsp;&lt;a&nbsp;href="http://www.pmwiki.org"&gt;www.pmwiki.org&nbsp;&lt;/a&gt;&nbsp;in&nbsp;the&nbsp;module
<br>/script/phpdiff.php&nbsp;.&nbsp;
<br>
<br>&lt;br&nbsp;/&gt;
<br>&lt;h3&gt;Implementation&nbsp;of&nbsp;DIFF&nbsp;in&nbsp;pure-php&lt;/h3&gt;
<br><span style="color: #0000BB">&lt;?
<br>&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#example&nbsp;usage:
<br>&nbsp;&nbsp;&nbsp;&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$f1</span><span style="color: #007700">=Array(&nbsp;&nbsp;</span><span style="color: #DD0000">"&lt;html&gt;"</span><span style="color: #007700">,
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"&lt;head&gt;&lt;title&gt;Text&lt;/title&gt;&lt;/head&gt;"</span><span style="color: #007700">,
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"&lt;body&gt;"</span><span style="color: #007700">,
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"code&nbsp;a"</span><span style="color: #007700">,
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"code&nbsp;b"</span><span style="color: #007700">,
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"code&nbsp;c"</span><span style="color: #007700">,
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"code&nbsp;d"</span><span style="color: #007700">,
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"code&nbsp;e"</span><span style="color: #007700">,
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"code&nbsp;g"</span><span style="color: #007700">,
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"&lt;/body&gt;"</span><span style="color: #007700">,
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"&lt;/html&gt;"&nbsp;</span><span style="color: #007700">);
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$f2</span><span style="color: #007700">=Array(&nbsp;&nbsp;</span><span style="color: #DD0000">"&lt;html&gt;"</span><span style="color: #007700">,
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"&lt;head&gt;&lt;title&gt;Text2&lt;/title&gt;&lt;/head&gt;"</span><span style="color: #007700">,
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"&lt;body&gt;"</span><span style="color: #007700">,
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"code&nbsp;a"</span><span style="color: #007700">,
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"code&nbsp;a"</span><span style="color: #007700">,
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"code&nbsp;c"</span><span style="color: #007700">,
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"code&nbsp;d"</span><span style="color: #007700">,
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"code&nbsp;e"</span><span style="color: #007700">,
<br>
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"code&nbsp;g"</span><span style="color: #007700">,
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"code&nbsp;f"</span><span style="color: #007700">,
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"&lt;/body&gt;"</span><span style="color: #007700">,
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">"&lt;/html&gt;"&nbsp;</span><span style="color: #007700">);
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#you&nbsp;can&nbsp;use&nbsp;files&nbsp;as&nbsp;input&nbsp;and&nbsp;compare&nbsp;them
<br>&nbsp;&nbsp;&nbsp;&nbsp;#&nbsp;simply&nbsp;with,&nbsp;this&nbsp;gives&nbsp;you&nbsp;simple&nbsp;diff&nbsp;in&nbsp;your&nbsp;webserver.
<br>&nbsp;&nbsp;&nbsp;&nbsp;#
<br>&nbsp;&nbsp;&nbsp;&nbsp;#&nbsp;$f3=&nbsp;file&nbsp;("path&nbsp;to&nbsp;file");
<br>&nbsp;&nbsp;&nbsp;&nbsp;
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">print&nbsp;</span><span style="color: #DD0000">"&lt;pre&gt;"</span><span style="color: #007700">;
<br>&nbsp;&nbsp;&nbsp;&nbsp;print&nbsp;</span><span style="color: #DD0000">"Input-Data:&nbsp;&lt;xmp&gt;"</span><span style="color: #007700">;
<br>&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">print_r</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">$f1&nbsp;</span><span style="color: #007700">);
<br>&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">print_r</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">$f2&nbsp;</span><span style="color: #007700">);
<br>&nbsp;&nbsp;&nbsp;&nbsp;print&nbsp;</span><span style="color: #DD0000">"&lt;/xmp&gt;"</span><span style="color: #007700">;
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;print&nbsp;</span><span style="color: #DD0000">"&lt;hr&gt;Identlical&nbsp;lines&nbsp;hidden:&lt;br&gt;"</span><span style="color: #007700">;
<br>&nbsp;&nbsp;&nbsp;&nbsp;print&nbsp;</span><span style="color: #0000BB">arr_diff</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">$f1</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$f2&nbsp;</span><span style="color: #007700">);
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;print&nbsp;</span><span style="color: #DD0000">"&lt;hr&gt;Identlical&nbsp;lines&nbsp;shown:&lt;br&gt;"</span><span style="color: #007700">;
<br>&nbsp;&nbsp;&nbsp;&nbsp;print&nbsp;</span><span style="color: #0000BB">arr_diff</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">$f1</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$f2&nbsp;</span><span style="color: #007700">,</span><span style="color: #0000BB">1</span><span style="color: #007700">);
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">#comparing&nbsp;with&nbsp;array_diff()
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">print&nbsp;</span><span style="color: #DD0000">"&lt;hr&gt;Compared:&nbsp;array_diff(&nbsp;\$f1,&nbsp;\$f2&nbsp;);&lt;br&gt;&nbsp;"</span><span style="color: #007700">;
<br>&nbsp;&nbsp;&nbsp;&nbsp;print&nbsp;</span><span style="color: #DD0000">"&lt;xmp&gt;"</span><span style="color: #007700">;
<br>&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">print_r&nbsp;</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">array_diff</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">$f1</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$f2&nbsp;</span><span style="color: #007700">)&nbsp;);
<br>&nbsp;&nbsp;&nbsp;&nbsp;print&nbsp;</span><span style="color: #DD0000">"&lt;/xmp&gt;"</span><span style="color: #007700">;
<br>&nbsp;&nbsp;&nbsp;&nbsp;
<br>&nbsp;&nbsp;&nbsp;&nbsp;print&nbsp;</span><span style="color: #DD0000">"&lt;hr&gt;Compared:&nbsp;array_diff(&nbsp;\$f2,&nbsp;\$f1&nbsp;);&lt;br&gt;&nbsp;"</span><span style="color: #007700">;
<br>&nbsp;&nbsp;&nbsp;&nbsp;print&nbsp;</span><span style="color: #DD0000">"&lt;xmp&gt;"</span><span style="color: #007700">;
<br>&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">print_r&nbsp;</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">array_diff</span><span style="color: #007700">(&nbsp;</span><span style="color: #0000BB">$f2</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$f1&nbsp;</span><span style="color: #007700">)&nbsp;);
<br>&nbsp;&nbsp;&nbsp;&nbsp;print&nbsp;</span><span style="color: #DD0000">"&lt;/xmp&gt;"</span><span style="color: #007700">;
<br>&nbsp;&nbsp;&nbsp;&nbsp;print&nbsp;</span><span style="color: #DD0000">"&lt;/pre&gt;"</span><span style="color: #007700">;
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;print&nbsp;</span><span style="color: #DD0000">"&lt;hr&gt;"</span><span style="color: #007700">;
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;print&nbsp;</span><span style="color: #DD0000">"&amp;copy&nbsp;2007&nbsp;&lt;a&nbsp;href='mailto:d.u.phpnet@holomind.de?subject=diff'&gt;Daniel&nbsp;Unterberger&lt;/a&gt;.&lt;a&nbsp;href='http://www.avedanta.com'&gt;Ayurveda&lt;/a&gt;.&lt;a&nbsp;href='http://www.ayurmedia.de/projekte.php'&gt;AyurMedia&nbsp;Projekte&lt;/a&gt;&nbsp;"</span><span style="color: #007700">;
<br>&nbsp;&nbsp;&nbsp;&nbsp;print&nbsp;</span><span style="color: #DD0000">"&lt;a&nbsp;href='./diff.src.php'&gt;&nbsp;view&nbsp;source&nbsp;&lt;/a&gt;."</span><span style="color: #007700">;
<br></span><span style="color: #0000BB">?&gt;
<br></span>&lt;a&nbsp;href="http://www.ayurmedia.de"&gt;ayurmedia&lt;/a&gt;
<br>&amp;nbsp;&lt;a&nbsp;href="http://www.yogaexpo.de"&gt;Yoga&nbsp;Expo&nbsp;2010&lt;/a&gt;&lt;/body&gt;&lt;/html&gt;
<br></span>
</code><hr>
©2003-2007 Daniel Unterberger



</body></html>