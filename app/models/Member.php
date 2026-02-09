<?php
namespace App\Models;
class Member extends Model {
  public function all($filters = []) {
    $sql = 'SELECT id, member_number, first_name, last_name, studio_name, email, phone, mobile_phone, city, province, status, registration_date, created_at
            FROM members WHERE deleted_at IS NULL';
    $where = [];
    $params = [];
    if (!empty($filters['status'])) { $where[] = 'status = ?'; $params[] = $filters['status']; }
    if (!empty($filters['q'])) {
      $where[] = '(first_name LIKE ? OR last_name LIKE ? OR studio_name LIKE ? OR email LIKE ? OR phone LIKE ? OR mobile_phone LIKE ? OR city LIKE ? OR member_number LIKE ?)';
      $params = array_merge($params, array_fill(0, 8, '%'.$filters['q'].'%'));
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
    $fields = [
      'member_number', 'first_name', 'last_name', 'studio_name', 'email', 'phone', 'mobile_phone',
      'address', 'city', 'province', 'zip_code', 'birth_date', 'tax_code', 'billing_cf_piva',
      'is_revisor', 'revision_number', 'status', 'registration_date'
    ];
    $cols = implode(',', $fields);
    $placeholders = implode(',', array_fill(0, count($fields), '?'));
    
    $sql = "INSERT INTO members ($cols, created_at) VALUES ($placeholders, NOW())";
    $st = $this->pdo->prepare($sql);
    
    $values = [];
    foreach ($fields as $f) {
      $values[] = $data[$f] ?? null;
    }
    
    $st->execute($values);
    return $this->pdo->lastInsertId();
  }
  public function update($id, $data) {
    $fields = [
      'member_number', 'first_name', 'last_name', 'studio_name', 'email', 'phone', 'mobile_phone',
      'address', 'city', 'province', 'zip_code', 'birth_date', 'tax_code', 'billing_cf_piva',
      'is_revisor', 'revision_number', 'status', 'registration_date'
    ];
    
    $sets = [];
    $values = [];
    foreach ($fields as $f) {
      $sets[] = "$f = ?";
      $values[] = $data[$f] ?? null;
    }
    $values[] = $id; // For WHERE id=?
    
    $sql = "UPDATE members SET " . implode(', ', $sets) . ", updated_at=NOW() WHERE id=? AND deleted_at IS NULL";
    $st = $this->pdo->prepare($sql);
    $st->execute($values);
  }
  public function softDelete($id) {
    $st = $this->pdo->prepare('UPDATE members SET status=\'inactive\', deleted_at=NOW() WHERE id=? AND deleted_at IS NULL');
    $st->execute([$id]);
  }
}
