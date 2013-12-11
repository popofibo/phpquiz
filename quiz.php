<?php
session_start ();
?>
<!--
	Copyright (C) 2011 Nitin Pathak (www.popofibo.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 -->
<html>
<head>
<title>Online Quiz</title>
<link rel="stylesheet" type="text/css" href="quiz.css" />
</head>

<body bgcolor="#FFFFFF" text="#000000" link="#C0C0C0" vlink="#C0C0C0"
	alink="#C0C0C0" class="style2">

<div align="center">
<p class='style5'>


<table width='100%'>
	<tr>
		<td width="60%" colspan="2" align="center">
		<?php
		include 'header.htm';
		?>
		<hr class='hr3' />
		</td>
	</tr>
	<tr>
		<td>
		<table width="20%" align="left">
			<tr>
				<td align="left">
				<?php
				include 'leftnav.html';
				?>
				</td>
			</tr>
		</table>

		<table width="80%" align="right">
			<tr>
				<td width="80%" align='center'></br>
				</br>

<?php
static $index = 1;
if (isset ( $_REQUEST ['gpn'] ) && isset ( $_REQUEST ['name'] )) {
	$_SESSION ['gpn'] = $_REQUEST ['gpn'];
	$_SESSION ['username'] = $_REQUEST ['name'];
}

if (isset ( $_SESSION ['gpn'] ) && isset ( $_SESSION ['username'] )) {
	
	echo "<p class='style2' align='center'>Welcome " . $_SESSION ['username'] . "!</p>";
	
	$dbhost = 'dbname';
	$dbuser = 'dbusername';
	$dbpass = 'dbuserpassword';
	
	$conn = mysql_connect ( $dbhost, $dbuser, $dbpass ) or die ( "<div align='center'>Error connecting to mysql</div>" );
	
	$dbname = 'dbname';
	mysql_select_db ( $dbname );
	
	$xmlFile = "questions.xml";
	
	$data = implode ( "", file ( $xmlFile ) );
	$parser = xml_parser_create ();
	
	xml_parser_set_option ( $parser, XML_OPTION_CASE_FOLDING, 0 );
	xml_parser_set_option ( $parser, XML_OPTION_SKIP_WHITE, 1 );
	xml_parse_into_struct ( $parser, $data, $values, $tags );
	xml_parser_free ( $parser );
	
	$questionNo = 0;
	
	foreach ( $values as $key => $val ) {
		if ($val [tag] == "TEXT") {
			$questions [$questionNo] ['text'] = $val [value];
		}
		if ($val [tag] == "CHOICES") {
			$questions [$questionNo] ['choices'] = $val [value];
		}
		if ($val [tag] == "ANSWER") {
			$questions [$questionNo] ['answer'] = $val [value];
			$questionNo ++;
		}
	}
	
	import_request_variables ( "p", "post_" );
	
	if (! isset ( $post_answers )) {
		echo "<p align='center' class='style5'><b>Q1. " . $questions [0] ['text'] . "</b>\n";
		echo "<form action=\"$PHP_SELF\" method=\"post\">\n";
		
		$choices = explode ( ", ", $questions [0] ['choices'] );
		
		if (count ( $choices ) == 1) {
			echo "<input align='center' type=\"text\" name=\"answers[0]\" size=10>\n";
		} else {
			echo "<table align='center' class='style5'>";
			for($i = 0; $i < count ( $choices ); $i ++) {
				echo "<tr>
					<td align='left' class='style5'>";
				echo "<input type=\"radio\" name=\"answers[0]\" value=\"" . $choices [$i] . "\"> " . $choices [$i] . "<br>\n";
				echo "</td></tr>";
			}
			echo "</table>";
		}
		
		echo "<input align='center' type=\"image\" src='images/next.png' alt='Next Question!' />\n";
		echo "</form></p>\n";
	} 

	elseif (count ( $questions ) > count ( $post_answers )) {
		$nextQuestion = count ( $post_answers );
		$index = $nextQuestion + 1;
		echo "<div align='center' class='style5'><b>Q" . $index . ". " . $questions [$nextQuestion] ['text'] . "</b></div>\n";
		$index ++;
		echo "<p align='center' class='style5'><form action=\"$PHP_SELF\" method=\"post\">\n";
		
		for($i = 0; $i < count ( $post_answers ); $i ++) {
			echo "<input align='center' type=\"hidden\" name=\"answers[$i]\" value=\"$post_answers[$i]\">\n";
		}
		
		$choices = explode ( ", ", $questions [$nextQuestion] ['choices'] );
		
		if (count ( $choices ) == 1) {
			echo "<input align='center' type=\"text\" name=\"answers[$nextQuestion]\" size=10>\n";
		} else {
			echo "<table align='center' class='style5'>";
			for($i = 0; $i < count ( $choices ); $i ++) {
				echo "<tr>
					<td align='left' class='style5'>";
				echo "<input type=\"radio\" name=\"answers[$nextQuestion]\" value=\"" . $choices [$i] . "\">" . $choices [$i] . "<br>\n";
				echo "</td></tr>";
			}
			echo "</table>";
		}
		
		if (count ( $questions ) == count ( $post_answers ) + 1) {
			echo "<input align='center' type=\"image\" src='images/submit.png' alt=\"Calculate Score\">\n";
			if (isset ( $_POST ['Submit'] )) {
			
			}
		} else {
			echo "<input align='center' type=\"image\" src='images/next.png' alt='Next Question!' />\n";
		}
		
		echo "</form></p>\n";
	
	} else {
		// CALCULATE AND PRINT SCORE - Modify to store the score
		$noQuestions = count ( $questions );
		for($i = 0; $i < $noQuestions; $i ++) {
			if ($questions [$i] ['answer'] == $post_answers [$i]) {
				$noCorrectAnswers ++;
			}
		}
		
		$score = ($noCorrectAnswers / $noQuestions) * 100;
		
		$score = round ( $score );
		
		$username = trim ( $_SESSION ['username'] );
		$usergpn = trim ( $_SESSION ['gpn'] );
		// $hostname = gethostbyaddr ( $_SERVER ['REMOTE_ADDR'] );
		$hostname = getenv ( 'COMPUTERNAME' );
		// $username = getenv ( 'COMPUTERNAME' );
		putenv ( "TZ=Asia/Kolkata" );
		
		$mysqldate = date ( 'Y-m-d H:i:s', time () );
		
		echo "<div class='style4' align='center'>You scored $score%</div>\n</br>";
		
		mysql_query ( "INSERT INTO user_info (user_gpn, user_fullname, user_noofques, user_percent, user_timestamp, user_hostname) VALUES ('$usergpn', '$username', '$noCorrectAnswers', '$score', '$mysqldate', '$hostname')" ) or die ( /*mysql_error ()*/"<div class='style5' align='center'><em><font color='RED'>You have already been scored! Please wait for the results...</font></em><div>" );
		
		mysql_close ( $conn );
		
		$to = "popo.fibo@gmail.com";
		$subject = "Score for $username ($usergpn)";
		$message = "Hi $from - $username ($usergpn) answered $noCorrectAnswers out of $noQuestions correctly with percentage score as $score%";
		$from = "admin@popofibo.com";
		$headers = "From: $from";
		
		//mail ( $to, $subject, $message, $headers );
		//echo "<div class='style2' align='center'>Mail has been sent to $to with your score.</div>";
		

		if ($noCorrectAnswers == 0) {
			echo "<p class='style5' align='center'>You answered no questions correctly.</p>";
		}
		
		if ($noCorrectAnswers == 1) {
			echo "<p class='style5' align='center'>You answered 1 out of $noQuestions questions correctly.</p>";
		}
		
		if ($noCorrectAnswers > 1 && $noCorrectAnswers < $noQuestions) {
			echo "<p class='style5' align='center'>You answered $noCorrectAnswers out of $noQuestions questions correctly.</p>";
		}
		
		if ($noCorrectAnswers == $noQuestions) {
			echo "<p class='style5' align='center'>You answered all questions correctly!</p>";
		}
		echo "<img src='images/scorer.png' />";
	}
} else {
	
	echo "<p align='center' class='style2'>Please make sure you enter the correct credentials, 
	since there is no authentication against the credentials, 
	scores would be compiled against the corresponding values.</p></br>";
	
	echo "<form name='auth_form' method='POST' action='$PHP_SELF'>";
	
	echo "<table align='center' width='25%' border='0' class='style1'>";
	
	echo "<tr><td colspan='2'><b><em>Enter your credentials:</em></b></td></tr>";
	
	echo "<tr><td>GPN # : </td><td><input type='text' name='gpn' /></td></tr>";
	
	echo "<tr><td>Full Name : </td><td><input type='text' name='name' /></td></tr>";
	
	echo "<tr><td colspan='2' align='center'><input type='submit' name='zip' value='Log me in!' /></td></tr></table>";
	
	echo "</form>";
}

?>
				</td>
			</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td width="100%" colspan="2" align="center">
		<hr class='hr3' />
			<?php
			include 'footer.htm';
			?>
		</td>
	</tr>
</table>
</p>
</div>
</body>
</html>