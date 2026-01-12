<form method="post" action="<?php echo \App\Core\Helpers::url(isset($course)?('/courses/'.$course['id'].'/update'):'/courses'); ?>">
  <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
  <div class="row">
    <div class="col-md-8 mb-3">
      <label class="form-label">Titolo</label>
      <input type="text" name="title" class="form-control" value="<?php echo isset($course)?htmlspecialchars($course['title']):''; ?>" required>
    </div>
    <div class="col-md-4 mb-3">
      <label class="form-label">Anno</label>
      <input type="number" name="year" class="form-control" value="<?php echo isset($course)?(int)$course['year']:(int)date('Y'); ?>" required>
    </div>
    <div class="col-md-12 mb-3">
      <label class="form-label">Descrizione</label>
      <textarea name="description" class="form-control" rows="3"><?php echo isset($course)?htmlspecialchars($course['description']):''; ?></textarea>
    </div>
    <div class="col-md-4 mb-3">
      <label class="form-label">Data</label>
      <input type="date" name="course_date" class="form-control" value="<?php echo isset($course)?htmlspecialchars($course['course_date']):date('Y-m-d'); ?>" required>
    </div>
    <div class="col-md-4 mb-3">
      <label class="form-label">Ora inizio</label>
      <input type="time" name="start_time" class="form-control" value="<?php echo isset($course)?htmlspecialchars($course['start_time']):''; ?>">
    </div>
    <div class="col-md-4 mb-3">
      <label class="form-label">Ora fine</label>
      <input type="time" name="end_time" class="form-control" value="<?php echo isset($course)?htmlspecialchars($course['end_time']):''; ?>">
    </div>
  </div>
  <button class="btn btn-primary"><?php echo isset($course)?'Aggiorna':'Crea'; ?></button>
  <a href="<?php echo \App\Core\Helpers::url('/courses'); ?>" class="btn btn-secondary">Annulla</a>
</form>
