<?php
namespace App\Models;
class CourseParticipant extends Model {
  public function list($courseId) {
    $st = $this->pdo->prepare('SELECT cp.course_id, cp.member_id, cp.certificate_document_id, mb.first_name, mb.last_name, mb.email
      FROM course_participants cp JOIN members mb ON mb.id=cp.member_id WHERE cp.course_id=? ORDER BY mb.last_name, mb.first_name');
    $st->execute([(int)$courseId]);
    return $st->fetchAll();
  }
  public function add($courseId, $memberId) {
    $st = $this->pdo->prepare('INSERT IGNORE INTO course_participants (course_id, member_id) VALUES (?,?)');
    $st->execute([(int)$courseId, (int)$memberId]);
  }
  public function remove($courseId, $memberId) {
    $st = $this->pdo->prepare('DELETE FROM course_participants WHERE course_id=? AND member_id=?');
    $st->execute([(int)$courseId, (int)$memberId]);
  }
  public function getCertificateId($courseId, $memberId) {
    $st = $this->pdo->prepare('SELECT certificate_document_id FROM course_participants WHERE course_id=? AND member_id=?');
    $st->execute([(int)$courseId, (int)$memberId]);
    return $st->fetchColumn();
  }
  public function setCertificate($courseId, $memberId, $documentId) {
    $st = $this->pdo->prepare('UPDATE course_participants SET certificate_document_id=? WHERE course_id=? AND member_id=?');
    $st->execute([(int)$documentId, (int)$courseId, (int)$memberId]);
  }
}
