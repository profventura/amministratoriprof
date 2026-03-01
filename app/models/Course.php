<?php
namespace App\Models;
class Course extends Model {
  public function all() {
    $st = $this->pdo->query('SELECT * FROM courses ORDER BY course_date DESC, id DESC');
    return $st->fetchAll();
  }
  public function find($id) {
    $st = $this->pdo->prepare('SELECT * FROM courses WHERE id=?');
    $st->execute([(int)$id]);
    return $st->fetch();
  }
  public function create($data) {
    $st = $this->pdo->prepare('INSERT INTO courses (title,description,course_date,start_time,end_time,year,created_at) VALUES (?,?,?,?,?,?,NOW())');
    $st->execute([$data['title'],$data['description'] ?? null,$data['course_date'],$data['start_time'] ?? null,$data['end_time'] ?? null,(int)$data['year']]);
    return $this->pdo->lastInsertId();
  }
  public function update($id, $data) {
    $st = $this->pdo->prepare('UPDATE courses SET title=?, description=?, course_date=?, start_time=?, end_time=?, year=? WHERE id=?');
    $st->execute([$data['title'],$data['description'] ?? null,$data['course_date'],$data['start_time'] ?? null,$data['end_time'] ?? null,(int)$data['year'],(int)$id]);
  }
  public function delete($id) {
    $st = $this->pdo->prepare('DELETE FROM courses WHERE id=?');
    $st->execute([(int)$id]);
  }
}
