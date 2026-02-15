<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <h5 class="mb-0">Pagamenti e Quote</h5>
            </div>
            <div class="card-body p-4">
                
                <!-- Stato Quota Annuale -->
                <div class="mb-5">
                    <h6 class="text-primary mb-3">Quota Associativa <?php echo $year; ?></h6>
                    
                    <?php if ($membership && $membership['status'] === 'regular') { ?>
                        <div class="alert alert-success d-flex align-items-center">
                            <i class="ti ti-circle-check fs-1 me-3"></i>
                            <div>
                                <h5 class="alert-heading mb-1">Quota In Regola</h5>
                                <p class="mb-0">Hai già pagato la quota associativa per l'anno <?php echo $year; ?>.</p>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="alert alert-warning d-flex align-items-center mb-3">
                            <i class="ti ti-alert-circle fs-1 me-3"></i>
                            <div>
                                <h5 class="alert-heading mb-1">Quota Non Pagata</h5>
                                <p class="mb-0">La tua iscrizione per il <?php echo $year; ?> non risulta ancora regolarizzata.</p>
                            </div>
                        </div>
                        
                        <div class="card bg-light border-0 p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span>Importo Quota <?php echo $year; ?></span>
                                <span class="fs-4 fw-bold">€ 50,00</span>
                            </div>
                            <button class="btn btn-primary w-100" disabled>
                                <i class="ti ti-brand-paypal me-2"></i> Paga con PayPal / Carta (Presto Disponibile)
                            </button>
                            <div class="text-center mt-2 small text-muted">
                                Per ora contatta la segreteria per il pagamento tramite bonifico.
                            </div>
                        </div>
                    <?php } ?>
                </div>
                
                <hr>
                
                <!-- Storico Pagamenti (Placeholder) -->
                <h6 class="text-primary mb-3">Storico Pagamenti</h6>
                <p class="text-muted small">Lo storico dettagliato dei pagamenti sarà disponibile a breve.</p>
                
            </div>
        </div>
    </div>
</div>
