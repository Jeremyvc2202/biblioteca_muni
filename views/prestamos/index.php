<?php
use App\Core\Helpers;
$csrf = Helpers::csrfToken();
ob_start();
?>
<h1>Préstamos</h1>
<form method="get" class="toolbar">
  <input type="hidden" name="c" value="prestamos">
  <input type="hidden" name="a" value="index">
  <input type="search" name="q" placeholder="Buscar por libro o usuario" value="<?=htmlspecialchars($q??'')?>">
  <select name="range">
    <option value="">Estado</option>
    <option value="prestado"     <?=($range??'')==='hoy'?'selected':''?>>Préstado</option>
    <option value="de vuelto"  <?=($range??'')==='semana'?'selected':''?>>De vuelto</option>
    <option value="en biblioteca"     <?=($range??'')==='mes'?'selected':''?>>En Biblioteca</option>
  </select>
  <span>o</span>
  <input type="date" name="desde" value="<?=htmlspecialchars($desde??'')?>"> —
  <input type="date" name="hasta" value="<?=htmlspecialchars($hasta??'')?>">
  <button>Filtrar</button>
  <a class="btn" href="?c=prestamos&a=form">Nuevo</a>
</form>

<table class="table">
  <thead><tr><th>Fecha inicio</th><th>Fecha entrega</th><th>Usuario</th><th>Libro</th><th>Estado</th><th></th></tr></thead>
  <tbody>
  <?php foreach($prestamos as $p): ?>
    <tr>
      <td><?=$p['fecha_inicio']?></td>
      <td><?=$p['fecha_fin']?></td>
      <td><?=htmlspecialchars($p['usuario'])?></td>
      <td><?=htmlspecialchars($p['nombre_libro'])?></td>
      <td><?=htmlspecialchars($p['estado_prestamo'])?></td>
      <td class="actions">
        <a href="?c=prestamos&a=form&id=<?=$p['id']?>">Editar</a>
        <form method="post" action="?c=prestamos&a=delete" style="display:inline" onsubmit="return confirm('¿Eliminar préstamo?');">
          <input type="hidden" name="id" value="<?=$p['id']?>">
          <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($csrf)?>">
          <button class="link danger">Eliminar</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php
$pages = $pagination['pages']; $page = $pagination['page'];
$query = http_build_query(['c'=>'prestamos','a'=>'index','q'=>$q??'','range'=>$range??'','desde'=>$desde??'','hasta'=>$hasta??'']);
if ($pages > 1): ?>
<nav class="pagination">
  <?php for($i=1;$i<=$pages;$i++): ?>
    <a class="<?=$i==$page?'active':''?>" href="?<?=$query?>&page=<?=$i?>"><?=$i?></a>
  <?php endfor; ?>
</nav>
<?php endif; ?>

<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>