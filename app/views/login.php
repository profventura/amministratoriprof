<form method="post" action="<?php echo \App\Core\Helpers::url('/login'); ?>">
  <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
  <div class="mb-3">
    <label for="username" class="form-label">Nome utente</label>
    <input type="text" class="form-control" id="username" name="username" required autocomplete="username">
  </div>
  <div class="mb-4">
    <label for="password" class="form-label">Password</label>
    <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
  </div>
  <button type="submit" class="btn btn-primary w-100 py-8 mb-4 rounded-2">Entra</button>
</form>

