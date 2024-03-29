<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>Buscar departamento</title>
        <style>
            .error{
                color: red;
            }
        </style>
    </head>
    <body>
        <?php
        require_once '../core/201020libreriaValidacion.php';
        require_once '../config/confDBPDO.php';

        $errores = array(
            "codigo" => null,
            "descripcion" => null,
            "volumen" => null,
             "conexion" => null
        );

        $formulario = array(
            "codigo" => null,
            "descripcion" => null,
            "volumen" => null
        );

        define("OBLIGATORIO", 1);
        $entradaOK = true;

        if (isset($_REQUEST['enviar'])) {

            $errores["codigo"] = validacionFormularios::comprobarAlfaNumerico($_REQUEST['codigo'], 3, 3, OBLIGATORIO);
            $errores["descripcion"] = validacionFormularios::comprobarAlfaNumerico($_REQUEST['descripcion'], 255, 5, OBLIGATORIO);
            $errores["volumen"] = validacionFormularios::comprobarFloat($_REQUEST['volumen'], PHP_FLOAT_MAX, 0, OBLIGATORIO);

            foreach ($errores as $clave => $error) {
                if ($error != null) {
                    $_REQUEST[$clave] = "";
                    $entradaOK = false;
                }
            }
            if (isset($_REQUEST['codigo'])) {
                try {
                    $conexion = new PDO(DSN, USER, PASSWORD);
                    $prepare = $conexion->prepare("Select CodDepartamento from Departamento where CodDepartamento = :codigo");
                    $prepare->bindParam(":codigo", $_REQUEST['codigo']);
                    $ejecucion = $prepare->execute();
                    if ($ejecucion) {
                        if ($prepare->rowCount() > 0) {
                            $entradaOK = false;
                            $_REQUEST['codigo'] = "";
                            $errores["codigo"] .= " El codigo de departamento ya existe por favor introduce otro";
                        }
                    } else {
                        throw new ErrorException("Error al ejecutar la sentencia");
                    }
                } catch (Exception $e) {
                    $errores['conexion'] = "Error al realizar la conexion ( " . $e->getCode() . " )";
                    $entradaOK = false;
                } finally {
                    unset($conexion);
                    unset($prepare);
                    unset($ejecucion);
                }
            }
        } else {
            $entradaOK = false;
        }

        if ($entradaOK) {
            $formulario['codigo'] = $_REQUEST['codigo'];
            $formulario['descripcion'] = $_REQUEST['descripcion'];
            $formulario['volumen'] = $_REQUEST['volumen'];
            try {
                $conexion = new PDO(DSN, USER, PASSWORD);
                $prepare = $conexion->prepare("Insert into Departamento (CodDepartamento,DescDepartamento,VolumenNegocio) values (:codigo, :descripcion, :volumen)");
                $ejecucion = $prepare->execute(array(":codigo" => $formulario['codigo'], ":descripcion" => $formulario['descripcion'], ":volumen" => $formulario['volumen']));

                if ($ejecucion) {
                    echo "<p>Se ha insertado correctamente el departamento</p>";
                } else {
                    throw new ErrorException("Error al ejecutar la sentencia: ".$prepare ->errorInfo());
                }
            } catch (Exception $e) {
                echo "<p class=\"error\">Se ha producido un error al conectar con la base de datos( " . $e->getMessage() . ", " . $e->getCode() . ")</p>";
            } finally {
                unset($conexion);
            }
        } else {
            ?>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <label for="codigo">Introduce el codigo del departamento: </label>
                <input type="text" id="codigo" name="codigo" value="<?php if (isset($_REQUEST["codigo"])) echo $_REQUEST["codigo"]; ?>"><br>
                <?php
                echo!empty($errores['codigo']) ? "<p class=\"error\">" . $errores['codigo'] . "</p>" : "";
                ?>
                <label for="descripcion">Introduce una descripción del departamento: </label>
                <input type="text" id="descripcion" name="descripcion" value="<?php if (isset($_REQUEST["descripcion"])) echo $_REQUEST["descripcion"]; ?>"><br>
                <?php
                echo!empty($errores['descripcion']) ? "<p class=\"error\">" . $errores['descripcion'] . "</p>" : "";
                ?>
                <label for="volumen">Introduce el volumen de negocio: </label>
                <input type="text" id="volumen" name="volumen" value="<?php if (isset($_REQUEST["volumen"])) echo $_REQUEST["volumen"]; ?>"><br>
                <?php
                echo!empty($errores['volumen']) ? "<p class=\"error\">" . $errores['volumen'] . "</p>" : "";
                echo!empty($errores['conexion']) ? "<p class=\"error\">" .$errores['conexion'] . "</p>" : "";
                ?>
                <input type="submit" value="consulta" name="enviar">
            </form>
            <?php
        }
        ?>
    </body>
</html>
