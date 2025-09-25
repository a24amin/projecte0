<?php
require_once "../connexio.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['eliminar_id'])) {
        $stmt = $pdo->prepare("DELETE FROM respostes WHERE pregunta_id=:id");
        $stmt->execute(['id'=>$_POST['eliminar_id']]);
        $stmt2 = $pdo->prepare("DELETE FROM preguntes WHERE id=:id");
        $stmt2->execute(['id'=>$_POST['eliminar_id']]);
    } elseif (isset($_POST['editar_id'])) {
        $stmt = $pdo->prepare("UPDATE preguntes SET pregunta=:pregunta WHERE id=:id");
        $stmt->execute(['pregunta'=>$_POST['editar_pregunta'], 'id'=>$_POST['editar_id']]);
        foreach($_POST['id_resp'] as $i=>$idr){
            $texto = $_POST['respuestas'][$i];
            $correcta = ($i==$_POST['correcta']-1)?1:0;
            $stmt2 = $pdo->prepare("UPDATE respostes SET etiqueta=:etiqueta, correcta=:correcta WHERE id=:id");
            $stmt2->execute(['etiqueta'=>$texto,'correcta'=>$correcta,'id'=>$idr]);
        }
    } elseif (isset($_POST['pregunta'])) {
        $stmt = $pdo->prepare("INSERT INTO preguntes (pregunta) VALUES (:pregunta)");
        $stmt->execute(['pregunta'=>$_POST['pregunta']]);
        $idPregunta = $pdo->lastInsertId();
        for ($i=0;$i<4;$i++){
            $texto = $_POST['respuestas'][$i];
            $correcta = ($i==$_POST['correcta']-1)?1:0;
            $stmt2 = $pdo->prepare("INSERT INTO respostes (pregunta_id, etiqueta, correcta) VALUES (:pid,:etiqueta,:correcta)");
            $stmt2->execute(['pid'=>$idPregunta,'etiqueta'=>$texto,'correcta'=>$correcta]);
        }
    }

    header("Location: admin.php");
    exit;
}

$preguntas = $pdo->query("SELECT * FROM preguntes ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ca">
<head>
<meta charset="UTF-8">
<title>Admin Quiz Simple</title>
<style>
body{font-family:sans-serif;padding:20px;}
h1{text-align:center;}
form{margin-bottom:20px;border:1px solid #ccc;padding:10px;border-radius:6px;}
input[type=text]{width:100%;padding:6px;margin-bottom:5px;}
button{padding:6px 12px;margin-top:5px;cursor:pointer;}
.question{border-top:1px solid #ccc;padding:5px 0;}
.actions{margin-top:5px;}
</style>
</head>
<body>

<h1>Gestor de Preguntes</h1>

<h2>Crear pregunta nova</h2>
<form method="POST">
    <input type="text" name="pregunta" placeholder="Pregunta..." required><br>
    <?php for($i=0;$i<4;$i++): ?>
        <input type="text" name="respuestas[]" placeholder="Resposta <?= $i+1 ?>" required>
        <input type="radio" name="correcta" value="<?= $i+1 ?>" <?= $i==0?'checked':'' ?>> Correcta<br>
    <?php endfor; ?>
    <button type="submit">Crear</button>
</form>

<h2>Preguntes existents</h2>
<?php foreach($preguntas as $p): 
    $res = $pdo->prepare("SELECT * FROM respostes WHERE pregunta_id=:pid ORDER BY id ASC");
    $res->execute(['pid'=>$p['id']]);
    $respuestas = $res->fetchAll();
?>
<form method="POST" class="question">
    <input type="hidden" name="editar_id" value="<?= $p['id'] ?>">
    <input type="text" name="editar_pregunta" value="<?= htmlspecialchars($p['pregunta']) ?>"><br>
    <?php foreach($respuestas as $i=>$r): ?>
        <input type="hidden" name="id_resp[]" value="<?= $r['id'] ?>">
        <input type="text" name="respuestas[]" value="<?= htmlspecialchars($r['etiqueta']) ?>">
        <input type="radio" name="correcta" value="<?= $i+1 ?>" <?= $r['correcta']?'checked':'' ?>> Correcta<br>
    <?php endforeach; ?>
    <div class="actions">
        <button type="submit">Desa canvis</button>
        <button type="submit" name="eliminar_id" value="<?= $p['id'] ?>" onclick="return confirm('Segur que vols eliminar?');">Elimina</button>
    </div>
</form>
<?php endforeach; ?>

</body>
</html>
