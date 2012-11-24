<?php

session_start();
include 'outro.php';

$tmp = array();
if(array_key_exists('deletar', $_GET)){
	deletarArquivos();
	exit();
}
else if(!$_GET || !$_GET['partida']) header('Location: http://gustavohk.phpfogapp.com/t1/index.php?partida='.randNomeArquivo());
else{
	//passa o valor para inteiro
	$partida = (int)$_GET['partida'];
	//quando o valor È uma string e passado para um inteiro, ele fica igual a 0
	if($partida == 0) header('Location: http://gustavohk.phpfogapp.com/t1/index.php?partida='.randNomeArquivo());
	
	//configuracoes inciais
	$espectador = false;
	$jogador = false;
	$jogador_nome = false;
	$seleciona_jogador = false;
	$tabela = array(0,0,0,0,0,0,0,0,0);
	$email1 = '';
	$email2 = '';
	$email = '';
	$jogador1 = 'N„o Logado';
	$jogador2 = 'N„o Logado';
	$resultado = false;
	
	//separa os arquivos em pasta, no caso, no m·ximo 100 arquivos por pasta
	$pasta = (int)($partida/100);
	if(!file_exists('arquivos/'.$pasta)) mkdir('arquivos/'.$pasta); //cria diretorio
	$sub_pasta = (int)($pasta/100);
	if(!file_exists('arquivos/'.$pasta.'/'.$sub_pasta)) mkdir('arquivos/'.$pasta.'/'.$sub_pasta); //cria sub diretorio
	$file_name = 'arquivos/'.$pasta.'/'.$sub_pasta.'/'.$partida.'.txt';
	
	if(file_exists($file_name)){ //arquivo ja existe
		
		//segundo jogador
		$tmp = file_get_contents($file_name);
		$tmp = explode(';', $tmp);
		
		//indica no arquivo que o jogador 2 entrou
		if(count($tmp) == 1 && !$_POST && !array_key_exists('jogador', $_GET)){
			file_put_contents($file_name, ';!>email', FILE_APPEND);
			$jogador = 'jogador2';
		}
		//salva o nome e email do(s) jogador(es)
		else if((count($tmp) == 2 || count($tmp) == 1) && $_POST){
			
			if(array_key_exists('jogador1', $_POST)){
				
				if(array_key_exists('1', $tmp)){
					file_put_contents($file_name, $_POST['jogador1'].'>'.$_POST['email'].';'.$tmp[1]);
				}
				else{
					file_put_contents($file_name, $_POST['jogador1'].'>'.$_POST['email']);
				}
				$jogador1 = $jogador_nome = $_POST['jogador1'];
				$email1 = $_POST['email'];
			}
			else{
				file_put_contents($file_name, $tmp[0].';'.$_POST['jogador2'].'>'.$_POST['email']);
				$jogador2 = $jogador_nome = $_POST['jogador2'];
				$email2 = $_POST['email'];
			}

			$email = $_POST['email'];
		}
		//partida iniciada
		else if(array_key_exists('jogador', $_GET)){
			//pega nome do jogador
			$jogador_nome = $_GET['jogador'];
			$email = $_GET['email'];
			
			$tmp = file_get_contents($file_name);
			$tmp = explode(';', $tmp);
			//jogador 1
			$tmp[0] = explode('>', $tmp[0]);
			//jogador 2
			$tmp[1] = explode('>', $tmp[1]);
			
			if(array_key_exists(0, $tmp[0]) && $tmp[0][0] != '!'){
				$jogador1 = $tmp[0][0];
				$email1 = $tmp[0][1];
			}
			if(array_key_exists(0, $tmp[1]) && $tmp[0][1] != '!'){
				$jogador2 = $tmp[1][0];
				$email2 = $tmp[1][1];
			}
			
			//existe os dois jogadores com seus nomes
			if($tmp[0][0] != '!' && array_key_exists('0', $tmp[1]) && $tmp[1][0] != '!'){
				
				//cria a tabela no arquivo da partida
				if(!array_key_exists('2', $tmp)){
					
					file_put_contents($file_name, $tmp[0][0].'>'.$tmp[0][1].';'.$tmp[1][0].'>'.$tmp[1][1].';0>0>0>0>0>0>0>0>0;0');
					
					$tmp = file_get_contents($file_name);
					$tmp = explode(';', $tmp);
					$tmp[0] = explode('>', $tmp[0]);
					$tmp[1] = explode('>', $tmp[1]);
				}
				
				$tmp[2] = explode('>', $tmp[2]);
				
				$jogador1 = $tmp[0][0];
				$email1 = $tmp[0][1];
				
				$jogador2 = $tmp[1][0];
				$email2 = $tmp[1][1];
				
				$seleciona_jogador = $tmp[$tmp[3]][0];
				
				//foi realizado uma jogada
				if(array_key_exists('jogada', $_GET)){
					
					if($_GET['jogador'] == $tmp[0][0]){
						$tmp[2][$_GET['jogada']] = 1;
						$tmp[3] = 1;
					}
					else{
						$tmp[2][$_GET['jogada']] = 2;
						$tmp[3] = 0;
					}
					
					//usuarios
					$salva = $tmp[0][0].'>'.$tmp[0][1].';'.$tmp[1][0].'>'.$tmp[1][1];
					//tabela
					$salva .= ';'.$tmp[2][0].'>'.$tmp[2][1].'>'.$tmp[2][2].'>'.$tmp[2][3].'>'.$tmp[2][4].'>'.$tmp[2][5].'>'.$tmp[2][6].'>'.$tmp[2][7].'>'.$tmp[2][8];
					//quem jogou
					$salva .= ';'.$tmp[3];
					
					//salva a opcao escolhida no arquivo
					file_put_contents($file_name, $salva);
					//header('Location: http://gustavohk.phpfogapp.com/t1/index.php?partida='.$partida.'&jogador='.$jogador_nome.'&email='.$email);
				}
				
				//verifica se alguem ganhou
				$jogo_velha = verificaJogoVelha($tmp[2]);
				
				if($jogo_velha && file_exists($file_name)){ //verifica se terminou o jogo
					
					$file = file_get_contents('arquivos/ranking.txt');
					
					$file = explode(';', $file);
					$player = false;
					
					foreach($file as $key=>$value){
						$linha = explode('>', $value);
						if($linha[0] = $jogador_nome){
							$player = $key;
							break;
						}
					}
					
					
					//-------------------------------------------------------------------------------------
					
					//guarda um empate
					if($jogo_velha == 'empate'){
						
						$resultado = 'Ouve um empate, tentem denovo pra ver quem ganha.';
						
						if($player === false){
							$file[] = $jogador_nome.'>0>0>1;';
							file_put_contents('arquivos/ranking.txt', $file, FILE_APPEND);
						}
						else{
							//$linha = explode('>', $file[$player]);
							$file[$player] = $linha[0].'>'.(int)$linha[1].'>'.(int)$linha[2].'>'.((int)$linha[3] + 1).';';
							
							file_put_contents('arquivos/ranking.txt', $file);
						}
					}
					else{
						//jogador ganhou
						if($tmp[$tmp[3]][0] != $jogador_nome){
							if($player === false){
								$file[] = $jogador_nome.'>1>0>0;';
								file_put_contents('arquivos/ranking.txt', $file, FILE_APPEND);
							}
							else{
								//$linha = explode('>', $file[$player]);
								$file[$player] = $linha[0].'>'.((int)$linha[1] + 1).'>'.(int)$linha[2].'>'.(int)$linha[3].';';
									
								file_put_contents('arquivos/ranking.txt', $file);
							}
							
							$resultado = 'ParabÈns, vocÍ ganhou.';
						}
						//jogador perdeu
						else{
							if($player === false){
								$file[] = $jogador_nome.'>0>1>0;';
								file_put_contents('arquivos/ranking.txt', $file, FILE_APPEND);
							}
							else{
								//$linha = explode('>', $file[$player]);
								$file[$player] = $linha[0].'>'.(int)$linha[1].'>'.((int)$linha[2] + 1).'>'.(int)$linha[3].';';
								
								file_put_contents('arquivos/ranking.txt', $file);
							}
							
							$resultado = 'VocÍ perdeu, tente numa prÛxima vez.';
						}
					}
					
					//unlink($file_name);
					$jogador_nome = '';
					
				}
				
				//muda de linguagem
				if(array_key_exists('linguagem', $_GET)){
				
				}
				
				$tabela = $tmp[2];
				
			}
			
		}
		else{ //espectador
			//mostra tabela do andamento do jogo
			$espectador = true;
			$tmp = file_get_contents($file_name);
			$tmp = explode(';', $tmp);
			$tmp[0] = explode('>', $tmp[0]);
			$tmp[1] = explode('>', $tmp[1]);
			$tmp[2] = explode('>', $tmp[2]);
			
			$jogador1 = $tmp[0][0];
			$email1 = $tmp[0][1];
			$jogador2 = $tmp[1][0];
			$email2 = $tmp[1][1];
			
			$tabela = $tmp[2];
		}
		

		
	}
	else{ //arquivo nao existe
		//echo 'nao existe<br>';
		
		file_put_contents($file_name, '!>email');
		$jogador = 'jogador1';
	}
	
	//print_r($email1);
	//print_r($email2);
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>

<?php
	if($jogador_nome) echo '<meta http-equiv="refresh" content="2;URL=\'http://gustavohk.phpfogapp.com/t1/index.php?partida='.$partida.'&jogador='.$jogador_nome.'&email='.$email.'\'">'; 
	else if($espectador) echo '<meta http-equiv="refresh" content="2;URL=\'http://gustavohk.phpfogapp.com/t1/index.php?partida='.$partida.'">';
?>

<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link rel="stylesheet" href="css/index.css" type="text/css" media="screen" title="default">
<title>Jogo da Velha</title>
</head>
<body>

<?php
	echo '<div class=principal><div class="titulo">V………IA\'S GAME</div>';
	if($jogador && !array_key_exists('jogador', $_GET)){
		echo '<form action="index.php?partida='.$partida.'" method="post">';
		echo '<div class="imagem"><img src="img/veia.png" height="100" width="100" alt=""></div>';
		echo '<div class="tela_inicial"><div>Apelido: <input type="text" name="'.$jogador.'"></div>';
		echo '<div class="e-mail">E-mail: <input type="text" name="email"></div>';
		echo '<div class="ok"><input type="submit" value="OK"></div></div>';
		echo '<div class="link">Link: <input type="text" value="http://gustavohk.phpfogapp.com/t1/index.php?partida='.$partida.'" size="35"></div></form>';
	}
	else{
		
		if($resultado && array_key_exists('jogador', $_GET)){
			echo '<div class="resultado"><p>'.$resultado.'</p></div>';
		}
		
		else{
			echo '<div class="jogadores"><div><img src="'.getAvatar($email1).'" height="150" width="130" alt=""></div>';
			echo '<div class="vs">VS.</div>';
			echo '<div><img src="'.getAvatar($email2).'" height="150" width="130" alt=""></div>';
			echo '<div class="jogador1"><p>'.$jogador1.'</p></div><div class="jogador2"><p>'.$jogador2.'</p></div></div>';
			
			if($espectador){
				echo '<div class="resultado"><p>Espectador</p></div>';
			}
			else if($seleciona_jogador && $seleciona_jogador == $jogador_nome){
				echo '<div class="resultado"><p>Sua vez de jogar</p></div>';
			}
			else{
				echo '<div class="resultado"><p>Aguarde o outro jogaror</p></div>';
			}
		}
		
		//cria tabela
		$html = '<div class="table"><table cellpadding="0" cellspacing="0"><tr>';
		
		//mostra a tabela do jogo
		foreach($tabela as $key=>$value){
			//print_r($value);

			$html .= '<td>';
			if($espectador){
				$html .= selecionaImagem((int)$value);
			}
			else{
				//campos nao preenchidos
				if($value == 0 && $seleciona_jogador && $seleciona_jogador == $jogador_nome){
					$html .= '<a href="http://gustavohk.phpfogapp.com/t1/index.php?partida='.$partida.'&jogador='.$jogador_nome.'&email='.$email.'&jogada='.$key.'">';
					$html .= '<img src="img/loader.gif" height="80" width="80" alt=""></a>';
				}
				else{
					$html .= selecionaImagem($value);
				}
			}
			$html .= '</td>';
			if(!(((int)$key+1) % 3)){
				$html .= '</tr><tr>';
			}
		}
		
		$html = substr($html, 0, count($html) - 5);
		$html .= '</table></div>';
		
		echo $html;

	}

?>

</div></body>
</html>