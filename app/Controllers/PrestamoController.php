<?php
namespace App\Controllers;

use App\Models\Prestamo;
use App\Models\Usuario;
use App\Core\Helpers;

class PrestamoController {
  private Prestamo $model;
  private Usuario $usuarios;
  public function __construct() { $this->model = new Prestamo(); $this->usuarios = new Usuario(); }

  public function index() {
    $q = $_GET['q'] ?? null;
    $range = $_GET['range'] ?? null;
    [$desde, $hasta] = Helpers::rangeToDates($range);
    $desde = $_GET['desde'] ?? $desde;
    $hasta = $_GET['hasta'] ?? $hasta;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 10; $offset = ($page-1)*$limit;
    $prestamos = $this->model->all($q, $desde, $hasta, $limit, $offset);
    $total = $this->model->count($q, $desde, $hasta);
    $pagination = Helpers::paginate($total, $page, $limit);
    include __DIR__ . '/../../views/prestamos/index.php';
  }

  public function form() {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $prestamo = $id ? $this->model->find($id) : null;
    $errors = [];
    include __DIR__ . '/../../views/prestamos/form.php';
  }

  public function save() {
    Helpers::verifyCsrfOrFail($_POST['csrf_token'] ?? null);
    $id = $_POST['id'] ?? null;
    $data = Helpers::sanitize($_POST);
    $errors = [];

    if (empty($data['usuarios_id']) || !ctype_digit((string)$data['usuarios_id'])) $errors['usuarios_id'] = 'Seleccione un usuario';
    if (empty($data['fecha_inicio']) || !preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $data['fecha_inicio'])) $errors['fecha_inicio'] = 'Fecha inicio inválida';
    if (empty($data['fecha_fin']) || !preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $data['fecha_fin'])) $errors['fecha_fin'] = 'Fecha fin inválida';
    if (empty($data['nombre_libro'])) $errors['nombre_libro'] = 'Nombre del libro es obligatorio';

    if (!empty($_FILES['imagen_libro']['name'])) {
      $up = $_FILES['imagen_libro'];
      $ext = pathinfo($up['name'], PATHINFO_EXTENSION);
      $safe = bin2hex(random_bytes(8)) . '.' . $ext;
      $destDir = __DIR__ . '/../../public/assets/libros/';
      if (!is_dir($destDir)) mkdir($destDir, 0755, true);
      if (!move_uploaded_file($up['tmp_name'], $destDir . $safe)) {
        $errors['imagen_libro'] = 'Error al subir la imagen';
      } else {
        $data['imagen_libro'] = $safe;
      }
    }

    if ($errors) {
      $prestamo = $id ? array_merge($data, ['id'=>$id]) : $data;
      include __DIR__ . '/../../views/prestamos/form.php';
      return;
    }

    try {
      if ($id) { $this->model->update((int)$id, $data); }
      else { $id = $this->model->create($data); }
      header("Location: ?c=prestamos&a=index&msg=ok");
    } catch (\Throwable $e) {
      $prestamo = $id ? array_merge($data, ['id'=>$id]) : $data;
      $errors = ['_global' => $e->getMessage()];
      include __DIR__ . '/../../views/prestamos/form.php';
    }
  }

  public function delete() {
    Helpers::verifyCsrfOrFail($_POST['csrf_token'] ?? null);
    $id = (int)($_POST['id'] ?? 0);
    if ($id) $this->model->delete($id);
    header("Location: ?c=prestamos&a=index&msg=deleted");
  }
}
