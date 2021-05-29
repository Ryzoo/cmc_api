// Run this from the commandline:
// phantomjs runner.js | ffmpeg -y -c:v png -f image2pipe -r 24 -t 10  -i - -c:v libx264 -pix_fmt yuv420p -movflags +faststart output.mp4
// http://localhost:8080
// https://app.centrumklubu.pl

var system = require('system');
var args = system.args;
var page = require('webpage').create(),
	address = 'https://api.centrumklubu.pl/Services/cm/render/index.php?id=' + args[1] + '&token=' + args[3],
	allFrame = 0,
	width = 1200,
	height = 720;
var maxIter = 200;

function renderIt(frame) {
	if (frame >= allFrame) {
		phantom.exit();
		return;
	}

	var dir = "/home/forge/api.centrumklubu.pl/public_html/";

	page.render(dir+'render/zdj_' + args[2] + '/frame_' + frame + '.jpeg', {format: 'jpeg', quality: '100'});

	setTimeout(function () {
		page.evaluate(function () {
			$('#nextFrame').click();
		});
		renderIt(frame + 1);
	}, 5);
}

page.onError = function(msg, trace) {
	var msgStack = ['PHANTOM ERROR: ' + msg];
	if (trace && trace.length) {
	  msgStack.push('TRACE:');
	  trace.forEach(function(t) {
		msgStack.push(' -> ' + (t.file || t.sourceURL) + ': ' + t.line + (t.function ? ' (in function ' + t.function +')' : ''));
	  });
	}
	console.log(msgStack.join('\n'));
	phantom.exit(1);
  };

page.onConsoleMessage = function(msg) {
	system.stderr.writeLine('console: ' + msg);
};

page.viewportSize = {width: width, height: height};

page.open(address, function (status) {
	if (status !== 'success') {
		phantom.exit(1);
	} else {
		page.clipRect = {top: 0, left: 0, width: width, height: height};
		checkFromSecond();
	}
});


function checkFromSecond() {
	var value;
	maxIter = maxIter-1;

	if(maxIter <= 0){
		phantom.exit();
		return;
	}

	setTimeout(function () {
		value = checkValue();
		if (value == -2) checkFromSecond();
		else setTimeout(renderAll,5000);
	}, 100);
}

function renderAll(){
	page.evaluate(function () {
		$('#playIt').click();
	});

	allFrame = checkValue();

	setTimeout(function () {
		renderIt(0);
	}, 5);
}

function checkValue(){
	return page.evaluate(function () {
		if(!document.getElementById('allFrame')) return -2;
		return parseInt(document.getElementById('allFrame').value);
	});
}


