<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        require_once '../config/confDBPDO.php';
        $entradaOK = true;
        $archivo = null;
        $error = null;
        if (isset($_REQUEST["importar"])) {
            if (!empty($_FILES["archivo"]["name"])) {
                $archivo = $_FILES['archivo']['tmp_name'];
            } else {
                $error = "Introduce un archivo";
                $entradaOK = false;
            }
        } else {
            $entradaOK = false;
        }
        if ($entradaOK) {
            try {
                $dom = new DOMDocument;
                $dom->loadXML($archivo);
                $miDB = new PDO(DSN, USER, PASSWORD);
                $prepare = $miDB->prepare("Insert into Departamento values (:codigo, :descripcion, :fecha, :volumen)");

                $departamento = $dom->getElementsByTagName('Departamento');
                $miDB ->beginTransaction();
                foreach ($departamento as $dep) {
                    $valores = $dep->childNodes;

                    $aValores = array(
                        ":codigo" => $valores->item(1)->nodeValue,
                        ":descripcion" => $valores->item(3)->nodeValue,
                        ":fecha" => empty($valores->item(5)->nodeValue) ? null : $valores->item(5)->nodeValue,
                        ":volumen" => $valores->item(7)->nodeValue
                    );
                    $eje = $prepare->execute($aValores);
                    
                    if(!$eje){
                        throw new Exception("Error al insertar en la base de datos");
                    }
                }
                $miDB ->commit();
                echo "<p> Todos los datos han sido importados</p>";
            } catch (Exception $e) {
                echo "Error " . $e->getCode() . ", " . $e->getMessage() . ".";
                $miDB ->rollBack();
            } finally {
                unset($miDB);
            }
        } else {
            ?>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST"  enctype = "multipart/form-data">
                <input type="file" name="archivo" >
                <?php
                if (is_null($error)) {
                    echo "<span style=\"color:blue;\">$error</span>";
                }
                ?>
                <br>
                <input type="submit" name="importar" value="Importar">
            </form>
        <?php } ?>
    </body>
</html>
