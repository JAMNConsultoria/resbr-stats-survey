<?php
echo "<pre>";
print_r($_FILES);
echo "</pre>";
$nomeTabelaRelatorio ="relatorios_resbr";
#cria estrutura do banco de dados através do arquivo de syntax .sps
if (isset($_FILES['arquivoSPS']) and ($_FILES['arquivoSPS']['size']>0 )) {

	$uploadDir = "upload/";
	$spsFileName = $uploadDir . $_FILES['arquivoSPS']['name'];

	if(move_uploaded_file($_FILES['arquivoSPS']['tmp_name'], $spsFileName)) {
		echo "Arquivo gravado com sucesso";
		echo "<br><br>";
	}else{
		echo "Não foi possível gravar arquivo";
		echo "<br><br>";
	}
	$arrSQLCriaTab=variaveisMetadados($spsFileName,'/VARIABLES=','CACHE.');
	$arrCamposTradutor= variaveisEstruturaBD($spsFileName,'VARIABLE LEVEL V527(SCALE).','RESTORE LOCALE.');
	$queryCriaTabela=geraTabelaQuery($arrSQLCriaTab,$arrCamposTradutor,$nomeTabelaRelatorio);
	echo "<pre>{$queryCriaTabela}</pre>";
	$conn=conecta();
	$conn->exec($queryCriaTabela);

}


#lê arquivo CSV e insere no bd
if (isset($_FILES['arquivoCSV']) and ($_FILES['arquivoCSV']['size']>0 )) {

	$uploadDir = "upload/";
	$csvFileName = $uploadDir . $_FILES['arquivoCSV']['name'];

	if(move_uploaded_file($_FILES['arquivoCSV']['tmp_name'], $csvFileName)) {
		echo "Arquivo gravado com sucesso";
		echo "<br><br>";
	}else{
		echo "Não foi possível gravar arquivo";
		echo "<br><br>";
	}
	$rota = dirname(__FILE__);
$sqlInsert  =" LOAD DATA LOW_PRIORITY LOCAL INFILE '{$rota}\{$csvFileName}' INTO TABLE `{$nomeTabelaRelatorio}`";
	$sqlInsert .=" FIELDS TERMINATED BY ',' ESCAPED BY '\"' ";
	$sqlInsert .=" LINES TERMINATED BY '\r\n'";
    $sqlInsert .=" IGNORE 1 LINES; ";
	echo $sqlInsert;
	$conn=conecta();
	$conn->exec($sqlInsert);

}



function variaveisMetadados($spsFileName,$posInicial,$posFinal){
	$spss_tipos=array(" F"," DATETIME"," A");
	$spss_tipos_new=array(" F|"," DATETIME|"," A|");
	$arrSQLCriaTab = array();
	if (($handle = fopen($spsFileName, "r")) !== false) {
	    $inicia = false;
		while (($data = fgets($handle, 1000)) !== false) {
			#identifica inicio do bloco de variaveis
			if(trim($data)==$posInicial){
				$inicia=true;
			}
			#identifica fim do bloco de variaveis
			if(trim($data)==$posFinal){
				$inicia=false;
				return $arrSQLCriaTab;
				exit();
			}
			#armazenas as informacoes dos campos no array
			if($inicia){
				$data2 = str_replace($spss_tipos,$spss_tipos_new,trim($data));
				$detalhe = explode("|",implode("|",explode(" ",$data2)));
				if(count($detalhe)==3){
					$arrSQLCriaTab[] = array("campo"=>$detalhe[0],"tipo"=>$detalhe[1],"tamanho"=>str_replace(".","",$detalhe[2]));
				}
			}			
		}//fim while				
		fclose($handle);
	}//fim if	
}

function variaveisEstruturaBD($spsFileName,$posInicial,$posFinal){
	$arrCamposTradutor=array();
    $spss_de=array(" ","RENAMEVARIABLE","(",")",".");
	$spss_para=array("","","","","");	
	if (($handle = fopen($spsFileName, "r")) !== false) {
	    $inicia = false;
		while (($data = fgets($handle, 1000)) !== false) {
			#identifica inicio do bloco de variaveis
			if(trim($data)==$posInicial){
				$inicia=true;
			}
			#identifica fim do bloco de variaveis
			if(trim($data)==$posFinal){
				$inicia=false;
				return $arrCamposTradutor;
				exit();
			}
			#armazenas as informacoes dos campos no array
			if($inicia){
				$data2 = str_replace($spss_de,$spss_para,trim($data));
				$campos = explode("=",$data2);
				if(count($campos)==2){
					$arrCamposTradutor[$campos[0]] = $campos[1];
				}
			}			
		}//fim while				
		fclose($handle);
	}//fim if		
}


function geraTabelaQuery($arrTextoSPS,$arrCamposTradutor,$nomeTabelaRelatorio){
$head = "CREATE TABLE IF NOT EXISTS `{$nomeTabelaRelatorio}` ( \n";
	$foot = ") \n COMMENT='Questionário RESBR' \nCOLLATE='latin1_swedish_ci' \nENGINE=MYISAM;";
	$arrCampos = array();
	foreach ($arrTextoSPS as $indice => $estrutura){
		if (substr($estrutura['campo'],0,1)=='V'){
			#echo $estrutura['tipo'];
			$campo = $arrCamposTradutor[$estrutura['campo']];
			switch(trim($estrutura['tipo'])){
				case 'F':
					$arrCampos[]="\n`{$campo}` DOUBLE NULL";
				break;
				case 'A':
					$arrCampos[]="\n`{$campo}` VARCHAR({$estrutura['tamanho']}) NULL";								
				break;
				case 'DATETIME':
					$arrCampos[]="\n `{$campo}` DATETIME NULL";
				break;
				default:
				echo "campo não considerado: {$campo}<br>";
				
			}

		}
	}
	if (!empty($arrCampos)){
		$sintaxeSQL = $head.implode(',',$arrCampos).$foot;
	}else{
		$sintaxeSQL = "error";
	}
	return $sintaxeSQL;		
}

function conecta(){
	$servername = "localhost";
	$username = "root";
	$password = "";
    $dbname="tabquest";
	try {
		$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
		// set the PDO error mode to exception
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		echo "Connected successfully"; 
		}
	catch(PDOException $e)
		{
		echo "Connection failed: " . $e->getMessage();
		}	
	return $conn;	
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Upload de Arquivos</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container-fluid">
<form class="form-horizontal" role="form" method="POST" action="index.php" enctype="multipart/form-data">
  <div class="form-group">
    <label class="control-label col-sm-2" for="email">Arquivo de estrutura (formato syntax file SPSS ".sps"):</label>
    <div class="col-sm-10">
      <input type="file" name="arquivoSPS" class="form-control" id="arquivoSPS" placeholder="ArquivoSPS">
    </div>
  </div>

  <div class="form-group">
    <label class="control-label col-sm-2" for="email">Arquivo de dados (formato CSV):</label>
    <div class="col-sm-10">
      <input type="file" name="arquivoCSV" class="form-control" id="arquivo" placeholder="Arquivo">
    </div>
  </div>
  <div class="form-group"> 
    <div class="col-sm-offset-2 col-sm-10">
      <button type="submit" class="btn btn-default">Enviar</button>
    </div>
  </div>
</form>
</div>

</body>
</html>