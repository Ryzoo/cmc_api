<!DOCTYPE html>
<html lang="pl-PL">
  <head>
    <meta charset="utf-8">
    <title>render</title>
	  <link rel="stylesheet" type="text/css" href="dist/build.css">
  </head>
  <body>
    <div id="app"></div>
	<script>
		window.apiData = JSON.parse('<?php echo json_encode(array("id"=>$_GET["id"],"token"=>$_GET["token"])) ?>');
	</script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/native-promise-only/0.8.1/npo.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
            crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
  <script src="https://zimjs.org/cdn/1.3.3/createjs.js"></script>
  <script src="https://zimjs.org/cdn/cat/04/zim.js"></script>
  <script src="dist/build.js"></script>

  </body>
</html>
