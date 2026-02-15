<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <h5 class="mb-0">Il Mio Profilo</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?php echo \App\Core\Helpers::url('/portal/profile'); ?>" method="post">
                    <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
                    
                    <!-- Dati Non Modificabili -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Nome</label>
                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($member['first_name']); ?>" disabled readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Cognome</label>
                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($member['last_name']); ?>" disabled readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Codice Fiscale</label>
                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($member['tax_code']); ?>" disabled readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Username</label>
                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($member['username']); ?>" disabled readonly>
                        </div>
                    </div>
                    
                    <hr class="my-4 text-muted">
                    
                    <!-- Dati Modificabili -->
                    <h6 class="mb-3 text-primary">Contatti e Recapiti</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($member['email']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Telefono</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($member['phone']); ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="mobile_phone" class="form-label">Cellulare</label>
                            <input type="text" class="form-control" id="mobile_phone" name="mobile_phone" value="<?php echo htmlspecialchars($member['mobile_phone']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="studio_name" class="form-label">Nome Studio</label>
                            <input type="text" class="form-control" id="studio_name" name="studio_name" value="<?php echo htmlspecialchars($member['studio_name']); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Indirizzo</label>
                        <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($member['address']); ?>">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label for="city" class="form-label">Città</label>
                            <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($member['city']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="zip_code" class="form-label">CAP</label>
                            <input type="text" class="form-control" id="zip_code" name="zip_code" value="<?php echo htmlspecialchars($member['zip_code']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="province" class="form-label">Provincia</label>
                            <input type="text" class="form-control" id="province" name="province" value="<?php echo htmlspecialchars($member['province']); ?>" maxlength="2">
                        </div>
                    </div>
                    
                    <h6 class="mb-3 mt-4 text-primary">Dati Fatturazione (Opzionali)</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="billing_cf" class="form-label">CF Fatturazione</label>
                            <input type="text" class="form-control" id="billing_cf" name="billing_cf" value="<?php echo htmlspecialchars($member['billing_cf']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="billing_piva" class="form-label">P.IVA Fatturazione</label>
                            <input type="text" class="form-control" id="billing_piva" name="billing_piva" value="<?php echo htmlspecialchars($member['billing_piva']); ?>">
                        </div>
                    </div>
                    
                    <hr class="my-4 text-muted">
                    
                    <h6 class="mb-3 text-primary">Modifica Password</h6>
                    <div class="alert alert-light border mb-3 small">
                        Lasciare vuoti i campi sottostanti se non si desidera modificare la password.
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="new_password" class="form-label">Nuova Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" minlength="8">
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Conferma Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8">
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?php echo \App\Core\Helpers::url('/portal/dashboard'); ?>" class="btn btn-outline-secondary">Annulla</a>
                        <button type="submit" class="btn btn-primary px-4">Salva Modifiche</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
