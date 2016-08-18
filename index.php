<?php
echo "<pre>";
print_r($_POST);
print_r($_FILES);
echo "</pre>";

#cria estrutura do banco de dados através do arquivo de syntax .sps
if (isset($_FILES['arquivoSPS'])) {

	$uploadDir = "upload/";
	$spsFileName = $uploadDir . $_FILES['arquivoSPS']['name'];

	if(move_uploaded_file($_FILES['arquivoSPS']['tmp_name'], $spsFileName)) {
		echo "Arquivo gravado com sucesso";
		echo "<br><br>";
	}else{
		echo "Não foi possível gravar arquivo";
		echo "<br><br>";
	}
	$bloco=array('ini'=>'/VARIABLES=','end'=>'CACHE.');
	$spss_tipos=array(" F"," DATETIME"," A");
	$spss_tipos_new=array(" F|"," DATETIME|"," A|");
	
	$arrSQL = array();
	if (($handle = fopen($spsFileName, "r")) !== false) {
		$i=0;
	    $inicia = false;
		while (($data = fgets($handle, 1000)) !== false) {
			echo $i++;
			#identifica inicio do bloco de variaveis
			#echo "data:{$data}={$bloco['ini']}<br>";
			if(trim($data)==$bloco['ini']){
				$inicia=true;
			}
			#identifica fim do bloco de variaveis
			if(trim($data)==$bloco['end']){
				$inicia=false;
				echo "<pre>";
				print_r($arrSQL);
				echo "</pre>";				
				exit();
			}
			#armazenas as informacoes dos campos no array
			if($inicia){
				$data2 = str_replace($spss_tipos,$spss_tipos_new,trim($data));
				$detalhe = explode("|",implode("|",explode(" ",$data2)));
				$arrSQL[] = array("campo"=>$detalhe[0],"tipo"=>$detalhe[1],"tamanho"=>$detalhe[2]);
			}

			
		}//fim while
		
		fclose($handle);
	}//fim if

}


#lê arquivo CSV e insere no bd
if (isset($_FILES['arquivoCSV'])) {

	$uploadDir = "upload/";
	$csvFileName = $uploadDir . $_FILES['arquivoCSV']['name'];

	if(move_uploaded_file($_FILES['arquivoCSV']['tmp_name'], $csvFileName)) {
		echo "Arquivo gravado com sucesso";
		echo "<br><br>";
	}else{
		echo "Não foi possível gravar arquivo";
		echo "<br><br>";
	}

	if (($handle = fopen($csvFileName, "r")) !== false) {
		$i=0;

		while (($data = fgetcsv($handle, 10000, ",",'"')) !== false) {

			$num = count($data);
				for ($c = 0; $c < $num; $c++) {
					echo $data[$c]." ";
				}
				echo "<br />\n";
				
			}
		
			fclose($handle);
	}

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