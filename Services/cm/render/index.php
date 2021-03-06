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
	<script src="https://cdnjs.cloudflare.com/ajax/libs/native-promise-only/0.8.1/npo.js"></script>
  <script
  src="https://code.jquery.com/jquery-3.6.0.min.js"
  integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
  crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
  <script src="https://zimjs.org/cdn/1.1.0/createjs.js"></script>
  <script src="https://zimjs.org/cdn/7.3.0/zim.js"></script>
  <script src="dist/build.js"></script>
  </body>
</html>
