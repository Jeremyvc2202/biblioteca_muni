<?php
namespace App\Models;

use App\Core\Database;

class Prestamo extends BaseModel {

  public function all(?string $q = null, ?string $desde = null, ?string $hasta = null, int $limit=20, int $offset=0): array {
    $where = [];
    $params = [];

    if ($q) {
      $where[] = "(p.nombre_libro LIKE :q OR u.nombre LIKE :q OR u.apellido LIKE :q OR u.dni LIKE :q OR u.correo LIKE :q)";
      $params[':q'] = "%$q%";
    }
    if ($desde) { $where[] = "p.fecha_inicio >= :d"; $params[':d'] = $desde; }
    if ($hasta) { $where[] = "p.fecha_fin <= :h"; $params[':h'] = $hasta; }

    $sql = "SELECT p.*, CONCAT(u.nombre,' ',u.apellido) AS usuario
            FROM prestamos p
            JOIN usuarios u ON u.id = p.usuarios_id";
    if ($where) $sql .= " WHERE " . implode(" AND ", $where);
    $sql .= " ORDER BY p.fecha_inicio DESC LIMIT :o, :l";

    $st = $this->db->prepare($sql);
    foreach ($params as $k=>$v) $st->bindValue($k, $v);
    $st->bindValue(':o', $offset, \PDO::PARAM_INT);
    $st->bindValue(':l', $limit, \PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll();
  }

  public function count(?string $q = null, ?string $desde = null, ?string $hasta = null): int {
    $where = [];
    $params = [];

    if ($q) {
      $where[] = "(p.nombre_libro LIKE :q OR u.nombre LIKE :q OR u.apellido LIKE :q OR u.dni LIKE :q OR u.correo LIKE :q)";
      $params[':q'] = "%$q%";
    }
    if ($desde) { $where[] = "p.fecha_inicio >= :d"; $params[':d'] = $desde; }
    if ($hasta) { $where[] = "p.fecha_fin <= :h"; $params[':h'] = $hasta; }

    $sql = "SELECT COUNT(*) c FROM prestamos p
            JOIN usuarios u ON u.id = p.usuarios_id";
    if ($where) $sql .= " WHERE " . implode(" AND ", $where);

    $st = $this->db->prepare($sql);
    $st->execute($params);
    return (int)($st->fetch()['c'] ?? 0);
  }

  public function find(int $id): ?array {
    $st = $this->db->prepare("SELECT * FROM prestamos WHERE id=?");
    $st->execute([$id]);
    return $st->fetch() ?: null;
  }

  public function create(array $data): int {
    $st = $this->db->prepare("INSERT INTO prestamos
      (usuarios_id, fecha_inicio, fecha_fin, nombre_libro, estado_prestamo, imagen_libro)
      VALUES (?,?,?,?,?,?)");
    $st->execute([
      (int)$data['usuarios_id'],
      $data['fecha_inicio'], $data['fecha_fin'],
      trim($data['nombre_libro']),
      $data['estado_prestamo'] ?? 'Prestado',
      $data['imagen_libro'] ?? null
    ]);
    return (int)$this->db->lastInsertId();
  }

  public function update(int $id, array $data): bool {
    $st = $this->db->prepare("UPDATE prestamos SET
      usuarios_id=?, fecha_inicio=?, fecha_fin=?, nombre_libro=?, estado_prestamo=?, imagen_libro=?
      WHERE id=?");
    return $st->execute([
      (int)$data['usuarios_id'],
      $data['fecha_inicio'], $data['fecha_fin'],
      trim($data['nombre_libro']),
      $data['estado_prestamo'] ?? 'Prestado',
      $data['imagen_libro'] ?? null,
      $id
    ]);
  }

  public function delete(int $id): bool {
    $st = $this->db->prepare("DELETE FROM prestamos WHERE id=?");
    return $st->execute([$id]);
  }
}
