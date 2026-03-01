<?php
namespace App\Models;
class Document extends Model {
  public function create($memberId, $type, $year, $filePath) {
    $st = $this->pdo->prepare('INSERT INTO documents (member_id,type,year,file_path,created_at) VALUES (?,?,?,?,NOW())');
    $st->execute([(int)$memberId, $type, (int)$year, $filePath]);
    return $this->pdo->lastInsertId();
  }
  public function byMember($memberId) {
    $st = $this->pdo->prepare('SELECT id, type, year, file_path, created_at FROM documents WHERE member_id=? ORDER BY year DESC, id DESC');
    $st->execute([(int)$memberId]);
    return $st->fetchAll();
  }
  public function findByPath($filePath) {
    $st = $this->pdo->prepare('SELECT * FROM documents WHERE file_path=? LIMIT 1');
    $st->execute([$filePath]);
    return $st->fetch();
  }
  public function findReceiptByYearNumber($year, $number) {
    $base = 'storage/documents/receipts/' . (int)$year . '/receipt_' . $number;
    $st = $this->pdo->prepare('SELECT * FROM documents WHERE file_path LIKE ? LIMIT 1');
    $st->execute([$base . '.%']);
    return $st->fetch();
  }
  public function delete($id) {
    $st = $this->pdo->prepare('DELETE FROM documents WHERE id=?');
    return $st->execute([(int)$id]);
  }
  public function find($id) {
    $st = $this->pdo->prepare('SELECT * FROM documents WHERE id=?');
    $st->execute([(int)$id]);
    return $st->fetch();
  }
}
