<div class="row">
    <!-- Benvenuto e Stato -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h3 class="mb-3">Ciao, <?php echo htmlspecialchars($member['first_name']); ?>!</h3>
                <p class="text-muted mb-4">Benvenuto nella tua area riservata. Qui puoi gestire la tua iscrizione, i tuoi corsi e i tuoi dati.</p>
                
                <div class="d-flex flex-wrap gap-3">
                    <div class="bg-light rounded p-3 flex-grow-1">
                        <small class="text-muted d-block mb-1">Stato Iscrizione <?php echo date('Y'); ?></small>
                        <?php 
                        $currentYear = date('Y');
                        $status = 'Non Iscritto';
                        $statusColor = 'secondary';
                        foreach ($memberships as $ms) {
                            if ($ms['year'] == $currentYear) {
                                $status = ($ms['status'] == 'regular') ? 'In Regola' : 'In Attesa';
                                $statusColor = ($ms['status'] == 'regular') ? 'success' : 'warning';
                                break;
                            }
                        }
                        ?>
                        <span class="badge bg-<?php echo $statusColor; ?> fs-3"><?php echo $status; ?></span>
                    </div>
                    <div class="bg-light rounded p-3 flex-grow-1">
                        <small class="text-muted d-block mb-1">Numero Socio</small>
                        <span class="fw-bold fs-4 text-dark"><?php echo htmlspecialchars($member['member_number'] ?? '-'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Azioni Rapide -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h5 class="mb-3">Azioni Rapide</h5>
                <div class="d-grid gap-2">
                    <a href="<?php echo \App\Core\Helpers::url('/portal/profile'); ?>" class="btn btn-outline-primary text-start">
                        <i class="ti ti-user-edit me-2"></i> Modifica Dati
                    </a>
                    <a href="<?php echo \App\Core\Helpers::url('/portal/payments'); ?>" class="btn btn-outline-primary text-start">
                        <i class="ti ti-credit-card me-2"></i> Paga Quota <?php echo date('Y'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- I Miei Corsi -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <h5 class="mb-0">I Miei Corsi</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($my_courses)) { ?>
                    <div class="p-4 text-center text-muted">
                        <i class="ti ti-school fs-1 d-block mb-2"></i>
                        Non sei iscritto a nessun corso.
                    </div>
                <?php } else { ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($my_courses as $c) { ?>
                        <div class="list-group-item p-3 border-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($c['title']); ?></h6>
                                    <small class="text-muted"><i class="ti ti-calendar me-1"></i> <?php echo date('d/m/Y', strtotime($c['course_date'])); ?></small>
                                </div>
                                <?php if ($c['certificate_document_id']) { ?>
                                    <a href="<?php echo \App\Core\Helpers::url('/documents/'.$c['certificate_document_id'].'/download'); ?>" class="btn btn-sm btn-success">
                                        <i class="ti ti-certificate"></i> Attestato
                                    </a>
                                <?php } else { ?>
                                    <span class="badge bg-secondary">Iscritto</span>
                                <?php } ?>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Corsi Disponibili -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <h5 class="mb-0">Prossimi Corsi</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($available_courses)) { ?>
                    <div class="p-4 text-center text-muted">
                        Non ci sono nuovi corsi disponibili al momento.
                    </div>
                <?php } else { ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($available_courses as $c) { ?>
                        <div class="list-group-item p-3 border-light">
                            <h6 class="mb-1"><?php echo htmlspecialchars($c['title']); ?></h6>
                            <p class="small text-muted mb-2"><?php echo htmlspecialchars(substr($c['description'] ?? '', 0, 80)) . '...'; ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-primary fw-bold"><i class="ti ti-calendar me-1"></i> <?php echo date('d/m/Y', strtotime($c['course_date'])); ?></small>
                                <form method="post" action="<?php echo \App\Core\Helpers::url('/portal/courses/'.$c['id'].'/join'); ?>">
                                    <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3">Iscriviti</button>
                                </form>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
