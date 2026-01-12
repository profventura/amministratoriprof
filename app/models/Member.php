<?php
namespace App\Models;
class Member extends Model {
  public function all($filters = []) {
    $sql = 'SELECT id, first_name, last_name, email, phone, city, status, created_at
            FROM members WHERE deleted_at IS NULL';
    $where = [];
    $params = [];
    if (!empty($filters['status'])) { $where[] = 'status = ?'; $params[] = $filters['status']; }
    if (!empty($filters['q'])) {
      $where[] = '(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ? OR city LIKE ?)';
      $params = array_merge($params, array_fill(0, 5, '%'.$filters['q'].'%'));
    }
    if ($where) { $sql .= ' AND ' . implode(' AND ', $where); }
    $sql .= ' ORDER BY last_name, first_name';
    $st = $this->pdo->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
  }
  public function find($id) {
    $st = $this->pdo->prepare('SELECT * FROM members WHERE id=? AND deleted_at IS NULL');
    $st->execute([$id]);
    return $st->fetch();
  }
  public function create($data) {
    $st = $this->pdo->prepare('INSERT INTO members (first_name,last_name,email,phone,address,city,birth_date,tax_code,status,created_at) VALUES (?,?,?,?,?,?,?,?,?,NOW())');
    $st->execute([
      $data['first_name'],$data['last_name'],$data['email'] ?? null,$data['phone'] ?? null,
      $data['address'] ?? null,$data['city'] ?? null,$data['birth_date'] ?? null,$data['tax_code'] ?? null,
      $data['status'] ?? 'active'
    ]);
    return $this->pdo->lastInsertId();
  }
  public function update($id, $data) {
    $st = $this->pdo->prepare('UPDATE members SET first_name=?, last_name=?, email=?, phone=?, address=?, city=?, birth_date=?, tax_code=?, status=?, updated_at=NOW() WHERE id=? AND deleted_at IS NULL');
    $st->execute([
      $data['first_name'],$data['last_name'],$data['email'] ?? null,$data['phone'] ?? null,
      $data['address'] ?? null,$data['city'] ?? null,$data['birth_date'] ?? null,$data['tax_code'] ?? null,
      $data['status'] ?? 'active',$id
    ]);
  }
  public function softDelete($id) {
    $st = $this->pdo->prepare('UPDATE members SET status=\'inactive\', deleted_at=NOW() WHERE id=? AND deleted_at IS NULL');
    $st->execute([$id]);
  }
}
