<?php
require_once "../connexio.php";

$uploadDir = '../img/'; // Carpeta on es guardaran les imatges

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CREAR NOVA PREGUNTA
    if (isset($_POST['nova_pregunta'])) {
        $stmt = $pdo->prepare("INSERT INTO preguntes (pregunta) VALUES (:pregunta)");
        $stmt->execute(['pregunta' => $_POST['nova_pregunta']]);
        $pregunta_id = $pdo->lastInsertId();

        $respostes_text = $_POST['resposta_text'];
        $correcta = intval($_POST['correcta']);

        // Gestionar imatges pujant-les
        for ($i = 0; $i < 4; $i++) {
            $imatgeNom = '';
            if (isset($_FILES['resposta_img']['name'][$i]) && $_FILES['resposta_img']['error'][$i] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['resposta_img']['tmp_name'][$i];
                $imatgeNom = basename($_FILES['resposta_img']['name'][$i]);
                move_uploaded_file($tmpName, $uploadDir . $imatgeNom);
            }

            $stmt = $pdo->prepare("INSERT INTO respostes (pregunta_id, etiqueta, imatge, correcta) VALUES (:pid,:etiqueta,:imatge,:correcta)");
            $stmt->execute([
                'pid' => $pregunta_id,
                'etiqueta' => $respostes_text[$i],
                'imatge' => 'img/' . $imatgeNom,
                'correcta' => ($i + 1 === $correcta ? 1 : 0)
            ]);
        }
    }

    // EDITAR PREGUNTA
    if (isset($_POST['editar_pregunta_id'])) {
        $stmt = $pdo->prepare("UPDATE preguntes SET pregunta=:pregunta WHERE id=:id");
        $stmt->execute([
            'pregunta' => $_POST['editar_pregunta_text'],
            'id' => $_POST['editar_pregunta_id']
        ]);
    }

    // EDITAR RESPOSTES
    if (isset($_POST['editar_resposta_ids'])) {
        $correcta = intval($_POST['editar_correcta']);
        $ids = $_POST['editar_resposta_ids'];
        $texts = $_POST['editar_resposta_text'];

        for ($i = 0; $i < count($ids); $i++) {
            $imatgeNom = '';
            if (isset($_FILES['editar_resposta_img']['name'][$i]) && $_FILES['editar_resposta_img']['error'][$i] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['editar_resposta_img']['tmp_name'][$i];
                $imatgeNom = basename($_FILES['editar_resposta_img']['name'][$i]);
                move_uploaded_file($tmpName, $uploadDir . $imatgeNom);
            }

            $stmt = $pdo->prepare("UPDATE respostes SET etiqueta=:etiqueta, imatge=:imatge, correcta=:correcta WHERE id=:id");
            $stmt->execute([
                'etiqueta' => $texts[$i],
                'imatge' => $imatgeNom ? 'img/' . $imatgeNom : $_POST['editar_resposta_img_old'][$i],
                'correcta' => ($i + 1 == $correcta ? 1 : 0),
                'id' => $ids[$i]
            ]);
        }
    }

    // ELIMINAR PREGUNTA I RESPOSTES
    if (isset($_POST['eliminar_id'])) {
        $stmt = $pdo->prepare("DELETE FROM respostes WHERE pregunta_id=:id");
        $stmt->execute(['id' => $_POST['eliminar_id']]);
        $stmt2 = $pdo->prepare("DELETE FROM preguntes WHERE id=:id");
        $stmt2->execute(['id' => $_POST['eliminar_id']]);
    }

    header("Location: admin.php");
    exit;
}

// Recupera totes les preguntes
$preguntes = $pdo->query("SELECT * FROM preguntes ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ca">
<head>
<meta charset="UTF-8">
<title>Admin Quiz</title>
</head>
<body>
<h1>⚙️ Admin Quiz</h1>

<!-- Crear nova pregunta -->
<div class="card">
<h2>Crear nova pregunta</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="text" name="nova_pregunta" placeholder="Pregunta..." required><br>
    <?php for($i=1;$i<=4;$i++): ?>
        <input type="text" name="resposta_text[]" placeholder="Resposta <?=$i?>" required>
        <input type="file" name="resposta_img[]" accept="image/*" required>
        <input type="radio" name="correcta" value="<?=$i?>" <?=($i==1?'checked':'')?>> Correcta<br>
    <?php endfor; ?>
    <button type="submit" class="btn btn-green">Crear pregunta</button>
</form>
</div>

<!-- Preguntes existents -->
<div class="card">
<h2>Preguntes existents</h2>
<?php foreach($preguntes as $p):
    $respostes = $pdo->prepare("SELECT * FROM respostes WHERE pregunta_id=:pid ORDER BY id ASC");
    $respostes->execute(['pid'=>$p['id']]);
    $respostes = $respostes->fetchAll();
?>
<div class="card">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="editar_pregunta_id" value="<?= $p['id'] ?>">
        <input type="text" name="editar_pregunta_text" value="<?= htmlspecialchars($p['pregunta']) ?>" required>
        <div>
            <?php foreach($respostes as $i => $r): ?>
                <div class="resposta">
                    <input type="hidden" name="editar_resposta_ids[]" value="<?= $r['id'] ?>">
                    <input type="text" name="editar_resposta_text[]" value="<?= htmlspecialchars($r['etiqueta']) ?>" required>
                    <input type="hidden" name="editar_resposta_img_old[]" value="<?= htmlspecialchars($r['imatge']) ?>">
                    <input type="file" name="editar_resposta_img[]" accept="image/*">
                    <input type="radio" name="editar_correcta" value="<?= $i+1 ?>" <?=($r['correcta']==1?'checked':'')?>> Correcta
                </div>
            <?php endforeach; ?>
        </div>
        <button type="submit" class="btn btn-blue">Desa canvis</button>
    </form>
    <form method="POST" style="display:inline;">
        <input type="hidden" name="eliminar_id" value="<?= $p['id'] ?>">
        <button class="btn btn-red" onclick="return confirm('Segur que vols eliminar?');">Eliminar</button>
    </form>
</div>
<?php endforeach; ?>
</div>

</body>
</html>
