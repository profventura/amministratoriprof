<?php
namespace App\Models;
class Membership extends Model {
  public function getOrCreate($memberId, $year) {
    $st = $this->pdo->prepare('SELECT * FROM memberships WHERE member_id=? AND year=?');
    $st->execute([(int)$memberId, (int)$year]);
    $row = $st->fetch();
    if ($row) return $row;
    $st = $this->pdo->prepare('INSERT INTO memberships (member_id, year, status, created_at) VALUES (?,?,\'pending\',NOW())');
    $st->execute([(int)$memberId, (int)$year]);
    $id = $this->pdo->lastInsertId();
    return $this->find($id);
  }
  public function setRegular($id) {
    $st = $this->pdo->prepare('UPDATE memberships SET status=\'regular\' WHERE id=?');
    $st->execute([(int)$id]);
  }
  public function find($id) {
    $st = $this->pdo->prepare('SELECT * FROM memberships WHERE id=?');
    $st->execute([(int)$id]);
    return $st->fetch();
  }
  public function byYear($year) {
    $st = $this->pdo->prepare('SELECT m.id, m.member_id, m.year, m.status, mb.first_name, mb.last_name, mb.email
      FROM memberships m JOIN members mb ON mb.id=m.member_id WHERE m.year=? AND mb.deleted_at IS NULL ORDER BY mb.last_name, mb.first_name');
    $st->execute([(int)$year]);
    return $st->fetchAll();
  }
}
