<?php
system("python /var/www/lhpins.pyc --action set --pin 1 --status off");
while (true) {
	if(file_exists("/var/www/temp_files/commands.do")){
		$list = json_decode(file_get_contents("/var/www/temp_files/commands.do"));
                foreach($list as $command):
                    system("$command");
                endforeach;
		unlink("/var/www/temp_files/commands.do");
	}
	if(file_exists("/var/www/audio.mp3")){
		system("mplayer /var/www/audio.mp3");
		unlink("/var/www/audio.mp3");
	}
	usleep(50000);
}
?>
