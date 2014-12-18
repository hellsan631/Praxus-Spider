<?php

include_once "./header.php";

?>

<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Sidekick Admin</title>
	<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.1/css/font-awesome.css" rel="stylesheet">
	<link href="./css/admin.css" rel="stylesheet" type="text/css" />
	<link href="./css/redactor.css" rel="stylesheet" type="text/css" />
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script src="./js/redactor.min.js"></script>
	<script>
		var jq19 = jQuery.noConflict(true);
	</script>
	<script src="./js/admin.js"></script>

<link rel="icon" type="image/ico" href="http://targetproof.net/images/favicon.ico">

</head>
<body>

	<?php
		global $si;

		$si = 1;

		include "menu.php";
	?>

    <div id="loadedContent"></div>

	<section id="content">

		<?php if(isset($_SESSION['result'])): ?>

			<div id="results">
				<h3>Result</h3>
				<h6><?php echo $_SESSION['result']; ?></h6>
			</div>

		<?php

			unset($_SESSION['result']);

		endif; ?>

		<div class="clear-fix row">
			<h2>Member Directory</h2>
			<h6>An index of projects in the system</h6>

			<br/>

			<ul class="pages-list">
				<?php
					$members = new member;

                    $members = $members->getList();

                    foreach($members as $member){

                        echo "<li project-id=\"{$member->id}\">{$member->name}</li>";

                    }

                    echo "<li id='newProject'>Create New Member<i class='fa right fa-desktop'></i></li>";

				?>
			</ul>
		</div>


	</section>

</body>
</html>