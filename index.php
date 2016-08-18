<?php
echo "<pre>";
print_r($_POST);
print_r($_FILES);
echo "</pre>";

if (isset($_FILES['arquivo'])) {

	$uploadDir = "upload/";
	$csvFileName = $uploadDir . $_FILES['arquivo']['name'];

	if(move_uploaded_file($_FILES['arquivo']['tmp_name'], $csvFileName)) {
		echo "Arquivo gravado com sucesso";
		echo "<br><br>";
	}else{
		echo "Não foi possível gravar arquivo";
		echo "<br><br>";
	}

	if (($handle = fopen($csvFileName, "r")) !== false) {
		$i=0;
		while (($data = fgetcsv($handle, 10000, ",","'")) !== false) {
			echo $i++;
			echo "<pre>";
			print_r($data);
			echo "</pre>";

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
    <label class="control-label col-sm-2" for="email">Arquivo:</label>
    <div class="col-sm-10">
      <input type="file" name="arquivo" class="form-control" id="arquivo" placeholder="Arquivo">
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