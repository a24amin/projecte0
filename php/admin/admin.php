<?php
// Connexió a la base de dades
require_once "../connexio.php";

// Carpeta on es guardaran les imatges pujades
$uploadDir = '../img/';

// Comprovem si el formulari s'ha enviat
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CREAR NOVA PREGUNTA // 
    if (isset($_POST['nova_pregunta'])) {
        // Inserim la pregunta a la taula preguntes
        $stmt = $pdo->prepare("INSERT INTO preguntes (pregunta) VALUES (:pregunta)");
        $stmt->execute(['pregunta' => $_POST['nova_pregunta']]);
        $pregunta_id = $pdo->lastInsertId(); // ID de la nova pregunta

        $respostes_text = $_POST['resposta_text']; // array amb textos de respostes
        $correcta = intval($_POST['correcta']);    // número de la resposta correcta

        // Recórrer les 4 respostes
        for ($i = 0; $i < 4; $i++) {
            $imatgeNom = '';
            // Si s'ha pujat una imatge
            if (isset($_FILES['resposta_img']['name'][$i]) && $_FILES['resposta_img']['error'][$i] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['resposta_img']['tmp_name'][$i];
                $imatgeNom = basename($_FILES['resposta_img']['name'][$i]);
                move_uploaded_file($tmpName, $uploadDir . $imatgeNom); // guardar imatge
            }

            // Inserim cada resposta a la taula respostes
            $stmt = $pdo->prepare("INSERT INTO respostes (pregunta_id, etiqueta, imatge, correcta) VALUES (:pid,:etiqueta,:imatge,:correcta)");
            $stmt->execute([
                'pid' => $pregunta_id,
                'etiqueta' => $respostes_text[$i],
                'imatge' => 'img/' . $imatgeNom,
                'correcta' => ($i + 1 === $correcta ? 1 : 0) // marca la correcta
            ]);
        }
    }

    // EDITAR PREGUNTA // 
    if (isset($_POST['editar_pregunta_id'])) {
        $stmt = $pdo->prepare("UPDATE preguntes SET pregunta=:pregunta WHERE id=:id");
        $stmt->execute([
            'pregunta' => $_POST['editar_pregunta_text'],
            'id' => $_POST['editar_pregunta_id']
        ]);
    }

    // EDITAR RESPOSTES // 
    if (isset($_POST['editar_resposta_ids'])) {
        $correcta = intval($_POST['editar_correcta']); // quina és correcta
        $ids = $_POST['editar_resposta_ids'];          // IDs respostes
        $texts = $_POST['editar_resposta_text'];       // textos respostes

        for ($i = 0; $i < count($ids); $i++) {
            $imatgeNom = '';
            // Si s'ha pujat nova imatge
            if (isset($_FILES['editar_resposta_img']['name'][$i]) && $_FILES['editar_resposta_img']['error'][$i] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['editar_resposta_img']['tmp_name'][$i];
                $imatgeNom = basename($_FILES['editar_resposta_img']['name'][$i]);
                move_uploaded_file($tmpName, $uploadDir . $imatgeNom);
            }

            // Actualitzem cada resposta
            $stmt = $pdo->prepare("UPDATE respostes SET etiqueta=:etiqueta, imatge=:imatge, correcta=:correcta WHERE id=:id");
            $stmt->execute([
                'etiqueta' => $texts[$i],
                'imatge' => $imatgeNom ? 'img/' . $imatgeNom : $_POST['editar_resposta_img_old'][$i],
                'correcta' => ($i + 1 == $correcta ? 1 : 0),
                'id' => $ids[$i]
            ]);
        }
    }

    // ELIMINAR PREGUNTA I LES SEVES RESPOSTES //
    if (isset($_POST['eliminar_id'])) {
        $stmt = $pdo->prepare("DELETE FROM respostes WHERE pregunta_id=:id");
        $stmt->execute(['id' => $_POST['eliminar_id']]);
        $stmt2 = $pdo->prepare("DELETE FROM preguntes WHERE id=:id");
        $stmt2->execute(['id' => $_POST['eliminar_id']]);
    }

    // Redirigim a la mateixa pàgina després de qualsevol acció
    header("Location: admin.php");
    exit;
}

