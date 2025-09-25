<?php
require_once "../connexio.php";

// Detectar columna de FK en respuestas
$fk_col = 'pregunta_id';
$res = $pdo->query("SHOW COLUMNS FROM respostes LIKE 'pregunta_id'");
if(!$res->rowCount()) $fk_col = 'id_pregunta';

// ---------------- CREAR NUEVA PREGUNTA ----------------
if(isset($_POST['crear'])){
    $pregunta = $_POST['pregunta'];
    $respuestas = $_POST['respuestas'];
    $correcta = intval($_POST['correcta']); // índice 1-4

    // Insertar pregunta
    $stmt = $pdo->prepare("INSERT INTO preguntes (pregunta) VALUES (:pregunta)");
    $stmt->execute(['pregunta'=>$pregunta]);
    $id_preg = $pdo->lastInsertId();

    // Insertar respuestas con imagen por defecto
    foreach($respuestas as $i=>$r){
        $c = ($i+1==$correcta)?1:0;
        $stmt2 = $pdo->prepare("INSERT INTO respostes ($fk_col, etiqueta, correcta, imatge) VALUES (:pid, :etiqueta, :correcta, :imatge)");
        $stmt2->execute([
            'pid'=>$id_preg,
            'etiqueta'=>$r,
            'correcta'=>$c,
            'imatge'=>'img/default.png'
        ]);
    }

    header("Location: admin.php"); exit;
}

// ---------------- BORRAR PREGUNTA ----------------
if(isset($_GET['borrar'])){
    $id = intval($_GET['borrar']);
    $stmt = $pdo->prepare("DELETE FROM respostes WHERE $fk_col=:pid");
    $stmt->execute(['pid'=>$id]);

    $stmt2 = $pdo->prepare("DELETE FROM preguntes WHERE id=:id");
    $stmt2->execute(['id'=>$id]);

    header("Location: admin.php"); exit;
}

// ---------------- GUARDAR CAMBIOS ----------------
if(isset($_POST['guardar'])){
    $id = intval($_POST['id']);
    $pregunta = $_POST['pregunta'];
    $respuestas = $_POST['respuestas'];
    $id_resp = $_POST['id_resp'];
    $correcta = intval($_POST['correcta']); // índice 1-4

    // Actualizar pregunta
    $stmt = $pdo->prepare("UPDATE preguntes SET pregunta=:pregunta WHERE id=:id");
    $stmt->execute(['pregunta'=>$pregunta,'id'=>$id]);

    // Actualizar respuestas
    foreach($id_resp as $i=>$idr){
        $r = $respuestas[$i];
        $c = ($i+1==$correcta)?1:0;
        $stmt2 = $pdo->prepare("UPDATE respostes SET etiqueta=:etiqueta, correcta=:correcta WHERE id=:id_resp");
        $stmt2->execute([
            'etiqueta'=>$r,
            'correcta'=>$c,
            'id_resp'=>$idr
        ]);
    }

    header("Location: admin.php"); exit;
}

// ---------------- OBTENER TODAS LAS PREGUNTAS ----------------
$preguntas = $pdo->query("SELECT * FROM preguntes ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Admin Quiz</title>
<link rel="stylesheet" href="../../css/style.css">
</head>
<body>
<h1>Admin Quiz</h1>
<a href="index.html">Volver al inicio</a>
<hr>

<h3>Crear nueva pregunta</h3>
<form method="post">
    <input type="text" name="pregunta" placeholder="Texto de la pregunta" required><br>
    <?php for($i=1;$i<=4;$i++): ?>
        <input type="text" name="respuestas[]" placeholder="Respuesta <?=$i?>" required>
        <input type="radio" name="correcta" value="<?=$i?>" <?=($i==1?'checked':'')?>> Correcta<br>
    <?php endfor; ?>
    <button type="submit" name="crear">Crear</button>
</form>
<hr>

<h3>Preguntas existentes</h3>
<?php foreach($preguntas as $p): ?>
    <?php
        $stmt = $pdo->prepare("SELECT * FROM respostes WHERE $fk_col=:pid ORDER BY id ASC");
        $stmt->execute(['pid'=>$p['id']]);
        $respuestas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <form method="post">
        <input type="hidden" name="id" value="<?=$p['id']?>">
        <strong>Pregunta:</strong><br>
        <input type="text" name="pregunta" value="<?=htmlspecialchars($p['pregunta'])?>"><br>
        <strong>Respuestas:</strong><br>
        <?php $i=0; foreach($respuestas as $r): ?>
            <input type="hidden" name="id_resp[]" value="<?=$r['id']?>">
            <input type="text" name="respuestas[]" value="<?=htmlspecialchars($r['etiqueta'])?>">
            <input type="radio" name="correcta" value="<?=($i+1)?>" <?=($r['correcta']==1?'checked':'')?>> Correcta<br>
        <?php $i++; endforeach; ?>
        <button type="submit" name="guardar">Guardar</button>
        <a href="?borrar=<?=$p['id']?>" onclick="return confirm('Borrar?')">Borrar</a>
    </form>
    <hr>
<?php endforeach; ?>
</body>
</html>
