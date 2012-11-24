<?php 

function getAvatar($email){
	//pega avatar
    $grav_url = "http://www.gravatar.com/avatar/".md5(strtolower(trim($email)))."?d=".urlencode('img/velhinha.jpg')."&s=150";
    
  	$cURL = curl_init($grav_url);
  	curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
  	// Seguir qualquer redirecionamento que houver na URL
  	curl_setopt($cURL, CURLOPT_FOLLOWLOCATION, true);
  	curl_exec($cURL);
  	
  	// Pega o código de resposta HTTP
  	$result = curl_getinfo($cURL, CURLINFO_HTTP_CODE);
  	curl_close($cURL);
  	
  	if($result == 200){
  		return $grav_url;
  	}
  	else{
  		return 'img/velhinha.jpg';
  	}
    
}

function selecionaImagem($value){
	if($value == 1){
		return '<img src="img/o.png" height="80" width="80" alt="">';
	}
	else if($value == 2){
		return '<img src="img/x.png" height="80" width="80" alt="">';
	}
	else{
		return '<img src="img/loader.gif" height="80" width="80" alt="">';
	}
}

//retorna um nome randomico para algum arquivo
function randNomeArquivo(){
	while(true){
		$rand = rand(0, 10000);
		$pasta = (int)($rand/100);
		$sub_pasta = (int)($rand/100);
		if(!file_exists('arquivos/'.$pasta.'/'.$sub_pasta.'/'.$rand.'.txt')){
			return $rand;
		}
	}
}

function verificaJogoVelha($jogo){
	//valor 0 => nao jogado
	//valor 1 => jogador 1
	//valor 2 => jogador 2
	
	if($jogo[0] != 0){
		//primeira horizontal
		if($jogo[0] == $jogo[1] && $jogo[0] == $jogo[2]){
			return $jogo[0];
		}
		//primeira vertical
		else if($jogo[0] == $jogo[3] && $jogo[0] == $jogo[6]){
			return $jogo[0];
		}
		//primeira diagonal
		else if($jogo[0] == $jogo[4] && $jogo[0] == $jogo[8]){
			return $jogo[0];
		}
	}
	else if($jogo[2] != 0){
		//terceira vertical
		if($jogo[2] == $jogo[5] && $jogo[2] == $jogo[8]){
			return $jogo[2];
		}
		//segunda diagonal
		else if($jogo[2] == $jogo[4] && $jogo[2] == $jogo[6]){
			return $jogo[2];
		}
	}
	else if($jogo[7] != 0){
		//terceira horizontal
		if($jogo[6] == $jogo[7] && $jogo[6] == $jogo[8]){
			return $jogo[4];
		}
		//segunda vertical
		else if($jogo[1] == $jogo[7] && $jogo[4] == $jogo[7]){
			return $jogo[1];
		}
	}
	//segunda horizontal
	else if($jogo[3] != 0 && $jogo[3] == $jogo[4] && $jogo[3] == $jogo[5]){
		return $jogo[3];
	}
	
	for($i = 0; $i < 9; $i++){
		if($jogo[$i] == 0) break;
	}
	
	if($i == 9) return 'empate';
	
	return false;
}

?>