// RECUPERAR TOTES LES PREGUNTES // 
$preguntes = $pdo->query("SELECT * FROM preguntes ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ca">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Joc de Marques</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- ESTILS PERSONALITZATS -->
<style>
/* Cos de la pàgina */
body { 
    font-family: Georgia, 'Times New Roman', Times,; 
    background-color: #838282ff; 
    padding: 20px; 
}

/* Títol principal */
h1 { 
    text-align: center; 
    margin-bottom: 30px; 
}

/* Targetes (cards) de Bootstrap */
.card { 
    background-color: #fff; 
    border-radius: 10px; 
    padding: 20px; 
    margin-bottom: 25px; 
    box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
}

/* Titular dins de la card */
.card h2 { 
    margin-bottom: 20px; 
}

/* Contenidor d'una resposta */
.resposta { 
    display: flex; 
    flex-wrap: wrap; 
    gap: 10px; 
    margin-bottom: 10px; 
    align-items: center; 
}

/* Inputs de text i arxius dins de la resposta */
.resposta input[type="text"], .resposta input[type="file"] { 
    margin-right: 10px; 
    margin-bottom: 5px; 
}

/* Contenidor de botons */
.botons { 
    display: flex; 
    gap: 10px; 
    margin-top: 10px; 
}

/* Botó blau */
.btn-blue { 
    background-color: #0d6efd; 
    color: #fff; 
    border: none; 
}
.btn-blue:hover { 
    background-color: #0b5ed7; 
}

/* Botó vermell */
.btn-red { 
    background-color: #dc3545; 
    color: #fff; 
    border: none; 
}
.btn-red:hover { 
    background-color: #bb2d3b; 
}

/* Pregunta destacada amb marc */
.pregunta-enunciat { 
    font-weight: bold; 
    padding: 10px; 
    border: 2px solid #222529ff;
    border-radius: 8px;
    background-color: #e9f0ff;
    margin-bottom: 15px;
}
</style>
</head>
<body>

<h1><b>Administració Quiz</b></h1>

<!-- CREAR NOVA PREGUNTA -->
<div class="card">
<h2>Crear nova pregunta</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="text" name="nova_pregunta" class="form-control mb-2" placeholder="Pregunta..." required>
    <?php for($i=1;$i<=4;$i++): ?>
        <div class="resposta">
            <input type="text" name="resposta_text[]" class="form-control" placeholder="Resposta <?=$i?>" required>
            <input type="file" name="resposta_img[]" accept="image/*" required>
            <label class="form-check-label ms-2">
                <input type="radio" name="correcta" value="<?=$i?>" <?=($i==1?'checked':'')?>> Correcta
            </label>
        </div>
    <?php endfor; ?>
    <button type="submit" class="btn btn-blue">Crear pregunta</button>
</form>
</div>

<!-- PREGUNTES EXISTENTS -->
<div class="card">
<h2>Preguntes existents</h2>
<?php foreach($preguntes as $p):
    $respostes = $pdo->prepare("SELECT * FROM respostes WHERE pregunta_id=:pid ORDER BY id ASC");
    $respostes->execute(['pid'=>$p['id']]);
    $respostes = $respostes->fetchAll();
?>
<div class="card mb-3">
    <!-- Enunciat de la pregunta destacat -->
    <div class="pregunta-enunciat"><?= htmlspecialchars($p['pregunta']) ?></div>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="editar_pregunta_id" value="<?= $p['id'] ?>">
        <?php foreach($respostes as $i => $r): ?>
            <div class="resposta">
                <input type="hidden" name="editar_resposta_ids[]" value="<?= $r['id'] ?>">
                <input type="text" name="editar_resposta_text[]" class="form-control" value="<?= htmlspecialchars($r['etiqueta']) ?>" required>
                <input type="hidden" name="editar_resposta_img_old[]" value="<?= htmlspecialchars($r['imatge']) ?>">
                <input type="file" name="editar_resposta_img[]" accept="image/*">
                <label class="form-check-label ms-2">
                    <input type="radio" name="editar_correcta" value="<?= $i+1 ?>" <?=($r['correcta']==1?'checked':'')?>> Correcta
                </label>
            </div>
        <?php endforeach; ?>
        <div class="botons">
            <button type="submit" class="btn btn-blue">Desa canvis</button>
    </form>
    <form method="POST" style="display:inline;">
        <input type="hidden" name="eliminar_id" value="<?= $p['id'] ?>">
        <button class="btn btn-red" onclick="return confirm('Segur que vols eliminar?');">Eliminar</button>
    </form>
        </div>
</div>
<?php endforeach; ?>
</div>
