<?php
namespace App\Models;
class Payment extends Model {
  public function create($data) {
    $st = $this->pdo->prepare('INSERT INTO payments (member_id,membership_id,payment_date,amount,method,notes,receipt_number,receipt_year,created_at) VALUES (?,?,?,?,?,?,?,?,NOW())');
    $st->execute([
      (int)$data['member_id'], (int)$data['membership_id'], $data['payment_date'], $data['amount'], $data['method'],
      $data['notes'] ?? null, $data['receipt_number'] ?? null, (int)$data['receipt_year']
    ]);
    return $this->pdo->lastInsertId();
  }
}
