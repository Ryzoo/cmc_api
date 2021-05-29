<!DOCTYPE html>
<html lang="pl-PL">
  <head>
    <meta charset="utf-8">
    <title>render</title>
	  <link rel="stylesheet" type="text/css" href="dist/build.css">
  </head>
  <body>
    <div id="app"></div>
	<script defer>
		window.apiData = JSON.parse('<?php echo json_encode(array("id"=>$_GET["id"],"token"=>$_GET["token"])) ?>');
	</script>
	<script defer src="https://cdnjs.cloudflare.com/ajax/libs/native-promise-only/0.8.1/npo.js"></script>
    <script defer
            src="https://code.jquery.com/jquery-3.3.1.min.js"
            integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
            crossorigin="anonymous"></script>
	<script defer src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.18.0/axios.min.js"></script>
	<script defer src="https://d309knd7es5f10.cloudfront.net/createjs_1.1_min.js"></script>
	<script defer src="https://d309knd7es5f10.cloudfront.net/zim_7.3.0.js"></script>
    <script defer src="dist/build.js"></script>
  </body>
</html>
