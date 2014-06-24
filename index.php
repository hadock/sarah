<html lang="es-ES">
<head>	
	<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
	<input type="button" id="toggle" value="Stop"><br>
	<textarea style="height: 300px; width: 500px;" id="output"></textarea>

	<script src="speech.js"></script>
	<script>
			/** --- **/
			var output = document.getElementById("output");
			var button = document.getElementById("toggle");
			button.onclick = function() {
					if(this.value == "Stop") {
							sr.stop();
							this.value = "Start";
					} else {
							sr.start();
							this.value = "Stop";
					}
			}
			/** --- **/

			var sr = new SpeechRecognition();

			/** Events **/
			sr.on("starting", function() { console.log("[SpeechRecognition]", "Starting..."); });
			sr.on("started", function() { button.value = "Stop"; console.log("[SpeechRecognition]", "Started."); });
			sr.on("stopping", function() { console.log("[SpeechRecognition]", "Stopping..."); });
			sr.on("stopped", function() { button.value = "Start"; $("#toggle").click(); console.log("[SpeechRecognition]", "Stopped."); });

			sr.on("optionschanged", function() { console.log("[SpeechRecognition]", "Options changed."); });
			/** Events **/

			/** Set options **/
			//sr.set("language", "en-GB");
			sr.set({
					language: "es",
					continuous: true,                 
					interimResults: false
			});

			/** Error & Result Events **/
			sr.on("error", function(e) {
					console.log("[SpeechRecognition]", "Error:", e);
			});

			sr.on("result", function(evt) {
					console.log("[SpeechRecognition]", "Result:", evt);

					for (var i = evt.resultIndex; i < evt.results.length; ++i) {
					 if (evt.results[i].isFinal) {
							output.value += evt.results[i][0].transcript +" | ";
							$.get("test.php?text="+evt.results[i][0].transcript);
					 } else {
							//evt.results[i][0].transcript; <-- Not fully recognized (alias: interim script)!
					 }
					}

			});
			
			/** Start & Stop **/
			sr.start();
			//sr.stop();

	</script>
</body>
</html>
