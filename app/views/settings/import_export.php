<div class="row">
    <!-- EXPORT SECTION -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h4 class="card-title mb-4 text-primary"><iconify-icon icon="solar:export-line-duotone" class="align-text-bottom me-2"></iconify-icon>Esporta Dati</h4>
                <p class="text-muted mb-4">Scarica i dati in formato CSV compatibile con Excel.</p>
                
                <div class="d-grid gap-3">
                    <a href="<?php echo \App\Core\Helpers::url('/settings/export?entity=members'); ?>" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center">
                        <span><i class="ti ti-users me-2"></i> Soci</span>
                        <i class="ti ti-download"></i>
                    </a>
                    
                    <a href="<?php echo \App\Core\Helpers::url('/settings/export?entity=memberships'); ?>" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center">
                        <span><i class="ti ti-certificate me-2"></i> Iscrizioni</span>
                        <i class="ti ti-download"></i>
                    </a>
                    
                    <a href="<?php echo \App\Core\Helpers::url('/settings/export?entity=payments'); ?>" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center">
                        <span><i class="ti ti-credit-card me-2"></i> Pagamenti</span>
                        <i class="ti ti-download"></i>
                    </a>
                    
                    <a href="<?php echo \App\Core\Helpers::url('/settings/export?entity=courses'); ?>" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center">
                        <span><i class="ti ti-calendar me-2"></i> Corsi</span>
                        <i class="ti ti-download"></i>
                    </a>
                    
                    <a href="<?php echo \App\Core\Helpers::url('/settings/export?entity=receipts'); ?>" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center">
                        <span><i class="ti ti-receipt me-2"></i> Ricevute</span>
                        <i class="ti ti-download"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- IMPORT SECTION -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h4 class="card-title mb-4 text-danger"><iconify-icon icon="solar:import-line-duotone" class="align-text-bottom me-2"></iconify-icon>Importa Dati</h4>
                <p class="text-muted mb-4">Carica dati da file CSV. Assicurati che l'intestazione corrisponda ai nomi delle colonne del database.</p>
                
                <div class="accordion" id="importAccordion">
                    
                    <!-- Import Soci -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMembers">
                                Importa Soci
                            </button>
                        </h2>
                        <div id="collapseMembers" class="accordion-collapse collapse" data-bs-parent="#importAccordion">
                            <div class="accordion-body">
                                <form action="<?php echo \App\Core\Helpers::url('/settings/import'); ?>" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
                                    <input type="hidden" name="entity" value="members">
                                    <div class="mb-3">
                                        <label class="form-label">File CSV</label>
                                        <input type="file" name="file" class="form-control" accept=".csv" required>
                                    </div>
                                    <div class="mb-3 text-end">
                                        <a href="<?php echo \App\Core\Helpers::url('/settings/import/sample?entity=members'); ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="ti ti-download"></i> Scarica Esempio CSV
                                        </a>
                                    </div>
                                    <div class="alert alert-info small py-2">
                                        Colonne supportate: <code>Numero Socio, Nome, Cognome, Email, Telefono, Codice Fiscale, CF Fatturazione, P.IVA Fatturazione, Indirizzo, Città, CAP, Provincia, Stato</code>
                                    </div>
                                    <button type="submit" class="btn btn-danger w-100">Carica Soci</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Placeholder per altri import futuri -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOthers">
                                Altre entità (In sviluppo)
                            </button>
                        </h2>
                        <div id="collapseOthers" class="accordion-collapse collapse" data-bs-parent="#importAccordion">
                            <div class="accordion-body text-muted">
                                L'importazione massiva per Iscrizioni, Pagamenti, Corsi e Ricevute sarà disponibile prossimamente.
                                Per ora usa l'inserimento manuale o contatta l'assistenza.
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>