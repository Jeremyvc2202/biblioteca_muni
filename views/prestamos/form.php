<?php
use App\Core\Helpers;
$csrf = Helpers::csrfToken();
ob_start();
$isEdit = !empty($prestamo);

// Inicializar estado por defecto
if (!$isEdit) {
    $prestamo['estado_prestamo'] = 'Prestado'; // default si es nuevo
} else {
    $prestamo['estado_prestamo'] = $prestamo['estado_prestamo'] ?? 'Prestado';
}
?>
<h1><?=$isEdit?'Editar':'Nuevo'?> préstamo</h1>
<?php if (!empty($errors['_global'])): ?>
    <div class="flash error"><?=htmlspecialchars($errors['_global'])?></div>
<?php endif; ?>

<form method="post" action="?c=prestamos&a=save" class="form" id="prestamo-form" enctype="multipart/form-data">
  <?php if ($isEdit): ?><input type="hidden" name="id" value="<?=$prestamo['id']?>"><?php endif; ?>
  <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($csrf)?>">

  <!-- Usuario con autocompletado -->
  <label>Usuario
    <input type="hidden" name="usuarios_id" id="usuarios_id" value="<?=htmlspecialchars($prestamo['usuarios_id']??'')?>">
    <input id="usuario_search" name="usuario_search" placeholder="Buscar por DNI, nombre o correo" autocomplete="off"
           value="<?=htmlspecialchars($prestamo['usuario']??'')?>">
    <div id="usuario_results" class="autocomplete"></div>
    <?php if(!empty($errors['usuarios_id'])): ?><small class="error"><?=$errors['usuarios_id']?></small><?php endif; ?>
  </label>

  <div class="row">
    <label>Fecha inicio
      <input type="date" name="fecha_inicio" required value="<?=htmlspecialchars($prestamo['fecha_inicio']??'')?>">
      <?php if(!empty($errors['fecha_inicio'])): ?><small class="error"><?=$errors['fecha_inicio']?></small><?php endif; ?>
    </label>
    <label>Fecha entrega
      <input type="date" name="fecha_fin" required value="<?=htmlspecialchars($prestamo['fecha_fin']??'')?>">
      <?php if(!empty($errors['fecha_fin'])): ?><small class="error"><?=$errors['fecha_fin']?></small><?php endif; ?>
    </label>
  </div>

  <label>Nombre del libro
    <input name="nombre_libro" required value="<?=htmlspecialchars($prestamo['nombre_libro']??'')?>">
    <?php if(!empty($errors['nombre_libro'])): ?><small class="error"><?=$errors['nombre_libro']?></small><?php endif; ?>
  </label>

  <label>Estado
    <select name="estado_prestamo">
      <?php foreach (['Prestado','De vuelto','En Biblioteca'] as $e): ?>
        <option <?=$e==($prestamo['estado_prestamo']??'Prestado')?'selected':''?>><?=$e?></option>
      <?php endforeach; ?>
    </select>
  </label>
  
  <label>Imagen del libro
    <?php if (!empty($prestamo['imagen_libro'])): ?>
      <div>
        <img src="assets/libros/<?=htmlspecialchars($prestamo['imagen_libro'])?>" alt="portada" style="max-width:120px">
      </div>
    <?php endif; ?>
    <input type="file" name="imagen_libro" accept="image/*">
    <?php if(!empty($errors['imagen_libro'])): ?><small class="error"><?=$errors['imagen_libro']?></small><?php endif; ?>
  </label>

  <button type="submit">Guardar</button>
  <a class="btn" href="?c=prestamos&a=index">Volver</a>
</form>

<!-- JS Autocompletado Usuario -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const input = document.getElementById('usuario_search');
  const hiddenId = document.getElementById('usuarios_id');
  const results = document.getElementById('usuario_results');
  let timeout;

  input.addEventListener('input', function() {
    clearTimeout(timeout);
    const q = this.value.trim();
    if (!q) { results.innerHTML = ''; hiddenId.value=''; return; }

    timeout = setTimeout(() => {
      fetch(`?c=usuarios&a=search&q=${encodeURIComponent(q)}`)
        .then(res => res.json())
        .then(data => {
          data.items.sort((a,b) => a.nombre.localeCompare(b.nombre));
          results.innerHTML = data.items.map(u =>
            `<div class="suggest-item" data-id="${u.id}">${u.nombre} ${u.apellido}</div>`
          ).join('');
        });
    }, 300);
  });

  results.addEventListener('click', e => {
    if (e.target.classList.contains('suggest-item')) {
      input.value = e.target.textContent;
      hiddenId.value = e.target.dataset.id;
      results.innerHTML = '';
    }
  });

  document.addEventListener('click', e => {
    if (!results.contains(e.target) && e.target !== input) {
      results.innerHTML = '';
    }
  });
});
</script>

<!-- CSS Autocompletado -->
<style>
.autocomplete {
  border:1px solid #ccc;
  max-height:150px;
  overflow:auto;
  background:#fff;
  z-index:100;
  width:100%;
  position: absolute;
}
.suggest-item {
  padding:5px;
  cursor:pointer;
}
.suggest-item:hover {
  background:#eee;
}
.error {
  color:red;
  font-size:0.9em;
}
</style>

<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
